<?php
/**
 * List endpoint for pages/admin/admin.php. Returns the rendered rows so an
 * add/edit/delete can refresh the table in place instead of reloading the page.
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

adm_json_out(array(
    'success' => true,
    'rows'    => adm_render_rows(adm_fetch_rows($con), adm_can_delete()),
));