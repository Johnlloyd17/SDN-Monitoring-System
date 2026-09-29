<?php
require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();
?>
<?php
header('Content-Type: application/json');

include __DIR__ . '/../pages/connection.php';
require_once __DIR__ . '/../pages/participant/participant_rows.php';

// Whitelisted in participant_view_config(); unknown values fall back to 'cyber'.
$view = isset($_REQUEST['view']) ? (string) $_REQUEST['view'] : 'cyber';

$filters = participant_filters($view, $_REQUEST);

echo json_encode(array(
    'success' => true,
    'rows'    => participant_render_rows($con, $view, $filters, participant_can_manage($view)),
    'cards'   => participant_render_cards($con, $view, $filters),
));
