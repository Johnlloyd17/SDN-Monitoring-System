<?php
// Step 2 - Truncate and re-import once (standalone, no auth)
error_reporting(E_ALL);
ini_set('display_errors', 1);

function colIndex($letter) { $c = 0; for ($j = 0; $j < strlen($letter); $j++) $c = $c * 26 + (ord($letter[$j]) - ord('A') + 1); return $c - 1; }
function colName($i) { $s = ''; while ($i >= 0) { $s = chr(ord('A') + ($i % 26)) . $s; $i = intdiv($i, 26) - 1; } return $s; }
function parseDate($value) {
    $v = trim((string)$value); if ($v === '') return null;
    if (is_numeric($v)) { $n = floatval($v); if ($n >= 1 && $n <= 80000) { $d = new DateTime('1899-12-30'); $d->modify('+' . (int)$n . ' days'); return $d->format('Y-m-d'); } }
    $formats = ['Y-m-d', 'Y/m/d', 'm/d/Y', 'm-d-Y', 'd/m/Y', 'd-m-Y', 'F j, Y', 'F j Y', 'M j, Y', 'j M Y', 'd M Y', 'M-j-Y', 'd-M-Y', 'n/j/Y', 'j-n-Y', 'Ymd'];
    foreach ($formats as $f) { $dt = DateTime::createFromFormat($f, $v); if ($dt) { $errs = DateTime::getLastErrors(); if ($errs === false || ($errs['warning_count'] === 0 && $errs['error_count'] === 0)) { return $dt->format('Y-m-d'); } } }
    $t = strtotime($v); if ($t !== false) return date('Y-m-d', $t);
    return null;
}
function normalizeBool($value) { $v = strtoupper(trim((string)$value)); if ($v === '') return null; if (in_array($v, ['TRUE','YES','Y','1','CHECKED','X'], true)) return 1; if (in_array($v, ['FALSE','NO','N','0'], true)) return 0; return null; }

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;
$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 2: TRUNCATE + RE-IMPORT ONCE ===\n\n";

// 1. Backup current state
echo "1. Creating backup...\n";
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_final");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_final AS SELECT * FROM tblfwfa");
$backupCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa_backup_20261014_final"))['c'];
echo "   Backup created: $backupCount rows\n\n";

// 2. Truncate table
echo "2. Truncating tblfwfa...\n";
mysqli_query($con, "TRUNCATE TABLE tblfwfa");
echo "   Truncated\n\n";

// 3. Import the file ONCE using standalone import logic
echo "3. Importing sdn_fw4a_v2.xlsx (sheet: FW4A - Updated SDN Monitoring)...\n\n";

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
$rows = [];
foreach ($sx->sheetData->row as $row) {
    $cells = [];
    foreach ($row->c as $c) {
        $ref = (string)$c['r']; preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
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
$zip->close();

// Expand merged cells
$merges = [];
if (isset($sx->mergeCells)) {
    foreach ($sx->mergeCells->mergeCell as $mc) {
        if (preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', (string)$mc['ref'], $m)) {
            $merges[] = [colIndex($m[1]), (int)$m[2], colIndex($m[3]), (int)$m[4]];
        }
    }
}
function xlsxExpandMerges(&$rows, $merges) {
    foreach ($merges as $mg) {
        list($c1, $r1, $c2, $r2) = $mg;
        $i1 = $r1 - 1; $i2 = $r2 - 1;
        if ($i2 < 2) continue;
        $anchor = isset($rows[$i1][$c1]) ? trim((string)$rows[$i1][$c1]) : '';
        if ($anchor === '') continue;
        for ($rr = max(2, $i1); $rr <= $i2; $rr++) {
            if (!isset($rows[$rr])) $rows[$rr] = [];
            for ($cc = $c1; $cc <= $c2; $cc++) {
                $cur = isset($rows[$rr][$cc]) ? trim((string)$rows[$rr][$cc]) : '';
                if ($cur === '') $rows[$rr][$cc] = $anchor;
            }
        }
    }
}
xlsxExpandMerges($rows, $merges);

// CORRECT mapping: spreadsheet column index -> database field name
$spreadsheetToDb = [
    0  => 'item_no', 1 => 'locality', 2 => 'barangay', 3 => 'district',
    4  => 'transport_location', 5 => 'transport_type', 6 => 'site_code',
    7  => 'new_renamed', 8 => 'site_locations', 9 => 'transfer_new_locations',
    10 => 'remarks', 11 => 'nationwide_id', 12 => 'site_type',
    13 => 'date_of_activation', 14 => 'current_date_of_acceptance',
    15 => 'latitude', 16 => 'longitude',
    17 => 'procurement_initiative', 18 => 'installation_type',
    19 => 'uat', 20 => 'conforme',
    21 => 'strategy', 22 => 'link_type',
    37 => 'contact_details',
];

// Hardcode status column mapping for "FW4A - Updated SDN Monitoring" sheet
$statusCols = [
    23 => 'status_2026_10_06_am', 24 => 'status_2026_10_06_pm',
    25 => 'status_2026_10_07_am', 26 => 'status_2026_10_07_pm',
    27 => 'status_2026_10_08_am', 28 => 'status_2026_10_08_pm',
    29 => 'status_2026_10_12_am', 30 => 'status_2026_10_12_pm',
    31 => 'status_2026_10_13_am', 32 => 'status_2026_10_13_pm',
    33 => 'status_2026_10_14_am', 34 => 'status_2026_10_14_pm',
    35 => 'status_2026_10_15_am', 36 => 'status_2026_10_15_pm',
];
foreach ($statusCols as $c => $f) {
    $spreadsheetToDb[$c] = $f;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;
$con = mysqli_connect($host, $user, $pass, $db, $port) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 2: TRUNCATE + RE-IMPORT ONCE ===\n\n";

// 1. Backup current state
echo "1. Creating backup...\n";
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_backup_20261014_final");
mysqli_query($con, "CREATE TABLE tblfwfa_backup_20261014_final AS SELECT * FROM tblfwfa");
$backupCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa_backup_20261014_final"))['c'];
echo "   Backup created: $backupCount rows\n\n";

// 2. Truncate table
echo "2. Truncating tblfwfa...\n";
mysqli_query($con, "TRUNCATE TABLE tblfwfa");
echo "   Truncated\n\n";

// 3. Import the file ONCE using standalone import logic
echo "3. Importing sdn_fw4a_v2.xlsx (sheet: FW4A - Updated SDN Monitoring)...\n\n";

echo "3. Importing data...\n\n";

$inserted = 0;
$warnings = [];
$hardError = null;

foreach ($rows as $idx => $cells) {
    if ($idx < 2) continue;
    $rec = [];
    $hasData = false;
    foreach ($spreadsheetToDb as $c => $f) {
        $v = isset($cells[$c]) ? trim((string)$cells[$c]) : '';
        if ($v !== '') { $rec[$f] = $v; $hasData = true; }
    }
    if (!$hasData) continue;
    
    $g = function ($f) use ($rec) { return trim((string)($rec[$f] ?? '')); };
    
    $item_no    = $g('item_no');
    $locality   = $g('locality');
    $barangay   = $g('barangay');
    $district   = $g('district');
    $transportLocation = $g('transport_location');
    $transportType     = $g('transport_type');
    $siteCode     = $g('site_code');
    $newRenamed        = $g('new_renamed');
    $siteLocations     = $g('site_locations');
    $transferNew       = $g('transfer_new_locations');
    $remarks           = $g('remarks');
    $nationwideId = $g('nationwide_id');
    $siteType     = $g('site_type');
    $latRaw       = $g('latitude');
    $lngRaw       = $g('longitude');
    $procurement  = $g('procurement_initiative');
    $installation = $g('installation_type');
    $uatRaw       = $g('uat');
    $conformeRaw  = $g('conforme');
    $strategy     = $g('strategy');
    $linkType     = $g('link_type');
    
    $tag = $item_no !== '' ? "item_no=$item_no" : '';
    
    $actVal = parseDate($g('date_of_activation'));
    if ($g('date_of_activation') !== '' && $actVal === null) { $warnings[] = ($tag ? "$tag, " : '') . "date_of_activation unparseable"; }
    $accVal = parseDate($g('current_date_of_acceptance'));
    if ($g('current_date_of_acceptance') !== '' && $accVal === null) { $warnings[] = ($tag ? "$tag, " : '') . "current_date_of_acceptance unparseable"; }
    
    $lat = $lng = null;
    if ($latRaw !== '' && is_numeric($latRaw) && floatval($latRaw) >= -90 && floatval($latRaw) <= 90) $lat = floatval($latRaw);
    if ($lngRaw !== '' && is_numeric($lngRaw) && floatval($lngRaw) >= -180 && floatval($lngRaw) <= 180) $lng = floatval($lngRaw);
    
    if ($procurement !== '' && !in_array($procurement, ['Centrally Procured', 'Regional Procured'], true)) { $warnings[] = "$tag procurement invalid"; $procurement = ''; }
    if ($installation !== '' && !in_array($installation, ['Region Initiated', 'Manage Service'], true)) { $warnings[] = "$tag installation invalid"; $installation = ''; }
    
    $uat = null;
    if ($uatRaw !== '') { $v = strtoupper(trim($uatRaw)); if (in_array($v, ['TRUE','YES','Y','1','CHECKED','X'])) $uat = 1; elseif (in_array($v, ['FALSE','NO','N','0'])) $uat = 0; }
    $conforme = null;
    if ($conformeRaw !== '') { $v = strtoupper(trim($conformeRaw)); if (in_array($v, ['TRUE','YES','Y','1','CHECKED','X'])) $conforme = 1; elseif (in_array($v, ['FALSE','NO','N','0'])) $conforme = 0; }
    
    // Status columns
    $statusCols = [
        'status_2026_10_06_am', 'status_2026_10_06_pm',
        'status_2026_10_07_am', 'status_2026_10_07_pm',
        'status_2026_10_08_am', 'status_2026_10_08_pm',
        'status_2026_10_12_am', 'status_2026_10_12_pm',
        'status_2026_10_13_am', 'status_2026_10_13_pm',
        'status_2026_10_14_am', 'status_2026_10_14_pm',
        'status_2026_10_15_am', 'status_2026_10_15_pm',
    ];
    $statusVals = [];
    foreach ($statusCols as $col) {
        $v = $g($col);
        if ($v !== '' && in_array($v, ['Active','Inactive','Ongoing','Assist','Terminated','Deactivated','Ongoing Acceptance','For Installation','For Transfer','Down','No Signal','With SMS Only','With Data','Unknown'], true)) {
            $statusVals[] = "'" . mysqli_real_escape_string($con, $v) . "'";
        } else {
            $statusVals[] = 'NULL';
        }
    }
    
    $newRenamed       = $g('new_renamed');
    $connectionType   = $g('connection_type');
    $downTime         = $g('down_time');
    $connectivityStatus = $g('connectivity_status');
    $areaClassification = $g('area_classification');
    $withDictFreeWifi = $g('with_dict_free_wifi');
    $lastMonitoredAt  = $g('last_monitored_at');
    $monitoringNotes  = $g('monitoring_notes');
    
    $esc = function ($s) use ($con) { return mysqli_real_escape_string($con, $s); };
    $str = function ($s) use ($esc) { return $s !== '' ? "'" . $esc($s) . "'" : 'NULL'; };
    $num = function ($n) { return $n !== null ? (string)$n : 'NULL'; };
    
    $query = "INSERT INTO tblfwfa (
        item_no, locality, barangay, district, transport_location, transport_type,
        site_code, new_renamed, site_locations, transfer_new_locations, remarks,
        nationwide_id, site_type,
        date_of_activation, current_date_of_acceptance, latitude, longitude,
        procurement_initiative, installation_type, uat, conforme, strategy, link_type,
        status_2026_10_06_am, status_2026_10_06_pm,
        status_2026_10_07_am, status_2026_10_07_pm,
        status_2026_10_08_am, status_2026_10_08_pm,
        status_2026_10_12_am, status_2026_10_12_pm,
        status_2026_10_13_am, status_2026_10_13_pm,
        status_2026_10_14_am, status_2026_10_14_pm,
        status_2026_10_15_am, status_2026_10_15_pm,
        connection_type, down_time, connectivity_status,
        area_classification, with_dict_free_wifi, last_monitored_at, monitoring_notes,
        replacement_form_file, conforme_file, uat_file, additional_uat,
        site_coordinator_name, contact_details, source_sheet
    ) VALUES (
        " . ($item_no !== '' ? intval($item_no) : 'NULL') . ", " . $str($locality) . ", " . $str($barangay) . ", " . $str($district) . ", "
        . $str($transportLocation) . ", " . $str($transportType) . ", "
        . $str($siteCode) . ", " . $str($newRenamed) . ", " . $str($siteLocations) . ", " . $str($transferNew) . ", "
        . $str($remarks) . ", " . $str($nationwideId) . ", " . $str($siteType) . ", "
        . ($actVal !== null ? "'$actVal'" : 'NULL') . ", " . ($accVal !== null ? "'$accVal'" : 'NULL') . ", " . $num($lat) . ", " . $num($lng) . ", "
        . $str($procurement) . ", " . $str($installation) . ", " . ($uat !== null ? (string)$uat : 'NULL') . ", " . ($conforme !== null ? (string)$conforme : 'NULL') . ", "
        . $str($strategy) . ", " . $str($linkType) . ", "
        . implode(', ', $statusVals) . ", "
        . $str($g('connection_type')) . ", " . $str($g('down_time')) . ", " . $str($g('connectivity_status')) . ", "
        . $str($g('area_classification')) . ", " . $str($g('with_dict_free_wifi')) . ", " . $str($g('last_monitored_at')) . ", " . $str($g('monitoring_notes')) . ", "
        . $str($g('replacement_form_file')) . ", " . $str($g('conforme_file')) . ", " . $str($g('uat_file')) . ", " . $str($g('additional_uat')) . ", "
        . $str($g('site_coordinator_name')) . ", " . $str($g('contact_details')) . ", " . $str('FW4A - Updated SDN Monitoring')
    . ")";
    
    if (@mysqli_query($con, $query)) {
        $inserted++;
    } else {
        $hardError = mysqli_error($con);
        echo "Hard error at row $idx: $hardError\n";
        break;
    }
}

echo "Inserted: $inserted\n";
if ($hardError) echo "Hard error: $hardError\n";
if (!empty($warnings)) {
    echo "Warnings (" . count($warnings) . "):\n";
    foreach (array_slice($warnings, 0, 10) as $w) echo "  - $w\n";
}

$after = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "\nRows after: $after\n";

// 4. Verify
echo "\n4. Verification\n";
$finalCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Final total: $finalCount\n";
echo "Expected: 615\n";
echo "Match: " . ($finalCount == 615 ? "YES" : "NO - diff: " . ($finalCount - 615)) . "\n";

// Check duplicates
$q = "SELECT COUNT(*) as c FROM (
    SELECT item_no, locality, barangay, district, transport_location, transport_type,
           site_code, new_renamed, site_locations, transfer_new_locations, remarks,
           nationwide_id, site_type,
           date_of_activation, current_date_of_acceptance, latitude, longitude,
           procurement_initiative, installation_type, uat, conforme, strategy, link_type,
           status_2026_10_06_am, status_2026_10_06_pm,
           status_2026_10_07_am, status_2026_10_07_pm,
           status_2026_10_08_am, status_2026_10_08_pm,
           status_2026_10_12_am, status_2026_10_12_pm,
           status_2026_10_13_am, status_2026_10_13_pm,
           status_2026_10_14_am, status_2026_10_14_pm,
           status_2026_10_15_am, status_2026_10_15_pm,
           contact_details
    FROM tblfwfa
    GROUP BY item_no, locality, barangay, district, transport_location, transport_type,
             site_code, new_renamed, site_locations, transfer_new_locations, remarks,
             nationwide_id, site_type,
             date_of_activation, current_date_of_acceptance, latitude, longitude,
             procurement_initiative, installation_type, uat, conforme, strategy, link_type,
             status_2026_10_06_am, status_2026_10_06_pm,
             status_2026_10_07_am, status_2026_10_07_pm,
             status_2026_10_08_am, status_2026_10_08_pm,
             status_2026_10_12_am, status_2026_10_12_pm,
             status_2026_10_13_am, status_2026_10_13_pm,
             status_2026_10_14_am, status_2026_10_14_pm,
             status_2026_10_15_am, status_2026_10_15_pm,
             contact_details
    HAVING COUNT(*) > 1
) as t";
$dupCheck = mysqli_fetch_assoc(mysqli_query($con, $q))['c'];
echo "Duplicate groups: $dupCheck\n";

// Spot check
$check = mysqli_query($con, "SELECT id, item_no, locality, site_code FROM tblfwfa ORDER BY RAND() LIMIT 5");
while ($r = mysqli_fetch_assoc($check)) {
    echo "  id={$r['id']}, item_no={$r['item_no']}, locality={$r['locality']}, site_code={$r['site_code']}\n";
}

echo "\n=== DONE ===\n";