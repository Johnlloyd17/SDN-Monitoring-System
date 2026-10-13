<?php
// Standalone import test - no auth, no session
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(300);

// DB connection
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db   = getenv('DB_NAME') ?: 'dict_proj';

$candidatePorts = [getenv('DB_PORT'), 4306, 3306];
$candidatePorts = array_values(array_unique(array_filter($candidatePorts)));
$port = null;
foreach ($candidatePorts as $candidate) {
    $socket = @fsockopen($host, (int)$candidate, $errno, $errstr, 1);
    if ($socket) { fclose($socket); $port = (int)$candidate; break; }
}
if ($port === null) { die('MySQL port not found'); }

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';

echo "=== TESTING IMPORT (standalone) ===\n\n";

$before = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
echo "Rows before import: $before\n\n";

// ===== COPIED FUNCTIONS FROM import.php =====

function colIndex($letter) { $c = 0; for ($j = 0; $j < strlen($letter); $j++) $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1); return $c - 1; }
function colName($i) { $s = ''; while ($i >= 0) { $s = chr(ord('A') + ($i % 26)) . $s; $i = intdiv($i, 26) - 1; } return $s; }
function normLabel($s) { $s = strtolower(trim((string)$s)); return trim(preg_replace('/[\s_\-\.\/]+/', ' ', $s)); }

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
    if (isset($sx->mergeCells)) {
        foreach ($sx->mergeCells->mergeCell as $mc) {
            if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string)$mc['ref'], $m)) {
                $merges[] = [colIndex($m[1]), (int)$m[2], colIndex($m[3]), (int)$m[4]];
            }
        }
    }
    return ['rows' => $rows, 'merges' => $merges];
}

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

// Date parsing
function excelSerialToDate($serial) {
    $d = new DateTime('1899-12-30');
    $d->modify('+' . (int)$serial . ' days');
    return $d->format('Y-m-d');
}
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
function normalizeBool($value) {
    $v = strtoupper(trim((string)$value));
    if ($v === '') return null;
    if (in_array($v, ['TRUE', 'YES', 'Y', '1', 'CHECKED', 'X'], true)) return 1;
    if (in_array($v, ['FALSE', 'NO', 'N', '0'], true)) return 0;
    return null;
}

// Save row function (copied from import.php with source_sheet)
$VALID_STATUS = ['Active', 'Inactive', 'Ongoing', 'Assist', 'Terminated', 'Deactivated', 'Ongoing Acceptance', 'For Installation', 'For Transfer'];
$VALID_PROCUREMENT_INITIATIVE = ['Centrally Procured', 'Regional Procured'];
$VALID_INSTALLATION_TYPE = ['Region Initiated', 'Manage Service'];

function saveRow($con, $rec, $validStatus, $validProc, $validInst, &$warnings, &$hardError, $sourceSheet = 'Unknown') {
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

    // ENUMs
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

    // Booleans
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
        site_coordinator_name, contact_details, source_sheet
    ) VALUES (
        " . ($item_no !== '' ? intval($item_no) : 'NULL') . ", " . $str($locality) . ", " . $str($barangay) . ", " . $str($district) . ", "
        . $str($transportLocation) . ", " . $str($transportType) . ", " . $str($siteLocations) . ", " . $str($transferNew) . ", "
        . $str($remarks) . ", " . $str($siteCode) . ", " . $str($nationwideId) . ", " . $str($siteType) . ", "
        . ($actVal !== null ? "'$actVal'" : 'NULL') . ", " . ($accVal !== null ? "'$accVal'" : 'NULL') . ", " . $num($lat) . ", " . $num($lng) . ", "
        . $str($procurement) . ", " . $str($installation) . ", " . ($uat !== null ? (string)$uat : 'NULL') . ", " . ($conforme !== null ? (string)$conforme : 'NULL') . ", "
        . $str($strategy) . ", " . $str($status) . ", " . $str($linkType) . ", "
        . $str($replFile) . ", " . $str($confFile) . ", " . $str($uatFile) . ", " . $str($addUat) . ", "
        . $str($coordName) . ", " . $str($contact) . ", " . $str($sourceSheet)
    . ")";

    if (@mysqli_query($con, $query)) {
        return true;
    }
    $hardError = mysqli_error($con);
    return false;
}

// ===== RUN IMPORT =====

$z = xlsxOpen($filePath, $error);
if ($z === null) { echo "ERROR: $error\n"; exit; }

$chosen = xlsxDetectSheet($z['zip'], $z['shared'], $z['sheets'], $z['files'], $LABEL_MAP, $error);
$z['zip']->close();

if ($chosen === null) { echo "ERROR: No sheet detected: $error\n"; exit; }

echo "Chosen: {$chosen['sheet']['name']} (matched {$chosen['match']})\n";

$rows = $chosen['rows'];
xlsxExpandMerges($rows, $chosen['merges']);
$colToField = $chosen['colToField'];
$sourceSheet = $chosen['sheet']['name'];

$inserted = 0;
$warnings = [];
$hardError = null;

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
    if (saveRow($con, $rec, $VALID_STATUS, $VALID_PROCUREMENT_INITIATIVE, $VALID_INSTALLATION_TYPE, $warnings, $err, $sourceSheet)) {
        $inserted++;
    } else {
        $hardError = $err;
        echo "Hard error at row $idx: $err\n";
        break;
    }
}

echo "Inserted: $inserted\n";
if ($hardError) echo "Hard error: $hardError\n";
if (!empty($warnings)) {
    echo "Warnings (" . count($warnings) . "):\n";
    foreach (array_slice($warnings, 0, 20) as $w) echo "  - $w\n";
}

$after = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
echo "\nRows after import: $after\n";
echo "Net change: " . ($after - $before) . "\n";

// Verify source_sheet values
$sheets = mysqli_query($con, "SELECT source_sheet, COUNT(*) as c FROM tblfwfa GROUP BY source_sheet");
echo "\nSource sheet distribution:\n";
while ($r = mysqli_fetch_assoc($sheets)) {
    echo "  {$r['source_sheet']}: {$r['c']}\n";
}

// Check a few new rows
$newRows = mysqli_query($con, "SELECT id, item_no, locality, site_code, source_sheet FROM tblfwfa WHERE source_sheet = '$sourceSheet' LIMIT 5");
echo "\nSample new rows:\n";
while ($r = mysqli_fetch_assoc($newRows)) {
    echo "  ID {$r['id']}: item_no={$r['item_no']}, locality={$r['locality']}, site_code={$r['site_code']}\n";
}