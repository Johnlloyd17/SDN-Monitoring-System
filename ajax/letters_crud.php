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

$knownTypes = ['Incoming','Outgoing'];
$knownFw4a = ['Request','Provision'];
$knownResp = ['Y','N'];

if ($action === 'add') {
    $date_val = dateSql($con, 'txt_date');
    $type = $_POST['txt_type'] ?? '';
    if (!in_array($type, $knownTypes)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid Type "'.$type.'". Must be Incoming or Outgoing.']);
        exit;
    }
    $subject = escf($con, 'txt_subject');
    $fw4a = $_POST['txt_fw4a'] ?? '';
    if ($fw4a !== '' && !in_array($fw4a, $knownFw4a)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid FW4A "'.$fw4a.'". Must be Request or Provision.']);
        exit;
    }
    $fw4aSql = $fw4a !== '' ? "'$fw4a'" : 'NULL';
    $link_incoming = escf($con, 'txt_link_incoming');
    $for_response = $_POST['txt_for_response'] ?? '';
    if ($for_response !== '' && !in_array($for_response, $knownResp)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid For Response? "'.$for_response.'". Must be Y, N, or blank.']);
        exit;
    }
    $forRespSql = $for_response !== '' ? "'$for_response'" : 'NULL';
    $date_responded = dateSql($con, 'txt_date_responded');
    $link_outgoing = escf($con, 'txt_link_outgoing');
    $responsible_person = escf($con, 'txt_responsible_person');
    $who_attended = escf($con, 'txt_who_attended');
    $remarks = escf($con, 'txt_remarks');
    $post_activity_report = escf($con, 'txt_post_activity_report');

    $query = "INSERT INTO letters_monitoring (
        date, type, subject, fw4a, link_incoming, for_response,
        date_responded, link_outgoing, responsible_person, who_attended,
        remarks, post_activity_report
    ) VALUES (
        $date_val, '$type', '$subject', $fw4aSql, '$link_incoming', $forRespSql,
        $date_responded, '$link_outgoing', '$responsible_person', '$who_attended',
        '$remarks', '$post_activity_report'
    )";

    if (mysqli_query($con, $query)) {
        $action_log = 'Added Letter ' . $subject;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Letter added successfully']);
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
    $date_val = dateSql($con, 'txt_edit_date');
    $type = $_POST['txt_edit_type'] ?? '';
    if (!in_array($type, $knownTypes)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid Type "'.$type.'". Must be Incoming or Outgoing.']);
        exit;
    }
    $subject = escf($con, 'txt_edit_subject');
    $fw4a = $_POST['txt_edit_fw4a'] ?? '';
    if ($fw4a !== '' && !in_array($fw4a, $knownFw4a)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid FW4A "'.$fw4a.'". Must be Request or Provision.']);
        exit;
    }
    $fw4aSql = $fw4a !== '' ? "'$fw4a'" : 'NULL';
    $link_incoming = escf($con, 'txt_edit_link_incoming');
    $for_response = $_POST['txt_edit_for_response'] ?? '';
    if ($for_response !== '' && !in_array($for_response, $knownResp)) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Invalid For Response? "'.$for_response.'". Must be Y, N, or blank.']);
        exit;
    }
    $forRespSql = $for_response !== '' ? "'$for_response'" : 'NULL';
    $date_responded = dateSql($con, 'txt_edit_date_responded');
    $link_outgoing = escf($con, 'txt_edit_link_outgoing');
    $responsible_person = escf($con, 'txt_edit_responsible_person');
    $who_attended = escf($con, 'txt_edit_who_attended');
    $remarks = escf($con, 'txt_edit_remarks');
    $post_activity_report = escf($con, 'txt_edit_post_activity_report');

    $query = "UPDATE letters_monitoring SET
        date = $date_val,
        type = '$type',
        subject = '$subject',
        fw4a = $fw4aSql,
        link_incoming = '$link_incoming',
        for_response = $forRespSql,
        date_responded = $date_responded,
        link_outgoing = '$link_outgoing',
        responsible_person = '$responsible_person',
        who_attended = '$who_attended',
        remarks = '$remarks',
        post_activity_report = '$post_activity_report'
        WHERE id = $id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Updated Letter ' . $subject;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Letter updated successfully']);
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

    @mysqli_query($con, "DELETE FROM letters_monitoring WHERE id IN ($idList)");

    $logMsg = "Batch deleted $count letter(s) (IDs: $idList)";
    $logMsgEsc = mysqli_real_escape_string($con, $logMsg);
    @mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '$logMsgEsc')");

    ob_clean();
    echo json_encode(['success' => true, 'deleted' => $count, 'message' => "$count item(s) deleted"]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
