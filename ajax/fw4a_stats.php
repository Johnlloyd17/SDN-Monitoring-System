<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$strategy = isset($_GET['strategy']) ? trim($_GET['strategy']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$locality = isset($_GET['locality']) ? trim($_GET['locality']) : '';
$barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : '';

$strategyF = $strategy !== '' ? " AND strategy = '" . mysqli_real_escape_string($con, $strategy) . "'" : '';
$typeF = $type !== '' ? " AND type = '" . mysqli_real_escape_string($con, $type) . "'" : '';
$localityF = $locality !== '' ? " AND locality = '" . mysqli_real_escape_string($con, $locality) . "'" : '';
$barangayF = $barangay !== '' ? " AND barangay = '" . mysqli_real_escape_string($con, $barangay) . "'" : '';

$q = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Active'" . $strategyF . $typeF . $localityF . $barangayF);
$active = (int) mysqli_fetch_assoc($q)['c'];
$q = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE status = 'Inactive'" . $strategyF . $typeF . $localityF . $barangayF);
$inactive = (int) mysqli_fetch_assoc($q)['c'];

if ($locality !== '') {
    $wifiResult = mysqli_query($con, "SELECT COUNT(DISTINCT barangay) AS barangay_with_wifi FROM tblfwfa WHERE locality = '" . mysqli_real_escape_string($con, $locality) . "' AND status = 'Active'");
    $wifiCount = (int) mysqli_fetch_assoc($wifiResult)['barangay_with_wifi'];
    $totalResult = mysqli_query($con, "SELECT barangay_count FROM tblsdn WHERE municipality = '" . mysqli_real_escape_string($con, $locality) . "'");
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalCount = $totalRow ? (int) $totalRow['barangay_count'] : 0;
    $lgu = $totalCount > 0 ? number_format(($wifiCount / $totalCount) * 100, 2) . '%' : 'N/A';
} else {
    $totalResult = mysqli_query($con, "SELECT COUNT(*) AS total_municipalities FROM tblsdn");
    $totalCount = (int) mysqli_fetch_assoc($totalResult)['total_municipalities'];
    $activeResult = mysqli_query($con, "SELECT COUNT(DISTINCT locality) AS active_municipalities FROM tblfwfa WHERE status = 'Active'");
    $activeCount = (int) mysqli_fetch_assoc($activeResult)['active_municipalities'];
    $lgu = $totalCount > 0 ? number_format(($activeCount / $totalCount) * 100, 2) . '%' : 'N/A';
}

if ($barangay !== '') {
    $brgy = 'N/A';
} else {
    $wifiResult = mysqli_query($con, "SELECT COUNT(DISTINCT barangay, locality) AS barangays_with_wifi FROM tblfwfa WHERE status = 'Active'");
    $wifiCount = (int) mysqli_fetch_assoc($wifiResult)['barangays_with_wifi'];
    $totalResult = mysqli_query($con, "SELECT SUM(barangay_count) AS total_barangay_count FROM tblsdn");
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalCount = $totalRow ? (int) $totalRow['total_barangay_count'] : 0;
    $brgy = $totalCount > 0 ? number_format(($wifiCount / $totalCount) * 100, 2) . '%' : 'N/A';
}

echo json_encode([
    'penetration' => $lgu,
    'barangay' => $brgy,
    'active' => $active,
    'inactive' => $inactive
]);
