<?php
session_start();
if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../connection.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'item' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($con, "SELECT * FROM targets_initiatives WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}

if ($action === 'photos' && isset($_GET['id'])) {
    echo json_encode([]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
