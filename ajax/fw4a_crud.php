<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$photoDir = __DIR__ . '/../pages/fw4a_data/photo/';
if (!file_exists($photoDir)) {
    mkdir($photoDir, 0777, true);
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

function escf($con, $name) {
    return mysqli_real_escape_string($con, $_POST[$name] ?? '');
}

if ($action === 'add') {
    $locality = escf($con, 'txt_locality');
    $barangay = escf($con, 'txt_barangay');
    $district = escf($con, 'txt_district');
    $transport_location = escf($con, 'txt_transport_location');
    $transport_type = escf($con, 'txt_transport_type');
    $locations = escf($con, 'txt_locations');
    $type = escf($con, 'txt_type');
    $code = escf($con, 'txt_code');
    $nationwide_id = escf($con, 'txt_nationwide_id');
    $date_of_activation = !empty($_POST['txt_date_of_activation']) ? "'" . escf($con, 'txt_date_of_activation') . "'" : 'NULL';
    $current_date_of_acceptance = !empty($_POST['txt_current_date_of_acceptance']) ? "'" . escf($con, 'txt_current_date_of_acceptance') . "'" : 'NULL';
    $latitude = isset($_POST['txt_latitude']) && $_POST['txt_latitude'] !== '' ? (float)$_POST['txt_latitude'] : 'NULL';
    $longitude = isset($_POST['txt_longitude']) && $_POST['txt_longitude'] !== '' ? (float)$_POST['txt_longitude'] : 'NULL';
    $strategy = escf($con, 'txt_strategy');
    $status = escf($con, 'txt_status');
    $remarks = escf($con, 'txt_remarks');

    $query = "INSERT INTO tblfwfa (
        locality, barangay, district, transport_location, transport_type,
        locations, type, code, nationwide_id, date_of_activation,
        current_date_of_acceptance, latitude, longitude, strategy, status, remarks
    ) VALUES (
        '$locality', '$barangay', '$district', '$transport_location', '$transport_type',
        '$locations', '$type', '$code', '$nationwide_id', $date_of_activation,
        $current_date_of_acceptance, $latitude, $longitude, '$strategy', '$status', '$remarks'
    )";

    if (mysqli_query($con, $query)) {
        $action_log = 'Added Item ' . $locality;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Access point added successfully']);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to add: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit;
    }
    $locality = escf($con, 'txt_edit_locality');
    $barangay = escf($con, 'txt_edit_barangay');
    $district = escf($con, 'txt_edit_district');
    $transport_location = escf($con, 'txt_edit_transport_location');
    $transport_type = escf($con, 'txt_edit_transport_type');
    $locations = escf($con, 'txt_edit_locations');
    $type = escf($con, 'txt_edit_type');
    $code = escf($con, 'txt_edit_code');
    $nationwide_id = escf($con, 'txt_edit_nationwide_id');
    $date_of_activation = !empty($_POST['txt_edit_date_of_activation']) ? "'" . escf($con, 'txt_edit_date_of_activation') . "'" : 'NULL';
    $current_date_of_acceptance = !empty($_POST['txt_edit_current_date_of_acceptance']) ? "'" . escf($con, 'txt_edit_current_date_of_acceptance') . "'" : 'NULL';
    $latitude = isset($_POST['txt_edit_latitude']) && $_POST['txt_edit_latitude'] !== '' ? (float)$_POST['txt_edit_latitude'] : 'NULL';
    $longitude = isset($_POST['txt_edit_longitude']) && $_POST['txt_edit_longitude'] !== '' ? (float)$_POST['txt_edit_longitude'] : 'NULL';
    $strategy = escf($con, 'txt_edit_strategy');
    $status = escf($con, 'txt_edit_status');
    $remarks = escf($con, 'txt_edit_remarks');

    $query = "UPDATE tblfwfa SET
        locality = '$locality',
        barangay = '$barangay',
        district = '$district',
        transport_location = '$transport_location',
        transport_type = '$transport_type',
        locations = '$locations',
        type = '$type',
        code = '$code',
        nationwide_id = '$nationwide_id',
        date_of_activation = $date_of_activation,
        current_date_of_acceptance = $current_date_of_acceptance,
        latitude = $latitude,
        longitude = $longitude,
        strategy = '$strategy',
        status = '$status',
        remarks = '$remarks'
        WHERE id = $id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Updated Item ' . $locality;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Access point updated successfully']);
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Failed to update: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'delete') {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (!is_array($ids)) $ids = [$ids];

    $intIds = [];
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id > 0) { $intIds[] = $id; }
    }

    if (empty($intIds)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'No valid IDs provided']);
        exit;
    }

    $idList = implode(',', $intIds);
    $count = count($intIds);

    @mysqli_query($con, "DELETE FROM tblfwfa WHERE id IN ($idList)");

    $logMsg = "Batch deleted $count item(s) (IDs: $idList)";
    $logMsgEsc = mysqli_real_escape_string($con, $logMsg);
    @mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '$logMsgEsc')");

    ob_clean();
    echo json_encode(['success' => true, 'deleted' => $count, 'message' => "$count item(s) deleted"]);
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

if ($action === 'remove_photo') {
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
