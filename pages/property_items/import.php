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

$activityIdEnum = [
    'GOVNET-0001','GOVNET-0002','FW4A-0001','FW4A-0002','FW4A-0003','FW4A-0004','CSB-0001','ILCDB-0001','PNPKI-0001','FW4A-0005','FW4A-0006','FW4A-0007','PNPKI-0002','FW4A-0008','FW4A-0009','FW4A-0010','FW4A-0011','FW4A-0012','ILCDB-0002','FW4A-0013','FW4A-0014','ILCDB-0003','PNPKI-0003','PNPKI-0004','FW4A-0015','OTHERS-0001','CSB-0002','CSB-0003','OTHERS-0002','GOVNET-0003','FW4A-0016','ILCDB-0005','FW4A-0017','FW4A-0018','ILCDB-0009','ILCDB-0006','ILCDB-0007','FW4A-0019','ILCDB-0011','ILCDB-0010','FW4A-0020','FW4A-0021','FW4A-0022','FW4A-0023','PNPKI-0005','FW4A-0024','FW4A-0025','PNPKI-0006','OTHERS-0003','PNPKI-0007','PNPKI-0008','ILCDB-0013','ILCDB-0014','CSB-0006','CSB-0005','ILCDB-0016','CSB-0007','ILCDB-0017','OTHERS-0004','ILCDB-0035','ILCDB-0036','ILCDB-0018','ILCDB-0019','ILCDB-0015','ILCDB-0021','ILCDB-0020','ILCDB-0029','ILCDB-0030','ILCDB-0022','ILCDB-0034','ILCDB-0031','ILCDB-0028','ILCDB-0027','ILCDB-0032','ILCDB-0033','ILCDB-0023','ILCDB-0024','ILCDB-0004','ILCDB-0008','ILCDB-0012','ILCDB-0025','ILCDB-0026'
];
$typeOfItemsEnum = ['Meals', 'Fuel (Diesel)', 'Office Supplies', 'Plaque', 'Service'];
$paymentStatusEnum = ['Pending', 'Paid', 'Partial', 'Cancelled'];

function saveRow($con, $data) {
    global $activityIdEnum, $typeOfItemsEnum, $paymentStatusEnum;
    // $data indexes:
    // 0=pr_no 1=activity_id 2=activity_name 3=link_to_file
    // 4=project_fund_source 5=type_of_items_procured 6=amount 7=name_of_supplier
    // 8=jo_po 9=link_to_attachments 10=personnel_in_charge 11=date_forwarded_to_ro
    // 12=transmittal_report 13=payment_status 14=remarks
    $pr_no = mysqli_real_escape_string($con, trim($data[0]));
    $activity_name = mysqli_real_escape_string($con, trim($data[2]));
    $link_to_file = mysqli_real_escape_string($con, trim($data[3]));
    $project_fund_source = mysqli_real_escape_string($con, trim($data[4]));
    $name_of_supplier = mysqli_real_escape_string($con, trim($data[7]));
    $jo_po = mysqli_real_escape_string($con, trim($data[8]));
    $link_to_attachments = mysqli_real_escape_string($con, trim($data[9]));
    $personnel_in_charge = mysqli_real_escape_string($con, trim($data[10]));
    $transmittal_report = mysqli_real_escape_string($con, trim($data[12]));
    $remarks = mysqli_real_escape_string($con, trim($data[14]));

    // Activity ID validation (empty allowed)
    $activity_id = trim($data[1]);
    if ($activity_id !== '') {
        $matchedActivity = null;
        foreach ($activityIdEnum as $a) {
            if (strcasecmp($a, $activity_id) === 0) { $matchedActivity = $a; break; }
        }
        if ($matchedActivity === null) {
            return 'invalid_activity_id: ' . $activity_id;
        }
        $activity_id = $matchedActivity;
    }
    $activity_id = mysqli_real_escape_string($con, $activity_id);

    // Type of Items validation (empty allowed)
    $type_of_items_procured = trim($data[5]);
    if ($type_of_items_procured !== '') {
        $matchedType = null;
        foreach ($typeOfItemsEnum as $t) {
            if (strcasecmp($t, $type_of_items_procured) === 0) { $matchedType = $t; break; }
        }
        if ($matchedType === null) {
            return 'invalid_type_of_items: ' . $type_of_items_procured;
        }
        $type_of_items_procured = $matchedType;
    }
    $type_of_items_procured = mysqli_real_escape_string($con, $type_of_items_procured);

    // Amount: strip currency symbols/commas/spaces, keep as number
    $rawAmount = trim($data[6]);
    $amountSql = 'NULL';
    if ($rawAmount !== '') {
        $cleaned = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', $rawAmount));
        if ($cleaned !== '' && is_numeric($cleaned)) {
            $amountSql = (string)(float)$cleaned;
        } else {
            return 'invalid_amount: ' . $rawAmount;
        }
    }

    // Date Forwarded to RO
    $date_forwarded_to_ro = normalizeDate(trim($data[11]));
    $dateSql = $date_forwarded_to_ro !== '' ? "'$date_forwarded_to_ro'" : 'NULL';

    // Payment Status validation (empty allowed -> stored as blank)
    $payment_status = trim($data[13]);
    if ($payment_status !== '') {
        $matchedStatus = null;
        foreach ($paymentStatusEnum as $s) {
            if (strcasecmp($s, $payment_status) === 0) { $matchedStatus = $s; break; }
        }
        if ($matchedStatus === null) {
            return 'invalid_payment_status: ' . $payment_status;
        }
        $payment_status = $matchedStatus;
    }
    $payment_status = mysqli_real_escape_string($con, $payment_status);

    $query = "INSERT INTO procurement_tracking (
        pr_no, activity_id, activity_name, link_to_file, project_fund_source,
        type_of_items_procured, amount, name_of_supplier, jo_po, link_to_attachments,
        personnel_in_charge, date_forwarded_to_ro, transmittal_report, payment_status, remarks
    ) VALUES (
        '$pr_no', '$activity_id', '$activity_name', '$link_to_file', '$project_fund_source',
        '$type_of_items_procured', $amountSql, '$name_of_supplier', '$jo_po', '$link_to_attachments',
        '$personnel_in_charge', $dateSql, '$transmittal_report', '$payment_status', '$remarks'
    )";

    if (@mysqli_query($con, $query)) {
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

        $COL_COUNT = 15;

        if ($extension === 'xlsx') {
            $result = xlsxToArray($fileTmpName);
            if (!$result['success']) {
                echo json_encode($result);
                exit;
            }
            $colOffset = 0;
            $headerRow = $result['rows'][0] ?? array();
            if (strcasecmp(trim((string)($headerRow[0] ?? '')), 'no.') === 0) {
                $colOffset = 1; // sheet has a leading "No." column before PR No.; data starts at column B
            }
            foreach ($result['rows'] as $idx => $cells) {
                if ($idx < 1) { continue; } // skip the single header row
                $hasData = false;
                for ($c = $colOffset; $c < $colOffset + $COL_COUNT; $c++) {
                    if (trim((string)($cells[$c] ?? '')) !== '') { $hasData = true; break; }
                }
                if (!$hasData) { continue; }
                $data = array();
                for ($c = $colOffset; $c < $colOffset + $COL_COUNT; $c++) {
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
                $colOffset = strcasecmp(trim((string)($header[0] ?? '')), 'no.') === 0 ? 1 : 0;
                while (($data = fgetcsv($handle)) !== false) {
                    $rowNum++;
                    if (count($data) >= $colOffset + $COL_COUNT) {
                        $row = array_map(function ($v) { return trim((string)$v); }, array_slice($data, $colOffset, $COL_COUNT));
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
                        $error = 'CSV file does not have the correct number of columns (expecting ' . ($colOffset + $COL_COUNT) . ').';
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
