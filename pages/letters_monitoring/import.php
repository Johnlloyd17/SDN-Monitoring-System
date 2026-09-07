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
    $v = trim((string)$value);
    if ($v === '') return '';
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $v)) return $v;
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $v, $m)) {
        if (checkdate((int)$m[1], (int)$m[2], (int)$m[3])) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[1], (int)$m[2]);
        }
    }
    $ts = strtotime($v);
    if ($ts !== false) return date('Y-m-d', $ts);
    return '';
}

function looksLikeDateValue($value) {
    $v = trim((string)$value);
    if ($v === '') return false;
    if (is_numeric($v)) {
        return ((int)$v) > 30000; // Excel date-serial values (1900+) fall well above a row-number counter
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
    // 0=date 1=type 2=subject 3=fw4a 4=link_incoming 5=for_response
    // 6=date_responded 7=link_outgoing 8=responsible_person 9=who_attended
    // 10=remarks 11=post_activity_report
    $date_val = normalizeDate($data[0]);

    $knownTypes = ['Incoming','Outgoing'];
    $typeInput = trim((string)($data[1] ?? ''));
    $type = '';
    foreach ($knownTypes as $t) { if (strcasecmp($t, $typeInput) === 0) { $type = $t; break; } }
    if ($type === '' && in_array($typeInput, $knownTypes, true)) $type = $typeInput;
    if ($type === '') return 'invalid type: "' . $typeInput . '"';

    $subject = mysqli_real_escape_string($con, $data[2]);

    $knownFw4a = ['Request','Provision'];
    $fw4aInput = trim((string)($data[3] ?? ''));
    $fw4a = '';
    foreach ($knownFw4a as $t) { if (strcasecmp($t, $fw4aInput) === 0) { $fw4a = $t; break; } }
    if ($fw4a === '' && in_array($fw4aInput, $knownFw4a, true)) $fw4a = $fw4aInput;
    if ($fw4a === '' && $fw4aInput !== '') return 'invalid fw4a: "' . $fw4aInput . '"';

    $link_incoming = mysqli_real_escape_string($con, $data[4]);

    $respInput = trim((string)($data[5] ?? ''));
    $u = strtoupper($respInput);
    if ($u === 'Y' || $u === 'YES') {
        $for_response = 'Y';
    } elseif ($u === 'N' || $u === 'NO') {
        $for_response = 'N';
    } elseif ($respInput === '') {
        $for_response = ''; // blank -> stored as NULL
    } else {
        return 'invalid for_response: "' . $respInput . '"';
    }

    $date_responded = normalizeDate($data[6]);
    $link_outgoing = mysqli_real_escape_string($con, $data[7]);
    $responsible_person = mysqli_real_escape_string($con, $data[8]);
    $who_attended = mysqli_real_escape_string($con, $data[9]);
    $remarks = mysqli_real_escape_string($con, $data[10]);
    $post_activity_report = mysqli_real_escape_string($con, $data[11]);

    $dateSql = $date_val !== '' ? "'$date_val'" : 'NULL';
    $respSql = $date_responded !== '' ? "'$date_responded'" : 'NULL';
    $fw4aSql = $fw4a !== '' ? "'$fw4a'" : 'NULL';
    $forRespSql = $for_response !== '' ? "'$for_response'" : 'NULL';

    if (@mysqli_query($con, "INSERT INTO letters_monitoring (
        date, type, subject, fw4a, link_incoming, for_response,
        date_responded, link_outgoing, responsible_person, who_attended,
        remarks, post_activity_report
    ) VALUES (
        $dateSql, '$type', '$subject', $fw4aSql, '$link_incoming', $forRespSql,
        $respSql, '$link_outgoing', '$responsible_person', '$who_attended',
        '$remarks', '$post_activity_report'
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
                for ($c = $colOffset; $c < $colOffset + 12; $c++) {
                    if (trim((string)($cells[$c] ?? '')) !== '') { $hasData = true; break; }
                }
                if (!$hasData) { continue; }
                $data = array();
                for ($c = $colOffset; $c < $colOffset + 12; $c++) {
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
                    if (count($data) >= $colOffset + 12) {
                        $row = array_map(function ($v) { return trim((string)$v); }, array_slice($data, $colOffset, 12));
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
                        $error = 'CSV file does not have the correct number of columns (expecting ' . ($colOffset + 12) . ').';
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
