<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
?>
<?php

header('Content-Type: application/json');

include '../pages/connection.php';

$isStaff = (isset($_SESSION['role']) && $_SESSION['role'] === 'staff');
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

function pm_json_die($payload) {
    echo json_encode($payload);
    exit;
}

function pm_log($con, $msg) {
    $msg = mysqli_real_escape_string($con, $msg);
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '$msg')");
}

// Both lookup tables are whitelisted here; $table never comes from user input.
function pm_find_by_code($con, $table, $code) {
    $code = mysqli_real_escape_string($con, $code);
    $res = mysqli_query($con, "SELECT code, name FROM $table WHERE code = '$code' LIMIT 1");
    return ($res && ($row = mysqli_fetch_assoc($res))) ? $row : null;
}

function pm_name_taken($con, $table, $name, $exceptCode) {
    $safeName = mysqli_real_escape_string($con, $name);
    $except = mysqli_real_escape_string($con, $exceptCode);
    $res = mysqli_query($con, "SELECT code FROM $table WHERE name = '$safeName' AND code <> '$except' LIMIT 1");
    return ($res && mysqli_num_rows($res) > 0);
}

// ---------------------------------------------------------------
// Categories (property_categories) - list / add / edit.
// No delete action exists: rows are never removed.
// The code is immutable; only the name can be renamed later.
// ---------------------------------------------------------------
if ($action === 'list_categories') {
    $rows = [];
    $res = mysqli_query($con, "SELECT code, name FROM property_categories ORDER BY code");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
    }
    $used = [];
    foreach ($rows as $r) $used[$r['code']] = true;
    $next = null;
    for ($i = 1; $i <= 99; $i++) {
        $c = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        if (!isset($used[$c])) { $next = $c; break; }
    }
    pm_json_die(['success' => true, 'data' => $rows, 'next_free_code' => $next]);
}

if ($action === 'add_category') {
    if ($isStaff) {
        pm_json_die(['success' => false, 'error' => 'Staff are not allowed to add categories.']);
    }
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    if (!preg_match('/^[0-9]{2}$/', $code)) {
        pm_json_die(['success' => false, 'error' => 'Code must be exactly 2 digits (e.g. 03).']);
    }
    if ($name === '') {
        pm_json_die(['success' => false, 'error' => 'Name is required.']);
    }
    if (pm_find_by_code($con, 'property_categories', $code)) {
        pm_json_die(['success' => false, 'error' => 'Category code ' . $code . ' already exists.']);
    }
    if (pm_name_taken($con, 'property_categories', $name, $code)) {
        pm_json_die(['success' => false, 'error' => 'A category named "' . $name . '" already exists.']);
    }
    $safeName = mysqli_real_escape_string($con, $name);
    if (mysqli_query($con, "INSERT INTO property_categories (code, name) VALUES ('$code', '$safeName')")) {
        pm_log($con, 'Added Category: ' . $code . ' - ' . $name);
        pm_json_die(['success' => true, 'message' => 'Category added successfully.']);
    }
    if (mysqli_errno($con) === 1062) {
        pm_json_die(['success' => false, 'error' => 'That category code or name already exists.']);
    }
    pm_json_die(['success' => false, 'error' => 'Failed to add category: ' . mysqli_error($con)]);
}

if ($action === 'edit_category') {
    if ($isStaff) {
        pm_json_die(['success' => false, 'error' => 'Staff are not allowed to change categories.']);
    }
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    if ($code === '') {
        pm_json_die(['success' => false, 'error' => 'Missing category code.']);
    }
    if ($name === '') {
        pm_json_die(['success' => false, 'error' => 'Name is required.']);
    }
    if (!pm_find_by_code($con, 'property_categories', $code)) {
        pm_json_die(['success' => false, 'error' => 'Category not found.']);
    }
    if (pm_name_taken($con, 'property_categories', $name, $code)) {
        pm_json_die(['success' => false, 'error' => 'A category named "' . $name . '" already exists.']);
    }
    $safeName = mysqli_real_escape_string($con, $name);
    if (mysqli_query($con, "UPDATE property_categories SET name = '$safeName' WHERE code = '$code'")) {
        pm_log($con, 'Renamed Category ' . $code . ' to: ' . $name);
        pm_json_die(['success' => true, 'message' => 'Category updated successfully.']);
    }
    pm_json_die(['success' => false, 'error' => 'Failed to update category: ' . mysqli_error($con)]);
}

// ---------------------------------------------------------------
// Locations (property_locations) - list / add / edit.
// Same rules as categories: no delete, code immutable, and the
// code is uppercase letters + numbers only.
// ---------------------------------------------------------------
if ($action === 'list_locations') {
    $rows = [];
    $res = mysqli_query($con, "SELECT code, name FROM property_locations ORDER BY code");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
    }
    pm_json_die(['success' => true, 'data' => $rows]);
}

if ($action === 'add_location') {
    if ($isStaff) {
        pm_json_die(['success' => false, 'error' => 'Staff are not allowed to add locations.']);
    }
    $code = strtoupper(preg_replace('/\s+/', '', (string)($_POST['code'] ?? '')));
    $name = trim($_POST['name'] ?? '');
    if (!preg_match('/^[A-Z0-9]{1,16}$/', $code)) {
        pm_json_die(['success' => false, 'error' => 'Code can only use uppercase letters and numbers (max 16).']);
    }
    if ($name === '') {
        pm_json_die(['success' => false, 'error' => 'Name is required.']);
    }
    if (pm_find_by_code($con, 'property_locations', $code)) {
        pm_json_die(['success' => false, 'error' => 'Location code ' . $code . ' already exists.']);
    }
    if (pm_name_taken($con, 'property_locations', $name, $code)) {
        pm_json_die(['success' => false, 'error' => 'A location named "' . $name . '" already exists.']);
    }
    $safeName = mysqli_real_escape_string($con, $name);
    if (mysqli_query($con, "INSERT INTO property_locations (code, name) VALUES ('$code', '$safeName')")) {
        pm_log($con, 'Added Location: ' . $code . ' - ' . $name);
        pm_json_die(['success' => true, 'message' => 'Location added successfully.']);
    }
    if (mysqli_errno($con) === 1062) {
        pm_json_die(['success' => false, 'error' => 'That location code or name already exists.']);
    }
    pm_json_die(['success' => false, 'error' => 'Failed to add location: ' . mysqli_error($con)]);
}

if ($action === 'edit_location') {
    if ($isStaff) {
        pm_json_die(['success' => false, 'error' => 'Staff are not allowed to change locations.']);
    }
    $code = strtoupper(preg_replace('/\s+/', '', (string)($_POST['code'] ?? '')));
    $name = trim($_POST['name'] ?? '');
    if ($code === '') {
        pm_json_die(['success' => false, 'error' => 'Missing location code.']);
    }
    if ($name === '') {
        pm_json_die(['success' => false, 'error' => 'Name is required.']);
    }
    if (!pm_find_by_code($con, 'property_locations', $code)) {
        pm_json_die(['success' => false, 'error' => 'Location not found.']);
    }
    if (pm_name_taken($con, 'property_locations', $name, $code)) {
        pm_json_die(['success' => false, 'error' => 'A location named "' . $name . '" already exists.']);
    }
    $safeName = mysqli_real_escape_string($con, $name);
    if (mysqli_query($con, "UPDATE property_locations SET name = '$safeName' WHERE code = '$code'")) {
        pm_log($con, 'Renamed Location ' . $code . ' to: ' . $name);
        pm_json_die(['success' => true, 'message' => 'Location updated successfully.']);
    }
    pm_json_die(['success' => false, 'error' => 'Failed to update location: ' . mysqli_error($con)]);
}

http_response_code(400);
pm_json_die(['success' => false, 'error' => 'Invalid action']);
