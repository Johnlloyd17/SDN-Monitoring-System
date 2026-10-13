<?php
// Round-trip test - simple version
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(300);

// DB connection
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

echo "=== ROUND-TRIP TEST ===\n\n";

// Step 1: Create test table
echo "1. Creating test table tblfwfa_test...\n";
mysqli_query($con, "DROP TABLE IF EXISTS tblfwfa_test");
mysqli_query($con, "CREATE TABLE tblfwfa_test LIKE tblfwfa");
echo "   Done\n\n";

// Step 2: Export current data to CSV
echo "2. Exporting data to CSV...\n";
$query = "SELECT * FROM tblfwfa";
$result = mysqli_query($con, $query);
$rows = [];
while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }
mysqli_free_result($result);

$headers = [
    'Item No.' => 'item_no', 'Locality' => 'locality', 'Barangay' => 'barangay',
    'District' => 'district', 'Transport Location' => 'transport_location',
    'Transport Type' => 'transport_type', 'Site Locations' => 'site_locations',
    'Transfer/New Locations' => 'transfer_new_locations', 'Remarks' => 'remarks',
    'Site Code' => 'site_code', 'Nationwide ID' => 'nationwide_id', 'Site Type' => 'site_type',
    'Date of Activation' => 'date_of_activation', 'Current Date of Acceptance' => 'current_date_of_acceptance',
    'Latitude' => 'latitude', 'Longitude' => 'longitude',
    'Procurement Initiative' => 'procurement_initiative', 'Installation Type' => 'installation_type',
    'UAT' => 'uat', 'Conforme' => 'conforme', 'Strategy' => 'strategy', 'Status' => 'status',
    'Link Type' => 'link_type', 'Replacement Form File' => 'replacement_form_file',
    'Conforme File' => 'conforme_file', 'UAT File' => 'uat_file', 'Additional UAT' => 'additional_uat',
    'Name (Site Coordinators)' => 'site_coordinator_name', 'Contact Details' => 'contact_details',
    'Source Sheet' => 'source_sheet',
];

$csvFile = 'roundtrip_export_' . date('Ymd_His') . '.csv';
$output = fopen($csvFile, 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, array_keys($headers));

$headerFields = array_values($headers);
foreach ($rows as $row) {
    $csvRow = [];
    foreach ($headerFields as $field) {
        $value = $row[$field] ?? '';
        if (in_array($field, ['uat', 'conforme'])) {
            if ($value === '1' || $value === 1 || $value === true) $value = 'Yes';
            elseif ($value === '0' || $value === 0 || $value === false) $value = 'No';
            else $value = '';
        }
        $csvRow[] = $value;
    }
    fputcsv($output, $csvRow);
}
fclose($output);
echo "   Exported " . count($rows) . " rows to $csvFile\n\n";

// Step 3: Import CSV into test table
echo "3. Importing CSV into tblfwfa_test...\n";

$handle = fopen($csvFile, 'r');
$headerLine = fgetcsv($handle);
$imported = 0;
$errors = 0;

$VALID_STATUS = ['Active', 'Inactive', 'Ongoing', 'Assist', 'Terminated', 'Deactivated', 'Ongoing Acceptance', 'For Installation', 'For Transfer'];
$VALID_PROCUREMENT_INITIATIVE = ['Centrally Procured', 'Regional Procured'];
$VALID_INSTALLATION_TYPE = ['Region Initiated', 'Manage Service'];

while (($data = fgetcsv($handle)) !== false) {
    if (count($data) < 30) continue;
    
    $rec = [];
    for ($i = 0; $i < min(30, count($data)); $i++) {
        $rec[$headerFields[$i]] = trim($data[$i]);
    }
    
    $hasData = false;
    foreach ($rec as $v) if ($v !== '') { $hasData = true; break; }
    if (!$hasData) continue;
    
    // Extract fields
    $item_no    = $rec['item_no'] ?? '';
    $locality   = $rec['locality'] ?? '';
    $barangay   = $rec['barangay'] ?? '';
    $district   = $rec['district'] ?? '';
    $transportLocation = $rec['transport_location'] ?? '';
    $transportType     = $rec['transport_type'] ?? '';
    $siteLocations     = $rec['site_locations'] ?? '';
    $transferNew       = $rec['transfer_new_locations'] ?? '';
    $remarks           = $rec['remarks'] ?? '';
    $siteCode     = $rec['site_code'] ?? '';
    $nationwideId = $rec['nationwide_id'] ?? '';
    $siteType     = $rec['site_type'] ?? '';
    $latRaw       = $rec['latitude'] ?? '';
    $lngRaw       = $rec['longitude'] ?? '';
    $procurement  = $rec['procurement_initiative'] ?? '';
    $installation = $rec['installation_type'] ?? '';
    $uatRaw       = $rec['uat'] ?? '';
    $conformeRaw  = $rec['conforme'] ?? '';
    $strategy     = $rec['strategy'] ?? '';
    $status       = $rec['status'] ?? '';
    $linkType     = $rec['link_type'] ?? '';
    $replFile     = $rec['replacement_form_file'] ?? '';
    $confFile     = $rec['conforme_file'] ?? '';
    $uatFile      = $rec['uat_file'] ?? '';
    $addUat       = $rec['additional_uat'] ?? '';
    $coordName    = $rec['site_coordinator_name'] ?? '';
    $contact      = $rec['contact_details'] ?? '';
    $sourceSheet  = $rec['source_sheet'] ?? '';
    
    // Parse dates
    $actVal = null;
    $dateAct = $rec['date_of_activation'] ?? '';
    if ($dateAct !== '') {
        if (is_numeric($dateAct)) {
            $n = floatval($dateAct);
            if ($n >= 1 && $n <= 80000) {
                $d = new DateTime('1899-12-30');
                $d->modify('+' . (int)$n . ' days');
                $actVal = $d->format('Y-m-d');
            }
        } else {
            $t = strtotime($dateAct);
            if ($t !== false) $actVal = date('Y-m-d', $t);
        }
    }
    
    $accVal = null;
    $dateAcc = $rec['current_date_of_acceptance'] ?? '';
    if ($dateAcc !== '') {
        if (is_numeric($dateAcc)) {
            $n = floatval($dateAcc);
            if ($n >= 1 && $n <= 80000) {
                $d = new DateTime('1899-12-30');
                $d->modify('+' . (int)$n . ' days');
                $accVal = $d->format('Y-m-d');
            }
        } else {
            $t = strtotime($dateAcc);
            if ($t !== false) $accVal = date('Y-m-d', $t);
        }
    }
    
    // Coordinates
    $lat = $lng = null;
    if ($latRaw !== '' && is_numeric($latRaw) && floatval($latRaw) >= -90 && floatval($latRaw) <= 90) $lat = floatval($latRaw);
    if ($lngRaw !== '' && is_numeric($lngRaw) && floatval($lngRaw) >= -180 && floatval($lngRaw) <= 180) $lng = floatval($lngRaw);
    
    // ENUMs
    if ($procurement !== '' && !in_array($procurement, $VALID_PROCUREMENT_INITIATIVE, true)) $procurement = '';
    if ($installation !== '' && !in_array($installation, $VALID_INSTALLATION_TYPE, true)) $installation = '';
    if ($status !== '' && !in_array($status, $VALID_STATUS, true)) $status = '';
    
    // Booleans
    $uat = null;
    if ($uatRaw !== '') {
        $v = strtoupper(trim($uatRaw));
        if (in_array($v, ['TRUE', 'YES', 'Y', '1', 'CHECKED', 'X'], true)) $uat = 1;
        elseif (in_array($v, ['FALSE', 'NO', 'N', '0'], true)) $uat = 0;
    }
    $conforme = null;
    if ($conformeRaw !== '') {
        $v = strtoupper(trim($conformeRaw));
        if (in_array($v, ['TRUE', 'YES', 'Y', '1', 'CHECKED', 'X'], true)) $conforme = 1;
        elseif (in_array($v, ['FALSE', 'NO', 'N', '0'], true)) $conforme = 0;
    }
    
    // Escape values
    $item_no_sql = $item_no !== '' ? intval($item_no) : 'NULL';
    $locality_sql = $locality !== '' ? "'" . mysqli_real_escape_string($con, $locality) . "'" : 'NULL';
    $barangay_sql = $barangay !== '' ? "'" . mysqli_real_escape_string($con, $barangay) . "'" : 'NULL';
    $district_sql = $district !== '' ? "'" . mysqli_real_escape_string($con, $district) . "'" : 'NULL';
    $transportLocation_sql = $transportLocation !== '' ? "'" . mysqli_real_escape_string($con, $transportLocation) . "'" : 'NULL';
    $transportType_sql = $transportType !== '' ? "'" . mysqli_real_escape_string($con, $transportType) . "'" : 'NULL';
    $siteLocations_sql = $siteLocations !== '' ? "'" . mysqli_real_escape_string($con, $siteLocations) . "'" : 'NULL';
    $transferNew_sql = $transferNew !== '' ? "'" . mysqli_real_escape_string($con, $transferNew) . "'" : 'NULL';
    $remarks_sql = $remarks !== '' ? "'" . mysqli_real_escape_string($con, $remarks) . "'" : 'NULL';
    $siteCode_sql = $siteCode !== '' ? "'" . mysqli_real_escape_string($con, $siteCode) . "'" : 'NULL';
    $nationwideId_sql = $nationwideId !== '' ? "'" . mysqli_real_escape_string($con, $nationwideId) . "'" : 'NULL';
    $siteType_sql = $siteType !== '' ? "'" . mysqli_real_escape_string($con, $siteType) . "'" : 'NULL';
    $actVal_sql = $actVal !== null ? "'$actVal'" : 'NULL';
    $accVal_sql = $accVal !== null ? "'$accVal'" : 'NULL';
    $lat_sql = $lat !== null ? (string)$lat : 'NULL';
    $lng_sql = $lng !== null ? (string)$lng : 'NULL';
    $procurement_sql = $procurement !== '' ? "'" . mysqli_real_escape_string($con, $procurement) . "'" : 'NULL';
    $installation_sql = $installation !== '' ? "'" . mysqli_real_escape_string($con, $installation) . "'" : 'NULL';
    $uat_sql = $uat !== null ? (string)$uat : 'NULL';
    $conforme_sql = $conforme !== null ? (string)$conforme : 'NULL';
    $strategy_sql = $strategy !== '' ? "'" . mysqli_real_escape_string($con, $strategy) . "'" : 'NULL';
    $status_sql = $status !== '' ? "'" . mysqli_real_escape_string($con, $status) . "'" : 'NULL';
    $linkType_sql = $linkType !== '' ? "'" . mysqli_real_escape_string($con, $linkType) . "'" : 'NULL';
    $replFile_sql = $replFile !== '' ? "'" . mysqli_real_escape_string($con, $replFile) . "'" : 'NULL';
    $confFile_sql = $confFile !== '' ? "'" . mysqli_real_escape_string($con, $confFile) . "'" : 'NULL';
    $uatFile_sql = $uatFile !== '' ? "'" . mysqli_real_escape_string($con, $uatFile) . "'" : 'NULL';
    $addUat_sql = $addUat !== '' ? "'" . mysqli_real_escape_string($con, $addUat) . "'" : 'NULL';
    $coordName_sql = $coordName !== '' ? "'" . mysqli_real_escape_string($con, $coordName) . "'" : 'NULL';
    $contact_sql = $contact !== '' ? "'" . mysqli_real_escape_string($con, $contact) . "'" : 'NULL';
    $sourceSheet_sql = $sourceSheet !== '' ? "'" . mysqli_real_escape_string($con, $sourceSheet) . "'" : 'NULL';
    
    $query = "INSERT INTO tblfwfa_test (
        item_no, locality, barangay, district, transport_location, transport_type,
        site_locations, transfer_new_locations, remarks, site_code, nationwide_id, site_type,
        date_of_activation, current_date_of_acceptance, latitude, longitude,
        procurement_initiative, installation_type, uat, conforme, strategy, status, link_type,
        replacement_form_file, conforme_file, uat_file, additional_uat,
        site_coordinator_name, contact_details, source_sheet
    ) VALUES (
        $item_no_sql, $locality_sql, $barangay_sql, $district_sql, $transportLocation_sql, $transportType_sql,
        $siteLocations_sql, $transferNew_sql, $remarks_sql, $siteCode_sql, $nationwideId_sql, $siteType_sql,
        $actVal_sql, $accVal_sql, $lat_sql, $lng_sql,
        $procurement_sql, $installation_sql, $uat_sql, $conforme_sql, $strategy_sql, $status_sql, $linkType_sql,
        $replFile_sql, $confFile_sql, $uatFile_sql, $addUat_sql,
        $coordName_sql, $contact_sql, $sourceSheet_sql
    )";
    
    if (@mysqli_query($con, $query)) {
        $imported++;
    } else {
        $errors++;
    }
}
fclose($handle);

echo "   Imported: $imported, Errors: $errors\n\n";

// Step 4: Compare
echo "4. Comparing original vs imported...\n";
$origCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
$testCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa_test"))['c'];
echo "   Original: $origCount, Test: $testCount\n";

$orig = mysqli_query($con, "SELECT item_no, locality, barangay, site_code, source_sheet FROM tblfwfa ORDER BY id LIMIT 5");
$test = mysqli_query($con, "SELECT item_no, locality, barangay, site_code, source_sheet FROM tblfwfa_test ORDER BY id LIMIT 5");

echo "   Original sample:\n";
while ($r = mysqli_fetch_assoc($orig)) echo "     " . json_encode($r) . "\n";
echo "   Imported sample:\n";
while ($r = mysqli_fetch_assoc($test)) echo "     " . json_encode($r) . "\n";

$sheets = mysqli_query($con, "SELECT source_sheet, COUNT(*) as c FROM tblfwfa_test GROUP BY source_sheet");
echo "   Source sheet distribution in test table:\n";
while ($r = mysqli_fetch_assoc($sheets)) echo "     {$r['source_sheet']}: {$r['c']}\n";

// Cleanup
mysqli_query($con, "DROP TABLE tblfwfa_test");
echo "\n   Test table dropped\n";

echo "\n=== ROUND-TRIP COMPLETE ===\n";