<?php
/** @var mysqli $con Database connection */
global $con;
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db   = getenv('DB_NAME') ?: 'dict_proj';

$candidatePorts = [getenv('DB_PORT'), 4306, 3306];
$candidatePorts = array_values(array_unique(array_filter($candidatePorts)));

$port = null;
foreach ($candidatePorts as $candidate) {
    $socket = @fsockopen($host, (int)$candidate, $errno, $errstr, 1);
    if ($socket) {
        fclose($socket);
        $port = (int)$candidate;
        break;
    }
}

if ($port === null) {
    die('No connection could be made: MySQL port not found (checked: ' . implode(', ', $candidatePorts) . '). Error: ' . $errstr);
}

mysqli_report(MYSQLI_REPORT_OFF);
$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

date_default_timezone_set("Asia/Manila");
?>
