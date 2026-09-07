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

$query = "SELECT id, date, type, subject, fw4a, link_incoming, for_response,
          date_responded, link_outgoing, responsible_person, who_attended,
          remarks, post_activity_report
          FROM letters_monitoring WHERE id = $id LIMIT 1";
$result = mysqli_query($con, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Not found']);
}
