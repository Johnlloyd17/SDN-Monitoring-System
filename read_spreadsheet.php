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

$filePath = 'uploads/fw4a/FW4A.xlsx';
if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit;
}

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

echo "Sheet names:\n";
foreach ($sheets as $s) {
    echo "  - {$s['name']} (path: {$s['sp']})\n";
}

foreach ($sheets as $s) {
    if (!in_array($s['sp'], $files, true)) {
        echo "\n=== Sheet: {$s['name']} === SKIPPED (file not found)\n";
        continue;
    }
    echo "\n=== Sheet: {$s['name']} ===\n";
    
    $sx = @simplexml_load_string($zip->getFromName($s['sp']));
    if ($sx === false) {
        echo "Unable to parse worksheet.\n";
        continue;
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
    foreach ($sx->mergeCells->mergeCell as $mc) {
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string)$mc['ref'], $m)) {
            $merges[] = [colIndex($m[1]), (int)$m[2], colIndex($m[3]), (int)$m[4]];
        }
    }
    
    echo "Merged cells:\n";
    foreach ($merges as $mg) {
        echo "  " . colName($mg[0]) . $mg[1] . ":" . colName($mg[2]) . $mg[3] . "\n";
    }
    
    // First 3 rows
    echo "First 3 rows:\n";
    for ($row = 0; $row < min(3, count($rows)); $row++) {
        echo "Row " . ($row+1) . ": ";
        $maxCol = 0;
        foreach ($rows[$row] as $c => $v) if ($c > $maxCol) $maxCol = $c;
        for ($col = 0; $col <= $maxCol; $col++) {
            $val = isset($rows[$row][$col]) ? $rows[$row][$col] : '';
            echo "[" . colName($col) . "='$val']  ";
        }
        echo "\n";
    }
    
    // First 5 data rows (rows 4-8, 0-indexed: 3-7)
    echo "\nFirst 5 data rows:\n";
    for ($row = 3; $row < min(8, count($rows)); $row++) {
        echo "Row " . ($row+1) . ": ";
        $maxCol = 0;
        foreach ($rows[$row] as $c => $v) if ($c > $maxCol) $maxCol = $c;
        for ($col = 0; $col <= $maxCol; $col++) {
            $val = isset($rows[$row][$col]) ? $rows[$row][$col] : '';
            echo "[" . colName($col) . "='$val']  ";
        }
        echo "\n";
    }
    
    echo "Total rows in sheet: " . count($rows) . "\n";
    echo "Data rows (excluding first 3): " . max(0, count($rows) - 3) . "\n";
    
    // Check coordinate format
    echo "\nCoordinate columns:\n";
    $latCol = null;
    $lngCol = null;
    $headerRowIdx = 1; // row 2 (0-indexed)
    for ($col = 0; $col < 50; $col++) {
        $val = isset($rows[$headerRowIdx][$col]) ? strtolower(trim($rows[$headerRowIdx][$col])) : '';
        if ($val === 'latitude') $latCol = $col;
        if ($val === 'longitude') $lngCol = $col;
    }
    if ($latCol !== null && $lngCol !== null) {
        echo "  Latitude at col " . colName($latCol) . ", Longitude at col " . colName($lngCol) . "\n";
        for ($row = 3; $row < min(8, count($rows)); $row++) {
            $lat = isset($rows[$row][$latCol]) ? $rows[$row][$latCol] : '';
            $lng = isset($rows[$row][$lngCol]) ? $rows[$row][$lngCol] : '';
            echo "  Row " . ($row+1) . ": lat='$lat', lng='$lng'\n";
        }
    } else {
        echo "  Latitude/Longitude not found in header row 2\n";
    }
    
    // Check date columns
    echo "\nDate-related columns:\n";
    $headerRowIdx = 1; // row 2
    for ($col = 0; $col < 50; $col++) {
        $val = isset($rows[$headerRowIdx][$col]) ? strtolower(trim($rows[$headerRowIdx][$col])) : '';
        if (strpos($val, 'date') !== false || strpos($val, 'activation') !== false || strpos($val, 'acceptance') !== false) {
            echo "  Col " . colName($col) . ": '$val'\n";
            for ($row = 3; $row < min(8, count($rows)); $row++) {
                $v = isset($rows[$row][$col]) ? $rows[$row][$col] : '';
                echo "    Row " . ($row+1) . ": '$v'\n";
            }
        }
    }
    
    // Distinct values for key columns
    echo "\nDistinct values in first 100 data rows:\n";
    $columnsToCheck = ['status', 'strategy', 'site_type', 'locality', 'barangay', 'site_code', 'nationwide_id'];
    $colMap = [];
    for ($col = 0; $col < 50; $col++) {
        $val = isset($rows[$headerRowIdx][$col]) ? strtolower(trim($rows[$headerRowIdx][$col])) : '';
        if (in_array($val, $columnsToCheck)) {
            $colMap[$val] = $col;
        }
    }
    foreach ($columnsToCheck as $colName) {
        if (isset($colMap[$colName])) {
            $col = $colMap[$colName];
            $values = [];
            for ($row = 3; $row < min(103, count($rows)); $row++) {
                $v = isset($rows[$row][$col]) ? trim($rows[$row][$col]) : '';
                if ($v !== '') $values[$v] = true;
            }
            echo "  $colName (col " . colName($col) . "): " . count($values) . " distinct values\n";
            $sample = array_slice(array_keys($values), 0, 20);
            echo "    Sample: " . implode(', ', $sample) . "\n";
        } else {
            echo "  $colName: NOT FOUND in header row 2\n";
        }
    }
}

$zip->close();