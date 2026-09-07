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

if (isset($_GET['stats']) && $_GET['stats'] === '1') {
    $res = mysqli_query($con, "SELECT
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS paid,
        SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS unpaid,
        COUNT(*) AS total,
        COALESCE(SUM(amount), 0) AS amount_total,
        COALESCE(SUM(CASE WHEN status = 1 THEN amount END), 0) AS amount_paid,
        COALESCE(SUM(CASE WHEN status = 0 THEN amount END), 0) AS amount_unpaid
        FROM bills_monitoring");
    $billStats = ['paid' => 0, 'unpaid' => 0];
    $billAmounts = ['paid' => 0.0, 'unpaid' => 0.0];
    $total = 0;
    $amountTotal = 0.0;
    if ($res) {
        $cr = mysqli_fetch_assoc($res);
        $billStats['paid'] = (int)$cr['paid'];
        $billStats['unpaid'] = (int)$cr['unpaid'];
        $total = (int)$cr['total'];
        $billAmounts['paid'] = (float)$cr['amount_paid'];
        $billAmounts['unpaid'] = (float)$cr['amount_unpaid'];
        $amountTotal = (float)$cr['amount_total'];
    }

    $typeLabels = [];
    $typeData = [];
    $typeAmounts = [];
    $tq = mysqli_query($con, "SELECT type_of_billing, COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS amt FROM bills_monitoring WHERE type_of_billing IS NOT NULL AND type_of_billing != '' GROUP BY type_of_billing ORDER BY amt DESC, cnt DESC");
    while ($tr = mysqli_fetch_assoc($tq)) {
        $typeLabels[] = $tr['type_of_billing'];
        $typeData[] = (int)$tr['cnt'];
        $typeAmounts[] = (float)$tr['amt'];
    }

    echo json_encode([
        'total' => $total,
        'amountTotal' => $amountTotal,
        'billStats' => $billStats,
        'billAmounts' => $billAmounts,
        'typeLabels' => $typeLabels,
        'typeData' => $typeData,
        'typeAmounts' => $typeAmounts
    ]);
    exit;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? min(200, max(5, intval($_GET['per_page']))) : 5;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$typeBilling = isset($_GET['type_of_billing']) ? trim($_GET['type_of_billing']) : '';
$locationOffice = isset($_GET['location_office']) ? trim($_GET['location_office']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'date_received';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

$allowedSorts = ['date_received','type_of_billing','amount','location_office','due_date','disconnection_date','status','date_paid','remarks'];
if (!in_array($sort, $allowedSorts)) $sort = 'date_received';
if (!in_array($order, ['ASC','DESC'])) $order = 'DESC';

$where = ["1=1"];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(type_of_billing LIKE ? OR location_office LIKE ? OR remarks LIKE ? OR link_to_file LIKE ? OR link_to_or LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s,$s]);
    $types .= 'sssss';
}
if ($status === '1' || $status === '0') {
    $where[] = "status = ?";
    $params[] = (int)$status;
    $types .= 'i';
}
if ($typeBilling !== '') {
    $allowed = ['Water Bill','Internet Bill','Electricity Bill'];
    if (in_array($typeBilling, $allowed)) {
        $where[] = "type_of_billing = ?";
        $params[] = $typeBilling;
        $types .= 's';
    }
}
if ($locationOffice !== '') {
    if ($locationOffice === '__none__') {
        $where[] = "location_office IS NULL";
    } else {
        $allowed = ['SDN Provincial Office','SDN Hill Relay Station'];
        if (in_array($locationOffice, $allowed)) {
            $where[] = "location_office = ?";
            $params[] = $locationOffice;
            $types .= 's';
        }
    }
}
if ($dateFrom !== '') {
    $where[] = "date_received >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if ($dateTo !== '') {
    $where[] = "date_received <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

if (isset($_GET['get_ids']) && $_GET['get_ids'] === '1') {
    $idQuery = "SELECT id FROM bills_monitoring WHERE $whereClause";
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

$countQuery = "SELECT COUNT(*) AS total FROM bills_monitoring WHERE $whereClause";
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

$dataQuery = "SELECT id, date_received, type_of_billing, link_to_file, amount,
              location_office, due_date, disconnection_date, status, date_paid,
              remarks, link_to_or
              FROM bills_monitoring WHERE $whereClause ORDER BY $sort $order LIMIT ? OFFSET ?";
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
    $row['amount'] = $row['amount'] !== null ? number_format((float)$row['amount'], 2) : '';
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
