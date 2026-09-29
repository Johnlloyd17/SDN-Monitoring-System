<?php
require_once 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages\fwfa_letter\_t11_rows_test.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages/connection.php'; }

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
    mysqli_query($con, "INSERT INTO z11_logs (user, logdate, action) VALUES ('$user', NOW(), '$act')");
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

