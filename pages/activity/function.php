<?php
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
?>
<?php
if (!isset($con)) include __DIR__ . '/../connection.php';

// Always called as its own request (POST), never included by a page. Guard
// anyway so a stray include can never send a JSON content type for an HTML
// page, and never append JSON to the document.
$isDirectRequest = isset($_SERVER['SCRIPT_FILENAME'])
    && basename($_SERVER['SCRIPT_FILENAME']) === 'function.php';

if ($isDirectRequest) {
    header('Content-Type: application/json');
}

// Inert when included by a page: no headers, no output, no JSON appended
// to the HTML document.
if (!$isDirectRequest) {
    return;
}

/**
 * Column list for tblactivity plus the matching POST field name for each.
 * Shared by the add and edit branches so the two stay in sync.
 */
function activity_field_map($prefix)
{
    return array(
        'start'        => 'txt_' . $prefix . 'start',
        'end'          => 'txt_' . $prefix . 'end',
        'project'      => 'txt_' . $prefix . 'project',
        'subproject'   => 'txt_' . $prefix . 'subproject',
        'indicator'    => 'txt_' . $prefix . 'indicator',
        'activity'     => 'txt_' . $prefix . 'activity',
        'training'     => 'txt_' . $prefix . 'training',
        'municipality' => 'txt_' . $prefix . 'municipality',
        'barangay'     => 'txt_' . $prefix . 'barangay',
        'district'     => 'txt_' . $prefix . 'district',
        'agency'       => 'txt_' . $prefix . 'agency',
        'mode'         => 'txt_' . $prefix . 'mode',
        'sector'       => 'txt_' . $prefix . 'sector',
        'person'       => 'txt_' . $prefix . 'person',
        'resource'     => 'txt_' . $prefix . 'resource',
        'participants' => 'txt_' . $prefix . 'participants',
        'completers'   => 'txt_' . $prefix . 'completers',
        'male'         => 'txt_' . $prefix . 'male',
        'female'       => 'txt_' . $prefix . 'female',
        'approved'     => 'txt_' . $prefix . 'approved',
        'mov'          => 'txt_' . $prefix . 'mov',
        'remarks'      => 'txt_' . $prefix . 'remarks',
    );
}

function activity_fail($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(array('success' => false, 'message' => $message));
    exit;
}

function activity_log($con, $action)
{
    if (isset($_SESSION['role'])) {
        $role = mysqli_real_escape_string($con, $_SESSION['role']);
        $safe = mysqli_real_escape_string($con, $action);
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('$role', NOW(), '$safe')");
    }
}

/**
 * Persist uploaded attachments for a record.
 * Returns array('uploaded' => int, 'errors' => array).
 */
function activity_store_files($con, $id, $field)
{
    $uploaded = 0;
    $errors = array();

    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['tmp_name'])) {
        return array('uploaded' => 0, 'errors' => $errors);
    }

    $target = __DIR__ . '/photo/';
    if (!file_exists($target)) {
        mkdir($target, 0777, true);
    }

    foreach ($_FILES[$field]['tmp_name'] as $key => $tmp_name) {
        if (!is_uploaded_file($tmp_name)) {
            $errors[] = 'Invalid upload.';
            continue;
        }

        $milliseconds = round(microtime(true) * 1000);
        $safeName = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $_FILES[$field]['name'][$key]);
        $name = $milliseconds . $safeName;

        if (move_uploaded_file($tmp_name, $target . $name)) {
            $safeStored = mysqli_real_escape_string($con, $name);
            if (mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES ('" . intval($id) . "', '$safeStored')")) {
                $uploaded++;
            } else {
                $errors[] = 'Could not record file ' . $safeName . '.';
            }
        } else {
            $errors[] = 'Error uploading file: ' . $_FILES[$field]['name'][$key];
        }
    }

    return array('uploaded' => $uploaded, 'errors' => $errors);
}

if (isset($_POST['btn_add'])) {
    $fields = activity_field_map('');
    $columns = array_keys($fields);
    $values = array();
    foreach ($columns as $col) {
        $values[] = isset($_POST[$fields[$col]]) ? $_POST[$fields[$col]] : '';
    }

    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $sql = "INSERT INTO tblactivity (" . implode(', ', $columns) . ") VALUES ($placeholders)";

    $stmt = mysqli_prepare($con, $sql);
    if ($stmt === false) {
        activity_fail('Database error: ' . mysqli_error($con), 500);
    }
    $types = str_repeat('s', count($values));
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        activity_fail('Database error: ' . $err, 500);
    }
    $id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    activity_log($con, 'Added Item:' . (isset($_POST['txt_activity']) ? $_POST['txt_activity'] : ''));

    // Attachments are optional; the Add form currently has no file input.
    $files = activity_store_files($con, $id, 'files');

    echo json_encode(array(
        'success' => true,
        'id' => intval($id),
        'uploaded' => $files['uploaded'],
        'message' => 'Record added successfully.',
    ));
    exit;
}

if (isset($_POST['btn_save'])) {
    $id = isset($_POST['hidden_id']) ? intval($_POST['hidden_id']) : 0;
    if ($id <= 0) {
        activity_fail('Invalid record id.');
    }

    $fields = activity_field_map('edit_');
    $columns = array_keys($fields);
    $values = array();
    foreach ($columns as $col) {
        $values[] = isset($_POST[$fields[$col]]) ? $_POST[$fields[$col]] : '';
    }

    $set = implode(', ', array_map(function ($c) { return "$c = ?"; }, $columns));
    $sql = "UPDATE tblactivity SET $set WHERE id = ?";

    $stmt = mysqli_prepare($con, $sql);
    if ($stmt === false) {
        activity_fail('Database error: ' . mysqli_error($con), 500);
    }
    $types = str_repeat('s', count($values)) . 'i';
    $values[] = $id;
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        activity_fail('Database error: ' . $err, 500);
    }
    mysqli_stmt_close($stmt);

    activity_log($con, 'Updated Item ' . (isset($_POST['txt_edit_activity']) ? $_POST['txt_edit_activity'] : ''));

    echo json_encode(array(
        'success' => true,
        'id' => $id,
        'message' => 'Edit successfully saved.',
    ));
    exit;
}

if (isset($_POST['btn_delete'])) {
    // Capture the activity names first so the log matches the old wording.
    $ids = array();
    if (isset($_POST['chk_delete'])) {
        $ids = is_array($_POST['chk_delete']) ? $_POST['chk_delete'] : array($_POST['chk_delete']);
    }

    $ids = array_values(array_filter(array_map('intval', $ids), function ($v) { return $v > 0; }));

    if (count($ids) === 0) {
        activity_fail('No record was selected to delete.');
    }

    $in = implode(',', $ids);
    $names = array();
    $sel = mysqli_query($con, "SELECT id, activity FROM tblactivity WHERE id IN ($in)");
    if ($sel) {
        while ($row = mysqli_fetch_assoc($sel)) {
            $names[$row['id']] = $row['activity'];
        }
    }

    $ok = mysqli_query($con, "DELETE FROM tblactivity WHERE id IN ($in)");
    if (!$ok) {
        activity_fail('Database error: ' . mysqli_error($con), 500);
    }
    $deleted = mysqli_affected_rows($con);

    // NOTE: attachment rows are deliberately NOT cascaded. tblactivityphoto
    // is shared with the Tech4Ed module and keyed on a bare record id, so
    // "WHERE activityid IN (...)" would also delete the other module's
    // attachments for any colliding id. Matches the pre-existing behaviour.

    if ($deleted > 0) {
        activity_log($con, 'Deleted Item(s) ' . implode(', ', array_keys($names)));
    }

    echo json_encode(array(
        'success' => $deleted > 0,
        'deleted' => $deleted,
        'type' => $deleted > 0 ? 'success' : 'error',
        'message' => $deleted > 0
            ? $deleted . ' record(s) deleted successfully.'
            : 'No matching record was found to delete.',
    ));
    exit;
}

if (isset($_POST['btn_addimage'])) {
    $id = isset($_POST['hidden_id']) ? intval($_POST['hidden_id']) : 0;
    if ($id <= 0) {
        activity_fail('Invalid record id.');
    }

    $files = activity_store_files($con, $id, 'photos');
    if ($files['uploaded'] === 0) {
        activity_fail(count($files['errors']) ? implode(' ', $files['errors']) : 'No files were uploaded.');
    }

    echo json_encode(array(
        'success' => true,
        'uploaded' => $files['uploaded'],
        'errors' => $files['errors'],
        'message' => $files['uploaded'] . ' file(s) uploaded successfully.',
    ));
    exit;
}

if (isset($_POST['btn_remove'])) {
    $ids = array();
    if (isset($_POST['chk_deletephoto'])) {
        $ids = is_array($_POST['chk_deletephoto']) ? $_POST['chk_deletephoto'] : array($_POST['chk_deletephoto']);
    }

    $ids = array_values(array_filter(array_map('intval', $ids), function ($v) { return $v > 0; }));

    if (count($ids) === 0) {
        activity_fail('No file was selected to delete.');
    }

    // Remove the physical files alongside their rows.
    $in = implode(',', $ids);
    $res = mysqli_query($con, "SELECT id, filename FROM tblactivityphoto WHERE id IN ($in)");
    while ($row = mysqli_fetch_assoc($res)) {
        $path = __DIR__ . '/photo/' . basename($row['filename']);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    $ok = mysqli_query($con, "DELETE FROM tblactivityphoto WHERE id IN ($in)");
    if (!$ok) {
        activity_fail('Database error: ' . mysqli_error($con), 500);
    }
    $deleted = mysqli_affected_rows($con);

    echo json_encode(array(
        'success' => $deleted > 0,
        'deleted' => $deleted,
        'type' => $deleted > 0 ? 'success' : 'error',
        'message' => $deleted > 0
            ? $deleted . ' file(s) deleted successfully.'
            : 'No matching file was found to delete.',
    ));
    exit;
}

echo json_encode(array('success' => false, 'message' => 'Unknown action.'));
