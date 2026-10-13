<?php
// Step 2 - Remove extra copies
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

echo "=== STEP 2: REMOVE EXTRA COPIES ===\n\n";

// 1. Backup
echo "1. Creating backup...\n";
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_step2");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_step2 AS SELECT * FROM tblfwfa");
$backupCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa_backup_20261014_step2"))['c'];
echo "   Backup created: $backupCount rows\n\n";

// 2. Analyze what to delete
echo "2. Analyzing rows to delete...\n\n";

// The strategy: Keep the LOWEST id for each unique record (by all imported columns)
// but ONLY for the "FW4A - Updated SDN Monitoring" source sheet.
// Delete all "Legacy Import" rows (they're not in the current file).
// For duplicates within "FW4A - Updated SDN Monitoring", keep the lowest id.

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
    25 => 'status_2026_10_07_am', 24 => 'status_2026_10_07_pm',
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

// First, let's see the counts
echo "Current counts:\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
$legacy = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE source_sheet = 'Legacy Import'"))['c'];
$fw4a = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE source_sheet = 'FW4A - Updated SDN Monitoring'"))['c'];
echo "  Total: $total\n";
echo "  Legacy Import: $legacy\n";
echo "  FW4A - Updated SDN Monitoring: $fw4a\n\n";

// Step 1: Delete all Legacy Import rows (they're not in current file)
echo "Step 1: Deleting Legacy Import rows...\n";
$del = mysqli_query($con, "DELETE FROM tblfwfa WHERE source_sheet = 'Legacy Import'");
$legacyDeleted = mysqli_affected_rows($con);
echo "  Deleted $legacyDeleted Legacy Import rows\n\n";

// Step 2: For FW4A source, delete duplicates keeping lowest id
echo "Step 2: Deleting duplicate copies within FW4A source...\n";

// Find all duplicate groups in FW4A source
$colsSql = [];
foreach ($importCols as $idx => $name) {
    $colsSql[] = "`$name`";
}
$selectSql = implode(', ', $colsSql);

$q = "SELECT $selectSql, MIN(id) as keep_id, COUNT(*) as cnt, GROUP_CONCAT(id ORDER BY id) as all_ids
      FROM tblfwfa
      WHERE source_sheet = 'FW4A - Updated SDN Monitoring'
      GROUP BY " . implode(', ', array_map(function($name) { return "`$name`"; }, array_values($importCols))) . "
      HAVING COUNT(*) > 1";
$dupes = mysqli_query($con, $q);

$totalToDelete = 0;
$groups = 0;
$deleteIds = [];

while ($row = mysqli_fetch_assoc($dupes)) {
    $ids = explode(',', $row['all_ids']);
    $keepId = $row['keep_id'];
    foreach ($ids as $id) {
        if ($id != $keepId) {
            $deleteIds[] = $id;
        }
    }
    $groups++;
}

echo "  Found " . count($deleteIds) . " duplicate rows to delete in " . count($deleteIds) . " groups\n";

if (!empty($deleteIds)) {
    // Show what we'll delete
    echo "  IDs to delete: " . implode(', ', array_slice($deleteIds, 0, 20)) . (count($deleteIds) > 20 ? " ... (+" . (count($deleteIds) - 20) . " more)" : "") . "\n";
    
    // Ask for confirmation (in this script we'll just proceed since user said "go")
    echo "\nProceeding with deletion...\n";
    
    // Delete in chunks to avoid large query
    $chunkSize = 500;
    $deleted = 0;
    for ($i = 0; $i < count($deleteIds); $i += $chunkSize) {
        $chunk = array_slice($deleteIds, $i, $chunkSize);
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $types = str_repeat('i', count($chunk));
        $stmt = mysqli_prepare($con, "DELETE FROM tblfwfa WHERE id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types, ...$chunk);
        mysqli_stmt_execute($stmt);
        $deleted += mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
    }
    echo "  Deleted $deleted duplicate rows\n";
} else {
    echo "  No duplicates to delete\n";
}

// Final verification
echo "\n=== VERIFICATION ===\n";
$finalTotal = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
$legacy = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE source_sheet = 'Legacy Import'"))['c'];
$fw4a = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE source_sheet = 'FW4A - Updated SDN Monitoring'"))['c'];

echo "Final total: $finalTotal\n";
echo "  Legacy Import: $legacy\n";
echo "  FW4A - Updated SDN Monitoring: $fw4a\n\n";

// Check against file
$fileRows = 615; // from audit
echo "File has 615 data rows\n";
echo "Table now has $finalTotal rows\n";
echo "Difference: " . ($finalTotal - 615) . "\n";

// Check for remaining duplicates
$q = "SELECT COUNT(*) as c FROM (
    SELECT " . implode(', ', array_map(function($n) { return "`$n`"; }, array_values($importCols))) . "
    FROM tblfwfa
    GROUP BY " . implode(', ', array_map(function($n) { return "`$n`"; }, array_values($importCols))) . "
    HAVING COUNT(*) > 1
) as t";
$dupCheck = mysqli_fetch_assoc(mysqli_query($con, $q))['c'];
echo "Remaining duplicate groups: $dupCheck\n";

// Spot check 10 random rows against file
echo "\nSpot check 10 random rows:\n";
$check = mysqli_query($con, "SELECT id, item_no, locality, site_code, source_sheet FROM tblfwfa ORDER BY RAND() LIMIT 10");
while ($r = mysqli_fetch_assoc($check)) {
    echo "  id={$r['id']}, item_no={$r['item_no']}, locality={$r['locality']}, site_code={$r['site_code']}, source={$r['source_sheet']}\n";
}

echo "\n=== STEP 2 COMPLETE ===\n";