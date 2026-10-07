<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();
header('Content-Type: application/json');

include '../pages/connection.php';

// Series register: every inventory record that already carries a Property No.
// Format B numbers (YYYY-CC-NNN-LOC) are grouped by year and listed in
// running-number order; everything else appears under "Other formats".
$formatB = '/^([0-9]{4})-([0-9]{2})-([0-9]{3})-([A-Z0-9]{1,16})$/i';

$res = mysqli_query($con, "SELECT id, description, unit, serial, quantity, date, received, cost, assigned_to, inventory_item_no FROM inventory WHERE inventory_item_no IS NOT NULL AND TRIM(inventory_item_no) <> ''");

$rows = array();
$others = array();
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $raw = trim((string)$r['inventory_item_no']);
        $m = null;
        $r['is_format_b'] = (bool)preg_match($formatB, $raw, $m);
        if ($r['is_format_b']) {
            $r['f_year'] = (int)$m[1];
            $r['f_cc'] = strtoupper($m[2]);
            $r['f_seq'] = (int)$m[3];
            $r['f_loc'] = strtoupper($m[4]);
            $rows[] = $r;
        } else {
            $others[] = $r;
        }
    }
}

usort($rows, function ($a, $b) {
    if ($a['f_year'] !== $b['f_year']) return $a['f_year'] - $b['f_year'];
    if ($a['f_seq'] !== $b['f_seq']) return $a['f_seq'] - $b['f_seq'];
    if ($a['f_cc'] !== $b['f_cc']) return strcmp($a['f_cc'], $b['f_cc']);
    return strcmp($a['f_loc'], $b['f_loc']);
});
usort($others, function ($a, $b) {
    return strcmp($a['inventory_item_no'], $b['inventory_item_no']);
});

echo json_encode(array(
    'success' => true,
    'years' => count(array_unique(array_map(function ($r) { return $r['f_year']; }, $rows))),
    'data' => $rows,
    'others' => $others,
));