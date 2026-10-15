<?php
// Test different connection methods
$hosts = ['127.0.0.1', 'localhost', '::1'];

foreach ($hosts as $host) {
    echo "Trying $host...\n";
    $con = @mysqli_connect($host, 'root', '', 'dict_proj', 3306);
    if ($con) {
        echo "  SUCCESS on $host\n";
        $r = mysqli_query($con, "SELECT 1");
        if ($r) echo "  Query OK\n";
        mysqli_close($con);
    } else {
        echo "  FAILED: " . mysqli_connect_error() . "\n";
    }
    echo "\n";
}