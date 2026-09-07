<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$query = "SELECT id, date_received, type_of_billing, link_to_file, amount,
          location_office, due_date, disconnection_date, status, date_paid,
          remarks, link_to_or
          FROM bills_monitoring WHERE id = $id LIMIT 1";
$result = mysqli_query($con, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    $row['amount'] = $row['amount'] !== null ? rtrim(rtrim(number_format((float)$row['amount'], 2, '.', ''), '0'), '.') : '';
    $row['status'] = (int)$row['status'];
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Not found']);
}
