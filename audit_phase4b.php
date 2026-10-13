<?php
// Phase 4 Audit - Filters, Map, CRUD, Table
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

echo "=== PHASE 4 AUDIT: FILTERS, MAP, CRUD, TABLE ===\n\n";

// 1. Filter Options Analysis
echo "1. FILTER OPTIONS (distinct values):\n\n";

echo "   Strategy:\n";
$strat = mysqli_query($con, "SELECT strategy, COUNT(*) as c FROM tblfwfa WHERE strategy != '' GROUP BY strategy ORDER BY c DESC");
while ($r = mysqli_fetch_assoc($strat)) echo "     {$r['strategy']}: {$r['c']}\n";

echo "\n   Site Type:\n";
$type = mysqli_query($con, "SELECT site_type, COUNT(*) as c FROM tblfwfa WHERE site_type != '' GROUP BY site_type ORDER BY c DESC");
while ($r = mysqli_fetch_assoc($type)) echo "     {$r['site_type']}: {$r['c']}\n";

echo "\n   Locality:\n";
$loc = mysqli_query($con, "SELECT locality, COUNT(*) as c FROM tblfwfa WHERE locality != '' GROUP BY locality ORDER BY c DESC");
while ($r = mysqli_fetch_assoc($loc)) echo "     {$r['locality']}: {$r['c']}\n";

echo "\n   Barangay (top 30):\n";
$brgy = mysqli_query($con, "SELECT barangay, COUNT(*) as c FROM tblfwfa WHERE barangay != '' GROUP BY barangay ORDER BY c DESC LIMIT 30");
while ($r = mysqli_fetch_assoc($brgy)) echo "     {$r['barangay']}: {$r['c']}\n";

// Check for whitespace/spelling variants
echo "\n   Locality variants (trimmed):\n";
$locRaw = mysqli_query($con, "SELECT TRIM(locality) as loc, COUNT(*) as c FROM tblfwfa WHERE locality != '' GROUP BY TRIM(locality) ORDER BY c DESC");
while ($r = mysqli_fetch_assoc($locRaw)) echo "     '{$r['loc']}': {$r['c']}\n";

echo "\n   Barangay variants (trimmed, top 30):\n";
$brgyRaw = mysqli_query($con, "SELECT TRIM(barangay) as brg, COUNT(*) as c FROM tblfwfa WHERE barangay != '' GROUP BY TRIM(barangay) ORDER BY c DESC LIMIT 30");
while ($r = mysqli_fetch_assoc($brgyRaw)) echo "     '{$r['brg']}': {$r['c']}\n";

// 2. Map Coordinates Analysis
echo "\n\n2. MAP COORDINATES:\n";
$coord = mysqli_query($con, "SELECT id, locality, barangay, site_code, latitude, longitude FROM tblfwfa WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != 0 AND longitude != 0");
$coordCount = mysqli_num_rows($coord);
echo "   Rows with valid coords: $coordCount / 1230\n";

$badCoords = [];
while ($r = mysqli_fetch_assoc($coord)) {
    $lat = $r['latitude'];
    $lng = $r['longitude'];
    $issues = [];
    if ($lat < 7 || $lat > 13) $issues[] = "lat out of PH range";
    if ($lng < 116 || $lng > 127) $issues[] = "lng out of PH range";
    if ($lat == 0 && $lng == 0) $issues[] = "zero coords";
    if (!empty($issues)) {
        $badCoords[] = [$r['id'], $r['site_code'], $lat, $lng, $issues];
    }
}
echo "   Rows with coordinate issues: " . count($badCoords) . "\n";
foreach (array_slice($badCoords, 0, 10) as $b) {
    echo "     ID {$b[0]} ({$b[1]}): lat={$b[2]}, lng={$b[3]} -> " . implode(', ', $b[4]) . "\n";
}

// Check for swapped lat/lng
echo "\n   Checking for swapped lat/lng (lat > lng might indicate swap):\n";
$swapped = mysqli_query($con, "SELECT id, site_code, latitude, longitude FROM tblfwfa WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude > longitude");
while ($r = mysqli_fetch_assoc($swapped)) {
    echo "     ID {$r['id']} ({$r['site_code']}): lat={$r['latitude']}, lng={$r['longitude']}\n";
}

// 3. Status Enum vs Actual Values
echo "\n\n3. STATUS VALUES:\n";
$statusEnum = "enum('Active','Inactive','Ongoing','Assist','Terminated','Deactivated','Ongoing Acceptance','For Installation','For Transfer')";
echo "   DB Enum: $statusEnum\n";
echo "   Actual values in data:\n";
$st = mysqli_query($con, "SELECT status, COUNT(*) as c FROM tblfwfa GROUP BY status");
while ($r = mysqli_fetch_assoc($st)) {
    $val = $r['status'] === '' ? '(empty)' : $r['status'];
    echo "     '$val': {$r['c']}\n";
}

// 4. Transport Location / Type
echo "\n\n4. TRANSPORT LOCATION / TYPE:\n";
$tl = mysqli_query($con, "SELECT transport_location, COUNT(*) as c FROM tblfwfa WHERE transport_location != '' GROUP BY transport_location ORDER BY c DESC LIMIT 20");
while ($r = mysqli_fetch_assoc($tl)) echo "     {$r['transport_location']}: {$r['c']}\n";

$tt = mysqli_query($con, "SELECT transport_type, COUNT(*) as c FROM tblfwfa WHERE transport_type != '' GROUP BY transport_type ORDER BY c DESC");
while ($r = mysqli_fetch_assoc($tt)) echo "     {$r['transport_type']}: {$r['c']}\n";

// 5. Site Code uniqueness per source_sheet
echo "\n\n5. SITE CODE UNIQUENESS:\n";
$sc = mysqli_query($con, "SELECT site_code, source_sheet, COUNT(*) as c FROM tblfwfa WHERE site_code != '' GROUP BY site_code, source_sheet HAVING c > 1");
$dupes = mysqli_num_rows($sc);
echo "   Duplicate (site_code, source_sheet) pairs: $dupes\n";
if ($dupes > 0) {
    while ($r = mysqli_fetch_assoc($sc)) {
        echo "     {$r['site_code']} / {$r['source_sheet']}: {$r['c']}\n";
    }
}

// 6. Nationwide ID
echo "\n\n6. NATIONWIDE ID:\n";
$nid = mysqli_query($con, "SELECT nationwide_id, COUNT(*) as c FROM tblfwfa WHERE nationwide_id != '' GROUP BY nationwide_id HAVING c > 1");
$nidDupes = mysqli_num_rows($nid);
echo "   Duplicate nationwide_ids: $nidDupes\n";
$nidCount = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa WHERE nationwide_id != ''"))['c'];
echo "   Rows with nationwide_id: $nidCount\n";

// 7. Date columns
echo "\n\n7. DATES:\n";
$dates = mysqli_query($con, "SELECT date_of_activation, COUNT(*) as c FROM tblfwfa WHERE date_of_activation IS NOT NULL GROUP BY date_of_activation ORDER BY c DESC LIMIT 10");
while ($r = mysqli_fetch_assoc($dates)) echo "     {$r['date_of_activation']}: {$r['c']}\n";

$dates2 = mysqli_query($con, "SELECT current_date_of_acceptance, COUNT(*) as c FROM tblfwfa WHERE current_date_of_acceptance IS NOT NULL GROUP BY current_date_of_acceptance ORDER BY c DESC LIMIT 10");
while ($r = mysqli_fetch_assoc($dates2)) echo "     {$r['current_date_of_acceptance']}: {$r['c']}\n";

// 8. Table columns check - two-row header
echo "\n\n8. TABLE HEADER COLUMNS (from fw4a_data.php):\n";
$headerCols = [
    'Item No.', 'Locality', 'Barangay', 'District', 'Transport Location',
    'Transport Type', 'Site Locations', 'Transfer/New Locations', 'Remarks',
    'Site Code', 'Nationwide ID', 'Site Type',
    'Date of Activation', 'Current Date of Acceptance', 'Latitude', 'Longitude',
    'Procurement Initiative', 'Installation Type', 'UAT', 'Conforme',
    'Strategy', 'Status', 'Link Type',
    'Replacement Form File', 'Conforme File', 'UAT File', 'Additional UAT',
    'Name (Site Coordinators)', 'Contact Details'
];
echo "   Expected columns: " . count($headerCols) . "\n";
echo "   DB columns (excluding id, source_sheet): " . (29) . "\n";

// 9. Search columns (from fw4a_data.php AJAX)
echo "\n\n9. SEARCH COLUMNS (fw4a_data.php):\n";
$searchCols = ['item_no','locality','barangay','district','transport_location','transport_type','site_locations','transfer_new_locations','site_code','nationwide_id','site_type','date_of_activation','current_date_of_acceptance','latitude','longitude','procurement_initiative','installation_type','uat','conforme','strategy','status','link_type','site_coordinator_name','contact_details','remarks'];
echo "   Defined searchable: " . count($searchCols) . "\n";
echo "   Matches export headers: " . (count($headerCols) === count($searchCols) ? "YES" : "NO") . "\n";

echo "\n=== AUDIT COMPLETE ===\n";