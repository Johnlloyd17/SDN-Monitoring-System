<?php
// Fix remaining statuses and clean variants
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

echo "=== FIXING REMAINING ISSUES ===\n\n";

// Backup
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_phase4b");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_phase4b AS SELECT * FROM tblfwfa");
echo "Backup created\n\n";

// 1. Populate remaining empty statuses from daily status columns
echo "1. Populating remaining empty statuses...\n";
$statusCols = [
    'status_2026_10_13_pm', 'status_2026_10_13_am',
    'status_2026_10_12_pm', 'status_2026_10_12_am',
    'status_2026_10_08_pm', 'status_2026_10_08_am',
    'status_2026_10_07_pm', 'status_2026_10_07_am',
    'status_2026_10_06_pm', 'status_2026_10_06_am',
];

$updated = 0;
foreach ($statusCols as $col) {
    $stmt = mysqli_prepare($con, "UPDATE tblfwfa SET status = $col WHERE (status = '' OR status IS NULL) AND $col != ''");
    mysqli_stmt_execute($stmt);
    $upd = mysqli_stmt_affected_rows($stmt);
    if ($upd > 0) echo "  $col: updated $upd rows\n";
    $updated += $upd;
    mysqli_stmt_close($stmt);
}

// For remaining empty, default to 'For Installation'
$stmt = mysqli_prepare($con, "UPDATE tblfwfa SET status = 'For Installation' WHERE status = '' OR status IS NULL");
mysqli_stmt_execute($stmt);
$defaulted = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
echo "  Defaulted to 'For Installation': $defaulted\n";
echo "  Total status fixed: " . ($updated + $defaulted) . "\n\n";

// 2. Fix locality variant: Sta. Monica -> Santa Monica
echo "\n2. Fixing locality variant: Sta. Monica -> Santa Monica\n";
$stmt = mysqli_prepare($con, "UPDATE tblfwfa SET locality = 'Santa Monica' WHERE locality = 'Sta. Monica'");
mysqli_stmt_execute($stmt);
$upd = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
echo "  Updated: $upd rows\n";

// 3. Fix site type variant: LGU-Brgy. -> LGU-BRGY
echo "\n3. Fixing site type variant: LGU-Brgy. -> LGU-BRGY\n";
$stmt = mysqli_prepare($con, "UPDATE tblfwfa SET site_type = 'LGU-BRGY' WHERE site_type = 'LGU-Brgy.'");
mysqli_stmt_execute($stmt);
$upd = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
echo "  Updated: $upd rows\n";

// 4. Try to import more coordinates from spreadsheet (check rows without coords)
echo "\n4. Checking remaining rows without coordinates...\n";
$noCoords = mysqli_query($con, "SELECT id, site_code FROM tblfwfa WHERE (latitude IS NULL OR latitude = 0) AND (longitude IS NULL OR longitude = 0) AND site_code != ''");
$count = mysqli_num_rows($noCoords);
echo "  Rows without coords but with site_code: $count\n";

// The spreadsheet might have more coords in different format or merged cells
// For now, let's check if there are merged cells affecting P/Q columns

echo "\n=== VERIFICATION ===\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
$active = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$inactive = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Inactive'"))['c'];
$empty = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = '' OR status IS NULL"))['c'];

echo "Total: $total, Active: $active, Inactive: $inactive, Empty: $empty\n";

$totalMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblsdn"))['c'];
$activeMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT locality) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$lguRate = $totalMun > 0 ? round(($activeMun / $totalMun) * 100, 2) : 0;
echo "LGU Penetration: $lguRate% ($activeMun / $totalMun)\n";

$totalBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(barangay_count) AS c FROM tblsdn"))['c'];
$activeBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT barangay, locality) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$brgyRate = $totalBrgy > 0 ? round(($activeBrgy / $totalBrgy) * 100, 2) : 0;
echo "Barangay Penetration: $brgyRate% ($activeBrgy / $totalBrgy)\n";

$withCoords = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE latitude IS NOT NULL AND latitude != 0 AND longitude IS NOT NULL AND longitude != 0"))['c'];
echo "Coords: " . $withCoords . " / $total\n";

// Check filter cleanliness
echo "\nFilter cleanliness:\n";
$r = mysqli_query($con, "SELECT DISTINCT strategy FROM tblfwfa WHERE strategy IN ('Strategy')");
echo "  'Strategy' in strategy: " . mysqli_num_rows($r) . "\n";
$r = mysqli_query($con, "SELECT DISTINCT site_type FROM tblfwfa WHERE site_type IN ('Site Type')");
echo "  'Site Type' in site_type: " . mysqli_num_rows($r) . "\n";
$r = mysqli_query($con, "SELECT DISTINCT locality FROM tblfwfa WHERE locality IN ('Locality')");
echo "  'Locality' in locality: " . mysqli_num_rows($r) . "\n";
$r = mysqli_query($con, "SELECT DISTINCT barangay FROM tblfwfa WHERE barangay IN ('Barangay')");
echo "  'Barangay' in barangay: " . mysqli_num_rows($r) . "\n";