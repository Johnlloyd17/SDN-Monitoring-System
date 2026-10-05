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
//  - Project is stored as NULL when blank (the list/stats/export queries
//    no longer exclude blank-project records).
//  - Description gets a placeholder so downstream features (pass slip,
//    print sticker, file viewer) have a label.
//  - Quantity, Life, Cost, Date, etc. are stored as NULL instead of being
//    coerced to 0 / 0000-00-00 by MySQL.
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

function fieldsFilled(array $values) {
    foreach ($values as $value) {
        if (trim((string)$value) !== '') return true;
    }
    return false;
}

// ---------------------------------------------------------------
// Property No. generator: YYYY-CC-NNN-LOCATION (e.g. 2026-01-001-SDN)
//  - CC comes from the editable `property_categories` table
//  - NNN is ONE shared counter per year (all categories), kept in
//    `property_no_counter`; it restarts at 001 every new year
//  - Only used when the user leaves "Inventory Item no." empty
// ---------------------------------------------------------------
function propertyNoFail($msg) {
    return array('ok' => false, 'error' => $msg);
}

function generatePropertyNo($con, $year, $categoryCode, $locationCode) {
    $categoryCode = trim((string)$categoryCode);
    $locationCode = strtoupper(preg_replace('/\s+/', '', (string)$locationCode));
    $year = (int)$year;

    if ($categoryCode === '') {
        return propertyNoFail('Please choose a Category, or type the Inventory Item no. manually.');
    }
    if ($locationCode === '') {
        return propertyNoFail('Please type a Location Code, or type the Inventory Item no. manually.');
    }
    if (!preg_match('/^[A-Z0-9]+$/', $locationCode)) {
        return propertyNoFail('Location Code can only use letters and numbers.');
    }
    if ($year < 2000 || $year > 2100) {
        return propertyNoFail('The Date Acquired year looks invalid. Please check the date.');
    }

    $stmt = @mysqli_prepare($con, "SELECT code FROM property_categories WHERE code = ? LIMIT 1");
    if (!$stmt) {
        return propertyNoFail('Property category table not found. Please run property_no_migration.sql first.');
    }
    mysqli_stmt_bind_param($stmt, 's', $categoryCode);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $catRow = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    if (!$catRow) {
        return propertyNoFail('Unknown Category code. Please choose a Category from the list.');
    }
    $cc = $catRow['code'];

    // Atomic increment (safe if two people save at the same time).
    $ok = @mysqli_query($con, "INSERT INTO property_no_counter (seq_year, last_seq) VALUES ($year, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE last_seq = LAST_INSERT_ID(last_seq + 1)");
    if (!$ok) {
        return propertyNoFail('Property counter table not found. Please run property_no_migration.sql first.');
    }
    $seqRes = mysqli_query($con, "SELECT LAST_INSERT_ID() AS seq");
    $seqRow = $seqRes ? mysqli_fetch_assoc($seqRes) : null;
    $seq = $seqRow ? (int)$seqRow['seq'] : 0;
    if ($seq < 1) {
        return propertyNoFail('Could not generate the running number. Please try again.');
    }

    return array('ok' => true, 'no' => $year . '-' . $cc . '-' . str_pad((string)$seq, 3, '0', STR_PAD_LEFT) . '-' . $locationCode);
}

if ($action === 'add') {
    $filled = fieldsFilled(array(
        $_POST['txt_project'] ?? '',
        $_POST['txt_item'] ?? '',
        $_POST['txt_quantity'] ?? '',
        $_POST['txt_unit'] ?? '',
        $_POST['txt_description'] ?? '',
        $_POST['txt_received'] ?? '',
        $_POST['txt_serial'] ?? '',
        $_POST['txt_date'] ?? '',
        str_replace(',', '', $_POST['txt_cost'] ?? ''),
        $_POST['txt_inventory_item_no'] ?? '',
        $_POST['txt_assigned_to'] ?? '',
        $_POST['txt_life'] ?? '',
        $_POST['txt_remarks'] ?? '',
    ));
    if (!$filled) {
        echo json_encode(['success' => false, 'error' => 'Please fill in at least one field before saving.']);
        exit;
    }
    $project = normText($_POST['txt_project'] ?? '');
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

    // Auto-generate the Property No. only when the box was left empty.
    $generatedPropertyNo = null;
    if ($inventoryItemNo === null) {
        $genYear = $date !== null ? (int)substr($date, 0, 4) : (int)date('Y');
        $gen = generatePropertyNo($con, $genYear, $_POST['txt_category'] ?? '', $_POST['txt_location_code'] ?? '');
        if (!$gen['ok']) {
            echo json_encode(['success' => false, 'error' => $gen['error']]);
            exit;
        }
        $inventoryItemNo = $gen['no'];
        $generatedPropertyNo = $inventoryItemNo;
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

        echo json_encode(['success' => true, 'message' => 'Item added successfully', 'inventory_item_no' => $generatedPropertyNo]);
    } else if (mysqli_errno($con) === 1062) {
        echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to another item.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add item: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    $filled = fieldsFilled(array(
        $_POST['txt_edit_project'] ?? '',
        $_POST['txt_edit_item'] ?? '',
        $_POST['txt_edit_quantity'] ?? '',
        $_POST['txt_edit_unit'] ?? '',
        $_POST['txt_edit_description'] ?? '',
        $_POST['txt_edit_received'] ?? '',
        $_POST['txt_edit_serial'] ?? '',
        $_POST['txt_edit_date'] ?? '',
        str_replace(',', '', $_POST['txt_edit_cost'] ?? ''),
        $_POST['txt_edit_inventory_item_no'] ?? '',
        $_POST['txt_edit_assigned_to'] ?? '',
        $_POST['txt_edit_life'] ?? '',
        $_POST['txt_edit_remarks'] ?? '',
    ));
    if (!$filled) {
        echo json_encode(['success' => false, 'error' => 'Please fill in at least one field before saving.']);
        exit;
    }
    $project = normText($_POST['txt_edit_project'] ?? '');
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

if ($action === 'group_delete') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
        echo json_encode(['success' => false, 'error' => 'Staff accounts cannot delete inventory records.']);
        exit;
    }

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (!is_array($ids)) $ids = [$ids];

    $groupDescription = normText($_POST['group_description'] ?? '');
    $groupUnit = normText($_POST['group_unit'] ?? '');
    if ($groupDescription === null) {
        echo json_encode(['success' => false, 'error' => 'Missing group description.']);
        exit;
    }

    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $q = mysqli_query($con, "SELECT description, unit FROM inventory WHERE id = $id LIMIT 1");
        $row = $q ? mysqli_fetch_assoc($q) : null;
        if (!$row) continue;

        $rowDesc = trim((string)$row['description']);
        $rowUnit = ($row['unit'] === null || trim((string)$row['unit']) === '') ? null : trim((string)$row['unit']);
        if ($rowDesc !== $groupDescription || $rowUnit !== $groupUnit) continue;

        $itemName = $row['description'];
        if (mysqli_query($con, "DELETE FROM inventory WHERE id = $id")) {
            $action_log = 'Deleted Item: ' . $itemName;
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => "$deleted unit(s) deleted from group"]);
    exit;
}

if ($action === 'group_update') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
        echo json_encode(['success' => false, 'error' => 'Staff accounts cannot modify group shared fields.']);
        exit;
    }

    $groupDescription = normText($_POST['group_description'] ?? '');
    $groupUnit = normText($_POST['group_unit'] ?? '');
    if ($groupDescription === null) {
        echo json_encode(['success' => false, 'error' => 'Missing group description.']);
        exit;
    }

    $project = normText($_POST['shared_project'] ?? '');
    $item = normText($_POST['shared_item'] ?? '');
    $cost = isset($_POST['shared_cost']) ? normText(str_replace(',', '', $_POST['shared_cost'])) : null;
    $life = normIntOrNull($_POST['shared_life'] ?? '');
    $received = normText($_POST['shared_received'] ?? '');
    $inventoryItemNo = normText($_POST['shared_inv_no'] ?? '');

    $unitMatch = $groupUnit === null
        ? 'unit IS NULL'
        : 'unit = ' . sqlVal($con, $groupUnit);

    $check = mysqli_query($con, "SELECT COUNT(*) c FROM inventory WHERE TRIM(description) = " . sqlVal($con, $groupDescription) . " AND $unitMatch AND (serial IS NOT NULL AND TRIM(serial) <> '')");
    $exists = $check ? intval(mysqli_fetch_assoc($check)['c']) : 0;
    if ($exists === 0) {
        echo json_encode(['success' => false, 'error' => 'No serial-numbered units match this group.']);
        exit;
    }

    $sql = "UPDATE inventory SET
            project = " . sqlVal($con, $project) . ",
            item = " . sqlVal($con, $item) . ",
            cost = " . sqlVal($con, $cost) . ",
            life = " . sqlVal($con, $life) . ",
            received = " . sqlVal($con, $received) . ",
            inventory_item_no = " . sqlVal($con, $inventoryItemNo) . "
            WHERE TRIM(description) = " . sqlVal($con, $groupDescription) . " AND $unitMatch AND (serial IS NOT NULL AND TRIM(serial) <> '')";

    if (mysqli_query($con, $sql)) {
        $affected = mysqli_affected_rows($con);
        $action_log = 'Updated group shared fields: ' . $groupDescription;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        echo json_encode(['success' => true, 'updated' => $affected, 'message' => "$affected unit(s) updated"]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
    }
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

if ($action === 'add_unit') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {
        echo json_encode(['success' => false, 'error' => 'Staff accounts cannot add inventory records.']);
        exit;
    }

    $groupDescription = normText($_POST['group_description'] ?? '');
    $groupUnit = normText($_POST['group_unit'] ?? '');
    if ($groupDescription === null) {
        echo json_encode(['success' => false, 'error' => 'Missing group description.']);
        exit;
    }

    $quantity = normIntOrNull($_POST['txt_quantity'] ?? '');
    $serial = normText($_POST['txt_serial'] ?? '');
    $date = normDateOrNull($_POST['txt_date'] ?? '');
    $assignedTo = normText($_POST['txt_assigned_to'] ?? '');
    $remarks = normText($_POST['txt_remarks'] ?? '');

    $filled = fieldsFilled(array($quantity, $serial, $date, $assignedTo, $remarks));
    if (!$filled) {
        echo json_encode(['success' => false, 'error' => 'Please fill in at least one field before saving.']);
        exit;
    }

    $project = null;
    $item = null;
    $received = null;
    $cost = null;
    $inventoryItemNo = null;
    $life = null;
    $archetypeId = intval($_POST['archetype_id'] ?? 0);
    if ($archetypeId > 0) {
        $archStmt = mysqli_prepare($con, "SELECT project, item, received, cost, inventory_item_no, life, description, unit FROM inventory WHERE id = ?");
        mysqli_stmt_bind_param($archStmt, 'i', $archetypeId);
        mysqli_stmt_execute($archStmt);
        $archResult = mysqli_stmt_get_result($archStmt);
        $archRow = mysqli_fetch_assoc($archResult);
        mysqli_stmt_close($archStmt);
        if ($archRow && trim((string)$archRow['description']) === $groupDescription && trim((string)$archRow['unit']) === $groupUnit) {
            $project = $archRow['project'];
            $item = $archRow['item'];
            $received = $archRow['received'];
            $cost = $archRow['cost'];
            $inventoryItemNo = $archRow['inventory_item_no'];
            $life = $archRow['life'];
        }
    }

    $serialTrimmed = $serial === null ? '' : $serial;
    if ($serialTrimmed !== '') {
        $checkQ = mysqli_query($con, "SELECT description FROM inventory WHERE serial_unique = '" . mysqli_real_escape_string($con, $serialTrimmed) . "' LIMIT 1");
        if ($checkQ && ($dupRow = mysqli_fetch_assoc($checkQ))) {
            $dupDesc = ($dupRow['description'] !== null && $dupRow['description'] !== '') ? $dupRow['description'] : 'an existing item';
            echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to ' . $dupDesc]);
            exit;
        }
    }

    $action_log = 'Added Item:' . $groupDescription;
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");

    $query = "INSERT INTO inventory (project, item, quantity, unit, description, received, inventory_item_no, assigned_to, serial, date, cost, life, remarks) VALUES (" .
        sqlVal($con, $project) . ", " . sqlVal($con, $item) . ", " . sqlVal($con, $quantity) . ", " . sqlVal($con, $groupUnit) . ", " . sqlVal($con, $groupDescription) . ", " .
        sqlVal($con, $received) . ", " . sqlVal($con, $inventoryItemNo) . ", " . sqlVal($con, $assignedTo) . ", " . sqlVal($con, $serial) . ", " . sqlVal($con, $date) . ", " .
        sqlVal($con, $cost) . ", " . sqlVal($con, $life) . ", " . sqlVal($con, $remarks) . ")";

    if (mysqli_query($con, $query)) {
        $id = mysqli_insert_id($con);
        echo json_encode([
            'success' => true,
            'message' => 'Unit added successfully',
            'unit' => array(
                'id' => $id,
                'quantity' => $quantity,
                'serial' => $serial,
                'date' => $date,
                'assigned_to' => $assignedTo,
                'remarks' => $remarks,
            ),
        ]);
    } else if (mysqli_errno($con) === 1062) {
        echo json_encode(['success' => false, 'error' => 'This serial number is already assigned to another item.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add unit: ' . mysqli_error($con)]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);