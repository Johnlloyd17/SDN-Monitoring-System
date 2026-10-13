<?php
// Full export test
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

echo "=== TESTING FULL EXPORT (CSV) ===\n\n";

$filterLocality = '';
$filterBarangay = '';
$filterType = '';
$filterStrategy = '';

$query = "SELECT
            item_no, locality, barangay, district, transport_location, transport_type,
            site_locations, transfer_new_locations, remarks, site_code, nationwide_id, site_type,
            date_of_activation, current_date_of_acceptance, latitude, longitude,
            procurement_initiative, installation_type, uat, conforme, strategy, status, link_type,
            replacement_form_file, conforme_file, uat_file, additional_uat,
            site_coordinator_name, contact_details, source_sheet
          FROM tblfwfa WHERE 1=1";

if (!empty($filterLocality)) $query .= " AND locality = '$filterLocality'";
if (!empty($filterBarangay)) $query .= " AND barangay = '$filterBarangay'";
if (!empty($filterType)) $query .= " AND site_type = '$filterType'";
if (!empty($filterStrategy)) $query .= " AND strategy = '$filterStrategy'";

$result = mysqli_query($con, $query);
if (!$result) { echo "Error: " . mysqli_error($con); exit; }

$rows = [];
while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }
mysqli_free_result($result);

echo "Total rows to export: " . count($rows) . "\n\n";

$headers = [
    'Item No.' => 'item_no',
    'Locality' => 'locality',
    'Barangay' => 'barangay',
    'District' => 'district',
    'Transport Location' => 'transport_location',
    'Transport Type' => 'transport_type',
    'Site Locations' => 'site_locations',
    'Transfer/New Locations' => 'transfer_new_locations',
    'Remarks' => 'remarks',
    'Site Code' => 'site_code',
    'Nationwide ID' => 'nationwide_id',
    'Site Type' => 'site_type',
    'Date of Activation' => 'date_of_activation',
    'Current Date of Acceptance' => 'current_date_of_acceptance',
    'Latitude' => 'latitude',
    'Longitude' => 'longitude',
    'Procurement Initiative' => 'procurement_initiative',
    'Installation Type' => 'installation_type',
    'UAT' => 'uat',
    'Conforme' => 'conforme',
    'Strategy' => 'strategy',
    'Status' => 'status',
    'Link Type' => 'link_type',
    'Replacement Form File' => 'replacement_form_file',
    'Conforme File' => 'conforme_file',
    'UAT File' => 'uat_file',
    'Additional UAT' => 'additional_uat',
    'Name (Site Coordinators)' => 'site_coordinator_name',
    'Contact Details' => 'contact_details',
    'Source Sheet' => 'source_sheet',
];

$headerLabels = array_keys($headers);
$headerFields = array_values($headers);

// Generate CSV to file directly (avoid memory issues)
$filename = 'fw4a_export_' . date('Ymd_His') . '.csv';
$output = fopen($filename, 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, $headerLabels);

$exported = 0;
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
    $exported++;
}

fclose($output);

$size = filesize($filename);
echo "Exported $exported rows to $filename ($size bytes)\n\n";

// Verify by reading back first few lines
$handle = fopen($filename, 'r');
echo "First 3 lines:\n";
for ($i = 0; $i < 3; $i++) {
    $line = fgets($handle);
    if ($line === false) break;
    echo $line;
}
fclose($handle);

// Check source_sheet distribution in export
$sheetCounts = [];
foreach ($rows as $row) {
    $sheet = $row['source_sheet'] ?? 'NULL';
    $sheetCounts[$sheet] = ($sheetCounts[$sheet] ?? 0) + 1;
}
echo "\nSource sheet distribution in export:\n";
foreach ($sheetCounts as $sheet => $count) {
    echo "  $sheet: $count\n";
}