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

require_once 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages\bpls_data\_t12_rows_test.php';


header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages/connection.php'; }

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
    mysqli_query($con, "INSERT INTO z12_logs (user, logdate, action) VALUES ('$user', NOW(), '$act')");
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
