<?php
// Step 3 - Add unique index and update import logic
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 3: ADD UNIQUE INDEX & UPDATE IMPORT ===\n\n";

// 1. Check current data for uniqueness
echo "1. Checking if unique key (all imported columns) is viable...\n";

$importCols = [
    'item_no', 'locality', 'barangay', 'district',
    'transport_location', 'transport_type', 'site_code', 'new_renamed',
    'site_locations', 'transfer_new_locations', 'remarks',
    'nationwide_id', 'site_type',
    'date_of_activation', 'current_date_of_acceptance', 'latitude', 'longitude',
    'procurement_initiative', 'installation_type', 'uat', 'conforme', 'strategy', 'link_type',
    'status_2026_10_06_am', 'status_2026_10_06_pm',
    'status_2026_10_07_am', 'status_2026_10_07_pm',
    'status_2026_10_08_am', 'status_2026_10_08_pm',
    'status_2026_10_12_am', 'status_2026_10_12_pm',
    'status_2026_10_13_am', 'status_2026_10_13_pm',
    'status_2026_10_14_am', 'status_2026_10_14_pm',
    'status_2026_10_15_am', 'status_2026_10_15_pm',
    'contact_details',
];

$colsSql = implode(', ', array_map(function($n) { return "`$n`"; }, $importCols));

$q = "SELECT COUNT(*) as c FROM (
    SELECT " . implode(', ', array_map(function($n) { return "`$n`"; }, $importCols)) . "
    FROM tblfwfa
    GROUP BY " . implode(', ', array_map(function($n) { return "`$n`"; }, $importCols)) . "
    HAVING COUNT(*) > 1
) as t";
$dupCheck = mysqli_fetch_assoc(mysqli_query($con, $q))['c'];
echo "  Duplicate groups (all cols): $dupCheck\n";

if ($dupCheck > 0) {
    echo "  Cannot add unique index - duplicates exist\n";
    exit;
}

// 2. Add unique index
echo "\n2. Adding unique index on all imported columns...\n";
$indexCols = implode(', ', array_map(function($n) { return "`$n`"; }, $importCols));
$sql = "ALTER TABLE tblfwfa ADD UNIQUE KEY uniq_import_data ($indexCols)";
if (mysqli_query($con, $sql)) {
    echo "  ✓ Unique index 'uniq_import_data' added\n";
} else {
    echo "  ✗ Failed: " . mysqli_error($con) . "\n";
    exit;
}

// 3. Test import twice
echo "\n3. Testing import twice...\n";

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
    'tmp_name' => $filePath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($filePath),
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