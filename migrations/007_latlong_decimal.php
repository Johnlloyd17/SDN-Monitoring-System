<?php
/**
 * Migration: Restore latitude/longitude to DECIMAL(10,7)
 * Reverses the migration-006 change (decimal → varchar).
 * Safe: verified 0 rows contained lat/lng data at conversion time.
 *
 * Run: php migrations/007_latlong_decimal.php
 *
 * Idempotent: safe to re-run.
 */
include __DIR__ . '/../pages/connection.php';

echo "=== Migration 007: latitude/longitude → DECIMAL(10,7) ===\n\n";

function colType($con, $table, $col) {
    $r = mysqli_query($con, "SHOW COLUMNS FROM `$table` LIKE '" . mysqli_real_escape_string($con, $col) . "'");
    return ($r && mysqli_num_rows($r) > 0) ? mysqli_fetch_assoc($r)['Type'] : '';
}

$latType = colType($con, 'tblfwfa', 'latitude');
$lngType = colType($con, 'tblfwfa', 'longitude');

echo "  latitude  type: $latType\n";
echo "  longitude type: $lngType\n\n";

if (stripos($latType, 'decimal') === false) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa MODIFY COLUMN `latitude` DECIMAL(10,7) DEFAULT NULL");
    echo ($r ? "    OK: latitude → DECIMAL(10,7)\n" : "    ERR: " . mysqli_error($con) . "\n");
} else {
    echo "    latitude already DECIMAL — kept.\n";
}

if (stripos($lngType, 'decimal') === false) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa MODIFY COLUMN `longitude` DECIMAL(10,7) DEFAULT NULL");
    echo ($r ? "    OK: longitude → DECIMAL(10,7)\n" : "    ERR: " . mysqli_error($con) . "\n");
} else {
    echo "    longitude already DECIMAL — kept.\n";
}

echo "\n=== Verification ===\n";
$rows = [];
$r = mysqli_query($con, "SHOW COLUMNS FROM tblfwfa");
while ($c = mysqli_fetch_assoc($r)) {
    if (in_array($c['Field'], ['latitude', 'longitude'])) {
        $rows[$c['Field']] = $c['Type'] . ($c['Null'] === 'YES' ? ' NULL' : ' NOT NULL');
    }
}
foreach ($rows as $k => $v) echo "  $k => $v\n";

// Validate stored values convert cleanly (should be empty table now)
$bad = mysqli_query($con, "SELECT COUNT(*) c FROM tblfwfa WHERE latitude IS NOT NULL AND TRIM(latitude) NOT REGEXP '^-?[0-9]+(\\.[0-9]+)?$' OR longitude IS NOT NULL AND TRIM(longitude) NOT REGEXP '^-?[0-9]+(\\.[0-9]+)?$'");
echo "  non-numeric lat/lng values: " . mysqli_fetch_assoc($bad)['c'] . "\n";
echo "\nMigration 007 complete.\n";
?>