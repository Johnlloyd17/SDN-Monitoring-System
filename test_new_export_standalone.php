<?php
// Standalone export test - no auth
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

echo "=== TESTING NEW EXPORT (standalone) ===\n\n";

$format = 'csv';

$query = "SELECT
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
            contact_details, connection_type, down_time, connectivity_status,
            area_classification, with_dict_free_wifi, last_monitored_at, monitoring_notes,
            replacement_form_file, conforme_file, uat_file, additional_uat,
            site_coordinator_name, contact_details, source_sheet
          FROM tblfwfa WHERE 1=1 LIMIT 5";

$result = mysqli_query($con, $query);
if (!$result) {
    echo "Error: " . mysqli_error($con);
    exit;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
mysqli_free_result($result);

echo "Rows fetched: " . count($rows) . "\n";

$headers = [
    'Item No.' => 'item_no',
    'Locality' => 'locality',
    'Barangay' => 'barangay',
    'District' => 'district',
    'Transport Location' => 'transport_location',
    'Transport Type' => 'transport_type',
    'Site Code' => 'site_code',
    'New Renamed' => 'new_renamed',
    'Site Locations' => 'site_locations',
    'Transfer/New Locations' => 'transfer_new_locations',
    'Remarks' => 'remarks',
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
    'Link Type' => 'link_type',
    '2026-10-06 AM' => 'status_2026_10_06_am',
    '2026-10-06 PM' => 'status_2026_10_06_pm',
    '2026-10-07 AM' => 'status_2026_10_07_am',
    '2026-10-07 PM' => 'status_2026_10_07_pm',
    '2026-10-08 AM' => 'status_2026_10_08_am',
    '2026-10-08 PM' => 'status_2026_10_08_pm',
    '2026-10-12 AM' => 'status_2026_10_12_am',
    '2026-10-12 PM' => 'status_2026_10_12_pm',
    '2026-10-13 AM' => 'status_2026_10_13_am',
    '2026-10-13 PM' => 'status_2026_10_13_pm',
    '2026-10-14 AM' => 'status_2026_10_14_am',
    '2026-10-14 PM' => 'status_2026_10_14_pm',
    '2026-10-15 AM' => 'status_2026_10_15_am',
    '2026-10-15 PM' => 'status_2026_10_15_pm',
    'Contact Details' => 'contact_details',
    'Connection Type' => 'connection_type',
    'Down Time' => 'down_time',
    'Connectivity Status' => 'connectivity_status',
    'Area Classification' => 'area_classification',
    'With DICT Free WiFi' => 'with_dict_free_wifi',
    'Last Monitored' => 'last_monitored_at',
    'Monitoring Notes' => 'monitoring_notes',
    'Replacement Form File' => 'replacement_form_file',
    'Conforme File' => 'conforme_file',
    'UAT File' => 'uat_file',
    'Additional UAT' => 'additional_uat',
    'Name (Site Coordinators)' => 'site_coordinator_name',
    'Contact Details (Coord)' => 'contact_details',
    'Source Sheet' => 'source_sheet',
];

$headerLabels = array_keys($headers);
$headerFields = array_values($headers);

echo "Header count: " . count($headerLabels) . "\n";

// Write to file directly
$filename = 'test_new_export_' . date('Ymd_His') . '.csv';
$output = fopen($filename, 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, $headerLabels);

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

$size = filesize($filename);
echo "Exported " . count($rows) . " rows to $filename ($size bytes)\n\n";

$lines = file($filename);
echo "First 3 lines:\n";
for ($i = 0; $i < min(3, count($lines)); $i++) {
    echo $lines[$i];
}

echo "\n=== DONE ===\n";