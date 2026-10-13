<?php
// Step 1 Audit - Database doubling investigation (fixed version)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

echo "=== STEP 1 AUDIT ===\n\n";

// a) Counts
echo "a) ROW COUNTS\n";
echo "-------------\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Rows in tblfwfa: $total\n";

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';
// Count file rows
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
if (!$targetSheet) { die("Sheet not found"); }
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

$dataRows = count($rows) - 3; // header rows 1-3
echo "Rows in tblfwfa: $total\n";
echo "Rows in file (data rows 4-612): $dataRows\n";
echo "Difference (table - file): " . ($total - $dataRows) . "\n";
echo "Ratio: " . round($total / $dataRows, 2) . "\n\n";

function colIndex($letter) { $c = 0; for ($j = 0; $j < strlen($letter); $j++) $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1); return $c - 1; }
function colName($i) { $s = ''; while ($i >= 0) { $s = chr(ord('A') + ($i % 26)) . $s; $i = intdiv($i, 26) - 1; } return $s; }

// b) Prove/disprove doubling - group by all imported columns
echo "b) GROUPING BY ALL IMPORTED COLUMNS\n";
echo "------------------------------------\n";

$importCols = [
    0 => 'item_no', 1 => 'locality', 2 => 'barangay', 3 => 'district',
    4 => 'transport_location', 5 => 'transport_type', 6 => 'site_code',
    7 => 'new_renamed', 8 => 'site_locations', 9 => 'transfer_new_locations',
    10 => 'remarks', 11 => 'nationwide_id', 12 => 'site_type',
    13 => 'date_of_activation', 14 => 'current_date_of_acceptance',
    15 => 'latitude', 16 => 'longitude',
    17 => 'procurement_initiative', 18 => 'installation_type',
    19 => 'uat', 20 => 'conforme',
    21 => 'strategy', 22 => 'link_type',
    23 => 'status_2026_10_06_am', 24 => 'status_2026_10_06_pm',
    25 => 'status_2026_10_07_am', 26 => 'status_2026_10_07_pm',
    27 => 'status_2026_10_08_am', 28 => 'status_2026_10_08_pm',
    29 => 'status_2026_10_12_am', 30 => 'status_2026_10_12_pm',
    31 => 'status_2026_10_13_am', 32 => 'status_2026_10_13_pm',
    33 => 'status_2026_10_14_am', 34 => 'status_2026_10_14_pm',
    35 => 'status_2026_10_15_am', 36 => 'status_2026_10_15_pm',
    37 => 'contact_details',
];

$colsSql = [];
foreach ($importCols as $idx => $name) {
    $colsSql[] = "`$name`";
}
$selectSql = implode(', ', $colsSql);

$query = "SELECT $selectSql, COUNT(*) as cnt, MIN(id) as min_id, MAX(id) as max_id, GROUP_CONCAT(id ORDER BY id) as ids
          FROM tblfwfa
          GROUP BY $selectSql
          ORDER BY cnt DESC, min_id";
$result = mysqli_query($con, $query);

$groups1 = $groups2 = $groups3plus = 0;
$totalGroups = 0;
$totalRowsAccounted = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $cnt = (int)$row['cnt'];
    $totalGroups++;
    $totalRowsAccounted += $cnt;
    if ($cnt == 1) $groups1++;
    elseif ($cnt == 2) $groups2++;
    else $groups3plus++;
    
    if ($cnt > 1) {
        echo "Group (cnt={$row['cnt']}): min_id={$row['min_id']}, max_id={$row['max_id']}, ids={$row['ids']}\n";
    }
}
echo "\nGroups with 1 row: $groups1\n";
echo "Groups with 2 rows: $groups2\n";
echo "Groups with 3+ rows: $groups3plus\n";
echo "Total unique groups: $totalGroups\n";
echo "Total rows accounted: $totalRowsAccounted\n\n";

// Check if table is exactly file rows twice (ids 1-609 and 610-1218 same data)
echo "Checking if table = file rows x 2 (sequential id pairs)...\n";
$mid = (int)($total / 2);
$stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM tblfwfa WHERE id <= ?");
mysqli_stmt_bind_param($stmt, 'i', $mid);
mysqli_stmt_execute($stmt);
$lowCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))[0];
mysqli_stmt_close($stmt);
echo "Rows with id <= $mid: $lowCount\n\n";

// c) Does file itself contain identical rows?
echo "c) DUPLICATES IN FILE ITSELF\n";
echo "----------------------------\n";

$LABEL_MAP = [
    'item no' => 'item_no', 'itemno' => 'item_no', 'item no.' => 'item_no',
    'locality' => 'locality', 'barangay' => 'barangay', 'district' => 'district',
    'transport location' => 'transport_location', 'transporttype' => 'transport_type', 'transport type' => 'transport_type',
    'site code' => 'site_code', 'new renamed' => 'new_renamed',
    'site locations' => 'site_locations', 'sitelocations' => 'site_locations',
    'transfer new locations' => 'transfer_new_locations', 'transfer new location' => 'transfer_new_locations',
    'remarks' => 'remarks', 'nationwide id' => 'nationwide_id',
    'site type' => 'site_type',
    'date of activation' => 'date_of_activation', 'dateactivated' => 'date_of_activation',
    'current date of acceptance' => 'current_date_of_acceptance',
    'latitude' => 'latitude', 'longitude' => 'longitude', 'longittude' => 'longitude',
    'procurement initiative' => 'procurement_initiative',
    'installation type' => 'installation_type',
    'uat' => 'uat', 'conforme' => 'conforme',
    'strategy' => 'strategy', 'link type' => 'link_type',
    'replacement form file' => 'replacement_form_file',
    'conforme file' => 'conforme_file',
    'uat file' => 'uat_file', 'uat f ile' => 'uat_file',
    'additional uat' => 'additional_uat',
    'name' => 'site_coordinator_name', 'site coordinators' => 'site_coordinator_name',
    'contact details' => 'contact_details',
];

function normLabel($s) { $s = strtolower(trim((string)$s)); return trim(preg_replace('/[\s_\-\.\/]+/', ' ', $s)); }

$zip2 = new ZipArchive();
if ($zip2->open($filePath) !== true) { die("Unable to open XLSX"); }
$shared = [];
$x = $zip2->getFromName('xl/sharedStrings.xml');
if ($x !== false) { $sx = @simplexml_load_string($x); if ($sx) foreach ($sx->si as $si) { if (isset($si->t)) $shared[] = (string)$si->t; else { $t = ''; foreach ($si->r as $r) $t .= (string)$r->t; $shared[] = $t; } } }
$files = []; for ($i = 0; $i < $zip2->numFiles; $i++) $files[] = $zip2->getNameIndex($i);
$sheets = [];
$wb = @simplexml_load_string($zip2->getFromName('xl/workbook.xml'));
if ($wb !== false) {
    $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    $rels = @simplexml_load_string($zip2->getFromName('xl/_rels/workbook.xml.rels'));
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
$sx = @simplexml_load_string($zip2->getFromName($targetSheet['sp']));
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
$zip2->close();

// Build records from data rows (skip first 3 header rows)
$importCols = [
    0 => 'item_no', 1 => 'locality', 2 => 'barangay', 3 => 'district',
    4 => 'transport_location', 5 => 'transport_type', 6 => 'site_code',
    7 => 'new_renamed', 8 => 'site_locations', 9 => 'transfer_new_locations',
    10 => 'remarks', 11 => 'nationwide_id', 12 => 'site_type',
    13 => 'date_of_activation', 14 => 'current_date_of_acceptance',
    15 => 'latitude', 16 => 'longitude',
    17 => 'procurement_initiative', 18 => 'installation_type',
    19 => 'uat', 20 => 'conforme',
    21 => 'strategy', 22 => 'link_type',
    23 => 'status_2026_10_06_am', 24 => 'status_2026_10_06_pm',
    25 => 'status_2026_10_07_am', 26 => 'status_2026_10_07_pm',
    27 => 'status_2026_10_08_am', 28 => 'status_2026_10_08_pm',
    29 => 'status_2026_10_12_am', 30 => 'status_2026_10_12_pm',
    31 => 'status_2026_10_13_am', 32 => 'status_2026_10_13_pm',
    33 => 'status_2026_10_14_am', 34 => 'status_2026_10_14_pm',
    35 => 'status_2026_10_15_am', 36 => 'status_2026_10_15_pm',
    37 => 'contact_details',
];

$fileRecords = [];
for ($idx = 3; $idx < count($rows); $idx++) {
    $cells = $rows[$idx];
    $hasData = false;
    foreach ($cells as $v) { if ($v !== '') { $hasData = true; break; } }
    if (!$hasData) continue;
    
    $keyParts = [];
    foreach ($importCols as $c => $name) {
        $v = isset($rows[$idx][$c]) ? trim($rows[$idx][$c]) : '';
        $keyParts[] = $v;
    }
    $key = implode('|', $keyParts);
    $fileRecords[] = $key;
}

$fileRecordCounts = array_count_values($fileRecords);
$dupesInFile = 0;
foreach ($fileRecordCounts as $k => $c) {
    if ($c > 1) { $dupesInFile += $c - 1; }
}
echo "Total file data rows processed: " . count($fileRecords) . "\n";
echo "Unique records in file: " . count($fileRecordCounts) . "\n";
echo "Duplicate rows in file (extra copies): " . ($dupesInFile) . "\n\n";

// d) For pairs of copies in DB: are they identical?
echo "d) PAIR DIFFERENCES IN DB\n";
echo "------------------------\n";

$colsSql = [];
foreach ($importCols as $idx => $name) { $colsSql[] = "`$name`"; }
$selectSql = implode(', ', $colsSql);

$diffQuery = "SELECT $selectSql, MIN(id) as id1, MAX(id) as id2
              FROM tblfwfa
              GROUP BY $selectSql
              HAVING COUNT(*) = 2
              LIMIT 20";
$diffResult = mysqli_query($con, $diffQuery);

$diffCount = 0;
$diffExamples = [];

while ($row = mysqli_fetch_assoc($diffResult)) {
    $id1 = $row['id1'] ?? $row['min_id'];
    $id2 = $row['id2'] ?? $row['max_id'];
    
    $q = "SELECT * FROM tblfwfa WHERE id IN ($id1, $id2)";
    $r = mysqli_query($con, $q);
    $rows2 = [];
    while ($r2 = mysqli_fetch_assoc($r)) {
        $rows2[$r2['id']] = $r2;
    }
    
    $diffs = [];
    foreach ($importCols as $c => $name) {
        $v1 = $rows2[$id1][$name] ?? '';
        $v2 = $rows2[$id2][$name] ?? '';
        if ($v1 !== $v2) {
            $diffs[] = "$name: id$id1='$v1' vs id$id2='$v2'";
        }
    }
    
    if (!empty($diffs)) {
        $diffCount++;
        if (count($diffExamples) < 5) {
            $diffExamples[] = ["id1=$id1, id2=$id2" => $diffs];
        }
    }
}

echo "Pairs with differences: $diffCount out of $groups2\n";
if (!empty($diffExamples)) {
    echo "Examples:\n";
    foreach ($diffExamples as $ex) {
        foreach ($ex as $ids => $diffs) {
            echo "  $ids:\n";
            foreach ($diffs as $d) echo "    $d\n";
        }
    }
}
echo "\n";

// e) Foreign keys referencing fw4a_data.id
echo "e) FOREIGN KEYS REFERENCING tblfwfa.id\n";
echo "-------------------------------------\n";
$fkQuery = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = 'tblfwfa' AND REFERENCED_COLUMN_NAME = 'id'
            AND TABLE_SCHEMA = '$db'";
$fkResult = mysqli_query($con, $fkQuery);
$fkCount = 0;
while ($row = mysqli_fetch_assoc($fkResult)) {
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} (constraint: {$row['CONSTRAINT_NAME']})\n";
    $fkCount++;
}
if ($fkCount == 0) echo "  None found via INFORMATION_SCHEMA\n";

// Check common tables that might reference it
$commonTables = ['tblactivityphoto', 'tbllogs'];
foreach ($commonTables as $t) {
    $check = @mysqli_query($con, "SHOW TABLES LIKE '$t'");
    if ($check && mysqli_num_rows($check) > 0) {
        $cols = mysqli_query($con, "SHOW COLUMNS FROM `$t`");
        while ($c = mysqli_fetch_assoc($cols)) {
            if (stripos($c['Field'], 'fwfa') !== false || stripos($c['Field'], 'activity') !== false) {
                echo "  Possible reference: $t.{$c['Field']}\n";
            }
        }
    }
}
echo "\n";

// f) How import decides if row exists
echo "f) IMPORT DEDUPLICATION LOGIC\n";
echo "-----------------------------\n";

$importContent = file_get_contents('pages/fw4a_data/import.php');
if (strpos($importContent, 'site_code') !== false && strpos($importContent, 'DUPLICATE') !== false) {
    echo "  Import checks for duplicate site_code\n";
}
if (strpos($importContent, 'source_sheet') !== false) {
    echo "  Import uses source_sheet\n";
}
if (strpos($importContent, 'fw4a_dup_value') !== false || strpos($importContent, 'duplicate') !== false) {
    echo "  Has duplicate checking function\n";
}

$idxResult = mysqli_query($con, "SHOW INDEX FROM tblfwfa WHERE Non_unique = 0");
echo "\nUnique indexes on tblfwfa:\n";
while ($row = mysqli_fetch_assoc($idxResult)) {
    echo "  {$row['Key_name']}: {$row['Column_name']}\n";
}

echo "\nTimestamp/id analysis:\n";
$ts = mysqli_query($con, "SELECT MIN(id) as min_id, MAX(id) as max_id FROM tblfwfa");
$tsRow = mysqli_fetch_assoc($ts);
if ($tsRow) {
    echo "  ID range: {$tsRow['min_id']} - {$tsRow['max_id']}\n";
}

$hasCreated = mysqli_query($con, "SHOW COLUMNS FROM tblfwfa LIKE 'created_at'");
if (mysqli_num_rows($hasCreated) > 0) {
    $ts2 = mysqli_query($con, "SELECT created_at, COUNT(*) as c FROM tblfwfa GROUP BY created_at ORDER BY created_at");
    while ($r = mysqli_fetch_assoc($ts2)) {
        echo "  {$r['created_at']}: {$r['c']} rows\n";
    }
} else {
    echo "  No created_at column found\n";
}

$ss = mysqli_query($con, "SELECT source_sheet, COUNT(*) as c, MIN(id) as min_id, MAX(id) as max_id FROM tblfwfa GROUP BY source_sheet ORDER BY min_id");
echo "\nSource sheet distribution:\n";
while ($r = mysqli_fetch_assoc($ss)) {
    echo "  {$r['source_sheet']}: {$r['c']} rows (id {$r['min_id']}-{$r['max_id']})\n";
}