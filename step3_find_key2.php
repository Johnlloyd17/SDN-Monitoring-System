<?php
// Check more combinations for unique key
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== FINDING UNIQUE KEY (MORE COLUMNS) ===\n\n";

$candidateKeys = [
    ['site_code', 'item_no', 'locality', 'barangay'],
    ['site_code', 'item_no', 'site_locations'],
    ['site_code', 'item_no', 'new_renamed'],
    ['site_code', 'item_no', 'strategy'],
    ['site_code', 'item_no', 'source_sheet', 'locality'],
    ['site_code', 'item_no', 'source_sheet', 'new_renamed'],
    ['site_code', 'item_no', 'source_sheet', 'site_locations'],
    ['site_code', 'item_no', 'source_sheet', 'date_of_activation'],
    ['item_no', 'locality', 'barangay', 'district', 'source_sheet'],
    ['site_code', 'new_renamed', 'source_sheet', 'item_no'],
    ['site_code', 'site_locations', 'new_renamed', 'source_sheet'],
    ['site_code', 'item_no', 'new_renamed', 'source_sheet'],
    ['site_code', 'item_no', 'site_locations', 'source_sheet'],
];

foreach ($candidateKeys as $keyCols) {
    $cols = implode(', ', array_map(function($n) { return "`$n`"; }, $keyCols));
    $q = "SELECT COUNT(*) as c FROM (SELECT $cols FROM tblfwfa GROUP BY $cols HAVING COUNT(*) > 1) as t";
    $dupCheck = mysqli_fetch_assoc(mysqli_query($con, $q))['c'];
    $status = $dupCheck === 0 ? '✓ UNIQUE' : '✗ DUPLICATES';
    echo "  " . implode(', ', $keyCols) . ": $status ($dupCheck dup groups)\n";
}

echo "\n=== DONE ===\n";