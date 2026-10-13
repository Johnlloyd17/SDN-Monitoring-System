<?php
// Standalone test - no auth required
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(300);

// Minimal DB connection
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

echo "<h2>Import Test: sdn_fw4a_v2.xlsx</h2>\n";
echo "<pre>\n";

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';
if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit;
}

echo "File exists: " . filesize($filePath) . " bytes\n\n";

// Include import functions (without auth)
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
    foreach ($sx->mergeCells->mergeCell as $mc) {
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string)$mc['ref'], $m)) {
            $merges[] = [colIndex($m[1]), (int)$m[2], colIndex($m[3]), (int)$m[4]];
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

$z = xlsxOpen($filePath, $error);
if ($z === null) { echo "ERROR: $error\n"; exit; }

echo "Opened XLSX successfully\n";
echo "Sheets found:\n";
foreach ($z['sheets'] as $s) {
    echo "  - {$s['name']}\n";
}

$chosen = xlsxDetectSheet($z['zip'], $z['shared'], $z['sheets'], $z['files'], $LABEL_MAP, $error);
$z['zip']->close();

if ($chosen === null) {
    echo "ERROR: No sheet detected: $error\n";
    exit;
}

echo "\nChosen sheet: {$chosen['sheet']['name']}\n";
echo "Matched columns: {$chosen['match']}\n";
echo "Unmatched: " . (empty($chosen['unmatched']) ? 'none' : implode(', ', $chosen['unmatched'])) . "\n";

$rows = $chosen['rows'];
xlsxExpandMerges($rows, $chosen['merges']);
$colToField = $chosen['colToField'];

echo "\nColumn mappings:\n";
foreach ($colToField as $c => $f) {
    echo "  Col " . colName($c) . " -> $f\n";
}

echo "\nFirst 3 data rows:\n";
$count = 0;
foreach ($rows as $idx => $cells) {
    if ($idx < 2) continue;
    if ($count >= 3) break;
    $rec = [];
    $hasData = false;
    foreach ($colToField as $c => $f) {
        $v = isset($cells[$c]) ? trim((string)$cells[$c]) : '';
        if ($v !== '') { $rec[$f] = $v; $hasData = true; }
    }
    if (!$hasData) continue;
    echo "Row " . ($idx+1) . ":\n";
    foreach ($rec as $k => $v) {
        echo "  $k = '$v'\n";
    }
    $count++;
}

echo "\nTotal data rows (est): " . (count($rows) - 2) . "\n";

echo "</pre>\n";