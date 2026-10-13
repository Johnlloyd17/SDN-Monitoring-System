<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect($host, $user, $pass, $db, $port);
if ($con) {
    echo "Connected successfully to port $port\n";
    $r = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa");
    echo "Rows: " . mysqli_fetch_assoc($r)['c'] . "\n";
} else {
    echo "Connection failed: " . mysqli_connect_error() . "\n";
}