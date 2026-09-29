<?php
require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();

require_once __DIR__ . '/../pages/planned_activities/planned_rows.php';
require_once __DIR__ . '/../pages/connection.php';

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$view   = isset($_GET['view']) ? (string) $_GET['view'] : '';
$cfg    = planned_view_config($view);

if ($cfg === null) {
    planned_json_out(array('error' => 'Unknown view.'), 400);
}

if ($id <= 0) {
    planned_json_out(array('error' => 'Missing id.'), 400);
}

if ($action === 'item') {
    $stmt = mysqli_prepare($con, "SELECT * FROM targets_initiatives WHERE id = ? AND project = ?");
    $project = $cfg['project'];
    mysqli_stmt_bind_param($stmt, 'is', $id, $project);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    if ($row) {
        planned_json_out($row);
    }
    planned_json_out(array('error' => 'Not found'), 404);
}

if ($action === 'photos') {
    // The original endpoint returned an empty array here, so attachments
    // uploaded from the add/edit modals were never shown in the view modal.
    $stmt = mysqli_prepare($con, "SELECT id, filename FROM tblactivityphoto WHERE activityid = ? ORDER BY id");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $out = array();
    while ($p = mysqli_fetch_assoc($res)) { $out[] = $p; }
    mysqli_stmt_close($stmt);
    planned_json_out($out);
}

planned_json_out(array('error' => 'Invalid action'), 400);
