<?php
require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();
?>

<?php

header('Content-Type: application/json');

include __DIR__ . '/../pages/connection.php';
require_once __DIR__ . '/../pages/tech4ed/tech4ed_rows.php';

$filters = tech4ed_filters($_REQUEST);

// Whitelisted in tech4ed_base_condition(); unknown values fall back to 'all'.
$view = isset($_REQUEST['view']) ? (string) $_REQUEST['view'] : 'all';

echo json_encode(array(
    'success' => true,
    'rows' => tech4ed_render_rows($con, $filters, tech4ed_can_manage(), $view),
));
