<?php
// Standalone export test - debug version
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

echo "=== TESTING EXPORT (CSV) ===\n\n";

$query = "SELECT item_no, locality, site_code FROM tblfwfa LIMIT 3";
$result = mysqli_query($con, $query);
$rows = [];
while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }

echo "Rows: " . count($rows) . "\n";
print_r($rows);

$headers = ['Item No.' => 'item_no', 'Locality' => 'locality', 'Site Code' => 'site_code'];
$headerLabels = array_keys($headers);
$headerFields = array_values($headers);

$output = fopen('php://memory', 'w');
fwrite($output, "\xEF\xBB\xBF");
echo "After BOM write\n";
fputcsv($output, $headerLabels);
echo "After header write\n";

foreach ($rows as $row) {
    $csvRow = [];
    foreach ($headerFields as $field) {
        $csvRow[] = $row[$field] ?? '';
    }
    fputcsv($output, $csvRow);
    echo "After row write\n";
}

rewind($output);
$csvContent = stream_get_contents($output);
fclose($output);

echo "Content length: " . strlen($csvContent) . "\n";
echo "Content:\n$csvContent\n";

$filename = 'test_export_debug.csv';
file_put_contents($filename, $csvContent);
echo "Saved to $filename\n";