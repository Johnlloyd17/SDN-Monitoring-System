<?php
// Test actual import using the import.php logic
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(300);

include "pages/connection.php";

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';

echo "=== TESTING IMPORT ===\n\n";

// Count before
$before = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
echo "Rows before import: $before\n\n";

// Simulate the import
require_once 'pages/fw4a_data/import.php';

// We need to capture the output
$_FILES['file'] = [
    'name' => 'sdn_fw4a_v2.xlsx',
    'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'tmp_name' => $filePath,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($filePath),
];

ob_start();
include 'pages/fw4a_data/import.php';
$output = ob_get_clean();

echo "Import output:\n$output\n\n";

$data = json_decode($output, true);
if ($data) {
    echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
    echo "Inserted: " . ($data['inserted'] ?? 0) . "\n";
    echo "Sheet: " . ($data['sheet'] ?? 'unknown') . "\n";
    if (!empty($data['warnings'])) {
        echo "Warnings: " . count($data['warnings']) . "\n";
        foreach (array_slice($data['warnings'], 0, 10) as $w) echo "  - $w\n";
    }
    if (!empty($data['error'])) echo "Error: " . $data['error'] . "\n";
} else {
    echo "Failed to parse JSON output\n";
}

// Count after
$after = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
echo "\nRows after import: $after\n";
echo "Net change: " . ($after - $before) . "\n";

echo "\n=== TESTING EXPORT (CSV) ===\n\n";
// Test CSV export
$testUrl = 'pages/fw4a_data/export.php?format=csv';
echo "CSV export URL: $testUrl\n";

// Test XLSX export (will fallback to CSV if no PhpSpreadsheet)
echo "\n=== TESTING EXPORT (XLSX) ===\n\n";
$testUrl2 = 'pages/fw4a_data/export.php?format=xlsx';
echo "XLSX export URL: $testUrl2\n";

echo "\n=== DONE ===\n";