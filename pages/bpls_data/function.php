<?php
/**
 * BPLS JSON endpoint for pages/bpls_data/bpls.php.
 *
 * This replaces the previous version, which was included inside the page and
 * built its SQL by string concatenation, redirected with header() and set
 * session flags for the notification includes. Three problems came with that:
 * the row id came from $_POST unescaped, attachment removal deleted from the
 * shared tblactivityphoto by raw id with no check that the file belonged to the
 * record, and none of the mutations checked the session role that the page used
 * to decide which buttons to draw.
 */
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
require_once __DIR__ . '/bpls_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../connection.php'; }

$module = bpls_module();
$manage = bpls_can_manage();

function bpls_json_out($data, $status = 200)
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data);
    exit;
}

/**
 * Reads the posted columns for a form prefix, e.g. 'txt_' maps the add form's
 * `txt_lgu` and 'txt_edit_' the edit form's `txt_edit_lgu`. The returned array
 * is keyed by column name and every value is a string, ready for binding.
 */
function bpls_read_fields($prefix)
{
    $fields = array();
    foreach (array_keys(bpls_columns()) as $col) {
        $key = $prefix . $col;
        $fields[$col] = isset($_POST[$key]) ? (string) $_POST[$key] : '';
    }
    return $fields;
}

function bpls_log($con, $action)
{
    if (!isset($_SESSION['role'])) { return; }
    $user = mysqli_real_escape_string($con, $_SESSION['role']);
    $act  = mysqli_real_escape_string($con, $action);
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('$user', NOW(), '$act')");
}

/** Confirms a BPLS row exists before attachments are touched. */
function bpls_row_exists($con, $id)
{
    $module = bpls_module();
    $stmt = mysqli_prepare($con, "SELECT id, lgu FROM {$module['table']} WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ? $row : null;
}

/** Deletes the attachments owned by one record, with their files. */
function bpls_drop_photos($con, $id)
{
    $module = bpls_module();
    $stmt = mysqli_prepare($con, "SELECT id, filename FROM {$module['photos']} WHERE activityid = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($p = mysqli_fetch_assoc($res)) {
        $file = $module['photoDir'] . '/' . basename($p['filename']);
        if (is_file($file)) { @unlink($file); }
        $del = mysqli_prepare($con, "DELETE FROM {$module['photos']} WHERE id = ?");
        $pid = (int) $p['id'];
        mysqli_stmt_bind_param($del, 'i', $pid);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);
    }
    mysqli_stmt_close($stmt);
}

/**
 * Records one attachment against a record. Split out of the upload so the
 * insert can be exercised without a real HTTP upload: binding the filename as
 * an integer would silently store 0 and orphan the file on disk.
 */
function bpls_record_photo($con, $id, $name)
{
    $module = bpls_module();
    $stmt = mysqli_prepare($con, "INSERT INTO {$module['photos']} (activityid, filename) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'is', $id, $name);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function bpls_store_photo($con, $id, $file)
{
    $module = bpls_module();
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
    if (!bpls_record_photo($con, $id, $name)) {
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
        bpls_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $f = bpls_read_fields('txt_');
    if (trim($f['lgu']) === '') {
        bpls_json_out(array('success' => false, 'error' => 'LGU Name is required.'), 422);
    }

    $cols = array_keys(bpls_columns());
    $place = array();
    $binding = '';
    $vals = array();
    foreach ($cols as $col) {
        $place[] = '?';
        $binding .= 's';
        $vals[] = $f[$col];
    }

    $sql = "INSERT INTO {$module['table']} (" . implode(',', $cols) . ') VALUES (' . implode(',', $place) . ')';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, $binding, ...$vals);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        bpls_json_out(array('success' => false, 'error' => 'Could not save the record.'), 500);
    }

    $id = (int) mysqli_insert_id($con);

    // The add modal has no file input, so this only ever runs if one is added
    // later; the view modal is the normal way to attach files.
    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['name'] as $k => $name) {
            bpls_store_photo($con, $id, array(
                'name'     => $name,
                'tmp_name' => $_FILES['photos']['tmp_name'][$k],
                'error'    => $_FILES['photos']['error'][$k],
            ));
        }
    }

    bpls_log($con, 'Added Item: ' . $f['lgu']);
    bpls_json_out(array('success' => true, 'id' => $id));
}

// ---------------------------------------------------------------------------
// Save
// ---------------------------------------------------------------------------
if (isset($_POST['btn_save'])) {
    if (!$manage) {
        bpls_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    if ($id <= 0 || !bpls_row_exists($con, $id)) {
        bpls_json_out(array('success' => false, 'error' => 'Item not found.'), 404);
    }

    $f = bpls_read_fields('txt_edit_');
    if (trim($f['lgu']) === '') {
        bpls_json_out(array('success' => false, 'error' => 'LGU Name is required.'), 422);
    }

    $sets = array();
    $binding = '';
    $vals = array();
    foreach (array_keys(bpls_columns()) as $col) {
        $sets[] = "`$col` = ?";
        $binding .= 's';
        $vals[] = $f[$col];
    }
    $vals[] = $id;

    $stmt = mysqli_prepare($con, "UPDATE {$module['table']} SET " . implode(',', $sets) . ' WHERE id = ?');
    mysqli_stmt_bind_param($stmt, $binding . 'i', ...$vals);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        bpls_json_out(array('success' => false, 'error' => 'Could not update the record.'), 500);
    }

    bpls_log($con, 'Updated Item ' . $f['lgu']);
    bpls_json_out(array('success' => true, 'id' => $id));
}

// ---------------------------------------------------------------------------
// Delete
// ---------------------------------------------------------------------------
if (isset($_POST['btn_delete'])) {
    if (!$manage) {
        bpls_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $ids = isset($_POST['chk_delete']) && is_array($_POST['chk_delete']) ? $_POST['chk_delete'] : array();
    $deleted = 0;
    foreach ($ids as $raw) {
        $id = (int) $raw;
        if ($id <= 0) { continue; }
        $existing = bpls_row_exists($con, $id);
        if (!$existing) { continue; }

        // A record owns its attachments, so they go with it. Deleting the row
        // alone used to leave the photo rows behind as orphans.
        bpls_drop_photos($con, $id);

        $del = mysqli_prepare($con, "DELETE FROM {$module['table']} WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $id);
        $ok = mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        if ($ok) {
            $deleted++;
            bpls_log($con, 'Deleted Item: ' . (isset($existing['lgu']) ? $existing['lgu'] : $id));
        }
    }

    bpls_json_out(array('success' => true, 'deleted' => $deleted));
}

// ---------------------------------------------------------------------------
// Attach files to an existing record
// ---------------------------------------------------------------------------
if (isset($_POST['btn_addimage'])) {
    if (!$manage) {
        bpls_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    if ($id <= 0 || !bpls_row_exists($con, $id)) {
        bpls_json_out(array('success' => false, 'error' => 'Item not found.'), 404);
    }

    $added = 0;
    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['name'] as $k => $name) {
            $single = array(
                'name'     => $name,
                'tmp_name' => $_FILES['photos']['tmp_name'][$k],
                'error'    => $_FILES['photos']['error'][$k],
            );
            if (bpls_store_photo($con, $id, $single)) { $added++; }
        }
    }

    bpls_log($con, 'Added attachment to BPLS item: ' . $id);
    bpls_json_out(array('success' => true, 'added' => $added));
}

// ---------------------------------------------------------------------------
// Remove attachments
// ---------------------------------------------------------------------------
if (isset($_POST['btn_remove'])) {
    if (!$manage) {
        bpls_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = isset($_POST['hidden_id']) ? (int) $_POST['hidden_id'] : 0;
    if ($id <= 0 || !bpls_row_exists($con, $id)) {
        bpls_json_out(array('success' => false, 'error' => 'Item not found.'), 404);
    }

    $ids = isset($_POST['chk_deletephoto']) && is_array($_POST['chk_deletephoto']) ? $_POST['chk_deletephoto'] : array();
    $removed = 0;
    foreach ($ids as $raw) {
        $pid = (int) $raw;
        if ($pid <= 0) { continue; }

        // tblactivityphoto is shared with the activity, planned and letter
        // modules, so a photo id on its own proves nothing. It is deleted only
        // when the same query confirms it belongs to this record; otherwise an
        // id from another module could be used to delete that module's file.
        $stmt = mysqli_prepare($con, "SELECT filename FROM {$module['photos']} WHERE id = ? AND activityid = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $pid, $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $p = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        if (!$p) { continue; }

        $file = $module['photoDir'] . '/' . basename($p['filename']);
        if (is_file($file)) { @unlink($file); }

        $del = mysqli_prepare($con, "DELETE FROM {$module['photos']} WHERE id = ? AND activityid = ?");
        mysqli_stmt_bind_param($del, 'ii', $pid, $id);
        $ok = mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        if ($ok) { $removed++; }
    }

    if ($removed > 0) {
        bpls_log($con, 'Removed ' . $removed . ' attachment(s) from BPLS item: ' . $id);
    }
    bpls_json_out(array('success' => true, 'removed' => $removed));
}

bpls_json_out(array('success' => false, 'error' => 'Unknown action.'), 400);
