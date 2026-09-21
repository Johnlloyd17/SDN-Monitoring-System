<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    http_response_code(403);
    echo json_encode(array('error' => 'Unauthorized'));
    exit;
}

include '../pages/connection.php';
require_once '../pages/settings/backup_lib.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'export') {
    ob_clean();
    $result = sdn_create_backup($con);
    if (!$result['ok']) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => $result['error']));
        exit;
    }
    sdn_log($con, 'Downloaded a database backup');
    echo json_encode(array('success' => true, 'file' => $result['filename']));
    exit;
}

if ($action === 'download') {
    $file = isset($_GET['file']) ? basename($_GET['file']) : '';
    if (!sdn_is_backup_filename($file)) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid file name'));
        exit;
    }
    $path = sdn_backups_dir() . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) {
        http_response_code(404);
        echo json_encode(array('error' => 'The backup file is no longer on the server. Please make a new backup.'));
        exit;
    }
    ob_clean();
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Length: ' . filesize($path));
    header('Content-Transfer-Encoding: binary');
    readfile($path);
    exit;
}

if ($action === 'restore') {
    ob_clean();
    if (!isset($_FILES['backup_file'])) {
        echo json_encode(array('success' => false, 'error' => 'No backup file was selected. Please choose a backup file first.'));
        exit;
    }
    $upload = $_FILES['backup_file'];
    if (isset($upload['error']) && $upload['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array('success' => false, 'error' => 'The file could not be read. Please choose a backup file and try again.'));
        exit;
    }
    if (!isset($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        echo json_encode(array('success' => false, 'error' => 'The file could not be read. Please choose a backup file and try again.'));
        exit;
    }
    if ($upload['size'] <= 0) {
        echo json_encode(array('success' => false, 'error' => 'The file you chose is empty. Please choose the backup file you downloaded from this page.'));
        exit;
    }
    if ($upload['size'] > 100 * 1024 * 1024) {
        echo json_encode(array('success' => false, 'error' => 'This file is too large to restore. Please use the backup file you downloaded from this page.'));
        exit;
    }
    if (!sdn_validate_backup($upload['tmp_name'])) {
        echo json_encode(array('success' => false, 'error' => 'This file does not look like a backup made by this system. Please choose the backup file you downloaded from the Backup section.'));
        exit;
    }

    $safety = sdn_create_safety_backup($con);
    if (!$safety['ok']) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => 'Could not prepare a safety copy of the current data, so the restore was cancelled. Nothing was changed. Please try again.'));
        exit;
    }

    $result = sdn_restore_database($con, $upload['tmp_name']);

    if ($result['ok']) {
        sdn_log($con, 'Restored the database from a backup file');
        echo json_encode(array(
            'success' => true,
            'safety_file' => $safety['filename'],
            'message' => 'Restore complete. Your system now shows the data from the backup file.'
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'error' => 'The restore stopped before it could finish, so the system may not be complete. A safety copy of your data was saved first, so nothing has been permanently lost. Please try restoring again.',
            'safety_file' => $safety['filename']
        ));
    }
    exit;
}

http_response_code(400);
echo json_encode(array('error' => 'Invalid action'));