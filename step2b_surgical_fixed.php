<?php
// Step 2B - Surgical deletion with CORRECT column mapping
error_reporting(E_ALL);
ini_set('display_errors', 1);

function colIndex($letter) { 
    $c = 0; 
    for ($j = 0; $j < strlen($letter); $j++) $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1); 
    return $c - 1; 
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 2B: SURGICAL DELETION WITH CORRECT MAPPING ===\n\n";

// 1. Backup
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_step2b");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_step2b AS SELECT * FROM tblfwfa");
echo "Backup created\n\n";

// 2. Read file records with CORRECT mapping
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

// CORRECT mapping: spreadsheet column index -> database field name
// Based on import.php FIELD_BY_COL and LABEL_MAP
$spreadsheetToDb = [
    0  => 'item_no',           // A
    1  => 'locality',          // B
    2  => 'barangay',          // C
    3  => 'district',          // D
    4  => 'transport_location', // E
    5  => 'transport_type',    // F
    6  => 'site_code',         // G
    7  => 'new_renamed',       // H
    8  => 'site_locations',    // I
    9  => 'transfer_new_locations', // J
    10 => 'remarks',           // K
    11 => 'nationwide_id',     // L
    12 => 'site_type',         // M
    13 => 'date_of_activation',     // N
    14 => 'current_date_of_acceptance', // O
    15 => 'latitude',          // P
    16 => 'longitude',         // Q
    17 => 'procurement_initiative',   // R
    18 => 'installation_type',        // S
    19 => 'uat',               // T
    20 => 'conforme',          // U
    21 => 'strategy',          // V
    22 => 'link_type',         // W
    // Status columns X-AK (indices 23-36) - 14 columns
    23 => 'status_2026_10_06_am',  // X
    24 => 'status_2026_10_06_pm',  // Y
    25 => 'status_2026_10_07_am',  // Z
    26 => 'status_2026_10_07_pm',  // AA
    27 => 'status_2026_10_08_am',  // AB
    28 => 'status_2026_10_08_pm',  // AC
    29 => 'status_2026_10_12_am',  // AD
    30 => 'status_2026_10_12_pm',  // AE
    31 => 'status_2026_10_13_am',  // AF
    32 => 'status_2026_10_13_pm',  // AG
    33 => 'status_2026_10_14_am',  // AH
    34 => 'status_2026_10_14_pm',  // AI
    35 => 'status_2026_10_15_am',  // AJ
    36 => 'status_2026_10_15_pm',  // AK
    37 => 'contact_details',       // AL
];



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

echo "=== STEP 2B: SURGICAL DELETION WITH CORRECT MAPPING ===\n\n";

// 1. Backup
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_step2b");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_step2b AS SELECT * FROM tblfwfa");
echo "Backup created\n\n";

// 2. Build file records with correct mapping
echo "2. Building file records with correct mapping...\n\n";

$fileRecords = [];
for ($idx = 3; $idx < count($rows); $idx++) {
    $cells = $rows[$idx];
    $hasData = false;
    foreach ($cells as $v) { if ($v !== '') { $hasData = true; break; } }
    if (!$hasData) continue;
    
    $rec = ['source_row' => $idx + 1];
    foreach ($spreadsheetToDb as $colIdx => $dbField) {
        $v = isset($cells[$colIdx]) ? trim($cells[$colIdx]) : '';
        $rec[$dbField] = $v;
    }
    $fileRecords[] = $rec;
}

echo "File records to match: " . count($fileRecords) . "\n\n";

// 2. Match file records to DB rows
echo "Matching file records to DB rows...\n\n";

$matchedDbIds = [];
$unmatchedFile = 0;
$multipleMatches = 0;

$dbFields = array_values($spreadsheetToDb);
$colsSql = implode(', ', array_map(function($n) { return "`$n`"; }, $dbFields));

foreach ($fileRecords as $fRec) {
    $whereParts = [];
    $params = [];
    $types = '';
    foreach ($dbFields as $name) {
        $whereParts[] = "`$name` = ?";
        $params[] = $fRec[$name] ?? '';
        $types .= 's';
    }
    $whereClause = implode(' AND ', $whereParts);
    
    $stmt = mysqli_prepare($con, "SELECT id FROM tblfwfa WHERE $whereClause ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $matches = [];
    while ($r = mysqli_fetch_assoc($result)) {
        $matches[] = $r['id'];
    }
    mysqli_stmt_close($stmt);
    
    if (count($matches) === 1) {
        $matchedDbIds[] = $matches[0];
    } elseif (count($matches) > 1) {
        sort($matches);
        $matchedDbIds[] = $matches[0];
        $multipleMatches++;
        echo "  Multiple matches for row {$fRec['source_row']}: " . implode(', ', $matches) . " -> keeping {$matches[0]}\n";
    } else {
        $unmatchedFile++;
        if ($unmatchedFile <= 10) {
            echo "  NO MATCH for source row {$fRec['source_row']}: site_code=" . ($fRec['site_code'] ?? 'none') . "\n";
        }
    }
}

echo "\nMatched DB IDs: " . count($matchedDbIds) . " unique\n";
echo "Multiple matches resolved: $multipleMatches\n";
echo "File records unmatched: $unmatchedFile\n\n";

// Get all DB IDs
$allDbIds = [];
$r = mysqli_query($con, "SELECT id FROM tblfwfa");
while ($r2 = mysqli_fetch_assoc($r)) $allDbIds[] = $r2['id'];

// IDs to keep = matched ones
$keepIds = array_unique($matchedDbIds);
$deleteIds = array_diff($allDbIds, $keepIds);

echo "\nTotal DB rows: " . count($allDbIds) . "\n";
echo "Rows to keep: " . count($keepIds) . "\n";
echo "Rows to delete: " . count($deleteIds) . "\n\n";

if (!empty($deleteIds)) {
    echo "Deleting " . count($deleteIds) . " rows...\n";
    $chunkSize = 100;
    $deleted = 0;
    $deleteIdsArray = array_values($deleteIds);
    for ($i = 0; $i < count($deleteIdsArray); $i += 100) {
        $chunk = array_slice($deleteIdsArray, $i, 100);
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $types = str_repeat('i', count($chunk));
        $stmt = mysqli_prepare($con, "DELETE FROM tblfwfa WHERE id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types, ...$chunk);
        mysqli_stmt_execute($stmt);
        $deleted += mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
    }
    echo "Deleted $deleted rows\n";
} else {
    echo "Nothing to delete\n";
}

// Final verification
$finalTotal = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "\n=== FINAL VERIFICATION ===\n";
echo "Final total: $finalTotal\n";
echo "Expected (file): " . count($fileRecords) . "\n";
echo "Match: " . ($finalTotal == count($fileRecords) ? "YES" : "NO - diff: " . ($finalTotal - count($fileRecords))) . "\n";

// Check duplicates
$q = "SELECT COUNT(*) as c FROM (
    SELECT " . implode(', ', array_map(function($n) { return "`$n`"; }, array_values($spreadsheetToDb))) . "
    FROM tblfwfa
    GROUP BY " . implode(', ', array_map(function($n) { return "`$n`"; }, $dbFields)) . "
    HAVING COUNT(*) > 1
) as t";
$dupCheck = mysqli_fetch_assoc(mysqli_query($con, $q))['c'];
echo "Remaining duplicate groups: $dupCheck\n";

// Spot check
$check = mysqli_query($con, "SELECT id, item_no, locality, site_code FROM tblfwfa ORDER BY RAND() LIMIT 10");
while ($r = mysqli_fetch_assoc($check)) {
    echo "  id={$r['id']}, item_no={$r['item_no']}, locality={$r['locality']}, site_code={$r['site_code']}\n";
}

