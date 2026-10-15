<?php
// Step 1 Fast Audit - Database focused
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dict_proj';
$port = 3306;

$con = mysqli_connect('localhost', 'root', '', 'dict_proj', 3306) or die(mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 1 FAST AUDIT REPORT ===\n\n";

// a) Safety: truncate on real table? Backup?
echo "a) SAFETY CHECK\n";
echo "----------------\n";
$backups = [];
$r = mysqli_query($con, "SHOW TABLES LIKE 'tblfwfa_backup%'");
while ($row = mysqli_fetch_assoc($r)) {
    $t = array_keys($row)[0];
    $cnt = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM `{$row[$t]}`"))['c'];
    echo "  Backup: {$row[$t]} - $cnt rows\n";
}
if (empty($backups)) echo "  NO BACKUP TABLES FOUND\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Current tblfwfa: $total rows\n\n";

// b) Header row check
echo "b) HEADER ROW CHECK\n";
echo "-------------------\n";
$headerCheck = mysqli_query($con, "SELECT id, item_no, locality, site_code, site_locations, source_sheet FROM tblfwfa WHERE site_code = 'Site Code' OR site_locations = 'Site Locations' OR item_no = '0' OR item_no = '' OR item_no IS NULL");
while ($row = mysqli_fetch_assoc($headerCheck)) {
    echo "  id={$row['id']}: item_no='{$row['item_no']}', locality='{$row['locality']}', site_code='{$row['site_code']}', site_locations='{$row['site_locations']}', source={$row['source_sheet']}\n";
}

// Count item_no = 0, blank, NULL
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE item_no = '0' OR item_no = '' OR item_no IS NULL");
echo "Rows with item_no=0/blank/NULL: " . mysqli_fetch_assoc($r)['c'] . "\n\n";

// c) File vs Table match (using existing table data only, no spreadsheet reload)
echo "\nc) FILE vs TABLE MATCH (using DB only)\n";
echo "----------------------\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Table rows: $total\n";

// Check blanks
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE site_code = '' OR site_code IS NULL");
echo "Blank Site Code: " . mysqli_fetch_assoc($r)['c'] . "\n";
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE item_no = '' OR item_no IS NULL OR item_no = '0'");
echo "  Blank/zero Item No: " . mysqli_fetch_assoc($r)['c'] . "\n\n";

// d) References
echo "d) REFERENCES to fw4a_data.id\n";
echo "--------------------------\n";
$fkQuery = "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'tblfwfa' AND REFERENCED_COLUMN_NAME = 'id' AND TABLE_SCHEMA = 'dict_proj'";
$fkResult = mysqli_query($con, $fkQuery);
while ($row = mysqli_fetch_assoc($fkResult)) {
    echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} (constraint: {$row['CONSTRAINT_NAME']})\n";
}
$checkTables = ['tblactivityphoto', 'tbllogs'];
foreach (['tblactivityphoto', 'tbllogs'] as $t) {
    $check = @mysqli_query($con, "SHOW TABLES LIKE '$t'");
    if ($check && mysqli_num_rows($check) > 0) {
        $cols = mysqli_query($con, "SHOW COLUMNS FROM `$t`");
        while ($c = mysqli_fetch_assoc($cols)) {
            if (stripos($c['Field'], 'fwfa') !== false || stripos($c['Field'], 'activity') !== false) {
                echo "  Possible reference: $t.{$c['Field']}\n";
            }
        }
    }
}

// e) Slow first load - we can't measure from here, but we can check DB health
echo "\ne) DATABASE HEALTH (proxy for slow load)\n";
echo "--------------------------------------\n";
$r = mysqli_query($con, "SHOW FULL PROCESSLIST");
while ($r2 = mysqli_fetch_assoc($r)) {
    if ($r2['Time'] > 10 || ($r2['Command'] === 'Sleep' && $r2['Time'] > 60 && $r2['State'] !== '')) {
        echo "  Process {$r2['Id']}: {$r2['Command']}, Time={$r2['Time']}, State={$r2['State']}, Info={$r2['Info']}\n";
    }
}
$r = mysqli_query($con, "CHECK TABLE tblfwfa");
while ($r2 = mysqli_fetch_assoc($r)) {
    echo "  CHECK TABLE: {$r2['Table']} - {$r2['Msg_type']}: {$r2['Msg_text']}\n";
}

// Check MySQL error log (last 50 lines)
$errorLog = @file('/var/log/mysql/error.log', FILE_IGNORE_NEW_LINES);
if ($errorLog) {
    echo "\nMySQL error log (last 20 lines):\n";
    foreach (array_slice($errorLog, -20) as $line) echo "  $line\n";
} else {
    echo "\nMySQL error log not accessible at /var/log/mysql/error.log\n";
}

// Check Apache error log
$apacheLog = @file('/var/log/apache2/error.log', FILE_IGNORE_NEW_LINES);
if ($apacheLog) {
    echo "\nApache error log (last 20 lines):\n";
    foreach (array_slice($apacheLog, -20) as $line) echo "  $line\n";
} else {
    echo "\nApache error log not accessible at /var/log/apache2/error.log\n";
}

echo "\n=== AUDIT COMPLETE ===\n";