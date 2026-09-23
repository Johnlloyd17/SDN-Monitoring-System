<?php
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "../connection.php";

$isStaff = isset($_SESSION['staff']);

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
// ACTION: Create UAT header + equipment items (one save)
// ============================================================
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

    $itemRows = array();
    foreach ($item_names as $index => $itemName) {
        $rn = trim($itemName ?? '');
        $rqty = trim($qtys[$index] ?? '');
        $runit = trim($units[$index] ?? '');
        $rdesc = trim($descriptions[$index] ?? '');
        $rser = trim($serial_numbers[$index] ?? '');
        if ($rn === '' && $rqty === '' && $runit === '' && $rdesc === '' && $rser === '') continue;
        if ($rn === '') {
            echo "<script>alert('Please enter an Item Name for equipment row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
        if ($rqty === '') $rqty = 1;
        $itemRows[] = array('qty' => intval($rqty), 'unit' => $runit, 'item_name' => $rn, 'description' => $rdesc, 'serial_numbers' => $rser);
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

        $insertItem = "INSERT INTO uat_items (uat_id, qty, unit, item_name, description, serial_numbers)
                       VALUES ($uat_id, $qty, '$unit', '$item_name', '$description', '$serial_numbers')";

        if (mysqli_query($con, $insertItem)) {
            $insertedCount++;
        } else {
            echo "<script>alert('Error saving equipment for row: " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
    }

    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
        VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Created UAT for: $transport_location (Municipality: $municipality)')");

    $_SESSION['added'] = 1;
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

    $itemRows = array();
    foreach ($item_names as $index => $itemName) {
        $rn = trim($itemName ?? '');
        $rqty = trim($qtys[$index] ?? '');
        $runit = trim($units[$index] ?? '');
        $rdesc = trim($descriptions[$index] ?? '');
        $rser = trim($serial_numbers[$index] ?? '');
        if ($rn === '' && $rqty === '' && $runit === '' && $rdesc === '' && $rser === '') continue;
        if ($rn === '') {
            echo "<script>alert('Please enter an Item Name for equipment row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
        if ($rqty === '') $rqty = 1;
        $itemRows[] = array('qty' => intval($rqty), 'unit' => $runit, 'item_name' => $rn, 'description' => $rdesc, 'serial_numbers' => $rser);
    }

    // Replace equipment rows: delete all then re-insert (keeps FK parent valid)
    mysqli_query($con, "DELETE FROM uat_items WHERE uat_id = $id");

    foreach ($itemRows as $row) {
        $qty = $row['qty'];
        $unit = mysqli_real_escape_string($con, $row['unit']);
        $item_name = mysqli_real_escape_string($con, $row['item_name']);
        $description = mysqli_real_escape_string($con, $row['description']);
        $serial_numbers = mysqli_real_escape_string($con, $row['serial_numbers']);

        $insertItem = "INSERT INTO uat_items (uat_id, qty, unit, item_name, description, serial_numbers)
                       VALUES ($id, $qty, '$unit', '$item_name', '$description', '$serial_numbers')";
        if (!mysqli_query($con, $insertItem)) {
            echo "<script>alert('Error saving equipment for row: " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
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
        if (mysqli_query($con, "DELETE FROM uat WHERE id = $id")) {
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