<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('memory_limit', '256M');
set_time_limit(300);
include "../connection.php";

// Valid ENUM values for this table
$VALID_STATUS = ['Active', 'Inactive', 'Ongoing', 'Assist', 'Terminated', 'Deactivated', 'Ongoing Acceptance', 'For Installation', 'For Transfer'];
$VALID_PROCUREMENT_INITIATIVE = ['Centrally Procured', 'Regional Procured'];
$VALID_INSTALLATION_TYPE = ['Region Initiated', 'Manage Service'];

// A..AC template positions (0-based col index => DB field), used by the CSV
// full-29 path and as the canonical insert order.
$FIELD_BY_COL = [
    0  => 'item_no', 1 => 'locality', 2 => 'barangay', 3 => 'district',
    4  => 'transport_location', 5 => 'transport_type', 6 => 'site_locations',
    7  => 'transfer_new_locations', 8 => 'remarks', 9 => 'site_code',
    10 => 'nationwide_id', 11 => 'site_type', 12 => 'date_of_activation',
    13 => 'current_date_of_acceptance', 14 => 'latitude', 15 => 'longitude',
    16 => 'procurement_initiative', 17 => 'installation_type', 18 => 'uat',
    19 => 'conforme', 20 => 'strategy', 21 => 'status', 22 => 'link_type',
    23 => 'replacement_form_file', 24 => 'conforme_file', 25 => 'uat_file',
    26 => 'additional_uat', 27 => 'site_coordinator_name', 28 => 'contact_details',
];

// Header labels (normalized) → DB field
$LABEL_MAP = [
    'item no' => 'item_no', 'itemno' => 'item_no', 'item no.' => 'item_no',
    'locality' => 'locality', 'barangay' => 'barangay', 'district' => 'district',
    'transport location' => 'transport_location', 'transporttype' => 'transport_type', 'transport type' => 'transport_type',
    'site locations' => 'site_locations', 'sitelocations' => 'site_locations',
    'transfer new locations' => 'transfer_new_locations', 'transfer new location' => 'transfer_new_locations',
    'site code' => 'site_code', 'nationwide id' => 'nationwide_id',
    'site type' => 'site_type',
    'date of activation' => 'date_of_activation', 'dateactivated' => 'date_of_activation',
    'current date of acceptance' => 'current_date_of_acceptance',
    'latitude' => 'latitude', 'longitude' => 'longitude', 'longittude' => 'longitude',
    'procurement initiative' => 'procurement_initiative',
    'installation type' => 'installation_type',
    'uat' => 'uat', 'conforme' => 'conforme',
    'strategy' => 'strategy', 'status' => 'status', 'remarks' => 'remarks',
    'link type' => 'link_type',
    'replacement form file' => 'replacement_form_file',
    'conforme file' => 'conforme_file',
    'uat file' => 'uat_file', 'uat f ile' => 'uat_file',
    'additional uat' => 'additional_uat',
    'name' => 'site_coordinator_name', 'site coordinators' => 'site_coordinator_name',
    'contact details' => 'contact_details',
];

function isValidFileExtension($filename) {
    $validExtensions = ['csv', 'xls', 'xlsx'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $validExtensions);
}

function normLabel($s) {
    $s = strtolower(trim((string)$s));
    return trim(preg_replace('/[\s_\-\.\/]+/', ' ', $s));
}

// Excel serial number -> Y-m-d. Fractional serials (date+time) -> date only.
function excelSerialToDate($serial) {
    $d = new DateTime('1899-12-30');
    $d->modify('+' . (int)$serial . ' days');
    return $d->format('Y-m-d');
}

// Robust date parsing: Excel serial OR common text formats. Returns 'Y-m-d' or null.
function parseDate($value) {
    $v = trim((string)$value);
    if ($v === '') return null;
    if (is_numeric($v)) {
        $n = floatval($v);
        if ($n >= 1 && $n <= 80000) return excelSerialToDate($v);
    }
    $formats = ['Y-m-d', 'Y/m/d', 'm/d/Y', 'm-d-Y', 'd/m/Y', 'd-m-Y', 'F j, Y', 'F j Y', 'M j, Y', 'j M Y', 'd M Y', 'M-j-Y', 'd-M-Y', 'n/j/Y', 'j-n-Y', 'Ymd'];
    foreach ($formats as $f) {
        $dt = DateTime::createFromFormat($f, $v);
        if ($dt) {
            $errs = DateTime::getLastErrors();
            if ($errs === false || ($errs['warning_count'] === 0 && $errs['error_count'] === 0)) {
                return $dt->format('Y-m-d');
            }
        }
    }
    $t = strtotime($v);
    if ($t !== false) return date('Y-m-d', $t);
    return null;
}

// Normalize TRUE/FALSE / Yes/No / 1/0 / checkbox values to 1 or 0. Empty -> null.
function normalizeBool($value) {
    $v = strtoupper(trim((string)$value));
    if ($v === '') return null;
    if (in_array($v, ['TRUE', 'YES', 'Y', '1', 'CHECKED', 'X'], true)) return 1;
    if (in_array($v, ['FALSE', 'NO', 'N', '0'], true)) return 0;
    return null; // unknown marker — leave NULL, caller decides
}

/**
 * saveRow: insert one FW4A row from a FIELD-KEYED record.
 *
 * Per-cell validation is LENIENT: an invalid value (bad ENUM, non-numeric
 * coordinate, unparseable date, unknown boolean marker) leaves that one field
 * NULL and logs a warning — the row is still inserted. Only a hard DB error
 * rejects the row (hardError returned).
 *
 * Returns true on success, false on hard DB error (hardError filled).
 */
function saveRow($con, $rec, $validStatus, $validProc, $validInst, &$warnings, &$hardError) {
    $g = function ($f) use ($rec) { return trim((string)($rec[$f] ?? '')); };

    $item_no    = $g('item_no');
    $locality   = $g('locality');
    $barangay   = $g('barangay');
    $district   = $g('district');
    $transportLocation = $g('transport_location');
    $transportType     = $g('transport_type');
    $siteLocations     = $g('site_locations');
    $transferNew       = $g('transfer_new_locations');
    $remarks           = $g('remarks');
    $siteCode     = $g('site_code');
    $nationwideId = $g('nationwide_id');
    $siteType     = $g('site_type');
    $latRaw       = $g('latitude');
    $lngRaw       = $g('longitude');
    $procurement  = $g('procurement_initiative');
    $installation = $g('installation_type');
    $uatRaw       = $g('uat');
    $conformeRaw  = $g('conforme');
    $strategy     = $g('strategy');
    $status       = $g('status');
    $linkType     = $g('link_type');
    $replFile     = $g('replacement_form_file');
    $confFile     = $g('conforme_file');
    $uatFile      = $g('uat_file');
    $addUat       = $g('additional_uat');
    $coordName    = $g('site_coordinator_name');
    $contact      = $g('contact_details');

    $tag = $item_no !== '' ? "item_no=$item_no" : '';

    // Dates
    $actVal = parseDate($g('date_of_activation'));
    if ($g('date_of_activation') !== '' && $actVal === null) {
        $warnings[] = ($tag ? "$tag, " : '') . "date_of_activation [" . $g('date_of_activation') . "] unparseable — set NULL";
    }
    $accVal = parseDate($g('current_date_of_acceptance'));
    if ($g('current_date_of_acceptance') !== '' && $accVal === null) {
        $warnings[] = ($tag ? "$tag, " : '') . "current_date_of_acceptance [" . $g('current_date_of_acceptance') . "] unparseable — set NULL";
    }

    // Coordinates
    $lat = null; $lng = null;
    if ($latRaw !== '') {
        if (!is_numeric($latRaw)) {
            $warnings[] = ($tag ? "$tag, " : '') . "latitude [$latRaw] non-numeric — set NULL";
        } elseif (floatval($latRaw) < -90 || floatval($latRaw) > 90) {
            $warnings[] = ($tag ? "$tag, " : '') . "latitude [$latRaw] out of range — set NULL";
        } else {
            $lat = floatval($latRaw);
        }
    }
    if ($lngRaw !== '') {
        if (!is_numeric($lngRaw)) {
            $warnings[] = ($tag ? "$tag, " : '') . "longitude [$lngRaw] non-numeric — set NULL";
        } elseif (floatval($lngRaw) < -180 || floatval($lngRaw) > 180) {
            $warnings[] = ($tag ? "$tag, " : '') . "longitude [$lngRaw] out of range — set NULL";
        } else {
            $lng = floatval($lngRaw);
        }
    }

    // ENUMs — invalid → NULL + warning
    if ($procurement !== '' && !in_array($procurement, $validProc, true)) {
        $warnings[] = ($tag ? "$tag, " : '') . "procurement_initiative [$procurement] invalid — set NULL";
        $procurement = '';
    }
    if ($installation !== '' && !in_array($installation, $validInst, true)) {
        $warnings[] = ($tag ? "$tag, " : '') . "installation_type [$installation] invalid — set NULL";
        $installation = '';
    }
    if ($status !== '' && !in_array($status, $validStatus, true)) {
        $warnings[] = ($tag ? "$tag, " : '') . "status [$status] invalid — set NULL";
        $status = '';
    }

    // Booleans — unconvertible marker → NULL + warning
    $uat = normalizeBool($uatRaw);
    if ($uatRaw !== '' && $uat === null) {
        $warnings[] = ($tag ? "$tag, " : '') . "uat [$uatRaw] unrecognized — set NULL";
    }
    $conforme = normalizeBool($conformeRaw);
    if ($conformeRaw !== '' && $conforme === null) {
        $warnings[] = ($tag ? "$tag, " : '') . "conforme [$conformeRaw] unrecognized — set NULL";
    }

    $esc = function ($s) use ($con) { return mysqli_real_escape_string($con, $s); };
    $str = function ($s) use ($esc) { return $s !== '' ? "'" . $esc($s) . "'" : 'NULL'; };
    $num = function ($n) { return $n !== null ? (string)$n : 'NULL'; };

    $query = "INSERT INTO tblfwfa (
        item_no, locality, barangay, district, transport_location, transport_type,
        site_locations, transfer_new_locations, remarks, site_code, nationwide_id, site_type,
        date_of_activation, current_date_of_acceptance, latitude, longitude,
        procurement_initiative, installation_type, uat, conforme, strategy, status, link_type,
        replacement_form_file, conforme_file, uat_file, additional_uat,
        site_coordinator_name, contact_details
    ) VALUES (
        " . ($item_no !== '' ? intval($item_no) : 'NULL') . ", " . $str($locality) . ", " . $str($barangay) . ", " . $str($district) . ", "
        . $str($transportLocation) . ", " . $str($transportType) . ", " . $str($siteLocations) . ", " . $str($transferNew) . ", "
        . $str($remarks) . ", " . $str($siteCode) . ", " . $str($nationwideId) . ", " . $str($siteType) . ", "
        . ($actVal !== null ? "'$actVal'" : 'NULL') . ", " . ($accVal !== null ? "'$accVal'" : 'NULL') . ", " . $num($lat) . ", " . $num($lng) . ", "
        . $str($procurement) . ", " . $str($installation) . ", " . ($uat !== null ? (string)$uat : 'NULL') . ", " . ($conforme !== null ? (string)$conforme : 'NULL') . ", "
        . $str($strategy) . ", " . $str($status) . ", " . $str($linkType) . ", "
        . $str($replFile) . ", " . $str($confFile) . ", " . $str($uatFile) . ", " . $str($addUat) . ", "
        . $str($coordName) . ", " . $str($contact)
    . ")";

    if (@mysqli_query($con, $query)) {
        return true;
    }
    $hardError = mysqli_error($con);
    return false;
}

// ---------- XLSX engine ----------

function colIndex($letter) {
    $c = 0;
    for ($j = 0; $j < strlen($letter); $j++) $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1);
    return $c - 1;
}
function colName($i) {
    $s = '';
    while ($i >= 0) { $s = chr(ord('A') + ($i % 26)) . $s; $i = intdiv($i, 26) - 1; }
    return $s;
}

function xlsxOpen($filePath, &$error) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) { $error = 'Unable to open the XLSX file.'; return null; }
    $shared = [];
    $x = $zip->getFromName('xl/sharedStrings.xml');
    if ($x !== false) {
        $sx = @simplexml_load_string($x);
        if ($sx) foreach ($sx->si as $si) {
            if (isset($si->t)) $shared[] = (string)$si->t;
            else { $t = ''; foreach ($si->r as $r) $t .= (string)$r->t; $shared[] = $t; }
        }
    }
    $files = [];
    for ($i = 0; $i < $zip->numFiles; $i++) $files[] = $zip->getNameIndex($i);
    $sheets = [];
    $wb = @simplexml_load_string($zip->getFromName('xl/workbook.xml'));
    if ($wb !== false) {
        $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $rels = @simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $ridMap = [];
        if ($rels !== false) foreach ($rels->Relationship as $r) $ridMap[(string)$r['Id']] = (string)$r['Target'];
        foreach ($wb->sheets->sheet as $s) {
            $rid = (string)$s->attributes($rns)['id'];
            $target = isset($ridMap[$rid]) ? $ridMap[$rid] : '';
            $sp = ($target === '') ? '' : ($target[0] === '/' ? ltrim($target, '/') : 'xl/' . $target);
            $sheets[] = ['name' => (string)$s['name'], 'sp' => $sp];
        }
    }
    return ['zip' => $zip, 'shared' => $shared, 'sheets' => $sheets, 'files' => $files];
}

function xlsxSheetCells($zip, $shared, $sp, &$error) {
    $sx = @simplexml_load_string($zip->getFromName($sp));
    if ($sx === false) { $error = "Unable to parse worksheet $sp."; return null; }
    $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $cells = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r'];
            preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
            if (!$m) continue;
            $col = colIndex($m[1]);
            $type = (string)$c['t'];
            if ($type === 's') { $idx = isset($c->v) ? (int)(string)$c->v : -1; $val = ($idx >= 0 && isset($shared[$idx])) ? $shared[$idx] : ''; }
            elseif ($type === 'inlineStr') { $val = isset($c->is->t) ? (string)$c->is->t : ''; }
            else { $val = isset($c->v) ? (string)$c->v : ''; }
            $cells[$col] = $val;
        }
        $rows[] = $cells;
    }
    $merges = [];
    foreach ($sx->mergeCells->mergeCell as $mc) {
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string)$mc['ref'], $m)) {
            $merges[] = [colIndex($m[1]), (int)$m[2], colIndex($m[3]), (int)$m[4]];
        }
    }
    return ['rows' => $rows, 'merges' => $merges];
}

// Expand merged ranges: propagate the anchor cell's value across the range.
// Header rows (sheet rows 1-2 => array idx 0-1) are left untouched.
function xlsxExpandMerges(&$rows, $merges) {
    foreach ($merges as $mg) {
        list($c1, $r1, $c2, $r2) = $mg;
        $i1 = $r1 - 1; $i2 = $r2 - 1;
        if ($i2 < 2) continue;
        $anchor = isset($rows[$i1][$c1]) ? trim((string)$rows[$i1][$c1]) : '';
        if ($anchor === '') continue;
        for ($rr = max(2, $i1); $rr <= $i2; $rr++) {
            if (!isset($rows[$rr])) $rows[$rr] = [];
            for ($cc = $c1; $cc <= $c2; $cc++) {
                $cur = isset($rows[$rr][$cc]) ? trim((string)$rows[$rr][$cc]) : '';
                if ($cur === '') $rows[$rr][$cc] = $anchor;
            }
        }
    }
}

// Detect the intended sheet by matching its header labels against the A..AC
// template. Returns [sheet, colToField, unmatchedLabels] or null.
function xlsxDetectSheet($z, $shared, $sheets, $files, $labelMap, &$error) {
    $best = null;
    foreach ($sheets as $s) {
        if (!in_array($s['sp'], $files, true)) continue;
        $e = '';
        $d = xlsxSheetCells($z, $shared, $s['sp'], $e);
        if ($d === null) continue;
        $rows = $d['rows'];
        if (count($rows) < 2) continue;
        $colToField = [];
        $unmatched = [];
        for ($c = 0; $c < 40; $c++) {
            $l1 = isset($rows[1][$c]) ? normLabel($rows[1][$c]) : '';
            $l0 = isset($rows[0][$c]) ? normLabel($rows[0][$c]) : '';
            $lb = $l1 !== '' ? $l1 : $l0;
            if ($lb === '') continue;
            if (isset($labelMap[$lb])) {
                $colToField[$c] = $labelMap[$lb];
            } else {
                $unmatched[] = colName($c) . " (" . ($rows[0][$c] !== '' ? $rows[0][$c] : '') . ($rows[1][$c] !== '' ? '/' . $rows[1][$c] : '') . ")";
            }
        }
        $match = count($colToField);
        if ($best === null || $match > $best['match']) {
            $best = ['sheet' => $s, 'rows' => $rows, 'merges' => $d['merges'], 'colToField' => $colToField, 'unmatched' => $unmatched, 'match' => $match];
        }
        if ($match >= 20) break;
    }
    return $best;
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filename = $_FILES['file']['name'];
    if (!isValidFileExtension($filename)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file format. Only CSV, XLS, and XLSX files are allowed.']);
        exit;
    }
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $fileTmpName = $_FILES['file']['tmp_name'];
    $inserted = 0;
    $hardError = null;
    $warnings = [];

    if ($extension === 'xlsx') {
        $z = xlsxOpen($fileTmpName, $hardError);
        if ($z === null) { echo json_encode(['success' => false, 'error' => $hardError]); exit; }

        $chosen = xlsxDetectSheet($z['zip'], $z['shared'], $z['sheets'], $z['files'], $LABEL_MAP, $hardError);
        $z['zip']->close();

        if ($chosen === null || $chosen['match'] < 12) {
            echo json_encode(['success' => false, 'error' => 'Could not find the expected FW4A AP layout. ' . ($chosen ? "Best candidate sheet '{$chosen['sheet']['name']}' matched only {$chosen['match']} of 29 columns." : 'No readable worksheet found.')]);
            exit;
        }
        if (!empty($chosen['unmatched'])) {
            echo json_encode(['success' => false, 'error' => "Header mismatch detected — aborting (no best-effort import). Unrecognized column(s) in sheet '{$chosen['sheet']['name']}': " . implode(', ', $chosen['unmatched'])]);
            exit;
        }

        $rows = $chosen['rows'];
        xlsxExpandMerges($rows, $chosen['merges']);
        $colToField = $chosen['colToField'];

        foreach ($rows as $idx => $cells) {
            if ($idx < 2) continue;
            $rec = [];
            $hasData = false;
            foreach ($colToField as $c => $f) {
                $v = isset($cells[$c]) ? trim((string)$cells[$c]) : '';
                if ($v !== '') { $rec[$f] = $v; $hasData = true; }
            }
            if (!$hasData) continue;
            $err = '';
            if (saveRow($con, $rec, $VALID_STATUS, $VALID_PROCUREMENT_INITIATIVE, $VALID_INSTALLATION_TYPE, $warnings, $err)) {
                $inserted++;
            } else {
                $hardError = $err;
                break;
            }
        }
    } elseif ($extension === 'csv') {
        $handle = fopen($fileTmpName, "r");
        if ($handle === false) { echo json_encode(['success' => false, 'error' => 'Error opening the CSV file.']); exit; }
        $rowNo = 1; // header line consumed below
        while (($data = fgetcsv($handle)) !== false) {
            $rowNo++;
            $rec = [];
            if (count($data) >= 29) {
                for ($i = 0; $i < 29; $i++) $rec[$FIELD_BY_COL[$i]] = trim((string)$data[$i]);
            } elseif (count($data) >= 16) {
                $legacy = array_map('trim', array_slice($data, 0, 16));
                // Old export order: Locality, Barangay, District, Transport Location, Transport Type,
                // Locations, Site Code, Nationwide ID, Site Type, Date of Activation, Date of Acceptance,
                // Latitude, Longitude, Strategy, Status, Remarks
                $rec['locality'] = $legacy[0]; $rec['barangay'] = $legacy[1]; $rec['district'] = $legacy[2];
                $rec['transport_location'] = $legacy[3]; $rec['transport_type'] = $legacy[4];
                $rec['site_locations'] = $legacy[5]; $rec['site_code'] = $legacy[6]; $rec['nationwide_id'] = $legacy[7];
                $rec['site_type'] = $legacy[8]; $rec['date_of_activation'] = $legacy[9]; $rec['current_date_of_acceptance'] = $legacy[10];
                $rec['latitude'] = $legacy[11]; $rec['longitude'] = $legacy[12]; $rec['strategy'] = $legacy[13];
                $rec['status'] = $legacy[14]; $rec['remarks'] = $legacy[15];
            } elseif (count($data) >= 10) {
                $legacy = array_map('trim', array_slice($data, 0, 10));
                $rec['locality'] = $legacy[0]; $rec['barangay'] = $legacy[1]; $rec['district'] = $legacy[2];
                $rec['transport_location'] = $legacy[3]; $rec['transport_type'] = $legacy[4];
                $rec['site_locations'] = $legacy[5]; $rec['site_code'] = $legacy[6]; $rec['nationwide_id'] = $legacy[7];
                $rec['site_type'] = $legacy[8]; $rec['date_of_activation'] = $legacy[9];
            } else {
                fclose($handle);
                echo json_encode(['success' => false, 'error' => 'CSV file does not have the correct number of columns (expecting at least 10).']);
                exit;
            }
            $hasData = false;
            foreach ($rec as $v) if ($v !== '') { $hasData = true; break; }
            if (!$hasData) continue;
            $err = '';
            if (saveRow($con, $rec, $VALID_STATUS, $VALID_PROCUREMENT_INITIATIVE, $VALID_INSTALLATION_TYPE, $warnings, $err)) {
                $inserted++;
            } else {
                $hardError = $err;
                break;
            }
        }
        fclose($handle);
    } else {
        echo json_encode(['success' => false, 'error' => 'XLS file handling is not supported. Please convert to XLSX or CSV.']);
        exit;
    }

    ob_clean();
    $response = ['success' => true, 'inserted' => $inserted, 'sheet' => $extension === 'xlsx' ? ($chosen['sheet']['name'] ?? null) : null];
    if ($hardError !== null) { $response['success'] = false; $response['error'] = $hardError; }
    if (count($warnings) > 0) {
        $response['warn_count'] = count($warnings);
        $response['warnings'] = array_slice($warnings, 0, 50);
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
}
?>