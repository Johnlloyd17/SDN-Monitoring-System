<?php
/** @var mysqli $con Database connection */
global $con;
$con = mysqli_connect('localhost','root','','dict_proj', 4306) or die(mysqli_connect_error());

date_default_timezone_set("Asia/Manila");
?>
