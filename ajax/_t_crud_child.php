<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
$_SESSION['role'] = 'Admin';
session_write_close();
$_POST = json_decode(file_get_contents($argv[1]), true);
include 'fw4a_crud.php';