<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

function getSimpleValues($con, $column, $filters = []) {
    $where = ["$column IS NOT NULL", "$column != ''"];
    $params = [];
    $types = '';

    foreach ($filters as $col => $val) {
        if ($val !== '' && $col !== 'year') {
            $where[] = "$col = ?";
            $params[] = $val;
            $types .= 's';
        }
    }

    $whereClause = implode(' AND ', $where);
    $query = "SELECT DISTINCT $column FROM inventory WHERE $whereClause ORDER BY $column ASC";

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

function getYearValues($con, $filters = []) {
    $where = ["date != ''", "date IS NOT NULL"];
    $params = [];
    $types = '';

    foreach ($filters as $col => $val) {
        if ($val !== '' && $col !== 'year') {
            $where[] = "$col = ?";
            $params[] = $val;
            $types .= 's';
        }
    }

    $whereClause = implode(' AND ', $where);
    $query = "SELECT DISTINCT YEAR(date) AS yr FROM inventory WHERE $whereClause ORDER BY yr ASC";

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
        if ($row['yr'] !== null) {
            $values[] = $row['yr'];
        }
    }
    return $values;
}

$project = isset($_GET['project']) ? trim($_GET['project']) : '';
$ics = isset($_GET['ics']) ? trim($_GET['ics']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$remarks = isset($_GET['remarks']) ? trim($_GET['remarks']) : '';

echo json_encode([
    'projects' => getSimpleValues($con, 'project', ['ics'=>$ics, 'year'=>$year, 'remarks'=>$remarks]),
    'ics_list' => getSimpleValues($con, 'ics', ['project'=>$project, 'year'=>$year, 'remarks'=>$remarks]),
    'years' => getYearValues($con, ['project'=>$project, 'ics'=>$ics, 'remarks'=>$remarks]),
    'remarks' => getSimpleValues($con, 'remarks', ['project'=>$project, 'ics'=>$ics, 'year'=>$year]),
    'classifications' => getSimpleValues($con, 'classification')
]);
