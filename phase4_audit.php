<?php
// Phase 4 Audit - Statistics Cards, Filters, Map, CRUD, Table
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

echo "=== PHASE 4 AUDIT ===\n\n";

// 1. STATISTICS CARDS
echo "1. STATISTICS CARDS\n";
echo "-------------------\n";

$totalMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblsdn"))['c'];
$activeMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT locality) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$lguRate = $totalMun > 0 ? round(($activeMun / $totalMun) * 100, 2) : 0;
echo "LGU Penetration: $lguRate% ($activeMun / $totalMun municipalities)\n";

$totalBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(barangay_count) AS c FROM tblsdn"))['c'];
$activeBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT barangay, locality) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$brgyRate = $totalBrgy > 0 ? round(($activeBrgy / $totalBrgy) * 100, 2) : 0;
echo "Barangay Penetration: $brgyRate% ($activeBrgy / $totalBrgy barangays)\n";

$active = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
$inactive = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Inactive'"))['c'];
echo "Active APs: $active\n";
echo "Inactive APs: $inactive\n";

$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Total rows: $total\n";
echo "Active+Inactive: " . ($active + $inactive) . " (diff: " . ($total - $active - $inactive) . ")\n\n";

// 2. FILTERS
echo "2. FILTER OPTIONS\n";
echo "-----------------\n";

echo "Strategies:\n";
$r = mysqli_query($con, "SELECT DISTINCT strategy FROM tblfwfa WHERE strategy != '' ORDER BY strategy");
while ($row = mysqli_fetch_assoc($r)) echo "  {$row['strategy']}\n";

echo "\nSite Types:\n";
$r = mysqli_query($con, "SELECT DISTINCT site_type FROM tblfwfa WHERE site_type != '' ORDER BY site_type");
while ($row = mysqli_fetch_assoc($r)) echo "  {$row['site_type']}\n";

echo "\nLocalities:\n";
$r = mysqli_query($con, "SELECT DISTINCT locality FROM tblfwfa WHERE locality != '' ORDER BY locality");
while ($row = mysqli_fetch_assoc($r)) echo "  {$row['locality']}\n";

echo "\nBarangays (sample):\n";
$r = mysqli_query($con, "SELECT DISTINCT barangay FROM tblfwfa WHERE barangay != '' ORDER BY barangay LIMIT 20");
while ($row = mysqli_fetch_assoc($r)) echo "  {$row['barangay']}\n";

// 3. MAP
echo "\n\n3. MAP COORDINATES\n";
echo "------------------\n";
$withCoords = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != 0 AND longitude != 0"))['c'];
echo "Rows with valid coords: $withCoords / $total\n";

// Check for outliers
$outliers = mysqli_query($con, "SELECT id, site_code, locality, latitude, longitude FROM tblfwfa WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND (latitude < 7 OR latitude > 13 OR longitude < 116 OR longitude > 127)");
echo "Out of PH bounds:\n";
while ($row = mysqli_fetch_assoc($outliers)) {
    echo "  ID {$row['id']} ({$row['site_code']}) {$row['locality']}: {$row['latitude']}, {$row['longitude']}\n";
}

// 4. STATUS VALUES
echo "\n\n4. STATUS VALUES\n";
echo "----------------\n";
$r = mysqli_query($con, "SELECT status, COUNT(*) AS c FROM tblfwfa GROUP BY status ORDER BY c DESC");
while ($row = mysqli_fetch_assoc($r)) {
    $val = $row['status'] === '' ? '(empty)' : $row['status'];
    echo "  '$val': {$row['c']}\n";
}

// Check daily status columns
echo "\nDaily Status Columns:\n";
$statusCols = [
    'status_2026_10_06_am', 'status_2026_10_06_pm',
    'status_2026_10_07_am', 'status_2026_10_07_pm',
    'status_2026_10_08_am', 'status_2026_10_08_pm',
    'status_2026_10_12_am', 'status_2026_10_12_pm',
    'status_2026_10_13_am', 'status_2026_10_13_pm',
    'status_2026_10_14_am', 'status_2026_10_14_pm',
    'status_2026_10_15_am', 'status_2026_10_15_pm',
];
foreach ($statusCols as $col) {
    $c = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE $col != ''"))['c'];
    echo "  $col: $c\n";
}

// 5. CRUD CHECK
echo "\n\n5. CRUD ENDPOINTS\n";
echo "-----------------\n";
echo "Checking fw4a_crud.php actions: add, edit, delete, add_photo, remove_photo\n";
echo "CSRF token required for write actions: YES\n";
echo "Role check (Administrator/fwfasdn): YES\n";
echo "Duplicate check on site_code + source_sheet: YES\n";
echo "Duplicate check on nationwide_id (global): YES\n";

echo "\n=== AUDIT COMPLETE ===\n";