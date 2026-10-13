<?php
// Direct import test - bypass web auth
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(300);

include "pages/connection.php";

$filePath = 'uploads/fw4a/sdn_fw4a_v2.xlsx';

echo "=== TESTING IMPORT (direct) ===\n\n";

$before = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
echo "Rows before import: $before\n\n";

// Copy the import logic here directly
require_once 'pages/fw4a_data/import.php';

// We'll call the functions directly without the web wrapper
$z = xlsxOpen($filePath, $error);
if ($z === null) { echo "ERROR: $error\n"; exit; }

$chosen = xlsxDetectSheet($z['zip'], $z['shared'], $z['sheets'], $z['files'], $LABEL_MAP, $error);
$z['zip']->close();

if ($chosen === null) { echo "ERROR: No sheet detected: $error\n"; exit; }

echo "Chosen: {$chosen['sheet']['name']} (matched {$chosen['match']})\n";

$rows = $chosen['rows'];
xlsxExpandMerges($rows, $chosen['merges']);
$colToField = $chosen['colToField'];
$sourceSheet = $chosen['sheet']['name'];

$inserted = 0;
$warnings = [];
$hardError = null;

$VALID_STATUS = ['Active', 'Inactive', 'Ongoing', 'Assist', 'Terminated', 'Deactivated', 'Ongoing Acceptance', 'For Installation', 'For Transfer'];
$VALID_PROCUREMENT_INITIATIVE = ['Centrally Procured', 'Regional Procured'];
$VALID_INSTALLATION_TYPE = ['Region Initiated', 'Manage Service'];

foreach ($rows as $idx => $cells) {
    if ($idx < 2) continue;
    $rec = [];
    $hasData = false;
    foreach ($colToField as $c => $f) {
        $v = isset($cells[$c]) ? trim((string)$cells[$c]) : '';
        if ($v !== '') { $rec[$f] = $v; $hasData = true; }
    }
    if (!$hasData) continue;
    $err = '';
    if (saveRow($con, $rec, $VALID_STATUS, $VALID_PROCUREMENT_INITIATIVE, $VALID_INSTALLATION_TYPE, $warnings, $err, $sourceSheet)) {
        $inserted++;
    } else {
        $hardError = $err;
        echo "Hard error at row $idx: $err\n";
        break;
    }
}

echo "Inserted: $inserted\n";
if ($hardError) echo "Hard error: $hardError\n";
if (!empty($warnings)) {
    echo "Warnings (" . count($warnings) . "):\n";
    foreach (array_slice($warnings, 0, 20) as $w) echo "  - $w\n";
}

$after = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa"))['c'];
echo "\nRows after import: $after\n";
echo "Net change: " . ($after - $before) . "\n";

// Verify source_sheet values
$sheets = mysqli_query($con, "SELECT source_sheet, COUNT(*) as c FROM tblfwfa GROUP BY source_sheet");
echo "\nSource sheet distribution:\n";
while ($r = mysqli_fetch_assoc($sheets)) {
    echo "  {$r['source_sheet']}: {$r['c']}\n";
}