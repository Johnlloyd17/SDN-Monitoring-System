<?php
// Ultra minimal test
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(10);

echo "Testing connection...\n";
$con = mysqli_connect('localhost', 'root', '', 'dict_proj', 3306);
if (!$con) {
    echo "FAILED: " . mysqli_connect_error() . "\n";
    exit;
}
echo "Connected OK\n";

$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa");
if (!$r) {
    echo "Query failed: " . mysqli_error($con) . "\n";
    exit;
}
$row = mysqli_fetch_assoc($r);
echo "Rows: " . $row['c'] . "\n";
echo "DONE\n";