<?php
/**
 * List endpoint for pages/bpls_data/bpls.php. Returns the rendered rows and the
 * filter select for the current selection, so a filter change refreshes the
 * table in place instead of reloading the page.
 */
require_once 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages/auth_check.php'; require_auth_api();
require_once 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages\bpls_data\_t12_rows_test.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages/connection.php'; }

if (!function_exists('bpls_json_out')) {
    function bpls_json_out($data, $status = 200)
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data);
        exit;
    }
}

$params = bpls_filter_params($_GET);

// The filter select is re-rendered too, not just the table: the option list
// comes from the same request that applied the filter, so a value that no
// longer exists cannot stay selected in a stale dropdown.
bpls_json_out(array(
    'success'    => true,
    'rows'       => bpls_render_rows(bpls_fetch_rows($con, $params), bpls_can_manage()),
    'filterForm' => bpls_render_filters($con, $params),
));
