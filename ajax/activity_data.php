<?php
require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();
?>

<?php

header('Content-Type: application/json');

include __DIR__ . '/../pages/connection.php';
require_once __DIR__ . '/../pages/activity/activity_rows.php';

// Whitelisted in activity_view_config(); unknown values fall back to 'cyber'.
$view = isset($_REQUEST['view']) ? (string) $_REQUEST['view'] : 'cyber';

$filters = activity_filters($view, $_REQUEST);

echo json_encode(array(
    'success' => true,
    'rows'    => activity_render_rows($con, $view, $filters, activity_can_manage($view)),
    'cards'   => activity_render_cards($con, $view, $filters),
));
