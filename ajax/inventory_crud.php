<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$photoDir = __DIR__ . '/../pages/property_records/photo/';
if (!file_exists($photoDir)) {
    mkdir($photoDir, 0777, true);
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// Blank cells are normalized so they never corrupt the data:
//  - Project gets a placeholder (every list/stats/export query filters
//    `project != ''`, so a blank Project would otherwise make the record
//    invisible).
//  - Description gets a placeholder so downstream features (pass slip,
//    print sticker, file viewer) have a label.
//  - Quantity, Life, Cost, Date, etc. are stored as NULL instead of being
//    coerced to 0 / 0000-00-00 by MySQL.
$PROJECT_FALLBACK = 'Unspecified';
$DESCRIPTION_FALLBACK = '(No description)';

function normText($value) {
    $value = trim((string)$value);
    return $value === '' ? null : $value;
}
function normIntOrNull($value) {
    $value = trim((string)$value);
    if ($value === '') return null;
    $intValue = (int)$value;
    return $value == $intValue ? $intValue : $value;
}
function normDateOrNull($value) {
    $value = trim((string)$value);
    if ($value === '') return null;
    $dt = date_create($value);
    return $dt ? $dt->format('Y-m-d') : null;
}
function sqlVal($con, $value) {
    if ($value === null) return 'NULL';
    return "'" . mysqli_real_escape_string($con, $value) . "'";
}

if ($action === 'add') {
    $project = normText($_POST['txt_project'] ?? '');
    $project = $project === null ? $PROJECT_FALLBACK : $project;
    $item = normText($_POST['txt_item'] ?? '');
    $quantity = normIntOrNull($_POST['txt_quantity'] ?? '');
    $unit = normText($_POST['txt_unit'] ?? '');
    $description = normText($_POST['txt_description'] ?? '');
    $description = $description === null ? $DESCRIPTION_FALLBACK : $description;
    $received = normText($_POST['txt_received'] ?? '');
    $serial = normText($_POST['txt_serial'] ?? '');
    $date = normDateOrNull($_POST['txt_date'] ?? '');
    $cost = normText(str_replace(',', '', $_POST['txt_cost'] ?? ''));
    $inventoryItemNo = normText($_POST['txt_inventory_item_no'] ?? '');
    $assignedTo = normText($_POST['txt_assigned_to'] ?? '');
    $life = normIntOrNull($_POST['txt_life'] ?? '');
    $remarks = normText($_POST['txt_remarks'] ?? '');

    $serialTrimmed = $serial === null ? '' : $serial;
    if ($serialTrimmed !== '') {
        $checkQ = mysqli_query($con, "SELECT description FROM inventory WHERE serial_unique = '" . mysqli_real_escape_string($con, $serialTrimmed) . "' LIMIT 1");
        if ($checkQ && ($dupRow = mysqli_fetch_assoc($checkQ))) {
            $dupDesc = ($dupRow['description'] !== null && $dupRow['description'] !== '') ? $dupRow['description'] : 'an existing item';
            echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to ' . $dupDesc]);
            exit;
        }
    }

    $action_log = 'Added Item:' . $description;
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");

    $query = "INSERT INTO inventory (project, item, quantity, unit, description, received, serial, date, cost, inventory_item_no, assigned_to, life, remarks) VALUES (" .
        sqlVal($con, $project) . ", " . sqlVal($con, $item) . ", " . sqlVal($con, $quantity) . ", " . sqlVal($con, $unit) . ", " . sqlVal($con, $description) . ", " .
        sqlVal($con, $received) . ", " . sqlVal($con, $serial) . ", " . sqlVal($con, $date) . ", " . sqlVal($con, $cost) . ", " . sqlVal($con, $inventoryItemNo) . ", " .
        sqlVal($con, $assignedTo) . ", " . sqlVal($con, $life) . ", " . sqlVal($con, $remarks) . ")";

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
    } else if (mysqli_errno($con) === 1062) {
        echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to another item.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add item: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    $project = normText($_POST['txt_edit_project'] ?? '');
    $project = $project === null ? $PROJECT_FALLBACK : $project;
    $item = normText($_POST['txt_edit_item'] ?? '');
    $quantity = normIntOrNull($_POST['txt_edit_quantity'] ?? '');
    $unit = normText($_POST['txt_edit_unit'] ?? '');
    $description = normText($_POST['txt_edit_description'] ?? '');
    $description = $description === null ? $DESCRIPTION_FALLBACK : $description;
    $received = normText($_POST['txt_edit_received'] ?? '');
    $serial = normText($_POST['txt_edit_serial'] ?? '');
    $date = normDateOrNull($_POST['txt_edit_date'] ?? '');
    $cost = normText(str_replace(',', '', $_POST['txt_edit_cost'] ?? ''));
    $inventoryItemNo = normText($_POST['txt_edit_inventory_item_no'] ?? '');
    $assignedTo = normText($_POST['txt_edit_assigned_to'] ?? '');
    $life = normIntOrNull($_POST['txt_edit_life'] ?? '');
    $remarks = normText($_POST['txt_edit_remarks'] ?? '');

    $serialTrimmed = $serial === null ? '' : $serial;
    if ($serialTrimmed !== '') {
        $checkQ = mysqli_query($con, "SELECT description FROM inventory WHERE serial_unique = '" . mysqli_real_escape_string($con, $serialTrimmed) . "' AND id != $id LIMIT 1");
        if ($checkQ && ($dupRow = mysqli_fetch_assoc($checkQ))) {
            $dupDesc = ($dupRow['description'] !== null && $dupRow['description'] !== '') ? $dupRow['description'] : 'an existing item';
            echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to ' . $dupDesc]);
            exit;
        }
    }

    $query = "UPDATE inventory SET " .
        "project=" . sqlVal($con, $project) . ", " .
        "item=" . sqlVal($con, $item) . ", " .
        "quantity=" . sqlVal($con, $quantity) . ", " .
        "unit=" . sqlVal($con, $unit) . ", " .
        "description=" . sqlVal($con, $description) . ", " .
        "received=" . sqlVal($con, $received) . ", " .
        "serial=" . sqlVal($con, $serial) . ", " .
        "date=" . sqlVal($con, $date) . ", " .
        "cost=" . sqlVal($con, $cost) . ", " .
        "inventory_item_no=" . sqlVal($con, $inventoryItemNo) . ", " .
        "assigned_to=" . sqlVal($con, $assignedTo) . ", " .
        "life=" . sqlVal($con, $life) . ", " .
        "remarks=" . sqlVal($con, $remarks) .
        " WHERE id=$id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Edited Item: ' . $description;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
    } else if (mysqli_errno($con) === 1062) {
        echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to another item.']);
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
