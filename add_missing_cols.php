<?php
// Add missing columns to tblfwfa
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db   = getenv('DB_NAME') ?: 'dict_proj';

$candidatePorts = [getenv('DB_PORT'), 4306, 3306];
$candidatePorts = array_values(array_unique(array_filter($candidatePorts)));
$port = null;
foreach ($candidatePorts as $candidate) {
    $socket = @fsockopen($host, (int)$candidate, $errno, $errstr, 1);
    if ($socket) { fclose($socket); $port = (int)$candidate; break; }
}
if ($port === null) { die('MySQL port not found'); }

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== ADDING MISSING COLUMNS ===\n\n";

$r = mysqli_query($con, "SHOW COLUMNS FROM tblfwfa");
$existing = [];
while ($row = mysqli_fetch_assoc($r)) { $existing[] = $row['Field']; }
echo "Existing columns: " . count($existing) . "\n\n";

$missingCols = [
    'new_renamed' => "ADD COLUMN `new_renamed` varchar(255) NULL AFTER `site_code`",
    'connection_type' => "ADD COLUMN `connection_type` varchar(50) NULL AFTER `transport_type`",
    'down_time' => "ADD COLUMN `down_time` datetime NULL AFTER `status_2026_10_15_pm`",
    'connectivity_status' => "ADD COLUMN `connectivity_status` varchar(100) NULL AFTER `down_time`",
    'area_classification' => "ADD COLUMN `area_classification` varchar(50) NULL AFTER `connectivity_status`",
    'with_dict_free_wifi' => "ADD COLUMN `with_dict_free_wifi` enum('Yes','No') NULL AFTER `area_classification`",
    'last_monitored_at' => "ADD COLUMN `last_monitored_at` datetime NULL AFTER `with_dict_free_wifi`",
    'monitoring_notes' => "ADD COLUMN `monitoring_notes` text NULL AFTER `last_monitored_at`",
    'replacement_form_file' => "ADD COLUMN `replacement_form_file` text NULL AFTER `monitoring_notes`",
    'conforme_file' => "ADD COLUMN `conforme_file` text NULL AFTER `replacement_form_file`",
    'uat_file' => "ADD COLUMN `uat_file` text NULL AFTER `conforme_file`",
    'additional_uat' => "ADD COLUMN `additional_uat` text NULL AFTER `uat_file`",
    'site_coordinator_name' => "ADD COLUMN `site_coordinator_name` varchar(255) NULL AFTER `additional_uat`",
];

foreach ($missingCols as $col => $sql) {
    if (in_array($col, $existing)) {
        echo "  ⊘ $col (exists)\n";
        continue;
    }
    $fullSql = "ALTER TABLE `tblfwfa` $sql";
    if (mysqli_query($con, $fullSql)) {
        echo "  ✓ $col\n";
    } else {
        echo "  ✗ $col — " . mysqli_error($con) . "\n";
    }
}

echo "\nVerifying:\n";
$r = mysqli_query($con, "DESCRIBE tblfwfa");
while ($row = mysqli_fetch_assoc($r)) {
    echo "  {$row['Field']} — {$row['Type']}\n";
}

echo "\n=== DONE ===\n";