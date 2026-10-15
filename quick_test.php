<?php
// Minimal connection test
$con = @mysqli_connect('127.0.0.1', 'root', '', 'dict_proj', 3306);
if (!$con) {
    echo "FAIL: " . mysqli_connect_error() . "\n";
    exit(1);
}
echo "OK\n";
$r = mysqli_query($con, "SELECT COUNT(*) c FROM tblfwfa");
echo "Rows: " . mysqli_fetch_assoc($r)['c'] . "\n";