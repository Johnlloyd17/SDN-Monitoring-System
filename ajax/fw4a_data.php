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

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? min(200, max(5, intval($_GET['per_page']))) : 5;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$strategy = isset($_GET['strategy']) ? trim($_GET['strategy']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$locality = isset($_GET['locality']) ? trim($_GET['locality']) : '';
$barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'locality';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'ASC';

$allowedSorts = ['item_no','locality','barangay','district','transport_location','transport_type','site_locations','transfer_new_locations','site_code','nationwide_id','site_type','date_of_activation','current_date_of_acceptance','latitude','longitude','procurement_initiative','installation_type','uat','conforme','strategy','status','link_type','site_coordinator_name','contact_details','remarks'];
if (!in_array($sort, $allowedSorts)) $sort = 'locality';
if (!in_array($order, ['ASC','DESC'])) $order = 'ASC';

$where = ["locality != ''"];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(item_no LIKE ? OR locality LIKE ? OR barangay LIKE ? OR district LIKE ? OR transport_location LIKE ? OR transport_type LIKE ? OR site_locations LIKE ? OR transfer_new_locations LIKE ? OR site_code LIKE ? OR nationwide_id LIKE ? OR site_type LIKE ? OR procurement_initiative LIKE ? OR installation_type LIKE ? OR strategy LIKE ? OR status LIKE ? OR link_type LIKE ? OR site_coordinator_name LIKE ? OR contact_details LIKE ? OR remarks LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s,$s]);
    $types .= str_repeat('s', 19);
}
if ($strategy !== '') {
    $where[] = "strategy = ?";
    $params[] = $strategy;
    $types .= 's';
}
if ($type !== '') {
    $where[] = "site_type = ?";
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

if (isset($_GET['get_ids']) && $_GET['get_ids'] === '1') {
    $idQuery = "SELECT id FROM tblfwfa WHERE $whereClause";
    $idStmt = mysqli_prepare($con, $idQuery);
    if ($types !== '') {
        mysqli_stmt_bind_param($idStmt, $types, ...$params);
    }
    mysqli_stmt_execute($idStmt);
    $idResult = mysqli_stmt_get_result($idStmt);
    $ids = [];
    while ($r = mysqli_fetch_assoc($idResult)) {
        $ids[] = intval($r['id']);
    }
    mysqli_stmt_close($idStmt);
    echo json_encode(['ids' => $ids]);
    exit;
}

$countQuery = "SELECT COUNT(*) AS total FROM tblfwfa WHERE $whereClause";
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

$dataQuery = "SELECT * FROM tblfwfa WHERE $whereClause ORDER BY $sort $order LIMIT ? OFFSET ?";
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
