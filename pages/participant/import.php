<?php
require_once __DIR__ . '/../auth_check.php';
require_auth_api();
?>
<?php

header('Content-Type: application/json');

include __DIR__ . '/../connection.php';
require_once __DIR__ . '/participant_rows.php';

function import_fail($message) {
    echo json_encode(array('success' => false, 'error' => $message));
    exit;
}

if (!isset($_FILES['file']) || !isset($_FILES['file']['error']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    import_fail('No file uploaded or upload error.');
}

$filename = $_FILES['file']['name'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($extension, array('csv', 'xls', 'xlsx'), true)) {
    import_fail('Invalid file format. Only CSV, XLS, and XLSX files are allowed.');
}
if ($extension !== 'csv') {
    // Handling for XLS and XLSX would require a library such as PhpSpreadsheet.
    import_fail('XLS and XLSX file handling is not implemented.');
}

// The page is scoped to one project, so imported rows belong to that project
// regardless of what the file's Project column happens to contain. Taking the
// file's value would drop rows off the page they were imported on.
$view = isset($_POST['view']) ? (string) $_POST['view'] : 'cyber';
$cfg  = participant_view_config($view);
$project = $cfg['project'];

$handle = fopen($_FILES['file']['tmp_name'], 'r');
if ($handle === false) {
    import_fail('Error opening the CSV file.');
}

fgetcsv($handle); // skip header row

// start, end, activity, indicator, fullname, sex, contact, email, mode,
// agency, sector, project, person, remarks
$stmt = mysqli_prepare($con, "INSERT INTO tblparticipant
    (`start`, `end`, `activity`, `indicator`, `fullname`, `sex`, `contact`, `email`,
     `mode`, `agency`, `sector`, `project`, `person`, `remarks`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    fclose($handle);
    import_fail('Could not prepare the insert.');
}

$imported = 0;
$skipped  = 0;

while (($data = fgetcsv($handle)) !== false) {
    if (count($data) < 14) {
        // Short rows are a malformed file, not a record to skip silently.
        fclose($handle);
        mysqli_stmt_close($stmt);
        import_fail('CSV file does not have the correct number of columns.');
    }

    $row = array(
        trim($data[0]), trim($data[1]), trim($data[2]), trim($data[3]),
        trim($data[4]), trim($data[5]), trim($data[6]), trim($data[7]),
        trim($data[8]), trim($data[9]), trim($data[10]),
        $project,                      // forced to this page's project
        trim($data[12]), trim($data[13]),
    );

    if ($row[0] === '' || $row[1] === '' || $row[4] === '') {
        $skipped++;
        continue;
    }

    mysqli_stmt_bind_param($stmt, 'ssssssssssssss', ...$row);
    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        fclose($handle);
        mysqli_stmt_close($stmt);
        import_fail($err);
    }
    $imported++;
}

fclose($handle);
mysqli_stmt_close($stmt);

$message = $imported . ' record(s) imported.';
if ($skipped > 0) {
    $message .= ' ' . $skipped . ' row(s) skipped for a missing date or fullname.';
}

echo json_encode(array('success' => true, 'imported' => $imported, 'skipped' => $skipped, 'error' => $message));
