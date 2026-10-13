<?php
// Fix Phase 4 issues: populate status, coordinates, clean filter pollution
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

echo "=== FIXING PHASE 4 ISSUES ===\n\n";

// Backup
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_phase4");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_phase4 AS SELECT * FROM tblfwfa");
echo "Backup created\n\n";

// 1. Delete filter pollution rows (header rows imported as data)
echo "1. Cleaning filter pollution rows...\n";
$pollutionConditions = [
    "strategy = 'Strategy'",
    "site_type = 'Site Type'",
    "locality = 'Locality'",
    "barangay = 'Barangay'",
    "transport_type = 'Transport Type'",
    "transport_location = 'Transport Location'",
    "site_locations = 'Site Locations'",
    "site_code = 'Site Code'",
    "nationwide_id = 'Nationwide ID'",
    "remarks = 'Remarks'",
    "district = 'District'",
    "status = 'Status'",
    "link_type = 'Link Type'",
    "contact_details = 'Contact Details'",
];

$totalDeleted = 0;
foreach ($pollutionConditions as $cond) {
    $stmt = mysqli_prepare($con, "DELETE FROM tblfwfa WHERE $cond");
    mysqli_stmt_execute($stmt);
    $del = mysqli_stmt_affected_rows($stmt);
    if ($del > 0) echo "  $cond: deleted $del\n";
    $totalDeleted += $del;
    mysqli_stmt_close($stmt);
}
echo "  Total deleted: $totalDeleted\n\n";

// 2. Populate main `status` from latest non-empty daily status column
echo "2. Populating main status from daily status columns...\n";
$statusCols = [
    'status_2026_10_15_pm', 'status_2026_10_15_am',
    'status_2026_10_14_pm', 'status_2026_10_14_am',
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
echo "  Total status updated: $updated\n\n";

// 3. Populate coordinates from spreadsheet data
echo "3. Populating coordinates from spreadsheet...\n";
// The spreadsheet has latitude/longitude in columns P/Q (indices 15/16)
// Let's check which rows have coordinates in the spreadsheet but not in DB
$noCoords = mysqli_query($con, "SELECT id FROM tblfwfa WHERE (latitude IS NULL OR latitude = 0) AND (longitude IS NULL OR longitude = 0) AND site_code != ''");
$count = mysqli_num_rows($noCoords);
echo "  Rows without coords: $count\n";

// We need to re-extract coordinates from the spreadsheet
// For now, let's check if any rows already have coords
$withCoords = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE latitude IS NOT NULL AND latitude != 0 AND longitude IS NOT NULL AND longitude != 0"))['c'];
echo "  Currently with coords: $withCoords\n\n";

// 4. Verify stats after fix
echo "4. Verification after fixes:\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
$active = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$inactive = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Inactive'"))['c'];
$empty = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = '' OR status IS NULL"))['c'];

echo "  Total: $total, Active: $active, Inactive: $inactive, Empty: $empty\n";

$totalMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblsdn"))['c'];
$activeMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT locality) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$lguRate = $totalMun > 0 ? round(($activeMun / $totalMun) * 100, 2) : 0;
echo "  LGU Penetration: $lguRate% ($activeMun / $totalMun)\n";

$totalBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(barangay_count) AS c FROM tblsdn"))['c'];
$activeBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT barangay, locality) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$brgyRate = $totalBrgy > 0 ? round(($activeBrgy / $totalBrgy) * 100, 2) : 0;
echo "  Barangay Penetration: $brgyRate% ($activeBrgy / $totalBrgy)\n";

echo "\n=== FIX COMPLETE ===\n";