<?php
/** @var mysqli $con Database connection */
global $con;
$con = mysqli_connect('localhost','root','','dict_proj') or die(mysqli_error());

date_default_timezone_set("Asia/Manila");
?>