<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? min(100, max(5, intval($_GET['per_page']))) : 25;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$project = isset($_GET['project']) ? trim($_GET['project']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$remarks = isset($_GET['remarks']) ? trim($_GET['remarks']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'project';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

$allowedSorts = ['project','item','quantity','unit','description','serial','cost','total_cost','date','received','inventory_item_no','assigned_to','life','remarks'];
if (!in_array($sort, $allowedSorts)) $sort = 'project';
if (!in_array($order, ['ASC','DESC'])) $order = 'DESC';

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(project LIKE ? OR item LIKE ? OR unit LIKE ? OR description LIKE ? OR serial LIKE ? OR inventory_item_no LIKE ? OR assigned_to LIKE ? OR remarks LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s,$s,$s,$s,$s,$s,$s,$s]);
    $types .= str_repeat('s', 8);
}
if ($project !== '') {
    $where[] = "project = ?";
    $params[] = $project;
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

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Collapsible groups: serial-bearing rows sharing the same Description + Unit, >1 member.
// Grouping is recomputed on the filtered result set, so search/filter operate at group level.
$whereAnd = $whereClause === '' ? 'WHERE ' : $whereClause . ' AND ';
$serialCond = "serial IS NOT NULL AND TRIM(serial) <> ''";
$noSerialCond = "(serial IS NULL OR TRIM(serial) = '')";

$gSql = "SELECT TRIM(description) AS gd, TRIM(unit) AS gu, COUNT(*) AS n, SUM(quantity) AS q, SUM(total_cost) AS tc, MAX(id) AS rep
         FROM inventory
         $whereAnd $serialCond
         GROUP BY TRIM(description), TRIM(unit)
         HAVING COUNT(*) > 1";

// G = summary rows (one per group, fields from newest member), D = serial-bearing singles, N = no-serial rows.
$unionSql = "SELECT m.id, m.project, m.item, g.q AS quantity, m.unit, m.description, m.received, m.inventory_item_no, m.assigned_to, m.serial, m.date, m.cost, g.tc AS total_cost, m.life, m.remarks, 1 AS is_group, g.n AS group_units, CONCAT(g.n, ' units · click to view') AS group_badge
    FROM ($gSql) g JOIN inventory m ON m.id = g.rep
  UNION ALL
    SELECT i.id, i.project, i.item, i.quantity, i.unit, i.description, i.received, i.inventory_item_no, i.assigned_to, i.serial, i.date, i.cost, i.total_cost, i.life, i.remarks, 0, NULL, NULL
    FROM inventory i
    $whereAnd $serialCond
    AND NOT EXISTS (
      SELECT 1 FROM ($gSql) g2
      WHERE (g2.gd <=> TRIM(i.description)) AND (g2.gu <=> TRIM(i.unit))
    )
  UNION ALL
    SELECT i.id, i.project, i.item, i.quantity, i.unit, i.description, i.received, i.inventory_item_no, i.assigned_to, i.serial, i.date, i.cost, i.total_cost, i.life, i.remarks, 0, NULL, NULL
    FROM inventory i
    $whereAnd $noSerialCond";

$countQuery = "SELECT COUNT(*) AS total FROM ($unionSql) dt";
$countStmt = mysqli_prepare($con, $countQuery);
if ($types !== '') {
    mysqli_stmt_bind_param($countStmt, str_repeat($types, 4), ...array_merge($params, $params, $params, $params));
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalRow = mysqli_fetch_assoc($countResult);
$total = intval($totalRow['total']);
$totalPages = max(1, ceil($total / $perPage));
mysqli_stmt_close($countStmt);

$dataQuery = "SELECT * FROM ($unionSql) dt ORDER BY $sort $order LIMIT ? OFFSET ?";
$dataTypes = ($types !== '' ? str_repeat($types, 4) : '') . 'ii';
$dataParams = ($types !== '' ? array_merge($params, $params, $params, $params) : []);
$dataParams = array_merge($dataParams, [$perPage, $offset]);
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