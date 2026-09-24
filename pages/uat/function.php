<?php
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "../connection.php";

$isStaff = isset($_SESSION['staff']);

// A pass slip item that is confirmed installed in a UAT becomes
// 'deployed' (installed, non-returnable). If a UAT link is later
// removed, the item reverts to 'borrowed' (or 'overdue' past its
// return date) so it can be returned through the pass slip flow.
function uat_mark_pass_slip_deployed($con, $psId) {
    $psId = intval($psId);
    if ($psId <= 0) return;
    mysqli_query($con, "UPDATE pass_slip SET status = 'deployed' WHERE id = $psId AND status IN ('borrowed','overdue')");
}

function uat_revert_pass_slip_status($con, $psId) {
    $psId = intval($psId);
    if ($psId <= 0) return;
    mysqli_query($con, "UPDATE pass_slip SET status = IF(return_date IS NOT NULL AND return_date < CURDATE(), 'overdue', 'borrowed') WHERE id = $psId AND status = 'deployed'");
}

// ============================================================
// ACTION: Get UAT header record (AJAX, used by edit/view modal)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_uat') {
    header('Content-Type: application/json');
    if (!isset($_GET['id'])) { echo json_encode(['success' => false, 'message' => 'Missing UAT ID.']); exit; }
    $id = intval($_GET['id']);

    $tableCheck = @mysqli_query($con, "SELECT 1 FROM uat LIMIT 0");
    if (!$tableCheck) { echo json_encode(['success' => false, 'message' => 'UAT table not found. Run the migration first.']); exit; }

    $h = mysqli_query($con, "SELECT * FROM uat WHERE id = '$id'");
    if (!$h || mysqli_num_rows($h) == 0) { echo json_encode(['success' => false, 'message' => 'UAT record not found.']); exit; }
    $header = mysqli_fetch_assoc($h);

    $items = array();
    $it = mysqli_query($con, "SELECT * FROM uat_items WHERE uat_id = '$id' ORDER BY id ASC");
    if ($it) {
        while ($r = mysqli_fetch_assoc($it)) { $items[] = $r; }
    }

    echo json_encode(['success' => true, 'uat' => $header, 'items' => $items]);
    exit;
}

// ============================================================
// ACTION: Search pulled-out Pass Slip items for UAT linking
// (AJAX type-ahead). Only active slips (borrowed/overdue) whose
// items are NOT yet linked to a UAT record are offered, so an item
// can only be "confirmed installed" once. Returned items are
// excluded (they never left permanently).
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'pass_slip_item_search') {
    header('Content-Type: application/json');

    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) { echo json_encode([]); exit; }

    $tableCheck = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0");
    if (!$tableCheck) { echo json_encode([]); exit; }

    $like = "%" . $q . "%";
    $stmt = mysqli_prepare($con, "SELECT ps.id, ps.pass_slip_no, ps.item_description, ps.serial_no, ps.qty, ps.unit,
                                         ps.pullout_date, ps.status, ps.inventory_id, i.serial AS inv_serial
                                  FROM pass_slip ps
                                  LEFT JOIN inventory i ON ps.inventory_id = i.id
                                  WHERE ps.status IN ('borrowed','overdue')
                                    AND (ps.item_description LIKE ? OR ps.serial_no LIKE ? OR ps.pass_slip_no LIKE ?)
                                    AND NOT EXISTS (SELECT 1 FROM uat_items ui WHERE ui.pass_slip_item_id = ps.id)
                                  ORDER BY ps.pullout_date DESC, ps.item_description ASC
                                  LIMIT 20");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = array();
    if ($result) {
        while ($r = mysqli_fetch_assoc($result)) {
            $data[] = array(
                'id'              => intval($r['id']),
                'pass_slip_no'    => $r['pass_slip_no'] ?? '',
                'item_description'=> $r['item_description'] ?? '',
                'serial_no'       => $r['serial_no'] ?? '',
                'inv_serial'      => $r['inv_serial'] ?? '',
                'qty'             => intval($r['qty']),
                'unit'            => $r['unit'] ?? '',
                'pullout_date'    => $r['pullout_date'] ?? '',
                'status'          => $r['status'] ?? ''
            );
        }
    }
    echo json_encode($data);
    exit;
}

// ============================================================
// ACTION: Search Inventory items for UAT linking (AJAX type-ahead).
// Selecting an item fills the equipment row and stores its id in
// uat_items.inventory_id. No inventory quantity is deducted here:
// only the Pass Slip flow deducts quantity.
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'inventory_item_search') {
    header('Content-Type: application/json');

    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) { echo json_encode([]); exit; }

    $tableCheck = @mysqli_query($con, "SELECT 1 FROM inventory LIMIT 0");
    if (!$tableCheck) { echo json_encode([]); exit; }

    $like = "%" . $q . "%";
    $stmt = mysqli_prepare($con, "SELECT id, item, description, serial, quantity, unit
                                  FROM inventory
                                  WHERE item LIKE ? OR description LIKE ? OR serial LIKE ?
                                  ORDER BY item ASC, description ASC
                                  LIMIT 20");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = array();
    if ($result) {
        while ($r = mysqli_fetch_assoc($result)) {
            $data[] = array(
                'id'          => intval($r['id']),
                'item'        => $r['item'] ?? '',
                'description' => $r['description'] ?? '',
                'serial'      => $r['serial'] ?? '',
                'quantity'    => intval($r['quantity']),
                'unit'        => $r['unit'] ?? ''
            );
        }
    }
    echo json_encode($data);
    exit;
}
if (isset($_POST['create_uat'])) {
    $municipality = mysqli_real_escape_string($con, trim($_POST['municipality'] ?? ''));
    $strategy = mysqli_real_escape_string($con, trim($_POST['strategy'] ?? ''));
    $transport_location = mysqli_real_escape_string($con, trim($_POST['transport_location'] ?? ''));
    $latitude = isset($_POST['latitude']) && trim($_POST['latitude']) !== '' ? "'" . mysqli_real_escape_string($con, trim($_POST['latitude'])) . "'" : "NULL";
    $longitude = isset($_POST['longitude']) && trim($_POST['longitude']) !== '' ? "'" . mysqli_real_escape_string($con, trim($_POST['longitude'])) . "'" : "NULL";

    if (empty($municipality) || empty($strategy) || empty($transport_location)) {
        echo "<script>alert('Municipality, Strategy, and Transport Location are required.'); window.history.back();</script>";
        exit;
    }

    // Collect item rows; skip fully-empty rows
    $qtys = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];
    $item_names = $_POST['item_name'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $serial_numbers = $_POST['serial_numbers'] ?? [];
    $inventory_ids = $_POST['inventory_id'] ?? [];

    $itemRows = array();
    foreach ($item_names as $index => $itemName) {
        $rn = trim($itemName ?? '');
        $rqty = trim($qtys[$index] ?? '');
        $runit = trim($units[$index] ?? '');
        $rdesc = trim($descriptions[$index] ?? '');
        $rser = trim($serial_numbers[$index] ?? '');
        $invId = isset($inventory_ids[$index]) ? intval($inventory_ids[$index]) : 0;
        if ($rn === '' && $rqty === '' && $runit === '' && $rdesc === '' && $rser === '' && $invId <= 0) continue;
        if ($rn === '') {
            echo "<script>alert('Please enter an Item Name for equipment row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
        if ($rqty === '') $rqty = 1;

        if ($invId > 0) {
            $checkInv = mysqli_query($con, "SELECT 1 FROM inventory WHERE id = $invId LIMIT 1");
            if (!$checkInv || mysqli_num_rows($checkInv) == 0) {
                echo "<script>alert('The Inventory item linked in row " . ($index + 1) . " no longer exists. Remove that link and try again.'); window.history.back();</script>";
                exit;
            }
        }

        $itemRows[] = array('qty' => intval($rqty), 'unit' => $runit, 'item_name' => $rn, 'description' => $rdesc, 'serial_numbers' => $rser, 'inventory_id' => $invId);
    }

    $insertHeader = "INSERT INTO uat (municipality, strategy, transport_location, latitude, longitude, created_by)
                     VALUES ('$municipality', '$strategy', '$transport_location', $latitude, $longitude,
                     '" . ($_SESSION['username'] ?? 'admin') . "')";

    if (!mysqli_query($con, $insertHeader)) {
        echo "<script>alert('Error saving UAT: " . mysqli_error($con) . "'); window.history.back();</script>";
        exit;
    }

    $uat_id = mysqli_insert_id($con);
    $insertedCount = 0;

    foreach ($itemRows as $row) {
        $qty = $row['qty'];
        $unit = mysqli_real_escape_string($con, $row['unit']);
        $item_name = mysqli_real_escape_string($con, $row['item_name']);
        $description = mysqli_real_escape_string($con, $row['description']);
        $serial_numbers = mysqli_real_escape_string($con, $row['serial_numbers']);
        $invValue = ($row['inventory_id'] > 0) ? $row['inventory_id'] : 'NULL';

        $insertItem = "INSERT INTO uat_items (uat_id, qty, unit, item_name, description, serial_numbers, inventory_id)
                       VALUES ($uat_id, $qty, '$unit', '$item_name', '$description', '$serial_numbers', $invValue)";

        $itemErr = null;
        try {
            if (mysqli_query($con, $insertItem)) {
                $insertedCount++;
            } else {
                $itemErr = mysqli_errno($con);
            }
        } catch (mysqli_sql_exception $e) {
            $itemErr = mysqli_errno($con);
        }
        if ($itemErr === 1062) {
            echo "<script>alert('A linked item in row " . ($index + 1) . " is already assigned to another UAT row. Select a different item.'); window.history.back();</script>";
            exit;
        }
        if ($itemErr === 1452) {
            echo "<script>alert('A linked item in row " . ($index + 1) . " no longer exists. Remove that link and try again.'); window.history.back();</script>";
            exit;
        }
        if ($itemErr !== null) {
            echo "<script>alert('Error saving equipment for row: " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
    }

    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
        VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Created UAT for: $transport_location (Municipality: $municipality)')");

    $_SESSION['new_uat'] = $uat_id;
    header("Location: uat.php");
    exit;
}

// ============================================================
// ACTION: Edit UAT header + equipment items (replace items)
// ============================================================
if (isset($_POST['edit_uat'])) {
    $id = intval($_POST['uat_id'] ?? 0);
    if ($id <= 0) {
        echo "<script>alert('Invalid UAT record.'); window.history.back();</script>";
        exit;
    }

    $municipality = mysqli_real_escape_string($con, trim($_POST['municipality'] ?? ''));
    $strategy = mysqli_real_escape_string($con, trim($_POST['strategy'] ?? ''));
    $transport_location = mysqli_real_escape_string($con, trim($_POST['transport_location'] ?? ''));
    $latitude = isset($_POST['latitude']) && trim($_POST['latitude']) !== '' ? "'" . mysqli_real_escape_string($con, trim($_POST['latitude'])) . "'" : "NULL";
    $longitude = isset($_POST['longitude']) && trim($_POST['longitude']) !== '' ? "'" . mysqli_real_escape_string($con, trim($_POST['longitude'])) . "'" : "NULL";

    if (empty($municipality) || empty($strategy) || empty($transport_location)) {
        echo "<script>alert('Municipality, Strategy, and Transport Location are required.'); window.history.back();</script>";
        exit;
    }

    $updateHeader = "UPDATE uat SET municipality = '$municipality', strategy = '$strategy',
                     transport_location = '$transport_location', latitude = $latitude, longitude = $longitude
                     WHERE id = $id";
    if (!mysqli_query($con, $updateHeader)) {
        echo "<script>alert('Error updating UAT: " . mysqli_error($con) . "'); window.history.back();</script>";
        exit;
    }

    $qtys = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];
    $item_names = $_POST['item_name'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $serial_numbers = $_POST['serial_numbers'] ?? [];
    $pass_slip_item_ids = $_POST['pass_slip_item_id'] ?? [];
    $inventory_ids = $_POST['inventory_id'] ?? [];

    // Capture pass slip links that exist right now so we can revert any
    // that are removed in this edit back to returnable (borrowed/overdue).
    $oldPsLinks = array();
    $oldLinkQ = mysqli_query($con, "SELECT pass_slip_item_id FROM uat_items WHERE uat_id = $id AND pass_slip_item_id IS NOT NULL");
    if ($oldLinkQ) {
        while ($ol = mysqli_fetch_assoc($oldLinkQ)) { $oldPsLinks[] = intval($ol['pass_slip_item_id']); }
    }

    $itemRows = array();
    $seenLinks = array();
    foreach ($item_names as $index => $itemName) {
        $rn = trim($itemName ?? '');
        $rqty = trim($qtys[$index] ?? '');
        $runit = trim($units[$index] ?? '');
        $rdesc = trim($descriptions[$index] ?? '');
        $rser = trim($serial_numbers[$index] ?? '');
        $linkId = isset($pass_slip_item_ids[$index]) ? intval($pass_slip_item_ids[$index]) : 0;
        $invId = isset($inventory_ids[$index]) ? intval($inventory_ids[$index]) : 0;
        if ($rn === '' && $rqty === '' && $runit === '' && $rdesc === '' && $rser === '' && $linkId <= 0 && $invId <= 0) continue;
        if ($rn === '') {
            echo "<script>alert('Please enter an Item Name for equipment row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
        if ($rqty === '') $rqty = 1;

        if ($linkId > 0 && $invId > 0) {
            echo "<script>alert('Row " . ($index + 1) . " cannot be linked to both a Pass Slip item and an Inventory item. Select only one source.'); window.history.back();</script>";
            exit;
        }

        if ($linkId > 0) {
            if (!in_array($linkId, $oldPsLinks)) {
                echo "<script>alert('Row " . ($index + 1) . " links a Pass Slip item that is not part of this record. New UAT rows can only link Inventory items.'); window.history.back();</script>";
                exit;
            }
            if (isset($seenLinks[$linkId])) {
                echo "<script>alert('The Pass Slip item in row " . ($index + 1) . " appears in more than one row. Each item can only appear once.'); window.history.back();</script>";
                exit;
            }
            $alreadyQ = mysqli_query($con, "SELECT 1 FROM uat_items WHERE pass_slip_item_id = $linkId AND uat_id != $id LIMIT 1");
            if ($alreadyQ && mysqli_num_rows($alreadyQ) > 0) {
                echo "<script>alert('The Pass Slip item in row " . ($index + 1) . " is already confirmed installed in another UAT record. Select a different item.'); window.history.back();</script>";
                exit;
            }
            $seenLinks[$linkId] = $index + 1;
        }

        if ($invId > 0) {
            $checkInv = mysqli_query($con, "SELECT 1 FROM inventory WHERE id = $invId LIMIT 1");
            if (!$checkInv || mysqli_num_rows($checkInv) == 0) {
                echo "<script>alert('The Inventory item linked in row " . ($index + 1) . " no longer exists. Remove that link and try again.'); window.history.back();</script>";
                exit;
            }
        }

        $itemRows[] = array('qty' => intval($rqty), 'unit' => $runit, 'item_name' => $rn, 'description' => $rdesc, 'serial_numbers' => $rser, 'pass_slip_item_id' => $linkId, 'inventory_id' => $invId);
    }

    // Replace equipment rows: delete all then re-insert (keeps FK parent valid)
    mysqli_query($con, "DELETE FROM uat_items WHERE uat_id = $id");

    $insertedCount = 0;
    foreach ($itemRows as $row) {
        $qty = $row['qty'];
        $unit = mysqli_real_escape_string($con, $row['unit']);
        $item_name = mysqli_real_escape_string($con, $row['item_name']);
        $description = mysqli_real_escape_string($con, $row['description']);
        $serial_numbers = mysqli_real_escape_string($con, $row['serial_numbers']);
        $linkValue = ($row['pass_slip_item_id'] > 0) ? $row['pass_slip_item_id'] : 'NULL';
        $invValue = ($row['inventory_id'] > 0) ? $row['inventory_id'] : 'NULL';

        $insertItem = "INSERT INTO uat_items (uat_id, qty, unit, item_name, description, serial_numbers, pass_slip_item_id, inventory_id)
                       VALUES ($id, $qty, '$unit', '$item_name', '$description', '$serial_numbers', $linkValue, $invValue)";
        $itemErr = null;
        try {
            if (mysqli_query($con, $insertItem)) {
                $insertedCount++;
            } else {
                $itemErr = mysqli_errno($con);
            }
        } catch (mysqli_sql_exception $e) {
            $itemErr = mysqli_errno($con);
        }
        if ($itemErr === 1062) {
            echo "<script>alert('The Pass Slip item in row " . ($index + 1) . " is already confirmed installed in another UAT record. Select a different item.'); window.history.back();</script>";
            exit;
        }
        if ($itemErr === 1452) {
            echo "<script>alert('A linked item in row " . ($index + 1) . " no longer exists. Remove that link and try again.'); window.history.back();</script>";
            exit;
        }
        if ($itemErr !== null) {
            echo "<script>alert('Error saving equipment for row: " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
    }

    // Pass slip items still linked after this edit are confirmed
    // installed ('deployed'); any removed link reverts to returnable.
    $newPsLinks = array();
    foreach ($itemRows as $row) {
        if ($row['pass_slip_item_id'] > 0) $newPsLinks[] = $row['pass_slip_item_id'];
    }
    foreach (array_diff($oldPsLinks, $newPsLinks) as $removedId) {
        uat_revert_pass_slip_status($con, $removedId);
    }
    foreach ($newPsLinks as $psId) {
        uat_mark_pass_slip_deployed($con, $psId);
    }

    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
        VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Edited UAT for: $transport_location (ID: $id)')");

    $_SESSION['edited'] = 1;
    header("Location: uat.php");
    exit;
}

// ============================================================
// ACTION: Delete UAT record (AJAX; equipment cascades via FK)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_uat') {
    header('Content-Type: application/json');
    if ($isStaff) { echo json_encode(['success' => false, 'message' => 'Staff does not have permission to delete UAT records.']); exit; }

    $ids = $_POST['ids'] ?? [];
    if (empty($ids)) { echo json_encode(['success' => false, 'message' => 'No UAT records selected.']); exit; }

    $tableCheck = @mysqli_query($con, "SELECT 1 FROM uat LIMIT 0");
    if (!$tableCheck) { echo json_encode(['success' => false, 'message' => 'UAT table not found.']); exit; }

    $deleted = 0;
    $logNames = array();
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id <= 0) continue;
        $q = mysqli_query($con, "SELECT transport_location FROM uat WHERE id = $id");
        if ($q && mysqli_num_rows($q) > 0) {
            $logNames[] = mysqli_fetch_assoc($q)['transport_location'];
        }
        // Pass slip items linked to this UAT revert to returnable once the
        // installation record is gone.
        $psIds = array();
        $linkQ = mysqli_query($con, "SELECT pass_slip_item_id FROM uat_items WHERE uat_id = $id AND pass_slip_item_id IS NOT NULL");
        if ($linkQ) {
            while ($lk = mysqli_fetch_assoc($linkQ)) { $psIds[] = intval($lk['pass_slip_item_id']); }
        }
        if (mysqli_query($con, "DELETE FROM uat WHERE id = $id")) {
            foreach ($psIds as $psId) { uat_revert_pass_slip_status($con, $psId); }
            $deleted++;
        }
    }

    if ($deleted > 0) {
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted UAT record(s): " . mysqli_real_escape_string($con, implode(', ', $logNames)) . "')");
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => $deleted . ' UAT record(s) deleted.']);
    exit;
}
?>