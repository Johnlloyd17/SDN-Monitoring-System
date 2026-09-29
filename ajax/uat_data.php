<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>

<?php

header('Content-Type: application/json');

include __DIR__ . '/../pages/connection.php';
require_once __DIR__ . '/../pages/uat/uat_rows.php';

$isAdmin = !isset($_SESSION['staff']);
$search = isset($_GET['search']) ? $_GET['search'] : (isset($_POST['search']) ? $_POST['search'] : '');

ob_start();
uat_render_rows($con, $search, $isAdmin);
$rows = ob_get_clean();

echo json_encode(array(
    'success' => true,
    'rows' => $rows,
    'stats' => uat_render_stats($con),
));
