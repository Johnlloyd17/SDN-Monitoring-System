<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php
include "../connection.php";

// Function to check if the file extension is valid
function isValidFileExtension($filename) {
    $validExtensions = ['csv', 'xls', 'xlsx'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $validExtensions);
}

// Check if a file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filename = $_FILES['file']['name'];

    // Validate file extension
    if (isValidFileExtension($filename)) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fileTmpName = $_FILES['file']['tmp_name'];

        if ($extension === 'csv') {
            $handle = fopen($fileTmpName, "r");
            if ($handle !== false) {
                // Skip header row
                $header = fgetcsv($handle);
                $rows = [];
                $rowNumber = 1;

                // Read every row first so the whole file can be validated
                // before a single insert happens (no partial imports).
                while (($data = fgetcsv($handle)) !== false) {
                    $rowNumber++;
                    if (count($data) < 14) {
                        fclose($handle);
                        echo json_encode(['success' => false, 'error' => 'Row ' . $rowNumber . ' of the CSV file does not have the correct number of columns (14 expected). No records were imported.']);
                        exit;
                    }
                    $rows[] = ['line' => $rowNumber, 'data' => $data];
                }
                fclose($handle);

                if (count($rows) === 0) {
                    echo json_encode(['success' => false, 'error' => 'The CSV file has no data rows to import.']);
                    exit;
                }

                // Normalize serials (trimmed, non-empty). MySQL treats the
                // stored serial as case-sensitive-ish via the generated
                // column, so match whitespace-trimmed values here too.
                $normalized = [];
                foreach ($rows as $idx => $rowRec) {
                    $s = trim((string)$rowRec['data'][5]);
                    if ($s === '') continue;
                    $normalized[$idx] = $s;
                }

                // 1) Detect duplicates within the imported file itself
                $seen = [];
                $inFileDups = [];
                foreach ($normalized as $idx => $s) {
                    if (isset($seen[$s])) {
                        $inFileDups[$s] = true;
                    } else {
                        $seen[$s] = $idx;
                    }
                }

                if (!empty($inFileDups)) {
                    echo json_encode(['success' => false, 'error' => 'Duplicate serial numbers found inside the imported file: ' . implode(', ', array_keys($inFileDups)) . '. No records were imported.']);
                    exit;
                }

                // 2) Detect serials that already exist in the inventory table
                $dbDups = [];
                if (!empty($seen)) {
                    $safeSerials = array_map(function ($s) use ($con) {
                        return "'" . mysqli_real_escape_string($con, $s) . "'";
                    }, array_keys($seen));
                    $inList = implode(',', $safeSerials);
                    $checkQ = mysqli_query($con, "SELECT serial_unique FROM inventory WHERE serial_unique IN ($inList)");
                    if ($checkQ) {
                        while ($r = mysqli_fetch_assoc($checkQ)) {
                            $dbDups[] = $r['serial_unique'];
                        }
                    }
                }

                if (!empty($dbDups)) {
                    echo json_encode(['success' => false, 'error' => 'The following serial numbers already exist in the inventory: ' . implode(', ', $dbDups) . '. No records were imported.']);
                    exit;
                }

                // 3) All rows validated - import them all.
                // Blank cells are normalized so they never corrupt the data:
                //  - Project gets a placeholder (every list/stats/export query
                //    filters `project != ''`, so a blank Project would otherwise
                //    make the imported row invisible).
                //  - Description gets a placeholder so downstream features
                //    (pass slip, print sticker, file viewer) have a label.
                //  - Quantity, Life, Cost, Date, etc. are stored as NULL instead
                //    of being coerced to 0 / 0000-00-00 by MySQL.
                $PROJECT_FALLBACK = 'Unspecified';
                $DESCRIPTION_FALLBACK = '(No description)';

                $normText = function ($value) {
                    $value = trim((string)$value);
                    return $value === '' ? null : $value;
                };

                $normQtyOrLife = function ($value) {
                    $value = trim((string)$value);
                    if ($value === '') return null;
                    $intValue = (int)$value;
                    return $value == $intValue ? $intValue : $value;
                };

                $normDate = function ($value) {
                    $value = trim((string)$value);
                    if ($value === '') return null;
                    $dt = date_create($value);
                    return $dt ? $dt->format('Y-m-d') : null;
                };

                $sqlVal = function ($value) use ($con) {
                    if ($value === null) return 'NULL';
                    return "'" . mysqli_real_escape_string($con, $value) . "'";
                };

                foreach ($rows as $rowRec) {
                    $data = $rowRec['data'];
                    $serial = $normText($data[5]);

                    // data[7] = Total Cost - always recomputed by the DB
                    // generated column, so it is ignored.

                    $query = "INSERT INTO inventory (
                        project, item, quantity, unit, description,
                        serial, cost, date, received, inventory_item_no,
                        assigned_to, life, remarks
                    ) VALUES (
                        " . $sqlVal($normText($data[0]) === null ? $PROJECT_FALLBACK : $normText($data[0])) . ",
                        " . $sqlVal($normText($data[1])) . ",
                        " . $sqlVal($normQtyOrLife($data[2])) . ",
                        " . $sqlVal($normText($data[3])) . ",
                        " . $sqlVal($normText($data[4]) === null ? $DESCRIPTION_FALLBACK : $normText($data[4])) . ",
                        " . $sqlVal($serial) . ",
                        " . $sqlVal($normText(str_replace(',', '', $data[6]))) . ",
                        " . $sqlVal($normDate($data[8])) . ",
                        " . $sqlVal($normText($data[9])) . ",
                        " . $sqlVal($normText($data[10])) . ",
                        " . $sqlVal($normText($data[11])) . ",
                        " . $sqlVal($normQtyOrLife($data[12])) . ",
                        " . $sqlVal($normText($data[13])) . "
                    )";

                    if (!mysqli_query($con, $query)) {
                        echo json_encode(['success' => false, 'error' => 'Import stopped partway: ' . mysqli_error($con)]);
                        exit;
                    }
                }

                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error opening the CSV file.']);
            }
        } else {
            // Handling for XLS and XLSX files would require libraries such as PhpSpreadsheet.
            echo json_encode(['success' => false, 'error' => 'XLS and XLSX file handling is not implemented.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file format. Only CSV, XLS, and XLSX files are allowed.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
}
?>