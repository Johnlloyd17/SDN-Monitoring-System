<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$_SESSION['role'] = 'Admin';
$con = @new mysqli('localhost', 'root', '', 'dict_proj');
$con->query("DELETE FROM tblfwfa WHERE locality LIKE 'CRUDTESTLOC%' OR site_code='CRUD-TEST-0001'");

function hit($post, $workerDir) {
    $tmp = tempnam(sys_get_temp_dir(), 'fw4a') . '.json';
    file_put_contents($tmp, json_encode($post));
    $out = shell_exec('php -d error_reporting=0 "' . __DIR__ . '\\_t_crud_child.php" "' . $tmp . '" 2>&1');
    unlink($tmp);
    echo trim($out) . "\n";
}

$workerDir = __DIR__;

$post = [
    'action' => 'add',
    'txt_item_no' => '999001', 'txt_locality' => 'CRUDTESTLOC', 'txt_barangay' => 'Test Brgy',
    'txt_district' => '1st', 'txt_transport_location' => 'Test RHU', 'txt_transport_type' => 'FOC (PLDT)',
    'txt_site_locations' => 'Test Location', 'txt_site_code' => 'CRUD-TEST-0001', 'txt_site_type' => 'LGU-HEALTH',
    'txt_date_of_activation' => '2023-12-01', 'txt_latitude' => '10.5', 'txt_longitude' => '125.75',
    'txt_procurement_initiative' => 'Regional Procured', 'txt_installation_type' => 'Region Initiated',
    'uat' => '1', 'conforme' => '1', 'txt_strategy' => 'PICS MUN', 'txt_status' => 'For Installation',
    'txt_link_type' => 'UTP', 'txt_site_coordinator_name' => 'CRUD Tester', 'txt_contact_details' => '09171234567',
    'txt_remarks' => 'crud test',
];
echo "--- 1) add VALID row ---\n";
hit($post, $workerDir);
$q = $con->query("SELECT id, item_no FROM tblfwfa WHERE locality='CRUDTESTLOC'");
if (!$q) { echo "DBERR: " . $con->error . "\n"; exit; }
$row = $q->fetch_assoc();
echo "db row id: " . ($row ? $row['id'] : 'NOT FOUND') . " count=" . ($q->num_rows) . "\n";
$id = $row ? $row['id'] : 0;

echo "--- 2) add INVALID lat 200 (expect reject) ---\n";
hit(['action' => 'add', 'txt_item_no' => '999002', 'txt_locality' => 'CRUDTESTLOC2', 'txt_latitude' => '200', 'txt_longitude' => '125.75'], $workerDir);
$q = $con->query("SELECT COUNT(*) c FROM tblfwfa WHERE locality='CRUDTESTLOC2'");
echo "rejected row count: " . $q->fetch_assoc()['c'] . "\n";

echo "--- 3) edit row id=$id ---\n";
hit([
    'action' => 'edit', 'hidden_id' => $id,
    'txt_edit_item_no' => '999001', 'txt_edit_locality' => 'CRUDTESTLOC_EDITED', 'txt_edit_barangay' => 'Test Brgy',
    'txt_edit_district' => '1st', 'txt_edit_transport_location' => 'Test RHU', 'txt_edit_transport_type' => 'FOC (PLDT)',
    'txt_edit_site_locations' => 'Test Location', 'txt_edit_site_code' => 'CRUD-TEST-0001', 'txt_edit_site_type' => 'LGU-HEALTH',
    'txt_edit_date_of_activation' => '2023-12-01', 'txt_edit_latitude' => '10.55', 'txt_edit_longitude' => '125.80',
    'txt_edit_procurement_initiative' => 'Regional Procured', 'txt_edit_installation_type' => 'Region Initiated',
    'uat' => '1', 'conforme' => '1', 'txt_edit_strategy' => 'PICS MUN', 'txt_edit_status' => 'Active',
    'txt_edit_link_type' => 'Fiber', 'txt_edit_site_coordinator_name' => 'CRUD Tester 2',
    'txt_edit_contact_details' => '09171234568', 'txt_edit_remarks' => 'crud test edited',
], $workerDir);
$q = $con->query("SELECT id, locality, latitude, longitude, status, link_type, site_coordinator_name FROM tblfwfa WHERE id=$id");
while ($t = $q->fetch_assoc()) echo "db: " . json_encode($t) . "\n";

echo "--- 4) delete [ id=$id ] ---\n";
hit(['action' => 'delete', 'ids' => [$id]], $workerDir);
$q = $con->query("SELECT COUNT(*) c FROM tblfwfa WHERE id=$id");
echo "remaining after delete: " . $q->fetch_assoc()['c'] . "\n";

echo "--- 5) leftover test rows ---\n";
$q = $con->query("SELECT COUNT(*) c FROM tblfwfa WHERE locality LIKE 'CRUDTESTLOC%' OR site_code='CRUD-TEST-0001'");
echo "leftover test rows: " . $q->fetch_assoc()['c'] . "\n";