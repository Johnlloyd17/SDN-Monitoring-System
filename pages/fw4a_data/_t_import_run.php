<?php
$_FILES['file'] = [
    'name' => 'FW4A.xlsx',
    'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'tmp_name' => __DIR__ . '/../../uploads/fw4a/FW4A.xlsx',
    'error' => UPLOAD_ERR_OK,
    'size' => filesize(__DIR__ . '/../../uploads/fw4a/FW4A.xlsx'),
];
include 'import.php';