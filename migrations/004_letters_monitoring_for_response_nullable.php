<?php
/**
 * Migration: Letters Monitoring - For Response? nullable
 *
 * Makes for_response nullable (NULL DEFAULT NULL) so that rows with a BLANK
 * "For Response?" value can be stored as NULL instead of being rejected.
 * Valid values remain 'Y'/'N'.
 *
 * Existing rows (21 imported, all Y/N - none blank) are unaffected by making
 * the column nullable.
 *
 * Run from project root:  php migrations/004_letters_monitoring_for_response_nullable.php
 */

require __DIR__ . '/../pages/connection.php';

$table = 'letters_monitoring';

echo "=== Pre-state ===\n";
$res = mysqli_query($con, "SELECT COUNT(*) c FROM $table");
echo 'rows: ' . mysqli_fetch_assoc($res)['c'] . "\n";
$r = mysqli_query($con, "SELECT for_response, COUNT(*) n FROM $table GROUP BY for_response");
echo 'for_response counts: ';
while ($x = mysqli_fetch_row($r)) echo "'".$x[0]."'=>".$x[1]." ";
echo "\n";

$sql = "ALTER TABLE $table MODIFY for_response ENUM('Y','N') NULL DEFAULT NULL";
if (mysqli_query($con, $sql)) {
    echo "OK: $sql\n";
} else {
    echo "ALTER FAILED: " . mysqli_error($con) . "\n";
    exit(1);
}

echo "\n=== Post schema ===\n";
$r = mysqli_query($con, "SHOW FULL COLUMNS FROM $table");
while ($row = mysqli_fetch_assoc($r)) {
    if ($row['Field'] === 'for_response' || $row['Field'] === 'fw4a') {
        printf("%-32s | %-30s | Null=%-4s | Default=%s\n", $row['Field'], $row['Type'], $row['Null'], var_export($row['Default'], true));
    }
}
echo "\nMigration complete.\n";
