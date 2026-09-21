<?php
session_start();
$_SESSION['role'] = 'Administrator';
$_SESSION['userid'] = 3;
$_SESSION['username'] = 'dictsdn';
header('Location: dashboard.php');
exit;