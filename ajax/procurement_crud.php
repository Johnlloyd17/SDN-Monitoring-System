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

if ($action === 'add') {
    $pr_no = mysqli_real_escape_string($con, $_POST['txt_pr_no'] ?? '');
    $activity_id = mysqli_real_escape_string($con, $_POST['txt_activity_id'] ?? '');
    $activity_name = mysqli_real_escape_string($con, $_POST['txt_activity_name'] ?? '');
    $link_to_file = mysqli_real_escape_string($con, $_POST['txt_link_to_file'] ?? '');
    $project_fund_source = mysqli_real_escape_string($con, $_POST['txt_project_fund_source'] ?? '');
    $type_of_items_procured = mysqli_real_escape_string($con, $_POST['txt_type_of_items_procured'] ?? '');
    $amount = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_amount'] ?? ''));
    $name_of_supplier = mysqli_real_escape_string($con, $_POST['txt_name_of_supplier'] ?? '');
    $jo_po = mysqli_real_escape_string($con, $_POST['txt_jo_po'] ?? '');
    $link_to_attachments = mysqli_real_escape_string($con, $_POST['txt_link_to_attachments'] ?? '');
    $personnel_in_charge = mysqli_real_escape_string($con, $_POST['txt_personnel_in_charge'] ?? '');
    $date_forwarded_to_ro = mysqli_real_escape_string($con, $_POST['txt_date_forwarded_to_ro'] ?? '');
    $transmittal_report = mysqli_real_escape_string($con, $_POST['txt_transmittal_report'] ?? '');
    $payment_status = mysqli_real_escape_string($con, $_POST['txt_payment_status'] ?? '');
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks'] ?? '');

    $action_log = 'Added Procurement PR No: ' . $pr_no;
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");

    $query = "INSERT INTO procurement_tracking (pr_no, activity_id, activity_name, link_to_file, project_fund_source, type_of_items_procured, amount, name_of_supplier, jo_po, link_to_attachments, personnel_in_charge, date_forwarded_to_ro, transmittal_report, payment_status, remarks) VALUES ('$pr_no', '$activity_id', '$activity_name', '$link_to_file', '$project_fund_source', '$type_of_items_procured', '$amount', '$name_of_supplier', '$jo_po', '$link_to_attachments', '$personnel_in_charge', '$date_forwarded_to_ro', '$transmittal_report', '$payment_status', '$remarks')";

    if (mysqli_query($con, $query)) {
        echo json_encode(['success' => true, 'message' => 'Procurement record added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add record: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    $pr_no = mysqli_real_escape_string($con, $_POST['txt_edit_pr_no'] ?? '');
    $activity_id = mysqli_real_escape_string($con, $_POST['txt_edit_activity_id'] ?? '');
    $activity_name = mysqli_real_escape_string($con, $_POST['txt_edit_activity_name'] ?? '');
    $link_to_file = mysqli_real_escape_string($con, $_POST['txt_edit_link_to_file'] ?? '');
    $project_fund_source = mysqli_real_escape_string($con, $_POST['txt_edit_project_fund_source'] ?? '');
    $type_of_items_procured = mysqli_real_escape_string($con, $_POST['txt_edit_type_of_items_procured'] ?? '');
    $amount = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_edit_amount'] ?? ''));
    $name_of_supplier = mysqli_real_escape_string($con, $_POST['txt_edit_name_of_supplier'] ?? '');
    $jo_po = mysqli_real_escape_string($con, $_POST['txt_edit_jo_po'] ?? '');
    $link_to_attachments = mysqli_real_escape_string($con, $_POST['txt_edit_link_to_attachments'] ?? '');
    $personnel_in_charge = mysqli_real_escape_string($con, $_POST['txt_edit_personnel_in_charge'] ?? '');
    $date_forwarded_to_ro = mysqli_real_escape_string($con, $_POST['txt_edit_date_forwarded_to_ro'] ?? '');
    $transmittal_report = mysqli_real_escape_string($con, $_POST['txt_edit_transmittal_report'] ?? '');
    $payment_status = mysqli_real_escape_string($con, $_POST['txt_edit_payment_status'] ?? '');
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks'] ?? '');

    $query = "UPDATE procurement_tracking SET pr_no='$pr_no', activity_id='$activity_id', activity_name='$activity_name', link_to_file='$link_to_file', project_fund_source='$project_fund_source', type_of_items_procured='$type_of_items_procured', amount='$amount', name_of_supplier='$name_of_supplier', jo_po='$jo_po', link_to_attachments='$link_to_attachments', personnel_in_charge='$personnel_in_charge', date_forwarded_to_ro='$date_forwarded_to_ro', transmittal_report='$transmittal_report', payment_status='$payment_status', remarks='$remarks' WHERE id=$id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Edited Procurement PR No: ' . $pr_no;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        echo json_encode(['success' => true, 'message' => 'Procurement record updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update record: ' . mysqli_error($con)]);
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

        $itemQ = mysqli_query($con, "SELECT pr_no FROM procurement_tracking WHERE id = $id");
        $itemRow = mysqli_fetch_assoc($itemQ);
        $itemName = $itemRow ? $itemRow['pr_no'] : 'Unknown';

        if (mysqli_query($con, "DELETE FROM procurement_tracking WHERE id = $id")) {
            $action_log = 'Deleted Procurement PR No: ' . $itemName;
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => "$deleted record(s) deleted"]);
    exit;
}

if ($action === 'get_item') {
    $id = intval($_GET['id'] ?? 0);
    $result = mysqli_query($con, "SELECT * FROM procurement_tracking WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    echo json_encode($row ? $row : ['error' => 'Not found']);
    exit;
}

if ($action === 'filters') {
    $currentProject = isset($_GET['project_fund_source']) ? trim($_GET['project_fund_source']) : '';
    $currentPayment = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';

    $projects = [];
    $r = mysqli_query($con, "SELECT DISTINCT project_fund_source FROM procurement_tracking WHERE project_fund_source != '' ORDER BY project_fund_source");
    while ($row = mysqli_fetch_assoc($r)) $projects[] = $row['project_fund_source'];

    $payments = [];
    $r = mysqli_query($con, "SELECT DISTINCT payment_status FROM procurement_tracking WHERE payment_status != '' ORDER BY payment_status");
    while ($row = mysqli_fetch_assoc($r)) $payments[] = $row['payment_status'];

    $years = [];
    $r = mysqli_query($con, "SELECT DISTINCT YEAR(date_forwarded_to_ro) AS yr FROM procurement_tracking WHERE date_forwarded_to_ro IS NOT NULL ORDER BY yr DESC");
    while ($row = mysqli_fetch_assoc($r)) $years[] = $row['yr'];

    echo json_encode([
        'project_fund_sources' => $projects,
        'payment_statuses' => $payments,
        'years' => $years
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
