<?php
// Import coordinates from spreadsheet
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db   = getenv('DB_NAME') ?: 'dict_proj';

$candidatePorts = [getenv('DB_PORT'), 4306, 3306];
$candidatePorts = array_values(array_unique(array_filter($candidatePorts)));
$port = null;
foreach ($candidatePorts as $candidate) {
    $socket = @fsockopen($host, (int)$candidate, $errno, $errstr, 1);
    if ($socket) { fclose($socket); $port = (int)$candidate; break; }
}
if ($port === null) { die('MySQL port not found'); }

$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== IMPORTING COORDINATES FROM SPREADSHEET ===\n\n";

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';

$zip = new ZipArchive();
if ($zip->open($filePath) !== true) { die("Unable to open XLSX"); }

$shared = [];
$x = $zip->getFromName('xl/sharedStrings.xml');
if ($x !== false) { $sx = @simplexml_load_string($x); if ($sx) foreach ($sx->si as $si) { if (isset($si->t)) $shared[] = (string)$si->t; else { $t = ''; foreach ($si->r as $r) $t .= (string)$r->t; $shared[] = $t; } } }

$files = []; for ($i = 0; $i < $zip->numFiles; $i++) $files[] = $zip->getNameIndex($i);
$sheets = [];
$wb = @simplexml_load_string($zip->getFromName('xl/workbook.xml'));
if ($wb !== false) {
    $rns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    $rels = @simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
    $ridMap = []; if ($rels !== false) foreach ($rels->Relationship as $r) $ridMap[(string)$r['Id']] = (string)$r['Target'];
    foreach ($wb->sheets->sheet as $s) {
        $rid = (string)$s->attributes($rns)['id'];
        $target = isset($ridMap[$rid]) ? $ridMap[$rid] : '';
        $sp = ($target === '') ? '' : ($target[0] === '/' ? ltrim($target, '/') : 'xl/' . $target);
        $sheets[] = ['name' => (string)$s['name'], 'sp' => $sp];
    }
}

$targetSheet = null;
foreach ($sheets as $s) { if ($s['name'] === 'FW4A - Updated SDN Monitoring') { $targetSheet = $s; break; } }
if (!$targetSheet) { die("Sheet not found"); }

$sx = @simplexml_load_string($zip->getFromName($targetSheet['sp']));
if ($sx === false) { die("Unable to parse worksheet"); }

$rows = [];
foreach ($sx->sheetData->row as $row) {
    $cells = [];
    foreach ($row->c as $c) {
        $ref = (string)$c['r']; preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
        if (!$m) continue;
        $col = $m[1]; // use letter for easier mapping
        $type = (string)$c['t'];
        if ($type === 's') { $idx = isset($c->v) ? (int)(string)$c->v : -1; $val = ($idx >= 0 && isset($shared[$idx])) ? $shared[$idx] : ''; }
        elseif ($type === 'inlineStr') { $val = isset($c->is->t) ? (string)$c->is->t : ''; }
        else { $val = isset($c->v) ? (string)$c->v : ''; }
        $cells[$col] = $val;
    }
    $rows[] = $cells;
}

$updated = 0;
$errors = 0;

// Data rows start at index 3 (row 4 in spreadsheet)
for ($idx = 3; $idx < count($rows); $idx++) {
    $cells = $rows[$idx];
    
    $siteCode = trim($cells['G'] ?? '');
    if ($siteCode === '') continue;
    
    $lat = trim($cells['P'] ?? '');
    $lng = trim($cells['Q'] ?? '');
    
    if ($lat === '' || $lng === '') continue;
    
    if (!is_numeric($lat) || !is_numeric($lng)) {
        $errors++;
        continue;
    }
    
    $latVal = floatval($lat);
    $lngVal = floatval($lng);
    
    if ($latVal < -90 || $latVal > 90 || $lngVal < -180 || $lngVal > 180) {
        $errors++;
        continue;
    }
    
    $stmt = mysqli_prepare($con, "UPDATE tblfwfa SET latitude = ?, longitude = ? WHERE site_code = ? AND (latitude IS NULL OR latitude = 0 OR longitude IS NULL OR longitude = 0)");
    mysqli_stmt_bind_param($stmt, 'dds', $latVal, $lngVal, $siteCode);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    if ($affected > 0) $updated++;
    mysqli_stmt_close($stmt);
}

echo "Coordinates updated: $updated\n";
echo "Errors: $errors\n";

$withCoords = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE latitude IS NOT NULL AND latitude != 0 AND longitude IS NOT NULL AND longitude != 0"))['c'];
echo "Rows with coords now: $withCoords\n";