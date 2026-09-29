<?php
require_once __DIR__ . '/../auth_check.php';
require_auth_api();
?>
<?php

header('Content-Type: application/json');

include __DIR__ . '/../connection.php';
require_once __DIR__ . '/participant_rows.php';

/**
 * Photos live next to the page. The old code used a bare "photo/" string, which
 * resolved against the working directory and broke under any non-Apache run.
 */
define('PARTICIPANT_PHOTO_DIR', __DIR__ . '/photo/');

function participant_json_out($success, $message, $type = 'success') {
    echo json_encode(array('success' => $success, 'message' => $message, 'type' => $type));
    exit;
}

function participant_log($con, $action) {
    if (!isset($_SESSION['role'])) {
        return;
    }
    $user = $_SESSION['role'];
    $stmt = mysqli_prepare($con, "INSERT INTO tbllogs (user, logdate, action) VALUES (?, NOW(), ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $user, $action);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/** The 14 editable columns, in the order the prepared statement binds them. */
function participant_editable_fields() {
    return array(
        'start', 'end', 'activity', 'indicator', 'fullname', 'sex', 'contact',
        'email', 'mode', 'agency', 'sector', 'project', 'person', 'remarks',
    );
}

$view = isset($_POST['view']) ? (string) $_POST['view'] : '';
$cfg  = participant_view_config($view !== '' ? $view : 'cyber');

// ---------------------------------------------------------------- add
if (isset($_POST['btn_add'])) {
    $vals = array();
    foreach (participant_editable_fields() as $col) {
        $key = 'txt_' . $col;
        $vals[$col] = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
    }

    // The page is scoped to one project, so a record added here must belong to
    // that project. Taking it from the CSV/typed field instead would let the
    // row disappear off the page it was entered on.
    $vals['project'] = $cfg['project'];

    if ($vals['fullname'] === '' || $vals['start'] === '' || $vals['end'] === '') {
        participant_json_out(false, 'Fullname, Start Date and End Date are required.', 'error');
    }

    $cols  = array_keys($vals);
    $types = str_repeat('s', count($cols));
    $sql   = "INSERT INTO tblparticipant (`" . implode('`, `', $cols) . "`) VALUES ("
           . implode(', ', array_fill(0, count($cols), '?')) . ")";

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        participant_json_out(false, 'Could not prepare the insert.', 'error');
    }
    $params = array();
    foreach ($cols as $c) {
        $params[] = $vals[$c];
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        participant_json_out(false, 'Could not save: ' . $err, 'error');
    }
    $id = (int) mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    participant_log($con, 'Added Item: ' . $vals['fullname']);

    // Optional attachments. A failed upload must not undo the record.
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        if (!is_dir(PARTICIPANT_PHOTO_DIR)) {
            @mkdir(PARTICIPANT_PHOTO_DIR, 0777, true);
        }
        foreach ($_FILES['files']['name'] as $i => $orig) {
            if (!isset($_FILES['files']['error'][$i]) || $_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $safe = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $orig);
            $name = round(microtime(true) * 1000) . $safe;
            if (move_uploaded_file($_FILES['files']['tmp_name'][$i], PARTICIPANT_PHOTO_DIR . $name)) {
                $p = mysqli_prepare($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES (?, ?)");
                if ($p) {
                    mysqli_stmt_bind_param($p, 'is', $id, $name);
                    mysqli_stmt_execute($p);
                    mysqli_stmt_close($p);
                }
            }
        }
    }

    participant_json_out(true, 'Participant added.');
}

// ---------------------------------------------------------------- save
if (isset($_POST['btn_save'])) {
    $id = isset($_POST['hidden_id']) ? intval($_POST['hidden_id']) : 0;
    if ($id <= 0) {
        participant_json_out(false, 'Invalid record id.', 'error');
    }

    $vals = array();
    foreach (participant_editable_fields() as $col) {
        $key = 'txt_edit_' . $col;
        $vals[$col] = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
    }
    $vals['project'] = $cfg['project'];

    $cols  = array_keys($vals);
    $set   = array();
    foreach ($cols as $c) {
        $set[] = '`' . $c . '` = ?';
    }
    $types = str_repeat('s', count($cols)) . 'i';
    $sql   = "UPDATE tblparticipant SET " . implode(', ', $set) . " WHERE id = ?";

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        participant_json_out(false, 'Could not prepare the update.', 'error');
    }
    $params = array();
    foreach ($cols as $c) {
        $params[] = $vals[$c];
    }
    $params[] = $id;
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        participant_json_out(false, 'Could not save: ' . $err, 'error');
    }
    $changed = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($changed === 0) {
        // Could be "no such id" or "identical values"; confirm the row exists.
        $chk = mysqli_prepare($con, "SELECT fullname FROM tblparticipant WHERE id = ?");
        mysqli_stmt_bind_param($chk, 'i', $id);
        mysqli_stmt_execute($chk);
        $r = mysqli_stmt_get_result($chk);
        $row = $r ? mysqli_fetch_assoc($r) : null;
        mysqli_stmt_close($chk);
        if (!$row) {
            participant_json_out(false, 'No matching record was found to save.', 'error');
        }
        participant_json_out(true, 'No changes to save.');
    }

    // The old code logged an undefined $item_no here, so the audit row read
    // "Updated Item " with no identifier at all.
    participant_log($con, 'Updated Item: ' . $vals['fullname']);

    participant_json_out(true, 'Participant updated.');
}

// ---------------------------------------------------------------- delete
if (isset($_POST['btn_delete'])) {
    if (!isset($_POST['chk_delete']) || !is_array($_POST['chk_delete'])) {
        participant_json_out(false, 'No record was selected to delete.', 'warning');
    }

    $deleted = 0;
    foreach ($_POST['chk_delete'] as $raw) {
        $id = (int) $raw;
        if ($id <= 0) {
            continue;
        }

        $sel = mysqli_prepare($con, "SELECT fullname FROM tblparticipant WHERE id = ?");
        mysqli_stmt_bind_param($sel, 'i', $id);
        mysqli_stmt_execute($sel);
        $res = mysqli_stmt_get_result($sel);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($sel);
        if (!$row) {
            continue;
        }

        $del = mysqli_prepare($con, "DELETE FROM tblparticipant WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $id);
        $ok = mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        if ($ok) {
            $deleted++;
            participant_log($con, 'Deleted Item: ' . $row['fullname']);
            // Attachment rows are deliberately left alone: tblactivityphoto is
            // shared by ~11 modules and has no discriminator, so deleting by
            // activityid could remove another module's rows.
        }
    }

    if ($deleted === 0) {
        participant_json_out(false, 'No matching record was found to delete.', 'warning');
    }
    echo json_encode(array(
        'success' => true,
        'message' => $deleted === 1 ? '1 record deleted.' : $deleted . ' records deleted.',
        'type'    => 'success',
        'deleted' => $deleted,
    ));
    exit;
}

// ---------------------------------------------------------------- add image
if (isset($_POST['btn_addimage'])) {
    $id = isset($_POST['hidden_id']) ? intval($_POST['hidden_id']) : 0;
    if ($id <= 0) {
        participant_json_out(false, 'Invalid record id.', 'error');
    }

    $chk = mysqli_prepare($con, "SELECT id FROM tblparticipant WHERE id = ?");
    mysqli_stmt_bind_param($chk, 'i', $id);
    mysqli_stmt_execute($chk);
    $res = mysqli_stmt_get_result($chk);
    $exists = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($chk);
    if (!$exists) {
        participant_json_out(false, 'No matching record was found.', 'error');
    }

    if (!isset($_FILES['photos']) || !is_array($_FILES['photos']['name'])) {
        participant_json_out(false, 'No file was selected.', 'warning');
    }
    if (!is_dir(PARTICIPANT_PHOTO_DIR)) {
        @mkdir(PARTICIPANT_PHOTO_DIR, 0777, true);
    }

    $saved = 0;
    foreach ($_FILES['photos']['name'] as $i => $orig) {
        if (!isset($_FILES['photos']['error'][$i]) || $_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $safe = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $orig);
        $name = round(microtime(true) * 1000) . $safe;
        if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], PARTICIPANT_PHOTO_DIR . $name)) {
            $p = mysqli_prepare($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES (?, ?)");
            if ($p) {
                mysqli_stmt_bind_param($p, 'is', $id, $name);
                mysqli_stmt_execute($p);
                mysqli_stmt_close($p);
            }
            $saved++;
        }
    }

    if ($saved === 0) {
        participant_json_out(false, 'No file could be saved.', 'error');
    }
    participant_json_out(true, $saved === 1 ? '1 file added.' : $saved . ' files added.');
}

// ---------------------------------------------------------------- remove image
if (isset($_POST['btn_remove'])) {
    if (!isset($_POST['chk_deletephoto']) || !is_array($_POST['chk_deletephoto'])) {
        participant_json_out(false, 'No file was selected to delete.', 'warning');
    }

    $removed = 0;
    foreach ($_POST['chk_deletephoto'] as $raw) {
        $photoId = (int) $raw;
        if ($photoId <= 0) {
            continue;
        }

        $sel = mysqli_prepare($con, "SELECT filename FROM tblactivityphoto WHERE id = ?");
        mysqli_stmt_bind_param($sel, 'i', $photoId);
        mysqli_stmt_execute($sel);
        $res = mysqli_stmt_get_result($sel);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($sel);
        if (!$row) {
            continue;
        }

        $del = mysqli_prepare($con, "DELETE FROM tblactivityphoto WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $photoId);
        $ok = mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        if ($ok) {
            $removed++;
            // The old code dropped the row but left the file on disk, which is
            // how pages/participant/photo/ accumulated orphans.
            $file = PARTICIPANT_PHOTO_DIR . basename($row['filename']);
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    if ($removed === 0) {
        participant_json_out(false, 'No matching file was found to delete.', 'warning');
    }
    participant_json_out(true, $removed === 1 ? '1 file removed.' : $removed . ' files removed.');
}

participant_json_out(false, 'Unknown action.', 'error');
