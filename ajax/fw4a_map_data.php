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
$type     = isset($_GET['type']) ? trim($_GET['type']) : '';
$locality = isset($_GET['locality']) ? trim($_GET['locality']) : '';
$barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : '';

$where = ["latitude IS NOT NULL", "longitude IS NOT NULL", "latitude != 0", "longitude != 0"];
$params = [];
$types  = '';

if ($strategy !== '') {
    $where[] = "strategy = ?";
    $params[] = $strategy;
    $types .= 's';
}
if ($type !== '') {
    $where[] = "type = ?";
    $params[] = $type;
    $types .= 's';
}
if ($locality !== '') {
    $where[] = "locality = ?";
    $params[] = $locality;
    $types .= 's';
}
if ($barangay !== '') {
    $where[] = "barangay = ?";
    $params[] = $barangay;
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

$query = "SELECT id, latitude, longitude, locality, barangay, district,
                 locations, type, code, nationwide_id, strategy, status, remarks
          FROM tblfwfa
          WHERE $whereClause
          ORDER BY locality ASC";

$stmt = mysqli_prepare($con, $query);
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$points = [];
while ($row = mysqli_fetch_assoc($result)) {
    $points[] = [
        'id'            => intval($row['id']),
        'lat'           => floatval($row['latitude']),
        'lng'           => floatval($row['longitude']),
        'locality'      => $row['locality'],
        'barangay'      => $row['barangay'],
        'district'      => $row['district'],
        'locations'     => $row['locations'],
        'type'          => $row['type'],
        'code'          => $row['code'],
        'nationwide_id' => $row['nationwide_id'],
        'strategy'      => $row['strategy'],
        'status'        => $row['status'],
        'remarks'       => $row['remarks']
    ];
}
mysqli_stmt_close($stmt);

echo json_encode([
    'points' => $points,
    'total'  => count($points)
]);
