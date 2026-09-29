<?php
/**
 * Item lookup for pages/admin/admin.php. The edit modal is shared, so the row
 * is fetched by id and the form is filled from the returned JSON.
 */
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
require_once __DIR__ . '/../pages/admin/admin_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../pages/connection.php'; }

if (!function_exists('adm_json_out')) {
    function adm_json_out($data, $status = 200)
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
$id     = adm_id(isset($_GET['id']) ? $_GET['id'] : 0);

if ($action !== 'item' || $id <= 0) {
    adm_json_out(array('error' => 'Invalid action.'), 400);
}

$module = adm_module();
$stmt = mysqli_prepare($con, "SELECT zone, username, password FROM {$module['table']} WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    adm_json_out(array('error' => 'Item not found'), 404);
}

adm_json_out($row);