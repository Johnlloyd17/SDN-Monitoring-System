<?php
require_once 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages/auth_check.php'; require_auth_api();
require_once 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages\fwfa_letter\_t11_rows_test.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include 'C:\xampp\htdocs\Copy of SDN MS\v9-11-2026\SDN Monitoring System\pages/connection.php'; }

$params = letter_filter_params($_GET);

// The filter selects are re-rendered too, not just the table: the four
// dropdowns cascade off each other, so after a change the option lists are
// stale unless they come back from the same request that applied the filter.
letter_json_out(array(
    'success' => true,
    'rows'    => letter_render_rows(letter_fetch_rows($con, $params), letter_can_manage()),
    'filterForm' => letter_render_filters($con, $params),
    'stats'   => letter_stat_values($con, $params),
));
