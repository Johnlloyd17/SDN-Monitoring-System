<?php
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
require_once __DIR__ . '/letter_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../connection.php'; }

$module = letter_module();
$manage = letter_can_manage();

/**
 * Reads the posted columns for a form prefix, e.g. 'txt_' maps the add form's
 * `txt_locality` and 'txt_edit_' the edit form's `txt_edit_locality`. The
 * returned array is keyed by column name and every value is a string, ready
 * for binding.
 */
function letter_read_fields($prefix)
{
    $fields = array();
    foreach (array_keys(letter_columns()) as $col) {
        $key = $prefix . $col;
        $fields[$col] = isset($_POST[$key]) ? (string) $_POST[$key] : '';
    }
    return $fields;
}

function letter_log($con, $action)
{
    if (!isset($_SESSION['role'])) { return; }
    $user = mysqli_real_escape_string($con, $_SESSION['role']);
    $act  = mysqli_real_escape_string($con, $action);
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('$user', NOW(), '$act')");
}

/** Confirms a letter row exists before attachments are touched. */
function letter_row_exists($con, $id)
{
    $module = letter_module();
    $stmt = mysqli_prepare($con, "SELECT id, location FROM {$module['table']} WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ? $row : null;
}

/**
 * Records one attachment against a letter. Split out of the upload so the
 * insert can be exercised without a real HTTP upload: binding the filename as
 * an integer would silently store 0 and orphan the file on disk.
 */
function letter_record_photo($con, $id, $name)
{
    $module = letter_module();
    $stmt = mysqli_prepare($con, "INSERT INTO {$module['photos']} (activityid, filename) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'is', $id, $name);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function letter_store_photo($con, $id, $file)
{
    $module = letter_module();
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if (!is_dir($module['photoDir']) && !mkdir($module['photoDir'], 0777, true) && !is_dir($module['photoDir'])) {
        return null;
    }

    $milliseconds = round(microtime(true) * 1000);
    $safe = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', (string) $file['name']);
    $name = $milliseconds . $safe;
    $path = $module['photoDir'] . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return null;
    }

    if (!letter_record_photo($con, $id, $name)) {
        @unlink($path);
        return null;
    }
    return $name;
}

// ---------------------------------------------------------------------------
// Add
// ---------------------------------------------------------------------------
if (isset($_POST['btn_add'])) {
    if (!$manage) {
        letter_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $f = letter_read_fields('txt_');
    if (trim($f['locality']) === '') {
        letter_json_out(array('success' => false, 'error' => 'Locality is required.'), 422);
    }

    $district = (int) $f['district'];
    $cols = array('locality', 'barangay', 'location', 'date', 'year', 'type', 'status', 'accomplished', 'remarks');
    $vals = array($f['locality'], $f['barangay'], $f['location'], $f['date'], $f['year'],
                  $f['type'], $f['status'], $f['accomplished'], $f['remarks']);

    $place   = array();
    $binding = '';
    foreach ($cols as $c) { $place[] = '?'; $binding .= 's'; }
    $place[]   = '?';
    $binding  .= 'i';
    $vals[]    = $district;

    $module = letter_module();
    $sql = "INSERT INTO {$module['table']} (" . implode(',', $cols) . ',district) VALUES (' . implode(',', $place) . ')';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, $binding, ...$vals);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        letter_json_out(array('success' => false, 'error' => 'Could not save the letter.'), 500);
    }

    $id = (int) mysqli_insert_id($con);
    letter_log($con, 'Added FWFA Letter: ' . $f['locality']);

    $added = 0;
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['name'] as $k => $name) {
            $single = array(
                'name'     => $name,
                'tmp_name' => $_FILES['files']['tmp_name'][$k],
                'error'    => $_FILES['files']['error'][$k],
            );
            if (letter_store_photo($con, $id, $single)) { $added++; }
        }
    }

    letter_json_out(array('success' => true, 'id' => $id, 'photos' => $added));
}

// ---------------------------------------------------------------------------
// Update
// ---------------------------------------------------------------------------
if (isset($_POST['btn_save'])) {
    if (!$manage) {
        letter_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    $existing = letter_row_exists($con, $id);
    if (!$existing) {
        letter_json_out(array('success' => false, 'error' => 'Item not found.'), 404);
    }

    $f = letter_read_fields('txt_edit_');
    if (trim($f['locality']) === '') {
        letter_json_out(array('success' => false, 'error' => 'Locality is required.'), 422);
    }

    $district = (int) $f['district'];
    $module = letter_module();
    $stmt = mysqli_prepare($con, "UPDATE {$module['table']} SET
            locality = ?, barangay = ?, district = ?, location = ?, date = ?,
            year = ?, type = ?, status = ?, accomplished = ?, remarks = ?
        WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssssssssi',
        $f['locality'], $f['barangay'], $district, $f['location'], $f['date'],
        $f['year'], $f['type'], $f['status'], $f['accomplished'], $f['remarks'], $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        letter_json_out(array('success' => false, 'error' => 'Could not update the letter.'), 500);
    }

    letter_log($con, 'Updated FWFA Letter: ' . $f['locality']);
    letter_json_out(array('success' => true, 'id' => $id));
}

// ---------------------------------------------------------------------------
// Delete
// ---------------------------------------------------------------------------
if (isset($_POST['btn_delete'])) {
    if (!$manage) {
        letter_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $ids = isset($_POST['chk_delete']) && is_array($_POST['chk_delete'])
        ? array_map('intval', $_POST['chk_delete'])
        : array();

    $deleted = 0;
    foreach ($ids as $id) {
        $existing = letter_row_exists($con, $id);
        if (!$existing) { continue; }

        // A letter owns its attachments, so they go with it. Deleting the row
        // alone used to leave the photo rows behind as orphans.
        $module = letter_module();
        $photoStmt = mysqli_prepare($con, "SELECT id, filename FROM {$module['photos']} WHERE activityid = ?");
        mysqli_stmt_bind_param($photoStmt, 'i', $id);
        mysqli_stmt_execute($photoStmt);
        $photoRes = mysqli_stmt_get_result($photoStmt);
        while ($p = mysqli_fetch_assoc($photoRes)) {
            $file = $module['photoDir'] . '/' . basename($p['filename']);
            if (is_file($file)) { @unlink($file); }
            $delPhoto = mysqli_prepare($con, "DELETE FROM {$module['photos']} WHERE id = ?");
            $pid = (int) $p['id'];
            mysqli_stmt_bind_param($delPhoto, 'i', $pid);
            mysqli_stmt_execute($delPhoto);
            mysqli_stmt_close($delPhoto);
        }
        mysqli_stmt_close($photoStmt);

        $del = mysqli_prepare($con, "DELETE FROM {$module['table']} WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $id);
        $ok = mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        if ($ok) {
            $deleted++;
            letter_log($con, 'Deleted FWFA Letter: ' . (isset($existing['location']) ? $existing['location'] : $id));
        }
    }

    letter_json_out(array('success' => true, 'deleted' => $deleted));
}

// ---------------------------------------------------------------------------
// Attach photos to an existing letter
// ---------------------------------------------------------------------------
if (isset($_POST['btn_addimage'])) {
    if (!$manage) {
        letter_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    if (!letter_row_exists($con, $id)) {
        letter_json_out(array('success' => false, 'error' => 'Item not found.'), 404);
    }

    $added = 0;
    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['name'] as $k => $name) {
            $single = array(
                'name'     => $name,
                'tmp_name' => $_FILES['photos']['tmp_name'][$k],
                'error'    => $_FILES['photos']['error'][$k],
            );
            if (letter_store_photo($con, $id, $single)) { $added++; }
        }
    }

    letter_log($con, 'Added attachment to FWFA Letter: ' . $id);
    letter_json_out(array('success' => true, 'added' => $added));
}

// ---------------------------------------------------------------------------
// Remove attachments
// ---------------------------------------------------------------------------
if (isset($_POST['btn_remove'])) {
    if (!$manage) {
        letter_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    if (!letter_row_exists($con, $id)) {
        letter_json_out(array('success' => false, 'error' => 'Item not found.'), 404);
    }

    $module = letter_module();
    $ids = isset($_POST['chk_deletephoto']) && is_array($_POST['chk_deletephoto'])
        ? array_map('intval', $_POST['chk_deletephoto'])
        : array();

    $removed = 0;
    foreach ($ids as $pid) {
        // Scoped to this letter on purpose: tblactivityphoto is shared with the
        // activity and planned-activity modules, so an unscoped delete here
        // could remove another module's attachment.
        $stmt = mysqli_prepare($con, "SELECT filename FROM {$module['photos']} WHERE id = ? AND activityid = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $pid, $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        if (!$row) { continue; }

        $file = $module['photoDir'] . '/' . basename($row['filename']);
        if (is_file($file)) { @unlink($file); }

        $del = mysqli_prepare($con, "DELETE FROM {$module['photos']} WHERE id = ? AND activityid = ?");
        mysqli_stmt_bind_param($del, 'ii', $pid, $id);
        if (mysqli_stmt_execute($del)) { $removed++; }
        mysqli_stmt_close($del);
    }

    letter_log($con, 'Removed attachment(s) from FWFA Letter: ' . $id);
    letter_json_out(array('success' => true, 'removed' => $removed));
}

letter_json_out(array('success' => false, 'error' => 'Unknown action.'), 400);
