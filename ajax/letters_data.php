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
    $counts = array(
        'needs_response' => 0, 'responded' => 0,
        'resp_yes' => 0, 'resp_no' => 0, 'not_specified' => 0,
        'request' => 0, 'provision' => 0
    );
    $res = mysqli_query($con, "SELECT
        SUM(CASE WHEN for_response = 'Y' AND date_responded IS NULL THEN 1 ELSE 0 END) AS needs_response,
        SUM(CASE WHEN date_responded IS NOT NULL THEN 1 ELSE 0 END) AS responded,
        SUM(CASE WHEN for_response = 'Y' THEN 1 ELSE 0 END) AS resp_yes,
        SUM(CASE WHEN for_response = 'N' THEN 1 ELSE 0 END) AS resp_no,
        SUM(CASE WHEN for_response IS NULL OR for_response = '' THEN 1 ELSE 0 END) AS not_specified,
        SUM(CASE WHEN fw4a = 'Request' THEN 1 ELSE 0 END) AS request,
        SUM(CASE WHEN fw4a = 'Provision' THEN 1 ELSE 0 END) AS provision,
        COUNT(*) AS total
        FROM letters_monitoring");
    if ($res) {
        $cr = mysqli_fetch_assoc($res);
        foreach ($counts as $k => $v) $counts[$k] = (int)$cr[$k];
        $total = (int)$cr['total'];
    } else {
        $total = 0;
    }

    $typeLabels = [];
    $typeData = [];
    $tq = mysqli_query($con, "SELECT type, COUNT(*) AS total FROM letters_monitoring WHERE type IS NOT NULL AND type != '' GROUP BY type ORDER BY total DESC");
    while ($tr = mysqli_fetch_assoc($tq)) {
        $typeLabels[] = $tr['type'];
        $typeData[] = (int)$tr['total'];
    }

    $fw4aLabels = [];
    $fw4aData = [];
    $fq = mysqli_query($con, "SELECT fw4a, COUNT(*) AS total FROM letters_monitoring WHERE fw4a IS NOT NULL AND fw4a != '' GROUP BY fw4a ORDER BY total DESC");
    while ($fr = mysqli_fetch_assoc($fq)) {
        $fw4aLabels[] = $fr['fw4a'];
        $fw4aData[] = (int)$fr['total'];
    }

    echo json_encode([
        'total' => $total,
        'counts' => $counts,
        'typeLabels' => $typeLabels,
        'typeData' => $typeData,
        'fw4aLabels' => $fw4aLabels,
        'fw4aData' => $fw4aData
    ]);
    exit;
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? min(200, max(5, intval($_GET['per_page']))) : 5;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$forResponse = isset($_GET['for_response']) ? trim($_GET['for_response']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'date';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

$allowedSorts = ['date','type','subject','fw4a','for_response','date_responded','responsible_person'];
if (!in_array($sort, $allowedSorts)) $sort = 'date';
if (!in_array($order, ['ASC','DESC'])) $order = 'DESC';

$where = ["1=1"];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(subject LIKE ? OR responsible_person LIKE ? OR who_attended LIKE ? OR remarks LIKE ? OR link_incoming LIKE ? OR link_outgoing LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s,$s,$s]);
    $types .= 'ssssss';
}
if ($type === 'Incoming' || $type === 'Outgoing') {
    $where[] = "type = ?";
    $params[] = $type;
    $types .= 's';
}
if ($forResponse === 'Y' || $forResponse === 'N') {
    $where[] = "for_response = ?";
    $params[] = $forResponse;
    $types .= 's';
}
if ($dateFrom !== '') {
    $where[] = "date >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if ($dateTo !== '') {
    $where[] = "date <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$card = isset($_GET['card']) ? trim($_GET['card']) : '';
$knownCards = ['needs_response','responded','request','provision','not_specified'];
if ($card !== '' && in_array($card, $knownCards)) {
    if ($card === 'needs_response') {
        $where[] = "(for_response = 'Y' AND date_responded IS NULL)";
    } elseif ($card === 'responded') {
        $where[] = "date_responded IS NOT NULL";
    } elseif ($card === 'request') {
        $where[] = "fw4a = ?";
        $params[] = 'Request';
        $types .= 's';
    } elseif ($card === 'provision') {
        $where[] = "fw4a = ?";
        $params[] = 'Provision';
        $types .= 's';
    } elseif ($card === 'not_specified') {
        $where[] = "(for_response IS NULL OR for_response = '')";
    }
}

$whereClause = implode(' AND ', $where);

if (isset($_GET['get_ids']) && $_GET['get_ids'] === '1') {
    $idQuery = "SELECT id FROM letters_monitoring WHERE $whereClause";
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

$countQuery = "SELECT COUNT(*) AS total FROM letters_monitoring WHERE $whereClause";
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

$dataQuery = "SELECT id, date, type, subject, fw4a, link_incoming, for_response,
              date_responded, link_outgoing, responsible_person, who_attended,
              remarks, post_activity_report
              FROM letters_monitoring WHERE $whereClause ORDER BY $sort $order LIMIT ? OFFSET ?";
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
