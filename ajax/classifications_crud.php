<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$isStaff = (isset($_SESSION['role']) && $_SESSION['role'] === 'staff');
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

function cls_json_die($payload) {
    echo json_encode($payload);
    exit;
}

function cls_log($con, $msg) {
    $msg = mysqli_real_escape_string($con, $msg);
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '$msg')");
}

if ($action === 'list') {
    $rows = [];
    $res = mysqli_query($con, "SELECT c.id, c.category, c.description, c.status, c.date_created, DATE_FORMAT(c.date_created, '%Y-%m-%d %H:%i:%s') AS date_created_fmt
        FROM classifications c
        ORDER BY c.category ASC");

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    cls_json_die(['success' => true, 'data' => $rows]);
}

if ($action === 'add') {
    if ($isStaff) {
        cls_json_die(['success' => false, 'error' => 'Staff are not allowed to add classifications.']);
    }

    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $status = (($_POST['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active';

    if ($name === '') {
        cls_json_die(['success' => false, 'error' => 'Classification name is required.']);
    }

    $safeName = mysqli_real_escape_string($con, $name);
    $check = mysqli_query($con, "SELECT id FROM classifications WHERE category = '$safeName'");
    if ($check && mysqli_num_rows($check) > 0) {
        cls_json_die(['success' => false, 'error' => 'A classification named "' . $name . '" already exists.']);
    }

    $q = "INSERT INTO classifications (category, sub_item, description, status)
          VALUES ('$safeName', '', '" . mysqli_real_escape_string($con, $desc) . "', '$status')";

    if (mysqli_query($con, $q)) {
        cls_log($con, 'Added Classification: ' . $name);
        cls_json_die(['success' => true, 'message' => 'Classification added successfully.']);
    }
    cls_json_die(['success' => false, 'error' => 'Failed to add classification: ' . mysqli_error($con)]);
}

if ($action === 'edit') {
    if ($isStaff) {
        cls_json_die(['success' => false, 'error' => 'Staff are not allowed to edit classifications.']);
    }

    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $status = (($_POST['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active';

    if ($id <= 0) {
        cls_json_die(['success' => false, 'error' => 'Invalid classification.']);
    }
    if ($name === '') {
        cls_json_die(['success' => false, 'error' => 'Classification name is required.']);
    }

    $oldRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT category FROM classifications WHERE id = $id"));
    if (!$oldRow) {
        cls_json_die(['success' => false, 'error' => 'Classification not found.']);
    }
    $oldName = $oldRow['category'];

    $safeName = mysqli_real_escape_string($con, $name);
    $check = mysqli_query($con, "SELECT id FROM classifications WHERE category = '$safeName' AND id != $id");
    if ($check && mysqli_num_rows($check) > 0) {
        cls_json_die(['success' => false, 'error' => 'A classification named "' . $name . '" already exists.']);
    }

    $q = "UPDATE classifications SET category = '$safeName', description = '" . mysqli_real_escape_string($con, $desc) . "', status = '$status' WHERE id = $id";

    if (mysqli_query($con, $q)) {
        cls_log($con, 'Edited Classification: ' . $name);
        cls_json_die(['success' => true, 'message' => 'Classification updated successfully.']);
    }
    cls_json_die(['success' => false, 'error' => 'Failed to update classification: ' . mysqli_error($con)]);
}

if ($action === 'delete') {
    if ($isStaff) {
        cls_json_die(['success' => false, 'error' => 'Staff are not allowed to delete classifications.']);
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        cls_json_die(['success' => false, 'error' => 'Invalid classification.']);
    }

    $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT category FROM classifications WHERE id = $id"));
    if (!$row) {
        cls_json_die(['success' => false, 'error' => 'Classification not found.']);
    }
    $name = $row['category'];
    $safeName = mysqli_real_escape_string($con, $name);

    if (mysqli_query($con, "DELETE FROM classifications WHERE id = $id")) {
        cls_log($con, 'Deleted Classification: ' . $name);
        cls_json_die(['success' => true, 'message' => 'Classification deleted successfully.']);
    }
    cls_json_die(['success' => false, 'error' => 'Failed to delete classification: ' . mysqli_error($con)]);
}

http_response_code(400);
cls_json_die(['success' => false, 'error' => 'Invalid action']);