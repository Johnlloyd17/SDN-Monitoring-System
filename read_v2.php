<?php
function colIndex($letter) {
    $c = 0;
    for ($j = 0; $j < strlen($letter); $j++) $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1);
    return $c - 1;
}
function colName($i) {
    $s = '';
    while ($i >= 0) { $s = chr(ord('A') + ($i % 26)) . $s; $i = intdiv($i, 26) - 1; }
    return $s;
}

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';

$zip = new ZipArchive();
if ($zip->open($filePath) !== true) {
    echo "Unable to open the XLSX file.\n";
    exit;
}

$shared = [];
$x = $zip->getFromName('xl/sharedStrings.xml');
if ($x !== false) {
    $sx = @simplexml_load_string($x);
    if ($sx) foreach ($sx->si as $si) {
        if (isset($si->t)) $shared[] = (string)$si->t;
        else { $t = ''; foreach ($si->r as $r) $t .= (string)$r->t; $shared[] = $t; }
    }
}

$files = [];
for ($i = 0; $i < $zip->numFiles; $i++) $files[] = $zip->getNameIndex($i);
$sheets = [];
$wb = @simplexml_load_string($zip->getFromName('xl/workbook.xml'));
if ($wb !== false) {
    $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    $rels = @simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
    $ridMap = [];
    if ($rels !== false) foreach ($rels->Relationship as $r) $ridMap[(string)$r['Id']] = (string)$r['Target'];
    foreach ($wb->sheets->sheet as $s) {
        $rid = (string)$s->attributes($rns)['id'];
        $target = isset($ridMap[$rid]) ? $ridMap[$rid] : '';
        $sp = ($target === '') ? '' : ($target[0] === '/' ? ltrim($target, '/') : 'xl/' . $target);
        $sheets[] = ['name' => (string)$s['name'], 'sp' => $sp];
    }
}

$targetSheet = null;
foreach ($sheets as $s) {
    if ($s['name'] === 'FW4A - Updated SDN Monitoring') {
        $targetSheet = $s;
        break;
    }
}
if (!$targetSheet) {
    echo "Sheet 'FW4A - Updated SDN Monitoring' not found!\n";
    echo "Available sheets:\n";
    foreach ($sheets as $s) echo "  - {$s['name']}\n";
    exit;
}

echo "Found sheet: {$targetSheet['name']}\n\n";

$sx = @simplexml_load_string($zip->getFromName($targetSheet['sp']));
if ($sx === false) {
    echo "Unable to parse worksheet.\n";
    exit;
}

$rows = [];
foreach ($sx->sheetData->row as $row) {
    $cells = [];
    foreach ($row->c as $c) {
        $ref = (string)$c['r'];
        preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
        if (!$m) continue;
        $col = colIndex($m[1]);
        $type = (string)$c['t'];
        if ($type === 's') { $idx = isset($c->v) ? (int)(string)$c->v : -1; $val = ($idx >= 0 && isset($shared[$idx])) ? $shared[$idx] : ''; }
        elseif ($type === 'inlineStr') { $val = isset($c->is->t) ? (string)$c->is->t : ''; }
        else { $val = isset($c->v) ? (string)$c->v : ''; }
        $cells[$col] = $val;
    }
    $rows[] = $cells;
}

$merges = [];
if (isset($sx->mergeCells)) {
    foreach ($sx->mergeCells->mergeCell as $mc) {
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string)$mc['ref'], $m)) {
            $merges[] = [colIndex($m[1]), (int)$m[2], colIndex($m[3]), (int)$m[4]];
        }
    }
}

echo "=== Merged Cells ===\n";
foreach ($merges as $mg) {
    echo "  " . colName($mg[0]) . $mg[1] . ":" . colName($mg[2]) . $mg[3] . "\n";
}

echo "\n=== Rows 1-3 (Header) ===\n";
for ($row = 0; $row < 3; $row++) {
    echo "Row " . ($row+1) . ": ";
    for ($col = 0; $col < 38; $col++) {
        $val = isset($rows[$row][$col]) ? $rows[$row][$col] : '';
        if ($val !== '') {
            echo "[" . colName($col) . "]='$val' ";
        }
    }
    echo "\n";
}

echo "\n=== Row 4 (First data) ===\n";
for ($col = 0; $col < 38; $col++) {
    $val = isset($rows[3][$col]) ? $rows[3][$col] : '';
    if ($val !== '') {
        echo "[" . colName($col) . "]='$val' ";
    }
}
echo "\n";

echo "\n=== Row 5 ===\n";
for ($col = 0; $col < 38; $col++) {
    $val = isset($rows[4][$col]) ? $rows[4][$col] : '';
    if ($val !== '') {
        echo "[" . colName($col) . "]='$val' ";
    }
}
echo "\n";

echo "\n=== Row 612 ===\n";
for ($col = 0; $col < 38; $col++) {
    $val = isset($rows[611][$col]) ? $rows[611][$col] : '';
    if ($val !== '') {
        echo "[" . colName($col) . "]='$val' ";
    }
}
echo "\n";

echo "\n=== Row 613 (after data) ===\n";
for ($col = 0; $col < 38; $col++) {
    $val = isset($rows[612][$col]) ? $rows[612][$col] : '';
    if ($val !== '') {
        echo "[" . colName($col) . "]='$val' ";
    }
}
echo "\n";

$zip->close();