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

function normalizeDate($value) {
    if (is_numeric($value)) {
        $d = excelSerialToDate($value);
        if ($d !== null) return $d;
    }
    // Handle mm/dd/yyyy or m/d/yyyy or yyyy-mm-dd
    $v = trim((string)$value);
    if ($v === '') return '';
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $v)) return $v;
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $v, $m)) {
        if (checkdate((int)$m[1], (int)$m[2], (int)$m[3])) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[1], (int)$m[2]);
        }
    }
    // Any other date format, try strtotime
    $ts = strtotime($v);
    if ($ts !== false) return date('Y-m-d', $ts);
    return '';
}

function looksLikeDateValue($value) {
    $v = trim((string)$value);
    if ($v === '') return false;
    if (is_numeric($v)) {
        return ((int)$v) > 30000; // Excel date-serial values fall well above a row-number counter
    }
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $v)) return true;
    if (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $v)) return true;
    return false;
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
            } elseif ($type === 'n') {
                $val = isset($c->v) ? (string)$c->v : '';
            } elseif ($type === 'd') {
                $val = isset($c->v) ? (string)$c->v : '';
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
    // $data indexes:
    // 0=date_received 1=type_of_billing 2=link_to_file 3=amount
    // 4=location_office 5=due_date 6=disconnection_date 7=status
    // 8=date_paid 9=remarks 10=link_to_or
    $date_received = normalizeDate($data[0]);
    $type_of_billing = mysqli_real_escape_string($con, trim($data[1]));
    $link_to_file = mysqli_real_escape_string($con, trim($data[2]));
    $amount = trim($data[3]);
    $rawLocation = trim($data[4]);
    $due_date = normalizeDate($data[5]);
    $disconnection_date = normalizeDate($data[6]);

    $knownTypes = ['Water Bill','Internet Bill','Electricity Bill'];
    $matchedType = null;
    foreach ($knownTypes as $t) {
        if (strcasecmp($t, $type_of_billing) === 0) { $matchedType = $t; break; }
    }
    if ($matchedType === null) {
        return 'invalid_type: ' . $type_of_billing;
    }
    $type_of_billing = $matchedType;

    $knownLocations = ['SDN Provincial Office','SDN Hill Relay Station'];
    $locationSql = 'NULL';
    if ($rawLocation !== '') {
        $matchedLoc = null;
        foreach ($knownLocations as $l) {
            if (strcasecmp($l, $rawLocation) === 0) { $matchedLoc = $l; break; }
        }
        if ($matchedLoc === null) {
            return 'invalid_location: ' . $rawLocation;
        }
        $locationSql = "'" . mysqli_real_escape_string($con, $matchedLoc) . "'";
    }

    $rawStatus = strtolower(trim($data[7]));
    $status = 0;
    if (in_array($rawStatus, ['paid','1','yes','true'])) {
        $status = 1;
    }

    $date_paid = normalizeDate($data[8]);
    $remarks = mysqli_real_escape_string($con, trim($data[9]));
    $link_to_or = mysqli_real_escape_string($con, trim($data[10]));

    $drsSql = $date_received !== '' ? "'$date_received'" : 'NULL';
    $dueSql = $due_date !== '' ? "'$due_date'" : 'NULL';
    $disSql = $disconnection_date !== '' ? "'$disconnection_date'" : 'NULL';
    $paidSql = $date_paid !== '' ? "'$date_paid'" : 'NULL';
    $amountSql = ($amount !== '') ? (float)$amount : 'NULL';

    if (@mysqli_query($con, "INSERT INTO bills_monitoring (
        date_received, type_of_billing, link_to_file, amount, location_office,
        due_date, disconnection_date, status, date_paid, remarks, link_to_or
    ) VALUES (
        $drsSql, '$type_of_billing', '$link_to_file', $amountSql, $locationSql,
        $dueSql, $disSql, $status, $paidSql, '$remarks', '$link_to_or'
    )")) {
        return 'inserted';
    }
    return 'db_error: ' . mysqli_error($con);
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
        $processed = 0;
        $skippedDetails = array();

        if ($extension === 'xlsx') {
            $result = xlsxToArray($fileTmpName);
            if (!$result['success']) {
                echo json_encode($result);
                exit;
            }
            $colOffset = 0;
            $firstDataRow = $result['rows'][1] ?? array();
            if (looksLikeDateValue($firstDataRow[1] ?? '') && !looksLikeDateValue($firstDataRow[0] ?? '')) {
                $colOffset = 1; // sheet has a leading "No." column before Date; data starts at column B
            }
            foreach ($result['rows'] as $idx => $cells) {
                if ($idx < 1) { continue; } // skip the single header row
                $hasData = false;
                for ($c = $colOffset; $c < $colOffset + 11; $c++) {
                    if (trim((string)($cells[$c] ?? '')) !== '') { $hasData = true; break; }
                }
                if (!$hasData) { continue; }
                $data = array();
                for ($c = $colOffset; $c < $colOffset + 11; $c++) {
                    $data[] = trim((string)($cells[$c] ?? ''));
                }
                $action = saveRow($con, $data);
                if ($action === 'inserted') { $inserted++; }
                else {
                    $skipped++;
                    $skippedDetails[] = array('row' => $idx + 1, 'reason' => (string)$action);
                }
            }
        } elseif ($extension === 'csv') {
            $handle = fopen($fileTmpName, "r");
            if ($handle !== false) {
                $header = fgetcsv($handle);
                $rowNum = 1; // header is row 1
                $colOffset = -1;
                while (($data = fgetcsv($handle)) !== false) {
                    $rowNum++;
                    if ($colOffset === -1) {
                        if (looksLikeDateValue($data[1] ?? '') && !looksLikeDateValue($data[0] ?? '')) {
                            $colOffset = 1; // leading "No." column present
                        } else {
                            $colOffset = 0;
                        }
                    }
                    if (count($data) >= $colOffset + 11) {
                        $row = array_map(function ($v) { return trim((string)$v); }, array_slice($data, $colOffset, 11));
                        $hasData = false;
                        foreach ($row as $v) { if ($v !== '') { $hasData = true; break; } }
                        if (!$hasData) { continue; }
                        $action = saveRow($con, $row);
                        if ($action === 'inserted') {
                            $inserted++;
                        } else {
                            $skipped++;
                            $skippedDetails[] = array('row' => $rowNum, 'reason' => (string)$action);
                        }
                    } else {
                        $success = false;
                        $error = 'CSV file does not have the correct number of columns (expecting ' . ($colOffset + 11) . ').';
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

        if ($success) {
            ob_clean();
            echo json_encode([
                'success' => true,
                'total' => $inserted,
                'inserted' => $inserted,
                'skipped' => $skipped,
                'skipped_details' => $skippedDetails
            ]);
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
