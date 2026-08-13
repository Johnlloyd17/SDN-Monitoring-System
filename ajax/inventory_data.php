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
$project = isset($_GET['project']) ? trim($_GET['project']) : '';
$ics = isset($_GET['ics']) ? trim($_GET['ics']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$remarks = isset($_GET['remarks']) ? trim($_GET['remarks']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'project';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

$allowedSorts = ['project','item','classification','quantity','unit','description','received','property','ics','serial','date','officer','cost','life','transferred','remarks'];
if (!in_array($sort, $allowedSorts)) $sort = 'project';
if (!in_array($order, ['ASC','DESC'])) $order = 'DESC';

$where = ["project != ''"];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(project LIKE ? OR item LIKE ? OR classification LIKE ? OR description LIKE ? OR property LIKE ? OR ics LIKE ? OR serial LIKE ? OR officer LIKE ? OR remarks LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s,$s,$s,$s,$s,$s]);
    $types .= str_repeat('s', 9);
}
if ($project !== '') {
    $where[] = "project = ?";
    $params[] = $project;
    $types .= 's';
}
if ($ics !== '') {
    $where[] = "ics = ?";
    $params[] = $ics;
    $types .= 's';
}
if ($year !== '') {
    $where[] = "YEAR(date) = ?";
    $params[] = $year;
    $types .= 's';
}
if ($remarks !== '') {
    $where[] = "remarks = ?";
    $params[] = $remarks;
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

$countQuery = "SELECT COUNT(*) AS total FROM inventory WHERE $whereClause";
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

$dataQuery = "SELECT * FROM inventory WHERE $whereClause ORDER BY $sort $order LIMIT ? OFFSET ?";
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
