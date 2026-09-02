<?php
/** @var mysqli $con Database connection */
global $con;
$port = getenv('DB_PORT') ?: 3306;
$con = mysqli_connect('localhost', 'root', '', 'dict_proj', (int)$port) or die(mysqli_connect_error());

date_default_timezone_set("Asia/Manila");
?>
