<?php
/**
 * List endpoint for pages/credentials/credentials.php. Returns the rendered
 * rows so an add/edit/delete can refresh the table in place instead of
 * reloading the page.
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

cred_json_out(array(
    'success' => true,
    'rows'    => cred_render_rows(cred_fetch_rows($con), cred_can_manage()),
));