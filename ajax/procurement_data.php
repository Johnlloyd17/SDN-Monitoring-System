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
$projectFundSource = isset($_GET['project_fund_source']) ? trim($_GET['project_fund_source']) : '';
$paymentStatus = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'id';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

$allowedSorts = ['pr_no','activity_id','activity_name','project_fund_source','type_of_items_procured','amount','name_of_supplier','jo_po','personnel_in_charge','date_forwarded_to_ro','transmittal_report','payment_status','remarks','id'];
if (!in_array($sort, $allowedSorts)) $sort = 'id';
if (!in_array($order, ['ASC','DESC'])) $order = 'DESC';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(pr_no LIKE ? OR activity_id LIKE ? OR activity_name LIKE ? OR project_fund_source LIKE ? OR type_of_items_procured LIKE ? OR name_of_supplier LIKE ? OR jo_po LIKE ? OR personnel_in_charge LIKE ? OR transmittal_report LIKE ? OR payment_status LIKE ? OR remarks LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s]);
    $types .= str_repeat('s', 11);
}
if ($projectFundSource !== '') {
    $where[] = "project_fund_source = ?";
    $params[] = $projectFundSource;
    $types .= 's';
}
if ($paymentStatus !== '') {
    $where[] = "payment_status = ?";
    $params[] = $paymentStatus;
    $types .= 's';
}
if ($year !== '') {
    $where[] = "YEAR(date_forwarded_to_ro) = ?";
    $params[] = $year;
    $types .= 's';
}

$whereClause = count($where) > 0 ? implode(' AND ', $where) : '1=1';

$countQuery = "SELECT COUNT(*) AS total FROM procurement_tracking WHERE $whereClause";
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

$dataQuery = "SELECT * FROM procurement_tracking WHERE $whereClause ORDER BY $sort $order LIMIT ? OFFSET ?";
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
