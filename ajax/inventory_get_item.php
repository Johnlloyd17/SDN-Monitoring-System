<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

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

if ($action === 'group' && isset($_GET['description'])) {
    $description = trim($_GET['description']);
    $unit = isset($_GET['unit']) ? trim($_GET['unit']) : '';
    if ($description === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Description is required']);
        exit;
    }
    $archetypeId = isset($_GET['archetype_id']) ? intval($_GET['archetype_id']) : 0;

    $stmt = mysqli_prepare($con, "SELECT id, project, item, quantity, unit, description, received, inventory_item_no, assigned_to, serial, date, cost, life, remarks FROM inventory WHERE BINARY TRIM(description) = ? AND BINARY TRIM(unit) = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'ss', $description, $unit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $members = [];
    $archetype = null;
    while ($row = mysqli_fetch_assoc($result)) {
        $member = array(
            'id' => intval($row['id']),
            'project' => $row['project'],
            'item' => $row['item'],
            'quantity' => $row['quantity'],
            'unit' => $row['unit'],
            'description' => $row['description'],
            'received' => $row['received'],
            'inventory_item_no' => $row['inventory_item_no'],
            'assigned_to' => $row['assigned_to'],
            'serial' => $row['serial'],
            'date' => $row['date'],
            'cost' => $row['cost'],
            'life' => $row['life'],
            'remarks' => $row['remarks'],
        );
        if ($archetypeId > 0 && $row['id'] == $archetypeId) {
            $archetype = $member;
        }
        $members[] = $member;
    }
    mysqli_stmt_close($stmt);

    if ($archetype === null && count($members) > 0) {
        $archetype = $members[0];
    }

    echo json_encode([
        'description' => $description,
        'unit' => $unit,
        'count' => count($members),
        'archetype' => $archetype,
        'members' => $members,
    ]);
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

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
