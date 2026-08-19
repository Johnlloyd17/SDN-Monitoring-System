<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('memory_limit', '256M');
set_time_limit(300);
include "../connection.php";

function isValidFileExtension($filename) {
    $validExtensions = ['csv', 'xls', 'xlsx'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $validExtensions);
}

function excelSerialToDate($serial) {
    if ($serial === '' || $serial === null || !is_numeric($serial)) {
        return null;
    }
    $date = new DateTime('1899-12-30');
    $date->modify('+' . ((int)$serial) . ' days');
    return $date->format('Y-m-d');
}

function xlsxToArray($filePath) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return ['success' => false, 'error' => 'Unable to open the XLSX file.'];
    }

    $shared = array();
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $sx = @simplexml_load_string($sharedXml);
        if ($sx !== false) {
            foreach ($sx->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string)$si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) { $text .= (string)$r->t; }
                    }
                    $shared[] = $text;
                }
            }
        }
    }

    $sheetFiles = array();
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
            $sheetFiles[] = $name;
        }
    }
    sort($sheetFiles);
    if (empty($sheetFiles)) {
        return ['success' => false, 'error' => 'No worksheet found in the XLSX file.'];
    }
    $sheetXml = $zip->getFromName($sheetFiles[0]);
    $zip->close();

    $sx = @simplexml_load_string($sheetXml);
    if ($sx === false) {
        return ['success' => false, 'error' => 'Unable to parse worksheet XML.'];
    }
    $rows = array();
    foreach ($sx->sheetData->row as $row) {
        $cells = array();
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            if (preg_match('/^([A-Z]+)\d+$/', $ref, $m)) {
                $colLetter = $m[1];
                $col = 0;
                for ($j = 0; $j < strlen($colLetter); $j++) {
                    $col = $col * 26 + (ord($colLetter[$j]) - ord('A') + 1);
                }
                $col--;
            } else {
                $col = count($cells);
            }
            $type = (string)$c['t'];
            if ($type === 's') {
                $idx = isset($c->v) ? (int)(string)$c->v : -1;
                $val = ($idx >= 0 && isset($shared[$idx])) ? $shared[$idx] : '';
            } elseif ($type === 'inlineStr') {
                $val = isset($c->is->t) ? (string)$c->is->t : '';
            } else {
                $val = isset($c->v) ? (string)$c->v : '';
            }
            $cells[$col] = $val;
        }
        ksort($cells);
        $rows[] = $cells;
    }
    return ['success' => true, 'rows' => $rows];
}

function saveRow($con, $data) {
    // $data: 0=locality 1=barangay 2=district 3=transport_location 4=transport_type
    //       5=locations 6=code 7=nationwide_id 8=type 9=date_of_activation
    //       10=current_date_of_acceptance 11=latitude 12=longitude 13=strategy 14=status 15=remarks
    $locality = mysqli_real_escape_string($con, $data[0]);
    $barangay = mysqli_real_escape_string($con, $data[1]);
    $district = mysqli_real_escape_string($con, $data[2]);
    $transportLocation = mysqli_real_escape_string($con, $data[3]);
    $transportType = mysqli_real_escape_string($con, $data[4]);
    $locations = mysqli_real_escape_string($con, $data[5]);
    $code = mysqli_real_escape_string($con, $data[6]);
    $nationwideId = mysqli_real_escape_string($con, $data[7]);
    $type = mysqli_real_escape_string($con, $data[8]);
    $dateActivation = excelSerialToDate($data[9]);
    $dateAcceptance = excelSerialToDate($data[10]);
    $latitude = trim($data[11]);
    $longitude = trim($data[12]);
    $strategy = mysqli_real_escape_string($con, $data[13]);
    $status = mysqli_real_escape_string($con, $data[14]);
    $remarks = mysqli_real_escape_string($con, $data[15]);

    $latSql = ($latitude === '') ? 'NULL' : (float)$latitude;
    $lngSql = ($longitude === '') ? 'NULL' : (float)$longitude;
    $actSql = $dateActivation ? "'$dateActivation'" : 'NULL';
    $accSql = $dateAcceptance ? "'$dateAcceptance'" : 'NULL';

    if (@mysqli_query($con, "INSERT INTO tblfwfa (
        locality, barangay, district, transport_location, transport_type,
        locations, type, code, nationwide_id, date_of_activation,
        current_date_of_acceptance, latitude, longitude, strategy, status, remarks
    ) VALUES (
        '$locality', '$barangay', '$district', '$transportLocation', '$transportType',
        '$locations', '$type', '$code', '$nationwideId', $actSql,
        $accSql, $latSql, $lngSql, '$strategy', '$status', '$remarks'
    )")) {
        return 'inserted';
    }
    return false;
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filename = $_FILES['file']['name'];

    if (isValidFileExtension($filename)) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fileTmpName = $_FILES['file']['tmp_name'];
        $success = true;
        $error = '';
        $inserted = 0;
        $skipped = 0;

        if ($extension === 'xlsx') {
            $result = xlsxToArray($fileTmpName);
            if (!$result['success']) {
                echo json_encode($result);
                exit;
            }
            foreach ($result['rows'] as $idx => $cells) {
                if ($idx < 2) { continue; } // skip the two header rows
                $hasData = false;
                for ($c = 1; $c <= 16; $c++) {
                    if (trim((string)($cells[$c] ?? '')) !== '') { $hasData = true; break; }
                }
                if (!$hasData) { continue; }
                $data = array(
                    trim((string)($cells[1] ?? '')),
                    trim((string)($cells[2] ?? '')),
                    trim((string)($cells[3] ?? '')),
                    trim((string)($cells[4] ?? '')),
                    trim((string)($cells[5] ?? '')),
                    trim((string)($cells[6] ?? '')),
                    trim((string)($cells[7] ?? '')),
                    trim((string)($cells[8] ?? '')),
                    trim((string)($cells[9] ?? '')),
                    trim((string)($cells[10] ?? '')),
                    trim((string)($cells[11] ?? '')),
                    trim((string)($cells[12] ?? '')),
                    trim((string)($cells[13] ?? '')),
                    trim((string)($cells[14] ?? '')),
                    trim((string)($cells[15] ?? '')),
                    trim((string)($cells[16] ?? ''))
                );
                $action = saveRow($con, $data);
                if ($action === 'inserted') { $inserted++; }
                else { $skipped++; }
            }
        } elseif ($extension === 'csv') {
            $handle = fopen($fileTmpName, "r");
            if ($handle !== false) {
                $header = fgetcsv($handle);
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 17) {
                        $row = array_map(function ($v) { return trim((string)$v); }, array_slice($data, 1, 16));
                        $hasData = false;
                        foreach ($row as $v) { if ($v !== '') { $hasData = true; break; } }
                        if (!$hasData) { continue; }
                        if (!saveRow($con, $row)) {
                            $success = false;
                            $error = mysqli_error($con);
                            break;
                        }
                        $processed++;
                    } elseif (count($data) >= 10) {
                        // Legacy 10-column format
                        $row = array_fill(0, 16, '');
                        for ($i = 0; $i < 10; $i++) {
                            $row[$i] = trim((string)$data[$i]);
                        }
                        if (!saveRow($con, $row)) {
                            $success = false;
                            $error = mysqli_error($con);
                            break;
                        }
                        $processed++;
                    } else {
                        $success = false;
                        $error = 'CSV file does not have the correct number of columns (expecting at least 10).';
                        break;
                    }
                }
                fclose($handle);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error opening the CSV file.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'XLS file handling is not supported. Please convert to XLSX or CSV.']);
            exit;
        }

        $total = $inserted + $skipped;
        if ($success) {
            ob_clean();
            echo json_encode(['success' => true, 'total' => $total, 'inserted' => $inserted, 'skipped' => $skipped]);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'error' => $error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file format. Only CSV, XLS, and XLSX files are allowed.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
}
?>
