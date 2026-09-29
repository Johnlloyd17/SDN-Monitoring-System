<?php
/**
 * Zone Leader JSON endpoint for pages/admin/admin.php.
 *
 * This replaces the previous version, which was included inside the page,
 * built its SQL by string concatenation, redirected with header() and set
 * session flags for the notification includes. Three problems came with that:
 * the update log referenced an undefined $txt_edit_busname (it is txt_edit_zone),
 * the update duplicate check did not exclude the row being edited (so saving
 * without changing the username always failed), and the delete endpoint took no
 * notice of the staff gate the page used to hide the Delete button.
 */
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
require_once __DIR__ . '/admin_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../connection.php'; }

$module = adm_module();
$manage = adm_can_manage();
$canDelete = adm_can_delete();

function adm_json_out($data, $status = 200)
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data);
    exit;
}

function adm_log($con, $action)
{
    if (!isset($_SESSION['role'])) { return; }
    $user = mysqli_real_escape_string($con, $_SESSION['role']);
    $act  = mysqli_real_escape_string($con, $action);
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('$user', NOW(), '$act')");
}

/**
 * Whether a username already belongs to another row. The edit form re-submits
 * its own username, so the row being edited has to be excluded or saving
 * without changing the username would always be reported as a duplicate.
 */
function adm_username_taken($con, $username, $excludeId = 0)
{
    $module = adm_module();
    $stmt = mysqli_prepare($con, "SELECT id FROM {$module['table']} WHERE username = ? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'si', $username, $excludeId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ? true : false;
}

// ---------------------------------------------------------------------------
// Add
// ---------------------------------------------------------------------------
if (isset($_POST['btn_add'])) {
    if (!$manage) {
        adm_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $zone     = isset($_POST['txt_zone']) ? trim((string) $_POST['txt_zone']) : '';
    $username = isset($_POST['txt_uname']) ? trim((string) $_POST['txt_uname']) : '';
    $password = isset($_POST['txt_pass']) ? (string) $_POST['txt_pass'] : '';

    if ($zone === '') {
        adm_json_out(array('success' => false, 'error' => 'Zone is required.'), 422);
    }
    if ($username === '') {
        adm_json_out(array('success' => false, 'error' => 'Username is required.'), 422);
    }
    if (adm_username_taken($con, $username)) {
        adm_json_out(array('success' => false, 'error' => 'Username already exists.'), 409);
    }

    $stmt = mysqli_prepare($con, "INSERT INTO {$module['table']} (zone, username, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $zone, $username, $password);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        adm_json_out(array('success' => false, 'error' => 'Could not save the zone leader.'), 500);
    }

    $newId = (int) mysqli_insert_id($con);
    adm_log($con, 'Added Zone number ' . $zone);
    adm_json_out(array('success' => true, 'id' => $newId));
}

// ---------------------------------------------------------------------------
// Save
// ---------------------------------------------------------------------------
if (isset($_POST['btn_save'])) {
    if (!$manage) {
        adm_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = adm_id(isset($_POST['hidden_id']) ? $_POST['hidden_id'] : 0);
    if ($id <= 0) {
        adm_json_out(array('success' => false, 'error' => 'Zone leader not found.'), 404);
    }

    $zone     = isset($_POST['txt_edit_zone']) ? trim((string) $_POST['txt_edit_zone']) : '';
    $username = isset($_POST['txt_edit_uname']) ? trim((string) $_POST['txt_edit_uname']) : '';
    $password = isset($_POST['txt_edit_pass']) ? (string) $_POST['txt_edit_pass'] : '';

    if ($zone === '') {
        adm_json_out(array('success' => false, 'error' => 'Zone is required.'), 422);
    }
    if ($username === '') {
        adm_json_out(array('success' => false, 'error' => 'Username is required.'), 422);
    }
    if (adm_username_taken($con, $username, $id)) {
        adm_json_out(array('success' => false, 'error' => 'Username already exists.'), 409);
    }

    $stmt = mysqli_prepare($con, "UPDATE {$module['table']} SET zone = ?, username = ?, password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $zone, $username, $password, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        adm_json_out(array('success' => false, 'error' => 'Could not update the zone leader.'), 500);
    }

    adm_log($con, 'Updated Zone number ' . $zone);
    adm_json_out(array('success' => true, 'id' => $id));
}

// ---------------------------------------------------------------------------
// Delete
// ---------------------------------------------------------------------------
if (isset($_POST['btn_delete'])) {
    if (!$canDelete) {
        adm_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $ids = isset($_POST['chk_delete']) && is_array($_POST['chk_delete']) ? $_POST['chk_delete'] : array();
    $deleted = 0;
    $idList = array();
    foreach ($ids as $raw) {
        $delId = adm_id($raw);
        if ($delId <= 0) { continue; }
        $del = mysqli_prepare($con, "DELETE FROM {$module['table']} WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $delId);
        $ok = mysqli_stmt_execute($del);
        mysqli_stmt_close($del);
        if ($ok) {
            $deleted++;
            $idList[] = $delId;
        }
    }

    if ($deleted > 0) {
        adm_log($con, 'Batch deleted ' . $deleted . ' zone leader(s) (IDs: ' . implode(',', $idList) . ')');
    }
    adm_json_out(array('success' => true, 'deleted' => $deleted));
}

adm_json_out(array('success' => false, 'error' => 'Unknown action.'), 400);