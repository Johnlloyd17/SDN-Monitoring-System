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

if ($action === 'filters') {
    $projectFundSource = isset($_GET['project_fund_source']) ? trim($_GET['project_fund_source']) : '';
    $paymentStatus = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';

    function getDistinctValues($con, $column, $filters = []) {
        $where = ["$column IS NOT NULL", "$column != ''"];
        $params = [];
        $types = '';

        foreach ($filters as $col => $val) {
            if ($val !== '' && $col !== 'year') {
                $where[] = "$col = ?";
                $params[] = $val;
                $types .= 's';
            }
        }

        $whereClause = implode(' AND ', $where);
        $query = "SELECT DISTINCT $column FROM procurement_tracking WHERE $whereClause ORDER BY $column ASC";

        if (!empty($params)) {
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $result = mysqli_query($con, $query);
        }

        $values = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $val = reset($row);
            if ($val !== null && trim($val) !== '') {
                $values[] = $val;
            }
        }
        return $values;
    }

    function getYearValues($con, $filters = []) {
        $where = ["date_forwarded_to_ro IS NOT NULL"];
        $params = [];
        $types = '';

        foreach ($filters as $col => $val) {
            if ($val !== '' && $col !== 'year') {
                $where[] = "$col = ?";
                $params[] = $val;
                $types .= 's';
            }
        }

        $whereClause = implode(' AND ', $where);
        $query = "SELECT DISTINCT YEAR(date_forwarded_to_ro) AS yr FROM procurement_tracking WHERE $whereClause AND date_forwarded_to_ro IS NOT NULL ORDER BY yr ASC";

        if (!empty($params)) {
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $result = mysqli_query($con, $query);
        }

        $values = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['yr'] !== null) {
                $values[] = $row['yr'];
            }
        }
        return $values;
    }

    echo json_encode([
        'project_fund_sources' => getDistinctValues($con, 'project_fund_source', ['payment_status' => $paymentStatus]),
        'payment_statuses' => getDistinctValues($con, 'payment_status', ['project_fund_source' => $projectFundSource]),
        'years' => getYearValues($con, ['project_fund_source' => $projectFundSource, 'payment_status' => $paymentStatus])
    ]);
    exit;
}

if ($action === 'get_item' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($con, "SELECT * FROM procurement_tracking WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($row) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Record not found']);
    }
    exit;
}

$activityIdEnum = [
    'GOVNET-0001','GOVNET-0002','FW4A-0001','FW4A-0002','FW4A-0003','FW4A-0004','CSB-0001','ILCDB-0001','PNPKI-0001','FW4A-0005','FW4A-0006','FW4A-0007','PNPKI-0002','FW4A-0008','FW4A-0009','FW4A-0010','FW4A-0011','FW4A-0012','ILCDB-0002','FW4A-0013','FW4A-0014','ILCDB-0003','PNPKI-0003','PNPKI-0004','FW4A-0015','OTHERS-0001','CSB-0002','CSB-0003','OTHERS-0002','GOVNET-0003','FW4A-0016','ILCDB-0005','FW4A-0017','FW4A-0018','ILCDB-0009','ILCDB-0006','ILCDB-0007','FW4A-0019','ILCDB-0011','ILCDB-0010','FW4A-0020','FW4A-0021','FW4A-0022','FW4A-0023','PNPKI-0005','FW4A-0024','FW4A-0025','PNPKI-0006','OTHERS-0003','PNPKI-0007','PNPKI-0008','ILCDB-0013','ILCDB-0014','CSB-0006','CSB-0005','ILCDB-0016','CSB-0007','ILCDB-0017','OTHERS-0004','ILCDB-0035','ILCDB-0036','ILCDB-0018','ILCDB-0019','ILCDB-0015','ILCDB-0021','ILCDB-0020','ILCDB-0029','ILCDB-0030','ILCDB-0022','ILCDB-0034','ILCDB-0031','ILCDB-0028','ILCDB-0027','ILCDB-0032','ILCDB-0033','ILCDB-0023','ILCDB-0024','ILCDB-0004','ILCDB-0008','ILCDB-0012','ILCDB-0025','ILCDB-0026'
];

function validateActivityId($con, $activityId) {
    global $activityIdEnum;
    if ($activityId === '') {
        return ['ok' => true, 'value' => ''];
    }
    if (!in_array($activityId, $activityIdEnum, true)) {
        return ['ok' => false, 'error' => 'Invalid Activity ID selected.'];
    }
    return ['ok' => true, 'value' => $activityId];
}

$typeOfItemsEnum = ['Meals', 'Fuel (Diesel)', 'Office Supplies', 'Plaque', 'Service'];

function validateTypeOfItems($con, $value) {
    global $typeOfItemsEnum;
    if ($value === '') {
        return ['ok' => true, 'value' => ''];
    }
    if (!in_array($value, $typeOfItemsEnum, true)) {
        return ['ok' => false, 'error' => 'Invalid Type of Items selected.'];
    }
    return ['ok' => true, 'value' => $value];
}

if ($action === 'add') {
    $pr_no = mysqli_real_escape_string($con, $_POST['txt_pr_no'] ?? '');
    $activityCheck = validateActivityId($con, $_POST['txt_activity_id'] ?? '');
    if (!$activityCheck['ok']) {
        echo json_encode(['success' => false, 'error' => $activityCheck['error']]);
        exit;
    }
    $activity_id = mysqli_real_escape_string($con, $activityCheck['value']);
    $typeCheck = validateTypeOfItems($con, $_POST['txt_type_of_items_procured'] ?? '');
    if (!$typeCheck['ok']) {
        echo json_encode(['success' => false, 'error' => $typeCheck['error']]);
        exit;
    }
    $type_of_items_procured = mysqli_real_escape_string($con, $typeCheck['value']);
    $activity_name = mysqli_real_escape_string($con, $_POST['txt_activity_name'] ?? '');
    $link_to_file = mysqli_real_escape_string($con, $_POST['txt_link_to_file'] ?? '');
    $project_fund_source = mysqli_real_escape_string($con, $_POST['txt_project_fund_source'] ?? '');
    $amount = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_amount'] ?? ''));
    $name_of_supplier = mysqli_real_escape_string($con, $_POST['txt_name_of_supplier'] ?? '');
    $jo_po = mysqli_real_escape_string($con, $_POST['txt_jo_po'] ?? '');
    $link_to_attachments = mysqli_real_escape_string($con, $_POST['txt_link_to_attachments'] ?? '');
    $personnel_in_charge = mysqli_real_escape_string($con, $_POST['txt_personnel_in_charge'] ?? '');
    $date_forwarded_to_ro = mysqli_real_escape_string($con, $_POST['txt_date_forwarded_to_ro'] ?? '');
    $transmittal_report = mysqli_real_escape_string($con, $_POST['txt_transmittal_report'] ?? '');
    $payment_status = mysqli_real_escape_string($con, $_POST['txt_payment_status'] ?? '');
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks'] ?? '');

    $action_log = 'Added Procurement Record: ' . $pr_no;
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");

    $query = "INSERT INTO procurement_tracking (pr_no, activity_id, activity_name, link_to_file, project_fund_source, type_of_items_procured, amount, name_of_supplier, jo_po, link_to_attachments, personnel_in_charge, date_forwarded_to_ro, transmittal_report, payment_status, remarks) VALUES ('$pr_no', '$activity_id', '$activity_name', '$link_to_file', '$project_fund_source', '$type_of_items_procured', '$amount', '$name_of_supplier', '$jo_po', '$link_to_attachments', '$personnel_in_charge', '$date_forwarded_to_ro', '$transmittal_report', '$payment_status', '$remarks')";

    if (mysqli_query($con, $query)) {
        echo json_encode(['success' => true, 'message' => 'Record added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add record: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    $pr_no = mysqli_real_escape_string($con, $_POST['txt_edit_pr_no'] ?? '');
    $activityCheck = validateActivityId($con, $_POST['txt_edit_activity_id'] ?? '');
    if (!$activityCheck['ok']) {
        echo json_encode(['success' => false, 'error' => $activityCheck['error']]);
        exit;
    }
    $activity_id = mysqli_real_escape_string($con, $activityCheck['value']);
    $typeCheck = validateTypeOfItems($con, $_POST['txt_edit_type_of_items_procured'] ?? '');
    if (!$typeCheck['ok']) {
        echo json_encode(['success' => false, 'error' => $typeCheck['error']]);
        exit;
    }
    $type_of_items_procured = mysqli_real_escape_string($con, $typeCheck['value']);
    $activity_name = mysqli_real_escape_string($con, $_POST['txt_edit_activity_name'] ?? '');
    $link_to_file = mysqli_real_escape_string($con, $_POST['txt_edit_link_to_file'] ?? '');
    $project_fund_source = mysqli_real_escape_string($con, $_POST['txt_edit_project_fund_source'] ?? '');
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
        $action_log = 'Edited Procurement Record: ' . $pr_no;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
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
            $action_log = 'Deleted Procurement Record: ' . $itemName;
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => "$deleted record(s) deleted"]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
