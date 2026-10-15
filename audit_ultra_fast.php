<?php
// Ultra-fast audit - minimal queries only
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(30);

$con = mysqli_connect('localhost', 'root', '', 'dict_proj', 3306) or die("Connection failed: " . mysqli_connect_error());
mysqli_set_charset($con, 'utf8mb4');

echo "=== STEP 1 FAST AUDIT ===\n\n";

// a) Safety
echo "a) SAFETY\n";
$r = mysqli_query($con, "SHOW TABLES LIKE 'tblfwfa_backup%'");
while ($row = mysqli_fetch_assoc($r)) {
    $t = array_keys($row)[0];
    $cnt = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM `{$row[$t]}`"))['c'];
    echo "  Backup: {$row[$t]} - $cnt rows\n";
}
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Current tblfwfa: $total rows\n\n";

// b) Header row check
echo "\nb) HEADER ROW CHECK\n";
$r = mysqli_query($con, "SELECT id, item_no, locality, site_code, site_locations, source_sheet FROM tblfwfa WHERE site_code = 'Site Code' OR site_locations = 'Site Locations' OR item_no = '0' OR item_no = '' OR item_no IS NULL");
while ($row = mysqli_fetch_assoc($r)) {
    echo "  id={$row['id']}: item_no='{$row['item_no']}', locality='{$row['locality']}', site_code='{$row['site_code']}', site_locations='{$row['site_locations']}', source={$row['source_sheet']}\n";
}
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE item_no = '0' OR item_no = '' OR item_no IS NULL");
echo "Rows with item_no=0/blank/NULL: " . mysqli_fetch_assoc($r)['c'] . "\n\n";

// c) File count (quick)
echo "\nc) FILE vs TABLE\n";
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa"))['c'];
echo "Table rows: $total\n";
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE site_code = '' OR site_code IS NULL");
echo "Blank Site Code: " . mysqli_fetch_assoc($r)['c'] . "\n";
$r = mysqli_query($con, "SELECT COUNT(*) AS c FROM tblfwfa WHERE item_no = '' OR item_no IS NULL OR item_no = '0'");
echo "Blank/zero Item No: " . mysqli_fetch_assoc($r)['c'] . "\n\n";

// d) References
echo "d) REFERENCES\n";
$r = mysqli_query($con, "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'tblfwfa' AND REFERENCED_COLUMN_NAME = 'id' AND TABLE_SCHEMA = 'dict_proj'");
while ($row = mysqli_fetch_assoc($r)) echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} ({$row['CONSTRAINT_NAME']})\n";
$r = mysqli_query($con, "SHOW COLUMNS FROM tblactivityphoto");
while ($r2 = mysqli_fetch_assoc($r)) if (stripos($r2['Field'], 'fwfa') !== false) echo "  tblactivityphoto.{$r2['Field']}\n";
$r = mysqli_query($con, "SHOW COLUMNS FROM tbllogs");
while ($r2 = mysqli_fetch_assoc($r)) if (stripos($r2['Field'], 'fwfa') !== false) echo "  tbllogs.{$r2['Field']}\n";

// d) Slow load - processlist
echo "\ne) PROCESSLIST\n";
$r = mysqli_query($con, "SHOW FULL PROCESSLIST");
while ($r2 = mysqli_fetch_assoc($r)) {
    if ($r2['Time'] > 10 || ($r2['Command'] === 'Sleep' && $r2['Time'] > 60)) {
        echo "  ID={$r2['Id']}, Time={$r2['Time']}, Cmd={$r2['Command']}, State={$r2['State']}\n";
    }
}

// Table health
$r = mysqli_query($con, "CHECK TABLE tblfwfa");
while ($r2 = mysqli_fetch_assoc($r)) echo "  CHECK: {$r2['Msg_type']}: {$r2['Msg_text']}\n";

echo "\n=== AUDIT DONE ===\n";