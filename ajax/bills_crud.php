<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

function escf($con, $name) {
    return mysqli_real_escape_string($con, $_POST[$name] ?? '');
}

function dateSql($con, $name) {
    $v = trim($_POST[$name] ?? '');
    if ($v === '' || $v === null) return 'NULL';
    return "'" . mysqli_real_escape_string($con, $v) . "'";
}

$knownTypes = ['Water Bill','Internet Bill','Electricity Bill'];
$knownLocations = ['SDN Provincial Office','SDN Hill Relay Station'];

if ($action === 'add') {
    $date_received = dateSql($con, 'txt_date_received');
    $type_of_billing = $_POST['txt_type_of_billing'] ?? '';
    if (!in_array($type_of_billing, $knownTypes)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid Type of Billing "'.$type_of_billing.'". Must be Water Bill, Internet Bill, or Electricity Bill.']);
        exit;
    }
    $link_to_file = escf($con, 'txt_link_to_file');
    $amount = ($_POST['txt_amount'] ?? '') !== '' ? (float)$_POST['txt_amount'] : 'NULL';
    $rawLocation = trim($_POST['txt_location_office'] ?? '');
    $locationSql = 'NULL';
    if ($rawLocation !== '') {
        if (!in_array($rawLocation, $knownLocations)) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid Location/Office "'.$rawLocation.'". Must be SDN Provincial Office or SDN Hill Relay Station.']);
            exit;
        }
        $locationSql = "'" . mysqli_real_escape_string($con, $rawLocation) . "'";
    }
    $due_date = dateSql($con, 'txt_due_date');
    $disconnection_date = dateSql($con, 'txt_disconnection_date');
    $status = isset($_POST['txt_status']) && $_POST['txt_status'] === '1' ? 1 : 0;
    $date_paid = dateSql($con, 'txt_date_paid');
    $remarks = escf($con, 'txt_remarks');
    $link_to_or = escf($con, 'txt_link_to_or');

    $query = "INSERT INTO bills_monitoring (
        date_received, type_of_billing, link_to_file, amount, location_office,
        due_date, disconnection_date, status, date_paid, remarks, link_to_or
    ) VALUES (
        $date_received, '$type_of_billing', '$link_to_file', $amount, $locationSql,
        $due_date, $disconnection_date, $status, $date_paid, '$remarks', '$link_to_or'
    )";

    if (mysqli_query($con, $query)) {
        $action_log = 'Added Bill ' . $type_of_billing;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Bill added successfully']);
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
    $date_received = dateSql($con, 'txt_edit_date_received');
    $type_of_billing = $_POST['txt_edit_type_of_billing'] ?? '';
    if (!in_array($type_of_billing, $knownTypes)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid Type of Billing "'.$type_of_billing.'". Must be Water Bill, Internet Bill, or Electricity Bill.']);
        exit;
    }
    $link_to_file = escf($con, 'txt_edit_link_to_file');
    $amount = ($_POST['txt_edit_amount'] ?? '') !== '' ? (float)$_POST['txt_edit_amount'] : 'NULL';
    $rawLocation = trim($_POST['txt_edit_location_office'] ?? '');
    $locationSql = 'NULL';
    if ($rawLocation !== '') {
        if (!in_array($rawLocation, $knownLocations)) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid Location/Office "'.$rawLocation.'". Must be SDN Provincial Office or SDN Hill Relay Station.']);
            exit;
        }
        $locationSql = "'" . mysqli_real_escape_string($con, $rawLocation) . "'";
    }
    $due_date = dateSql($con, 'txt_edit_due_date');
    $disconnection_date = dateSql($con, 'txt_edit_disconnection_date');
    $status = isset($_POST['txt_edit_status']) && $_POST['txt_edit_status'] === '1' ? 1 : 0;
    $date_paid = dateSql($con, 'txt_edit_date_paid');
    $remarks = escf($con, 'txt_edit_remarks');
    $link_to_or = escf($con, 'txt_edit_link_to_or');

    $query = "UPDATE bills_monitoring SET
        date_received = $date_received,
        type_of_billing = '$type_of_billing',
        link_to_file = '$link_to_file',
        amount = $amount,
        location_office = $locationSql,
        due_date = $due_date,
        disconnection_date = $disconnection_date,
        status = $status,
        date_paid = $date_paid,
        remarks = '$remarks',
        link_to_or = '$link_to_or'
        WHERE id = $id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Updated Bill ' . $type_of_billing;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Bill updated successfully']);
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

    @mysqli_query($con, "DELETE FROM bills_monitoring WHERE id IN ($idList)");

    $logMsg = "Batch deleted $count bill(s) (IDs: $idList)";
    $logMsgEsc = mysqli_real_escape_string($con, $logMsg);
    @mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '$logMsgEsc')");

    ob_clean();
    echo json_encode(['success' => true, 'deleted' => $count, 'message' => "$count item(s) deleted"]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
