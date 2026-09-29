<?php
require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();

require_once __DIR__ . '/../pages/planned_activities/planned_rows.php';
require_once __DIR__ . '/../pages/connection.php';

$view = isset($_POST['view']) ? (string) $_POST['view'] : (isset($_GET['view']) ? (string) $_GET['view'] : '');
$cfg  = planned_view_config($view);
if ($cfg === null) {
    planned_json_out(array('success' => false, 'error' => 'Unknown view.'), 400);
}

$src    = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;
$params = planned_filter_params($cfg, $src);
$rows   = planned_fetch_rows($con, $cfg, $params);
$manage = planned_can_manage($cfg);

planned_json_out(array(
    'success'  => true,
    'view'     => $view,
    'count'    => count($rows),
    'canManage'=> $manage,
    'headers'  => planned_render_headers($manage),
    'rows'     => planned_render_rows($rows, $manage),
));
