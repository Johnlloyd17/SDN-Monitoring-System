<?php
require_once __DIR__ . '/../pages/auth_check.php';
require_auth_api();
?>

<?php

header('Content-Type: application/json');

include __DIR__ . '/../pages/connection.php';
require_once __DIR__ . '/../pages/pass_slip/pass_slip_rows.php';

$isStaff = isset($_SESSION['staff']);

$slipFilters = ps_slip_filters($_REQUEST);
$icsFilters = ps_ics_filters($_REQUEST);

$slipData = ps_build_slip_data($con, $slipFilters);

echo json_encode(array(
    'success' => true,
    'rows' => ps_render_slip_rows($con, $slipFilters, $isStaff),
    'ics_rows' => ps_render_ics_rows($con, $icsFilters),
    'data' => (object) $slipData,
));
