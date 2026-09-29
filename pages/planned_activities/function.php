<?php
require_once __DIR__ . '/../auth_check.php';
require_auth_api();

require_once __DIR__ . '/planned_rows.php';
require_once __DIR__ . '/../connection.php';

// Attachments live next to this file. The original code used a bare "photo/"
// relative path, which resolved against the CWD rather than this module.
if (!defined('PLANNED_PHOTO_DIR')) { define('PLANNED_PHOTO_DIR', __DIR__ . '/photo'); }
if (!is_dir(PLANNED_PHOTO_DIR)) { @mkdir(PLANNED_PHOTO_DIR, 0777, true); }

$view = isset($_POST['view']) ? (string) $_POST['view'] : '';
$cfg  = planned_view_config($view);
if ($cfg === null) {
    planned_json_out(array('success' => false, 'error' => 'Unknown view.'), 400);
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user = isset($_SESSION['username']) ? $_SESSION['username'] : '';

/** The form fields that map onto targets_initiatives columns. */
function planned_read_fields($prefix)
{
    // The form field names are the column names with a prefix: the add modal
    // posts txt_<column> and the edit modal posts txt_edit_<column>.
    $cols = array(
        'start', 'end', 'subproject', 'indicator', 'activity', 'training',
        'municipality', 'barangay', 'district', 'agency', 'mode', 'sector',
        'person', 'resource', 'participants', 'completers', 'male', 'female',
        'approved', 'mov', 'remarks', 'type',
    );
    $out = array();
    foreach ($cols as $col) {
        $out[$col] = isset($_POST[$prefix . $col]) ? trim((string) $_POST[$prefix . $col]) : '';
    }
    // The old add modal posted "completers" without any prefix.
    if ($out['completers'] === '' && isset($_POST['completers'])) {
        $out['completers'] = trim((string) $_POST['completers']);
    }
    return $out;
}

function planned_log($con, $action)
{
    if (isset($_SESSION['role'])) {
        $u = mysqli_real_escape_string($con, $_SESSION['role']);
        $a = mysqli_real_escape_string($con, $action);
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('$u', NOW(), '$a')");
    }
}

/**
 * Mirrors a planned activity into tblactivity.
 *
 * The original code INSERTed a new tblactivity row on every save, so editing a
 * planned activity that had remarks produced a duplicate phantom Activity row
 * (and on add it wrote $type into the `indicator` column). On add we now store
 * the mirrored row's id in targets_initiatives.mirrored_activity_id; on save we
 * UPDATE that row instead of inserting a duplicate.
 *
 * An empty `remarks` leaves any existing mirror untouched, as before.
 */
function planned_sync_mirror($con, $plannedId, $v, $project, $isInsert, $oldVals = null)
{
    if ($v['remarks'] === '') { return; }

    $columns = array('start', 'end', 'project', 'subproject', 'indicator', 'activity',
        'training', 'municipality', 'barangay', 'district', 'agency', 'mode', 'sector',
        'person', 'resource', 'participants', 'completers', 'male', 'female',
        'approved', 'mov', 'remarks');
    $vals = array();
    foreach ($columns as $c) { $vals[$c] = ($c === 'project') ? $project : $v[$c]; }

    $mirrorId = 0;
    if (!$isInsert) {
        $st = mysqli_prepare($con, "SELECT mirrored_activity_id FROM targets_initiatives WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $plannedId);
        mysqli_stmt_execute($st);
        $row = mysqli_stmt_get_result($st)->fetch_assoc();
        if ($row && !empty($row['mirrored_activity_id'])) {
            $chk = mysqli_prepare($con, "SELECT id FROM tblactivity WHERE id = ?");
            mysqli_stmt_bind_param($chk, 'i', $row['mirrored_activity_id']);
            mysqli_stmt_execute($chk);
            $m = mysqli_stmt_get_result($chk)->fetch_assoc();
            if ($m) { $mirrorId = (int) $row['mirrored_activity_id']; }
        }
        if ($mirrorId === 0 && $oldVals !== null) {
            // No stored link: the row predates the mirrored_activity_id column.
            // Match on the values the planned row had BEFORE this save, not the
            // new ones, otherwise renaming the activity would always miss and
            // insert a duplicate.
            $fb = mysqli_prepare($con, "SELECT id FROM tblactivity
                     WHERE project = ? AND activity <=> ? AND start <=> ? AND end <=> ?
                     ORDER BY id DESC LIMIT 1");
            $s = $oldVals['start']; $e = $oldVals['end'];
            $p = $oldVals['project']; $a = $oldVals['activity'];
            mysqli_stmt_bind_param($fb, 'ssss', $p, $a, $s, $e);
            mysqli_stmt_execute($fb);
            $f = mysqli_stmt_get_result($fb)->fetch_assoc();
            if ($f) { $mirrorId = (int) $f['id']; }
        }
    }

    $sets = array(); $bind = array();
    foreach ($columns as $c) { $sets[] = "`$c` = ?"; $bind[] = &$vals[$c]; }
    $sql = "UPDATE tblactivity SET " . implode(', ', $sets) . " WHERE id = ?";

    if ($mirrorId === 0) {
        $ins = "INSERT INTO tblactivity (" . implode(',', $columns) . ") VALUES ("
             . implode(',', array_fill(0, count($columns), '?')) . ")";
        $insVals = array();
        foreach ($columns as $c) { $insVals[] = $vals[$c]; }
        $st = mysqli_prepare($con, $ins);
        $types = str_repeat('s', count($insVals));
        mysqli_stmt_bind_param($st, $types, ...$insVals);
        if (!mysqli_stmt_execute($st)) { return; }
        $mirrorId = (int) mysqli_stmt_insert_id($st);
    } else {
        $st = mysqli_prepare($con, $sql);
        $types = str_repeat('s', count($columns)) . 'i';
        $bind[] = $mirrorId;
        mysqli_stmt_bind_param($st, $types, ...$bind);
        mysqli_stmt_execute($st);
    }

    $up = mysqli_prepare($con, "UPDATE targets_initiatives SET mirrored_activity_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($up, 'ii', $mirrorId, $plannedId);
    mysqli_stmt_execute($up);
}

if (isset($_POST['btn_add'])) {
    if (!planned_can_manage($cfg)) { planned_json_out(array('success' => false, 'error' => 'Not authorized.'), 403); }

    $v = planned_read_fields('txt_');
    if ($v['activity'] === '') { planned_json_out(array('success' => false, 'error' => 'Activity Name is required.'), 400); }

    $project = $cfg['project']; // forced from the view, never taken from the form
    $cols = array('start', 'end', 'project', 'subproject', 'indicator', 'activity', 'training',
        'municipality', 'barangay', 'district', 'agency', 'mode', 'sector', 'person',
        'resource', 'participants', 'completers', 'male', 'female', 'approved', 'mov',
        'remarks', 'type');
    $vals = array();
    foreach ($cols as $c) { $vals[] = ($c === 'project') ? $project : $v[$c]; }

    $ph  = implode(',', array_fill(0, count($cols), '?'));
    $st  = mysqli_prepare($con, "INSERT INTO targets_initiatives (" . implode(',', $cols) . ") VALUES ($ph)");
    mysqli_stmt_bind_param($st, str_repeat('s', count($cols)), ...$vals);
    if (!mysqli_stmt_execute($st)) {
        planned_json_out(array('success' => false, 'error' => 'Could not save the activity.'), 500);
    }
    $newId = (int) mysqli_stmt_insert_id($st);

    planned_sync_mirror($con, $newId, $v, $project, true);

    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $k => $tmp) {
            if (!is_uploaded_file($tmp)) { continue; }
            $orig = isset($_FILES['files']['name'][$k]) ? $_FILES['files']['name'][$k] : 'file';
            $name = round(microtime(true) * 1000) . preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $orig);
            if (move_uploaded_file($tmp, PLANNED_PHOTO_DIR . '/' . $name)) {
                $ip = mysqli_prepare($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES (?, ?)");
                mysqli_stmt_bind_param($ip, 'is', $newId, $name);
                mysqli_stmt_execute($ip);
            }
        }
    }

    planned_log($con, 'Added Item: ' . $v['activity']);
    planned_json_out(array('success' => true, 'id' => $newId, 'message' => 'Activity added.'));
}

if (isset($_POST['btn_save'])) {
    if (!planned_can_manage($cfg)) { planned_json_out(array('success' => false, 'error' => 'Not authorized.'), 403); }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    if ($id <= 0) { planned_json_out(array('success' => false, 'error' => 'Missing record id.'), 400); }

    $v = planned_read_fields('txt_edit_');
    if ($v['activity'] === '') { planned_json_out(array('success' => false, 'error' => 'Activity Name is required.'), 400); }

    // Confirm the row really belongs to this view before touching it, and keep
    // the pre-update values so the mirror can be matched even if this save
    // renames the activity.
    $own = mysqli_prepare($con, "SELECT id, start, end, project, activity FROM targets_initiatives WHERE id = ? AND project = ?");
    $project = $cfg['project'];
    mysqli_stmt_bind_param($own, 'is', $id, $project);
    mysqli_stmt_execute($own);
    $existing = mysqli_stmt_get_result($own)->fetch_assoc();
    if (!$existing) {
        planned_json_out(array('success' => false, 'error' => 'Record not found in this project.'), 404);
    }

    $cols = array('start', 'end', 'subproject', 'indicator', 'activity', 'training',
        'municipality', 'barangay', 'district', 'agency', 'mode', 'sector', 'person',
        'resource', 'participants', 'completers', 'male', 'female', 'approved', 'mov',
        'remarks', 'type', 'project');
    $sets = array(); $vals = array();
    foreach ($cols as $c) { $sets[] = "`$c` = ?"; $vals[] = ($c === 'project') ? $project : $v[$c]; }
    $st = mysqli_prepare($con, "UPDATE targets_initiatives SET " . implode(', ', $sets) . " WHERE id = ?");
    $vals[] = $id;
    mysqli_stmt_bind_param($st, str_repeat('s', count($cols)) . 'i', ...$vals);
    if (!mysqli_stmt_execute($st)) {
        planned_json_out(array('success' => false, 'error' => 'Could not update the activity.'), 500);
    }

    planned_sync_mirror($con, $id, $v, $project, false, $existing);

    planned_log($con, 'Updated Item: ' . $v['activity']);
    planned_json_out(array('success' => true, 'message' => 'Activity updated.'));
}

if (isset($_POST['btn_delete'])) {
    if (!planned_can_manage($cfg)) { planned_json_out(array('success' => false, 'error' => 'Not authorized.'), 403); }

    $ids = isset($_POST['chk_delete']) ? (array) $_POST['chk_delete'] : array();
    if (!$ids) { planned_json_out(array('success' => false, 'error' => 'Nothing selected.'), 400); }

    $project = $cfg['project'];
    $done = 0; $names = array();
    foreach ($ids as $raw) {
        $id = (int) $raw;
        if ($id <= 0) { continue; }
        // Capture the mirrored activity id so the mirror can be cleaned up.
        $st = mysqli_prepare($con, "SELECT activity, mirrored_activity_id FROM targets_initiatives WHERE id = ? AND project = ?");
        mysqli_stmt_bind_param($st, 'is', $id, $project);
        mysqli_stmt_execute($st);
        $row = mysqli_stmt_get_result($st)->fetch_assoc();
        if (!$row) { continue; }
        if (!empty($row['mirrored_activity_id'])) {
            $md = (int) $row['mirrored_activity_id'];
            $ds = mysqli_prepare($con, "DELETE FROM tblactivity WHERE id = ?");
            mysqli_stmt_bind_param($ds, 'i', $md);
            mysqli_stmt_execute($ds);
        }
        $dl = mysqli_prepare($con, "DELETE FROM targets_initiatives WHERE id = ? AND project = ?");
        mysqli_stmt_bind_param($dl, 'is', $id, $project);
        mysqli_stmt_execute($dl);
        if (mysqli_stmt_affected_rows($dl) > 0) {
            $done++;
            $names[] = $row['activity'];
            planned_log($con, 'Deleted Item: ' . $row['activity']);
        }
    }
    planned_json_out(array('success' => true, 'deleted' => $done, 'message' => $done . ' record(s) deleted.'));
}

if (isset($_POST['btn_addimage'])) {
    if (!planned_can_manage($cfg)) { planned_json_out(array('success' => false, 'error' => 'Not authorized.'), 403); }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    $project = $cfg['project'];
    $own = mysqli_prepare($con, "SELECT id FROM targets_initiatives WHERE id = ? AND project = ?");
    mysqli_stmt_bind_param($own, 'is', $id, $project);
    mysqli_stmt_execute($own);
    if (!mysqli_stmt_get_result($own)->fetch_assoc()) {
        planned_json_out(array('success' => false, 'error' => 'Record not found in this project.'), 404);
    }

    $added = 0;
    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['tmp_name'] as $k => $tmp) {
            if (!is_uploaded_file($tmp)) { continue; }
            $orig = isset($_FILES['photos']['name'][$k]) ? $_FILES['photos']['name'][$k] : 'file';
            $name = round(microtime(true) * 1000) . preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $orig);
            if (move_uploaded_file($tmp, PLANNED_PHOTO_DIR . '/' . $name)) {
                $ip = mysqli_prepare($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES (?, ?)");
                mysqli_stmt_bind_param($ip, 'is', $id, $name);
                mysqli_stmt_execute($ip);
                $added++;
            }
        }
    }
    planned_json_out(array('success' => true, 'added' => $added, 'message' => $added . ' file(s) uploaded.'));
}

if (isset($_POST['btn_remove'])) {
    if (!planned_can_manage($cfg)) { planned_json_out(array('success' => false, 'error' => 'Not authorized.'), 403); }

    $ids = isset($_POST['chk_deletephoto']) ? (array) $_POST['chk_deletephoto'] : array();
    if (!$ids) { planned_json_out(array('success' => false, 'error' => 'Nothing selected.'), 400); }

    $removed = 0; $skipped = 0;
    foreach ($ids as $raw) {
        $pid = (int) $raw;
        if ($pid <= 0) { continue; }
        $st = mysqli_prepare($con, "SELECT activityid, filename FROM tblactivityphoto WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $pid);
        mysqli_stmt_execute($st);
        $row = mysqli_stmt_get_result($st)->fetch_assoc();
        if (!$row) { continue; }

        // tblactivityphoto is shared by every module that uses attachments and
        // has no module column, so confirm the parent row exists here AND that
        // the file actually lives in this module's photo directory before
        // deleting the row. Otherwise a stale id from another module would
        // have its attachment record removed.
        $aid = (int) $row['activityid'];
        $own = mysqli_prepare($con, "SELECT id FROM targets_initiatives WHERE id = ?");
        mysqli_stmt_bind_param($own, 'i', $aid);
        mysqli_stmt_execute($own);
        if (!mysqli_stmt_get_result($own)->fetch_assoc()) { $skipped++; continue; }

        $safe = basename($row['filename']);
        $path = PLANNED_PHOTO_DIR . '/' . $safe;
        if (!is_file($path)) { $skipped++; continue; }

        $dl = mysqli_prepare($con, "DELETE FROM tblactivityphoto WHERE id = ?");
        mysqli_stmt_bind_param($dl, 'i', $pid);
        mysqli_stmt_execute($dl);
        @unlink($path);
        $removed++;
    }
    $msg = $removed . ' file(s) removed.';
    if ($skipped) { $msg .= ' ' . $skipped . ' skipped (not owned by this module).'; }
    planned_json_out(array('success' => true, 'removed' => $removed, 'skipped' => $skipped, 'message' => $msg));
}

planned_json_out(array('success' => false, 'error' => 'Unknown action.'), 400);
