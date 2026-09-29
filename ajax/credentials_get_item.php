<?php
/**
 * Item lookup for pages/credentials/credentials.php. The edit modal is shared,
 * so the row is fetched by id and the form is filled from the returned JSON.
 */
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
require_once __DIR__ . '/../pages/credentials/credentials_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../pages/connection.php'; }

if (!function_exists('cred_json_out')) {
    function cred_json_out($data, $status = 200)
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
$id     = cred_id(isset($_GET['id']) ? $_GET['id'] : 0);

if ($action !== 'item' || $id <= 0) {
    cred_json_out(array('error' => 'Invalid action.'), 400);
}

$module = cred_module();
$stmt = mysqli_prepare($con, "SELECT name, username, password FROM {$module['table']} WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    cred_json_out(array('error' => 'Item not found'), 404);
}

cred_json_out($row);