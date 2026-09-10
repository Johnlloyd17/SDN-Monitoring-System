<?php
include 'pages/connection.php';

echo "=== TOTAL ROWS ===\n";
echo mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM tblfwfa"))['c'] . "\n\n";

echo "=== PER-COLUMN POPULATION (non-null + non-empty) ===\n";
$cols = ['item_no','locality','barangay','district','transport_location','transport_type','site_locations','transfer_new_locations','remarks','site_code','nationwide_id','site_type','date_of_activation','current_date_of_acceptance','latitude','longitude','procurement_initiative','installation_type','uat','conforme','strategy','status','link_type','replacement_form_file','conforme_file','uat_file','additional_uat','site_coordinator_name','contact_details'];
foreach ($cols as $c) {
    $r = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c, SUM(CASE WHEN $c IS NOT NULL AND $c != '' THEN 1 ELSE 0 END) filled FROM tblfwfa"));
    printf("  %-32s total=%s filled=%s\n", $c, $r['c'], $r['filled']);
}

echo "\n=== SAMPLE ROW (id 7948) ALL 29 ===\n";
$r = mysqli_query($con, "SELECT * FROM tblfwfa ORDER BY id LIMIT 1");
$row = mysqli_fetch_assoc($r);
foreach ($row as $k => $v) printf("  %-32s = %s\n", $k, var_export($v, true));

echo "\n=== SAMPLE ROW (id highest) ALL 29 ===\n";
$r = mysqli_query($con, "SELECT * FROM tblfwfa ORDER BY id DESC LIMIT 1");
$row = mysqli_fetch_assoc($r);
foreach ($row as $k => $v) printf("  %-32s = %s\n", $k, var_export($v, true));

echo "\n=== DISTINCT site_code values ===\n";
$r = mysqli_query($con, "SELECT site_code, COUNT(*) c FROM tblfwfa GROUP BY site_code");
while ($x = mysqli_fetch_assoc($r)) echo "  " . var_export($x['site_code'], true) . " => " . $x['c'] . "\n";

echo "\n=== DISTINCT status values ===\n";
$r = mysqli_query($con, "SELECT status, COUNT(*) c FROM tblfwfa GROUP BY status");
while ($x = mysqli_fetch_assoc($r)) echo "  " . var_export($x['status'], true) . " => " . $x['c'] . "\n";

echo "\n=== DISTINCT latitude NON-NULL count & lat/lng types ===\n";
$r = mysqli_query($con, "SELECT latitude, longitude FROM tblfwfa WHERE latitude IS NOT NULL AND latitude != '' OR longitude IS NOT NULL AND longitude != '' LIMIT 5");
while ($x = mysqli_fetch_assoc($r)) echo "  lat=" . var_export($x['latitude'], true) . " lng=" . var_export($x['longitude'], true) . "\n";
?>