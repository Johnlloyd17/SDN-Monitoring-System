<?php
// Phase 4 Audit - Statistics Cards Verification
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');

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

echo "=== PHASE 4 AUDIT: STATISTICS CARDS ===\n\n";

// 1. LGU Penetration Rate (overall - no filter)
echo "1. LGU Penetration Rate (Overall):\n";
$totalMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total_municipalities FROM tblsdn"))['total_municipalities'];
$activeMun = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT locality) AS active_municipalities FROM tblfwfa WHERE status = 'Active'"))['active_municipalities'];
$lguRate = $totalMun > 0 ? round(($activeMun / $totalMun) * 100, 2) : 0;
echo "   Total municipalities (tblsdn): $totalMun\n";
echo "   Active municipalities (tblfwfa): $activeMun\n";
echo "   Rate: $lguRate%\n";

// Check distinct localities in tblfwfa
$distinctMun = mysqli_query($con, "SELECT DISTINCT locality FROM tblfwfa WHERE locality != '' AND locality IS NOT NULL ORDER BY locality");
$munList = [];
while ($r = mysqli_fetch_assoc($distinctMun)) $munList[] = $r['locality'];
echo "   Distinct localities in tblfwfa: " . count($munList) . "\n";
echo "   List: " . implode(', ', $munList) . "\n";

// Check tblsdn municipalities
$sdnMun = mysqli_query($con, "SELECT municipality FROM tblsdn ORDER BY municipality");
$sdnList = [];
while ($r = mysqli_fetch_assoc($sdnMun)) $sdnList[] = $r['municipality'];
echo "   Municipalities in tblsdn: " . count($sdnList) . "\n";
echo "   List: " . implode(', ', $sdnList) . "\n";

// Find mismatches
$inFwfaNotSdn = array_diff($munList, $sdnList);
$inSdnNotFwfa = array_diff($sdnList, $munList);
echo "   In fwfa but not in sdn: " . implode(', ', $inFwfaNotSdn) . "\n";
echo "   In sdn but not in fwfa: " . implode(', ', $inSdnNotFwfa) . "\n\n";

// 2. Barangay Penetration Rate
echo "2. Barangay Penetration Rate:\n";
$totalBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(barangay_count) AS total_barangay_count FROM tblsdn"))['total_barangay_count'];
$activeBrgy = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT barangay, locality) AS barangays_with_wifi FROM tblfwfa WHERE status = 'Active'"))['barangays_with_wifi'];
$brgyRate = $totalBrgy > 0 ? round(($activeBrgy / $totalBrgy) * 100, 2) : 0;
echo "   Total barangays (tblsdn SUM): $totalBrgy\n";
echo "   Active barangay+locality pairs (tblfwfa): $activeBrgy\n";
echo "   Rate: $brgyRate%\n\n";

// 3. Active Access Points
echo "3. Active Access Points:\n";
$active = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Active'"))['c'];
echo "   Active (status='Active'): $active\n";

// 4. Inactive Access Points
echo "4. Inactive Access Points:\n";
$inactive = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Inactive'"))['c'];
echo "   Inactive (status='Inactive'): $inactive\n\n";

// 5. Total rows vs Active + Inactive
echo "5. Row Count Analysis:\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "   Total rows: $total\n";
echo "   Active + Inactive: " . ($active + $inactive) . "\n";
echo "   Difference: " . ($total - $active - $inactive) . "\n";

// Show other statuses
$otherStatuses = mysqli_query($con, "SELECT status, COUNT(*) as c FROM tblfwfa WHERE status NOT IN ('Active','Inactive') AND status != '' GROUP BY status");
echo "   Other statuses:\n";
while ($r = mysqli_fetch_assoc($otherStatuses)) {
    echo "     {$r['status']}: {$r['c']}\n";
}
echo "\n";

// 6. Status values in DB
echo "6. All Status Values:\n";
$allStatus = mysqli_query($con, "SELECT status, COUNT(*) as c FROM tblfwfa GROUP BY status");
while ($r = mysqli_fetch_assoc($allStatus)) {
    echo "   '{$r['status']}': {$r['c']}\n";
}
echo "\n";

// 7. Item No. analysis
echo "7. Item No. Analysis:\n";
$items = mysqli_query($con, "SELECT item_no FROM tblfwfa WHERE item_no IS NOT NULL ORDER BY item_no");
$itemNos = [];
while ($r = mysqli_fetch_assoc($items)) $itemNos[] = $r['item_no'];
echo "   Rows with item_no: " . count($itemNos) . "\n";
echo "   Min: " . min($itemNos) . ", Max: " . max($itemNos) . "\n";
$dupes = array_count_values($itemNos);
$dupes = array_filter($dupes, fn($c) => $c > 1);
echo "   Duplicate item_nos: " . count($dupes) . "\n";
if ($dupes) print_r($dupes);

// Check for gaps
$expected = range(1, max($itemNos));
$missing = array_diff($expected, $itemNos);
echo "   Missing numbers (1-" . max($itemNos) . "): " . count($missing) . "\n";
if (count($missing) < 50) echo "   Missing: " . implode(', ', array_slice($missing, 0, 20)) . "\n";