<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$photoDir = __DIR__ . '/../pages/property_records/photo/';
if (!file_exists($photoDir)) {
    mkdir($photoDir, 0777, true);
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'add') {
    $project = mysqli_real_escape_string($con, $_POST['txt_project'] ?? '');
    $item = mysqli_real_escape_string($con, $_POST['txt_item'] ?? '');
    $classification = mysqli_real_escape_string($con, $_POST['txt_classification'] ?? '');
    $quantity = mysqli_real_escape_string($con, $_POST['txt_quantity'] ?? '');
    $unit = mysqli_real_escape_string($con, $_POST['txt_unit'] ?? '');
    $description = mysqli_real_escape_string($con, $_POST['txt_description'] ?? '');
    $received = mysqli_real_escape_string($con, $_POST['txt_received'] ?? '');
    $property = mysqli_real_escape_string($con, $_POST['txt_property'] ?? '');
    $ics = mysqli_real_escape_string($con, $_POST['txt_ics'] ?? '');
    $serial = mysqli_real_escape_string($con, $_POST['txt_serial'] ?? '');
    $date = mysqli_real_escape_string($con, $_POST['txt_date'] ?? '');
    $officer = mysqli_real_escape_string($con, $_POST['txt_officer'] ?? '');
    $cost = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_cost'] ?? ''));
    $life = mysqli_real_escape_string($con, $_POST['txt_life'] ?? '');
    $transferred = mysqli_real_escape_string($con, $_POST['txt_transferred'] ?? '');
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks'] ?? '');
    $status = mysqli_real_escape_string($con, $_POST['txt_status'] ?? 'Available');
    if (!in_array($status, ['Available','For Deployment','Deployed','Temporary Deployed','Defective','Replaced'])) {
        $status = 'Available';
    }

    $action_log = 'Added Item:' . $description;
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");

    $query = "INSERT INTO inventory (project, item, classification, quantity, unit, description, received, property, ics, serial, date, officer, cost, life, transferred, remarks, status) VALUES ('$project', '$item', '$classification', '$quantity', '$unit', '$description', '$received', '$property', '$ics', '$serial', '$date', '$officer', '$cost', '$life', '$transferred', '$remarks', '$status')";

    if (mysqli_query($con, $query)) {
        $id = mysqli_insert_id($con);

        if (isset($_FILES['files']) && $_FILES['files']['error'][0] !== UPLOAD_ERR_NO_FILE) {
            foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                if ($tmp_name === '') continue;
                $milliseconds = round(microtime(true) * 1000);
                $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['files']['name'][$key]);
                $target = $photoDir . $name;
                if (move_uploaded_file($tmp_name, $target)) {
                    mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES ('$id', '$name')");
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Item added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add item: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    $project = mysqli_real_escape_string($con, $_POST['txt_edit_project'] ?? '');
    $item = mysqli_real_escape_string($con, $_POST['txt_edit_item'] ?? '');
    $classification = mysqli_real_escape_string($con, $_POST['txt_edit_classification'] ?? '');
    $quantity = mysqli_real_escape_string($con, $_POST['txt_edit_quantity'] ?? '');
    $unit = mysqli_real_escape_string($con, $_POST['txt_edit_unit'] ?? '');
    $description = mysqli_real_escape_string($con, $_POST['txt_edit_description'] ?? '');
    $received = mysqli_real_escape_string($con, $_POST['txt_edit_received'] ?? '');
    $property = mysqli_real_escape_string($con, $_POST['txt_edit_property'] ?? '');
    $ics = mysqli_real_escape_string($con, $_POST['txt_edit_ics'] ?? '');
    $serial = mysqli_real_escape_string($con, $_POST['txt_edit_serial'] ?? '');
    $date = mysqli_real_escape_string($con, $_POST['txt_edit_date'] ?? '');
    $officer = mysqli_real_escape_string($con, $_POST['txt_edit_officer'] ?? '');
    $cost = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_edit_cost'] ?? ''));
    $life = mysqli_real_escape_string($con, $_POST['txt_edit_life'] ?? '');
    $transferred = mysqli_real_escape_string($con, $_POST['txt_edit_transferred'] ?? '');
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks'] ?? '');
    $status = mysqli_real_escape_string($con, $_POST['txt_edit_status'] ?? 'Available');
    if (!in_array($status, ['Available','For Deployment','Deployed','Temporary Deployed','Defective','Replaced'])) {
        $status = 'Available';
    }

    $query = "UPDATE inventory SET project='$project', item='$item', classification='$classification', quantity='$quantity', unit='$unit', description='$description', received='$received', property='$property', ics='$ics', serial='$serial', date='$date', officer='$officer', cost='$cost', life='$life', transferred='$transferred', remarks='$remarks', status='$status' WHERE id=$id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Edited Item: ' . $description;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update item: ' . mysqli_error($con)]);
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

        $itemQ = mysqli_query($con, "SELECT description FROM inventory WHERE id = $id");
        $itemRow = mysqli_fetch_assoc($itemQ);
        $itemName = $itemRow ? $itemRow['description'] : 'Unknown';

        if (mysqli_query($con, "DELETE FROM inventory WHERE id = $id")) {
            $action_log = 'Deleted Item: ' . $itemName;
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => "$deleted item(s) deleted"]);
    exit;
}

if ($action === 'add_photo') {
    $id = intval($_POST['hidden_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
        exit;
    }

    $added = 0;
    if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($tmp_name === '') continue;
            $milliseconds = round(microtime(true) * 1000);
            $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['photos']['name'][$key]);
            $target = $photoDir . $name;
            if (move_uploaded_file($tmp_name, $target)) {
                mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES ('$id', '$name')");
                $added++;
            }
        }
    }

    echo json_encode(['success' => $added > 0, 'added' => $added, 'message' => "$added file(s) uploaded"]);
    exit;
}

if ($action === 'remove_photos') {
    $photoIds = isset($_POST['photo_ids']) ? $_POST['photo_ids'] : [];
    if (!is_array($photoIds)) $photoIds = [$photoIds];

    $removed = 0;
    foreach ($photoIds as $rawId) {
        $pid = intval($rawId);
        if ($pid <= 0) continue;

        $fileQ = mysqli_query($con, "SELECT filename FROM tblactivityphoto WHERE id = $pid");
        $fileRow = mysqli_fetch_assoc($fileQ);
        if ($fileRow) {
            $filePath = $photoDir . $fileRow['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            mysqli_query($con, "DELETE FROM tblactivityphoto WHERE id = $pid");
            $removed++;
        }
    }

    echo json_encode(['success' => $removed > 0, 'removed' => $removed, 'message' => "$removed file(s) removed"]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
