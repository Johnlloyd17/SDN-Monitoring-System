<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'item' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($con, "SELECT * FROM inventory WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
    }
    exit;
}

if ($action === 'photos' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($con, "SELECT * FROM tblactivityphoto WHERE activityid = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $photos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $filePath = "photo/" . basename($row['filename']);
        $ext = strtolower(pathinfo($row['filename'], PATHINFO_EXTENSION));
        $row['filepath'] = $filePath;
        $row['type'] = $ext;
        $photos[] = $row;
    }
    mysqli_stmt_close($stmt);

    echo json_encode($photos);
    exit;
}

if ($action === 'classifications') {
    $result = mysqli_query($con, "SELECT category, sub_item FROM classifications WHERE status = 'active' ORDER BY category ASC, sub_item ASC");
    $classifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $classifications[] = $row;
    }
    echo json_encode($classifications);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
