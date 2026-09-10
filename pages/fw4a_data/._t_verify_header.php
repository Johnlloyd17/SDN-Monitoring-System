<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

function auditHeader($role) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];
    if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
    $_SESSION['role'] = $role;
    $_SESSION['username'] = 'verify_tool';

    ob_start();
    include 'fw4a_data.php';
    $html = ob_get_clean();

    if (!preg_match('#<thead>(.*?)</thead>#s', $html, $m)) {
        return "NO THEAD FOUND";
    }
    $thead = $m[1];
    preg_match_all('#<tr class="grp-row">(.*?)</tr>#s', $thead, $r1);
    preg_match_all('#<tr class="grp-sub">(.*?)</tr>#s', $thead, $r2);
    $row1 = $r1[1][0] ?? '';
    $row2 = $r2[1][0] ?? '';

    preg_match_all('#<th([^>]*)>(.*?)</th>#s', $row1, $root1);
    $groups = [];
    $singles = [];
    foreach ($root1[1] as $i => $attrs) {
        $label = trim(strip_tags($root1[2][$i]));
        if (preg_match('#colspan="(\d+)"#', $attrs, $c)) {
            $groups[] = $label . '[' . $c[1] . ']';
        } else {
            $singles[] = $label;
        }
    }

    preg_match_all('#<th[^>]*>(.*?)</th>#s', $row2, $sub);
    $subCells = array_map('trim', $sub[1]);

    $line = [];
    $line[] = "ROLE ($role):";
    $line[] = "  grp-row cell count = " . count($root1[1]);
    $line[] = "  groups = " . implode(', ', $groups);
    $line[] = "  singles = " . implode(', ', $singles);
    $line[] = "  sub-row cells = " . count($subCells) . " -> " . implode(', ', $subCells);

    // effective column width = singles + sum(groups colspans)
    $eff = count($singles);
    foreach ($root1[1] as $attrs) {
        if (preg_match('#colspan="(\d+)"#', $attrs, $c)) $eff += intval($c[1]);
    }
    $line[] = "  EFFECTIVE total columns = $eff";
    return implode(PHP_EOL, $line);
}

echo auditHeader('Administrator');
echo PHP_EOL . PHP_EOL;
echo auditHeader('Encoder');
echo PHP_EOL;
?>