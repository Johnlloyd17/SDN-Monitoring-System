<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
require_once __DIR__ . '/../pages/fwfa_letter/letter_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../pages/connection.php'; }

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
