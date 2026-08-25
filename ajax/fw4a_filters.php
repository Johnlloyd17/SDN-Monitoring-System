<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

function getSimpleValues($con, $column, $filters = []) {
    $where = ["locality != ''", "$column IS NOT NULL", "$column != ''"];
    $params = [];
    $types = '';

    foreach ($filters as $col => $val) {
        if ($val !== '' && $col !== $column) {
            $where[] = "$col = ?";
            $params[] = $val;
            $types .= 's';
        }
    }

    $whereClause = implode(' AND ', $where);
    $query = "SELECT DISTINCT $column FROM tblfwfa WHERE $whereClause ORDER BY $column ASC";

    if (!empty($params)) {
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $result = mysqli_query($con, $query);
    }

    $values = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $val = reset($row);
        if ($val !== null && trim($val) !== '') {
            $values[] = $val;
        }
    }
    return $values;
}

$strategy = isset($_GET['strategy']) ? trim($_GET['strategy']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$locality = isset($_GET['locality']) ? trim($_GET['locality']) : '';
$barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : '';

echo json_encode([
    'strategies' => getSimpleValues($con, 'strategy', ['type'=>$type, 'locality'=>$locality, 'barangay'=>$barangay]),
    'types' => getSimpleValues($con, 'type', ['strategy'=>$strategy, 'locality'=>$locality, 'barangay'=>$barangay]),
    'localities' => getSimpleValues($con, 'locality', ['strategy'=>$strategy, 'type'=>$type, 'barangay'=>$barangay]),
    'barangays' => getSimpleValues($con, 'barangay', ['strategy'=>$strategy, 'type'=>$type, 'locality'=>$locality])
]);
