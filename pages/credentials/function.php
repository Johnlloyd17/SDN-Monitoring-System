<?php
/**
 * User Credentials JSON endpoint for pages/credentials/credentials.php.
 *
 * This replaces the previous version, which was included inside the page,
 * built its SQL by string concatenation, redirected with header() and set
 * session flags for the notification includes. Three problems came with that:
 * the btn_add handler ran twice (one click inserted two rows plus two tbllogs
 * entries), the update duplicate check did not exclude the row being edited
 * (so saving without changing the username always failed), and none of the
 * mutations checked the session role the page used to decide which buttons to
 * draw.
 */
require_once __DIR__ . '/../auth_check.php'; require_auth_api();
require_once __DIR__ . '/credentials_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../connection.php'; }

$module = cred_module();
$manage = cred_can_manage();

function cred_json_out($data, $status = 200)
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data);
    exit;
}

function cred_log($con, $action)
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
function cred_username_taken($con, $username, $excludeId = 0)
{
    $module = cred_module();
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
        cred_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $name     = isset($_POST['txt_name']) ? trim((string) $_POST['txt_name']) : '';
    $username = isset($_POST['txt_uname']) ? trim((string) $_POST['txt_uname']) : '';
    $password = isset($_POST['txt_pass']) ? (string) $_POST['txt_pass'] : '';

    if ($name === '') {
        cred_json_out(array('success' => false, 'error' => 'Name is required.'), 422);
    }
    if ($username === '') {
        cred_json_out(array('success' => false, 'error' => 'Username is required.'), 422);
    }
    if (cred_username_taken($con, $username)) {
        cred_json_out(array('success' => false, 'error' => 'Username already exists.'), 409);
    }

    $stmt = mysqli_prepare($con, "INSERT INTO {$module['table']} (name, username, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $name, $username, $password);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        cred_json_out(array('success' => false, 'error' => 'Could not save the credential.'), 500);
    }

    $newId = (int) mysqli_insert_id($con);
    cred_log($con, 'Added Credentials with name of ' . $name);
    cred_json_out(array('success' => true, 'id' => $newId));
}

// ---------------------------------------------------------------------------
// Save
// ---------------------------------------------------------------------------
if (isset($_POST['btn_save'])) {
    if (!$manage) {
        cred_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $id = cred_id(isset($_POST['hidden_id']) ? $_POST['hidden_id'] : 0);
    if ($id <= 0) {
        cred_json_out(array('success' => false, 'error' => 'Credential not found.'), 404);
    }

    $name     = isset($_POST['txt_edit_name']) ? trim((string) $_POST['txt_edit_name']) : '';
    $username = isset($_POST['txt_edit_uname']) ? trim((string) $_POST['txt_edit_uname']) : '';
    $password = isset($_POST['txt_edit_pass']) ? (string) $_POST['txt_edit_pass'] : '';

    if ($name === '') {
        cred_json_out(array('success' => false, 'error' => 'Name is required.'), 422);
    }
    if ($username === '') {
        cred_json_out(array('success' => false, 'error' => 'Username is required.'), 422);
    }
    if (cred_username_taken($con, $username, $id)) {
        cred_json_out(array('success' => false, 'error' => 'Username already exists.'), 409);
    }

    $stmt = mysqli_prepare($con, "UPDATE {$module['table']} SET name = ?, username = ?, password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sssi', $name, $username, $password, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        cred_json_out(array('success' => false, 'error' => 'Could not update the credential.'), 500);
    }

    cred_log($con, 'Update Credentials with name of ' . $name);
    cred_json_out(array('success' => true, 'id' => $id));
}

// ---------------------------------------------------------------------------
// Delete
// ---------------------------------------------------------------------------
if (isset($_POST['btn_delete'])) {
    if (!$manage) {
        cred_json_out(array('success' => false, 'error' => 'Not authorized.'), 403);
    }

    $ids = isset($_POST['chk_delete']) && is_array($_POST['chk_delete']) ? $_POST['chk_delete'] : array();
    $deleted = 0;
    $idList = array();
    foreach ($ids as $raw) {
        $delId = cred_id($raw);
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
        cred_log($con, 'Batch deleted ' . $deleted . ' credential(s) (IDs: ' . implode(',', $idList) . ')');
    }
    cred_json_out(array('success' => true, 'deleted' => $deleted));
}

cred_json_out(array('success' => false, 'error' => 'Unknown action.'), 400);