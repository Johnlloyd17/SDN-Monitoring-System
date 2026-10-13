<?php
// Add unique index and test import
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 3: ADD UNIQUE INDEX & TEST IMPORT ===\n\n";

// 1. Add unique index on site_code + item_no + site_locations
echo "1. Adding unique index on (site_code, item_no, site_locations)...\n";
$sql = "ALTER TABLE tblfwfa ADD UNIQUE KEY uniq_site_item_location (site_code, item_no, site_locations)";
if (mysqli_query($con, $sql)) {
    echo "  ✓ Unique index 'uniq_site_item_location' added\n";
} else {
    echo "  ✗ Failed: " . mysqli_error($con) . "\n";
    exit;
}

// 2. Test import twice
echo "\n2. Testing import twice...\n";

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

require_once 'pages/fw4a_data/import.php';

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';

echo "\nFirst import:\n";
$_FILES['file'] = [
    'name' => 'sdn_fw4a_v2.xlsx',
    'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'tmp_name' => 'uploads/fw4a/sdn_fw4a_v2.xlsx',
    'error' => UPLOAD_ERR_OK,
    'size' => filesize('uploads/fw4a/sdn_fw4a_v2.xlsx'),
];

ob_start();
include 'pages/fw4a_data/import.php';
$output1 = ob_get_clean();

echo "First import output:\n$output1\n";

$data1 = json_decode($output1, true);
if ($data1) {
    echo "First import: " . ($data1['success'] ? 'SUCCESS' : 'FAILED') . " - " . ($data1['inserted'] ?? 0) . " inserted\n";
    if (!empty($data1['error'])) echo "Error: " . $data1['error'] . "\n";
}

echo "\nSecond import (should add 0):\n";
ob_start();
include 'pages/fw4a_data/import.php';
$output2 = ob_get_clean();

echo "Second import output:\n$output2\n";

$data2 = json_decode($output2, true);
if ($data2) {
    echo "Second import: " . ($data2['success'] ? 'SUCCESS' : 'FAILED') . " - " . ($data2['inserted'] ?? 0) . " inserted\n";
    if (!empty($data2['error'])) echo "Error: " . $data2['error'] . "\n";
}

echo "\n=== STEP 3 COMPLETE ===\n";