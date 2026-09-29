<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>

<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

/*
 * "Change Account" in pages/header.php.
 *
 * Administrators are stored in tbluser, staff in tblstaff. login.php marks the
 * two apart with $_SESSION['role'] === 'Administrator' for administrators and a
 * non-empty $_SESSION['staff'] for staff, so the same test is used here to pick
 * the table -- this keeps the endpoint in step with the fields the modal renders.
 */
function account_table() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrator') ? 'tbluser' : 'tblstaff';
}

if ($action === 'update_account') {
    if (!isset($_SESSION['userid'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Your session has expired. Please log in again.']);
        exit;
    }

    $username = trim(isset($_POST['txt_username']) ? $_POST['txt_username'] : '');
    $password = trim(isset($_POST['txt_password']) ? $_POST['txt_password'] : '');

    if ($username === '' || $password === '') {
        echo json_encode(['success' => false, 'error' => 'Please enter both a username and a password.']);
        exit;
    }

    $table = account_table();

    // A username that already belongs to a *different* account is rejected
    // before the update runs, matching the duplicate check the rest of the
    // user-management screens do server-side.
    $check = mysqli_prepare($con, "SELECT id FROM $table WHERE username = ? AND id <> ?");
    $userId = (int)$_SESSION['userid'];
    mysqli_stmt_bind_param($check, 'si', $username, $userId);
    mysqli_stmt_execute($check);
    $taken = mysqli_stmt_get_result($check);
    if ($taken && mysqli_num_rows($taken) > 0) {
        mysqli_stmt_close($check);
        echo json_encode(['success' => false, 'error' => 'That username is already being used. Please choose another one.']);
        exit;
    }
    mysqli_stmt_close($check);

    $stmt = mysqli_prepare($con, "UPDATE $table SET username = ?, password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $username, $password, $userId);
    $ok = mysqli_stmt_execute($stmt);
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'The account could not be saved. Please try again.']);
        exit;
    }

    // Keep the session copy in step so the navbar and any guard that reads
    // $_SESSION['username'] shows the new name without a page reload.
    $_SESSION['username'] = $username;

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid action']);
