<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? min(100, max(5, intval($_GET['per_page']))) : 25;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = ["1=1"];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(user LIKE ? OR action LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s];
    $types = 'ss';
}

$whereClause = implode(' AND ', $where);

$countQuery = "SELECT COUNT(*) AS total FROM tbllogs WHERE $whereClause";
$countStmt = mysqli_prepare($con, $countQuery);
if ($types !== '') {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalRow = mysqli_fetch_assoc($countResult);
$total = intval($totalRow['total']);
$totalPages = max(1, ceil($total / $perPage));
mysqli_stmt_close($countStmt);

$dataQuery = "SELECT * FROM tbllogs WHERE $whereClause ORDER BY logdate DESC, id DESC LIMIT ? OFFSET ?";
$dataTypes = $types . 'ii';
$dataParams = array_merge($params, [$perPage, $offset]);
$dataStmt = mysqli_prepare($con, $dataQuery);
mysqli_stmt_bind_param($dataStmt, $dataTypes, ...$dataParams);
mysqli_stmt_execute($dataStmt);
$dataResult = mysqli_stmt_get_result($dataStmt);

$rows = [];
$counter = $offset + 1;
while ($row = mysqli_fetch_assoc($dataResult)) {
    $row['row_num'] = $counter++;
    $rows[] = $row;
}
mysqli_stmt_close($dataStmt);

echo json_encode([
    'data' => $rows,
    'total' => $total,
    'page' => $page,
    'per_page' => $perPage,
    'total_pages' => $totalPages
]);