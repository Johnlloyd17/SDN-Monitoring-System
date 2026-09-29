<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
require_once __DIR__ . '/../pages/fwfa_letter/letter_rows.php';
?>
<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($con)) { include __DIR__ . '/../pages/connection.php'; }

$module = letter_module();
$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!in_array($action, array('item', 'photos'), true) || $id <= 0) {
    letter_json_out(array('error' => 'Invalid action.'), 400);
}

// The photo table is shared with the activity and planned-activity modules, so
// every request is anchored to a letter row first. Without this check a caller
// could read or remove another module's attachment by passing its id.
$stmt = mysqli_prepare($con, "SELECT * FROM {$module['table']} WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    letter_json_out(array('error' => 'Item not found'), 404);
}

if ($action === 'item') {
    letter_json_out($row);
}

$stmt = mysqli_prepare($con, "SELECT * FROM {$module['photos']} WHERE activityid = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$photos = array();
while ($p = mysqli_fetch_assoc($res)) {
    $name = basename($p['filename']);
    $p['filepath'] = 'photo/' . rawurlencode($name);
    $p['type']     = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $photos[] = $p;
}
mysqli_stmt_close($stmt);

letter_json_out($photos);
