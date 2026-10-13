<?php
require_once 'pages/connection.php';
echo 'Connected to: ' . mysqli_get_host_info($con) . "\n";
echo 'DB: ' . mysqli_get_db_name($con) . "\n";