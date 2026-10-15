<?php
// Step 1 Audit - Comprehensive Report
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect('localhost', 'root', '', 'dict_proj', 3306) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 1 AUDIT REPORT ===\n\n";

// a) Safety: truncate on real table or copy? Backup location?
echo "a) SAFETY CHECK\n";
echo "----------------\n";
$backups = [];
$r = mysqli_query($con, "SHOW TABLES LIKE 'tblfwfa_backup%'");
while ($row = mysqli_fetch_assoc($r)) {
    $cnt = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM `{$row[array_keys($row)[0]]}`"))['c'];
    $backups[] = [$row[array_keys($row)[0]], $cnt];
    echo "  Backup: {$row[array_keys($row)[0]]} - $cnt rows\n";
}
if (empty($backups)) echo "  NO BACKUP TABLES FOUND\n";

$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "\n  Current tblfwfa: $total rows\n\n";

// b) Header row check
echo "b) HEADER ROW CHECK\n";
echo "-------------------\n";

$headerCheck = mysqli_query($con, "SELECT id, item_no, locality, site_code, site_locations, source_sheet FROM tblfwfa WHERE site_code = 'Site Code' OR site_locations = 'Site Locations' OR item_no = 0 OR item_no = '0' OR item_no = ''");
$count = 0;
while ($row = mysqli_fetch_assoc($headerCheck)) {
    $count++;
    echo "  Row id={$row['id']}: item_no='{$row['item_no']}', locality='{$row['locality']}', site_code='{$row['site_code']}', site_locations='{$row['site_locations']}', source={$row['source_sheet']}\n";
}
if ($count === 0) echo "  NO header rows found\n";
echo "\n";

// c) Match with file
echo "c) FILE vs TABLE MATCH\n";
echo "----------------------\n";

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con2 = mysqli_connect('localhost', 'root', '', 'dict_proj', 3306) or die(mysqli_connect_error());
mysqli_set_charset($con2, 'utf8mb4');

// Load file data
$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';
$zip = new ZipArchive();
if ($zip->open($filePath) !== true) { die("Unable to open XLSX"); }
$shared = [];
$x = $zip->getFromName('xl/sharedStrings.xml');
if ($x !== false) { $sx = @simplexml_load_string($x); if ($sx) foreach ($sx->si as $si) { if (isset($si->t)) $shared[] = (string)$si->t; else { $t = ''; foreach ($si->r as $r) $t .= (string)$r->t; $shared[] = $t; } } }
$files = []; for ($i = 0; $i < $zip->numFiles; $i++) $files[] = $zip->getNameIndex($i);
$sheets = [];
$wb = @simplexml_load_string($zip->getFromName('xl/workbook.xml'));
if ($wb !== false) {
    $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    $rels = @simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
    $ridMap = []; if ($rels !== false) foreach ($rels->Relationship as $r) $ridMap[(string)$r['Id']] = (string)$r['Target'];
    foreach ($wb->sheets->sheet as $s) {
        $rid = (string)$s->attributes($rns)['id'];
        $target = isset($ridMap[$rid]) ? $ridMap[$rid] : '';
        $sp = ($target === '') ? '' : ($target[0] === '/' ? ltrim($target, '/') : 'xl/' . $target);
        $sheets[] = ['name' => (string)$s['name'], 'sp' => $sp];
    }
}
$targetSheet = null;
foreach ($sheets as $s) { if ($s['name'] === 'FW4A - Updated SDN Monitoring') { $targetSheet = $s; break; } }
$sx = @simplexml_load_string($zip->getFromName($targetSheet['sp']));
$rows = [];
foreach ($sx->sheetData->row as $row) {
    $cells = [];
    foreach ($row->c as $c) {
        $ref = (string)$c['r']; preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
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
$zip->close();



$spreadsheetToDb = [
    0 => 'item_no', 1 => 'locality', 2 => 'barangay', 3 => 'district',
    4 => 'transport_location', 5 => 'transport_type', 6 => 'site_code',
    7 => 'new_renamed', 8 => 'site_locations', 9 => 'transfer_new_locations',
    10 => 'remarks', 11 => 'nationwide_id', 12 => 'site_type',
    13 => 'date_of_activation', 14 => 'current_date_of_acceptance',
    15 => 'latitude', 16 => 'longitude',
    17 => 'procurement_initiative', 18 => 'installation_type',
    19 => 'uat', 20 => 'conforme',
    21 => 'strategy', 22 => 'link_type',
    37 => 'contact_details',
];
$statusCols = [
    23 => 'status_2026_10_06_am', 24 => 'status_2026_10_06_pm',
    25 => 'status_2026_10_07_am', 26 => 'status_2026_10_07_pm',
    27 => 'status_2026_10_08_am', 28 => 'status_2026_10_08_pm',
    29 => 'status_2026_10_12_am', 30 => 'status_2026_10_12_pm',
    31 => 'status_2026_10_13_am', 32 => 'status_2026_10_13_pm',
    33 => 'status_2026_10_14_am', 34 => 'status_2026_10_14_pm',
    35 => 'status_2026_10_15_am', 36 => 'status_2026_10_15_pm',
];
foreach ($statusCols as $c => $f) { $spreadsheetToDb[$c] = $f; }

$fileRecords = [];
for ($idx = 3; $idx < count($rows); $idx++) {
    $cells = $rows[$idx];
    $hasData = false;
    foreach ($cells as $v) { if ($v !== '') { $hasData = true; break; } }
    if (!$hasData) continue;
    $rec = ['source_row' => $idx + 1];
    foreach ($spreadsheetToDb as $c => $f) {
        $v = isset($rows[$idx][$c]) ? trim($rows[$idx][$c]) : '';
        $rec[$f] = $v;
    }
    $fileRecords[] = $rec;
}

echo "File data rows (4-612): " . count($fileRecords) . "\n";

// Build table records
$importCols = [
    'item_no', 'locality', 'barangay', 'district', 'transport_location', 'transport_type',
    'site_code', 'new_renamed', 'site_locations', 'transfer_new_locations', 'remarks',
    'nationwide_id', 'site_type',
    'date_of_activation', 'current_date_of_acceptance', 'latitude', 'longitude',
    'procurement_initiative', 'installation_type', 'uat', 'conforme', 'strategy', 'link_type',
    'status_2026_10_06_am', 'status_2026_10_06_pm',
    'status_2026_10_07_am', 'status_2026_10_07_pm',
    'status_2026_10_08_am', 'status_2026_10_08_pm',
    'status_2026_10_12_am', 'status_2026_10_12_pm',
    'status_2026_10_13_am', 'status_2026_10_13_pm',
    'status_2026_10_14_am', 'status_2026_10_14_pm',
    'status_2026_10_15_am', 'status_2026_10_15_pm',
    'contact_details',
];

$dbFields = array_values($importCols);
$colsSql = implode(', ', array_map(function($n) { return "`$n`"; }, $importCols));
$r = mysqli_query($con, "SELECT $colsSql FROM tblfwfa");
$tableRecords = [];
while ($row = mysqli_fetch_assoc($r)) {
    $tableRecords[] = $row;
}
echo "Table rows: " . count($tableRecords) . "\n\n";

// Find missing file rows
echo "Missing from table (in file but not in DB):\n";
$missingCount = 0;
foreach ($fileRecords as $fRec) {
    $found = false;
    foreach ($tableRecords as $tRec) {
        $match = true;
        foreach ($importCols as $col) {
            $v1 = $fRec[$col] ?? '';
            $v2 = $tRec[$col] ?? '';
            if ($v1 !== $v2) { $match = false; break; }
        }
        if ($match) { $found = true; break; }
    }
    if (!$found) {
        $missingCount++;
        if ($missingCount <= 10) {
            echo "  File row {$fRec['source_row']}: site_code={$fRec['site_code']}, locality={$fRec['locality']}\n";
        }
    }
}
if ($missingCount > 10) echo "  ... and " . ($missingCount - 10) . " more\n";
echo "Total missing: $missingCount\n\n";

// Find extra table rows
echo "Extra in table (not in file):\n";
$extraCount = 0;
foreach ($tableRecords as $tRec) {
    $found = false;
    foreach ($fileRecords as $fRec) {
        $match = true;
        foreach ($importCols as $col) {
            $v1 = $fRec[$col] ?? '';
            $v2 = $tRec[$col] ?? '';
            if ($v1 !== $v2) { $match = false; break; }
        }
        if ($match) { $found = true; break; }
    }
    if (!$found) {
        $extraCount++;
        if ($extraCount <= 10) {
            echo "  DB id={$tRec['id']}: site_code={$tRec['site_code']}, locality={$tRec['locality']}\n";
        }
    }
}
if ($extraCount > 10) echo "  ... and " . ($extraCount - 10) . " more\n";
echo "Total extra: $extraCount\n\n";

// Check blank handling
echo "Blank Site Code in table:\n";
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE site_code = '' OR site_code IS NULL");
echo "  " . mysqli_fetch_assoc($r)['c'] . " rows\n";
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE item_no = '' OR item_no IS NULL OR item_no = '0'");
echo "  Blank/zero Item No: " . mysqli_fetch_assoc($r)['c'] . " rows\n\n";

// Check unique index behavior with blanks
echo "Unique index behavior with blanks:\n";
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE site_code = '' AND item_no = '' OR site_code IS NULL AND item_no IS NULL");
echo "  Rows with blank site_code AND blank item_no: " . mysqli_fetch_assoc($r)['c'] . "\n\n";

