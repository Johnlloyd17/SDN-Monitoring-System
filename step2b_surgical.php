<?php
// Step 2B - Surgical deletion to match file exactly
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

echo "=== STEP 2B: SURGICAL DELETION TO MATCH FILE EXACTLY ===\n\n";

// 1. Backup
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_step2b");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_step2b AS SELECT * FROM tblfwfa");
echo "Backup created\n\n";

// 2. Read file records
$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';
$zip = new ZipArchive();
if ($zip->open($filePath) !== true) { die("Unable to open XLSX"); }
$shared = [];
$x = $zip->getFromName('xl/sharedStrings.xml');
if ($x !== false) { $sx = @simplexml_load_string($x); if ($sx) foreach ($sx->si as $si) { if (isset($si->t)) $shared[] = (string)$si->t; else { $t = ''; foreach ($si->r as $r) $t .= (string)$r->t; $shared[] = $t; } } }
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
        $col = $m[1];
        $type = (string)$c['t'];
        if ($type === 's') { $idx = isset($c->v) ? (int)(string)$c->v : -1; $val = ($idx >= 0 && isset($shared[$idx])) ? $shared[$idx] : ''; }
        elseif ($type === 'inlineStr') { $val = isset($c->is->t) ? (string)$c->is->t : ''; }
        else { $val = isset($c->v) ? (string)$c->v : ''; }
        $cells[$col] = $val;
    }
    $rows[] = $cells;
}
$zip->close();

$importCols = [
    'A' => 'item_no', 'B' => 'locality', 'C' => 'barangay', 'D' => 'district',
    'E' => 'transport_location', 'F' => 'transport_type', 'G' => 'site_code',
    'H' => 'new_renamed', 'I' => 'site_locations', 'J' => 'transfer_new_locations',
    'K' => 'remarks', 'L' => 'nationwide_id', 'M' => 'site_type',
    'N' => 'date_of_activation', 'O' => 'current_date_of_acceptance',
    'P' => 'latitude', 'Q' => 'longitude',
    'R' => 'procurement_initiative', 'S' => 'installation_type',
    'T' => 'uat', 'U' => 'conforme',
    'V' => 'strategy', 'W' => 'link_type',
    'X' => 'status_2026_10_06_am', 'Y' => 'status_2026_10_06_pm',
    'Z' => 'status_2026_10_07_am', 'AA' => 'status_2026_10_07_pm',
    'AB' => 'status_2026_10_08_am', 'AC' => 'status_2026_10_08_pm',
    'AD' => 'status_2026_10_12_am', 'AE' => 'status_2026_10_12_pm',
    'AF' => 'status_2026_10_13_am', 'AG' => 'status_2026_10_13_pm',
    'AH' => 'status_2026_10_14_am', 'AI' => 'status_2026_10_14_pm',
    'AJ' => 'status_2026_10_15_am', 'AK' => 'status_2026_10_15_pm',
    'AL' => 'contact_details',
];

// Build file records
$fileRecords = [];
for ($idx = 3; $idx < count($rows); $idx++) {
    $cells = $rows[$idx];
    $hasData = false;
    foreach ($cells as $v) { if ($v !== '') { $hasData = true; break; } }
    if (!$hasData) continue;
    
    $rec = ['source_row' => $idx + 1];
    foreach ($importCols as $colLetter => $name) {
        $v = isset($rows[$idx + 1][$colLetter]) ? trim($rows[$idx + 1][$colLetter]) : '';
        $rec[$colLetter] = $v;
    }
    $fileRecords[] = $rec;
}

echo "File records to match: " . count($fileRecords) . "\n\n";

// 2. For each file record, find matching DB rows
echo "Matching file records to DB rows...\n";

$matchedDbIds = [];
$unmatchedFile = 0;
$multipleMatches = 0;

$colNames = [];
foreach ($importCols as $colLetter => $name) {
    $colNames[] = "`$name`";
}
$colsSql = implode(', ', $colNames);

foreach ($fileRecords as $fRec) {
    // Build WHERE clause
    $whereParts = [];
    $params = [];
    $types = '';
    foreach ($importCols as $colLetter => $name) {
        $v = $fRec[$colLetter] ?? '';
        $whereParts[] = "`$name` = ?";
        $params[] = $v;
        $types .= 's';
    }
    $whereClause = implode(' AND ', $whereParts);
    
    // Build params in the correct order matching importCols
    $params = [];
    foreach ($importCols as $colLetter => $name) {
        $params[] = $fRec[$colLetter] ?? '';
    }
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
        // Keep lowest ID, mark others for deletion
        sort($matches);
        $kept = $matches[0];
        $matchedDbIds[] = $kept;
        echo "  Multiple matches for source row {$fRec['source_row']}: " . implode(', ', $matches) . " -> keeping {$matches[0]}\n";
    } else {
        $unmatchedFile++;
        echo "  NO MATCH for source row {$fRec['source_row']}: " . ($fRec['G'] ?? 'no site_code') . "\n";
    }
}

echo "\nMatched DB IDs: " . count($matchedDbIds) . " unique\n";
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
    $chunkSize = 500;
    $deleted = 0;
    $deleteIdsArray = array_values($deleteIds);
    for ($i = 0; $i < count($deleteIdsArray); $i += 500) {
        $chunk = array_slice($deleteIdsArray, $i, 500);
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
    SELECT " . implode(', ', array_map(function($n) { return "`$n`"; }, array_values($importCols))) . "
    FROM tblfwfa
    GROUP BY " . implode(', ', array_map(function($n) { return "`$n`"; }, array_keys($importCols))) . "
    HAVING COUNT(*) > 1
) as t";
$dupCheck = mysqli_fetch_assoc(mysqli_query($con, $q))['c'];
echo "Remaining duplicate groups: $dupCheck\n";

// Spot check
$check = mysqli_query($con, "SELECT id, item_no, locality, site_code FROM tblfwfa ORDER BY RAND() LIMIT 10");
while ($r = mysqli_fetch_assoc($check)) {
    echo "  id={$r['id']}, item_no={$r['item_no']}, locality={$r['locality']}, site_code={$r['site_code']}\n";
}