<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$rows = [];
$res = mysqli_query($con, "SELECT category AS name FROM classifications WHERE status = 'active' ORDER BY category ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row['name'];
    }
}

echo json_encode($rows);