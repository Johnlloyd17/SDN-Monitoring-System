<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

include '../pages/connection.php';

// Values to exclude from filter dropdowns (header rows, garbage data)
$FILTER_EXCLUDE = [
    'strategy' => ['Strategy'],
    'site_type' => ['Site Type'],
    'locality' => ['Locality'],
    'barangay' => ['Barangay'],
    'transport_type' => ['Transport Type'],
    'transport_location' => ['Transport Location'],
    'site_locations' => ['Site Locations', 'Locations'],
    'site_code' => ['Site Code'],
    'nationwide_id' => ['Nationwide ID'],
    'status' => ['Status'],
    'district' => ['District'],
];

function getSimpleValues($con, $column, $filters = []) {
    global $FILTER_EXCLUDE;
    
    $where = ["locality != ''", "$column IS NOT NULL", "$column != ''"];
    $params = [];
    $types = '';
    
    // Add exclusions for this column
    $exclusions = $FILTER_EXCLUDE[$column] ?? [];
    foreach ($exclusions as $excl) {
        $where[] = "$column != ?";
        $params[] = $excl;
        $types .= 's';
    }
    
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
    'strategies' => getSimpleValues($con, 'strategy', ['site_type'=>$type, 'locality'=>$locality, 'barangay'=>$barangay]),
    'types' => getSimpleValues($con, 'site_type', ['strategy'=>$strategy, 'locality'=>$locality, 'barangay'=>$barangay]),
    'localities' => getSimpleValues($con, 'locality', ['strategy'=>$strategy, 'site_type'=>$type, 'barangay'=>$barangay]),
    'barangays' => getSimpleValues($con, 'barangay', ['strategy'=>$strategy, 'site_type'=>$type, 'locality'=>$locality])
]);
