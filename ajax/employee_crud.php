<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'add') {
    $full_name = trim($_POST['full_name'] ?? '');
    if ($full_name === '') {
        echo json_encode(['success' => false, 'error' => 'Full Name is required.']);
        exit;
    }

    $name = mysqli_real_escape_string($con, $full_name);

    $dupQ = mysqli_query($con, "SELECT 1 FROM employees WHERE full_name = '$name' LIMIT 1");
    if ($dupQ && mysqli_num_rows($dupQ) > 0) {
        echo json_encode(['success' => false, 'error' => 'An employee with this exact name already exists. Use Edit to update it instead.']);
        exit;
    }

    if (mysqli_query($con, "INSERT INTO employees (full_name) VALUES ('$name')")) {
        $eid = mysqli_insert_id($con);
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), 'Added Employee: " . mysqli_real_escape_string($con, $full_name) . "')");
        echo json_encode(['success' => true, 'message' => 'Employee added successfully', 'id' => $eid]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add employee: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid employee record.']);
        exit;
    }
    $full_name = trim($_POST['full_name'] ?? '');
    if ($full_name === '') {
        echo json_encode(['success' => false, 'error' => 'Full Name is required.']);
        exit;
    }

    $name = mysqli_real_escape_string($con, $full_name);

    $dupQ = mysqli_query($con, "SELECT 1 FROM employees WHERE full_name = '$name' AND id != $id LIMIT 1");
    if ($dupQ && mysqli_num_rows($dupQ) > 0) {
        echo json_encode(['success' => false, 'error' => 'An employee with this exact name already exists.']);
        exit;
    }

    if (mysqli_query($con, "UPDATE employees SET full_name = '$name' WHERE id = $id")) {
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), 'Edited Employee: " . mysqli_real_escape_string($con, $full_name) . "')");
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update employee: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'delete') {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (!is_array($ids)) $ids = [$ids];

    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $empQ = mysqli_query($con, "SELECT full_name FROM employees WHERE id = $id");
        $empRow = mysqli_fetch_assoc($empQ);
        $empName = $empRow ? $empRow['full_name'] : 'Unknown';

        if (mysqli_query($con, "DELETE FROM employees WHERE id = $id")) {
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), 'Deleted Employee: " . mysqli_real_escape_string($con, $empName) . "')");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => "$deleted employee(s) deleted"]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);