<?php
/**
 * Migration: Letters Monitoring - restore FW4A column (Column E)
 *
 * Re-adds the previously dropped fw4a column so the revised schema
 * includes it: ENUM('Request','Provision') NOT NULL DEFAULT 'Request'.
 * Table is currently empty (data deleted at 12:20 + user re-importing),
 * so this ALTER is non-destructive.
 *
 * Run from project root:  php migrations/002_letters_monitoring_restore_fw4a.php
 */

require __DIR__ . '/../pages/connection.php';

$table = 'letters_monitoring';

echo "=== Pre-state ===\n";
$res = mysqli_query($con, "SELECT COUNT(*) c FROM $table");
echo 'rows: ' . mysqli_fetch_assoc($res)['c'] . "\n";
$hasFw4a = mysqli_num_rows(mysqli_query($con, "SHOW COLUMNS FROM $table LIKE 'fw4a'")) > 0;
echo 'fw4a column exists: ' . ($hasFw4a ? 'yes' : 'no') . "\n";

if (!$hasFw4a) {
    $sql = "ALTER TABLE $table ADD COLUMN fw4a ENUM('Request','Provision') NOT NULL DEFAULT 'Request' AFTER subject";
    if (mysqli_query($con, $sql)) {
        echo "fw4a column added\n";
    } else {
        echo "ALTER FAILED: " . mysqli_error($con) . "\n";
        exit(1);
    }
} else {
    echo "fw4a already present, skipping ADD\n";
}

echo "\n=== Post schema ===\n";
$r = mysqli_query($con, "SHOW FULL COLUMNS FROM $table");
while ($row = mysqli_fetch_assoc($r)) {
    printf("%-32s | %-30s | Null=%-4s | Default=%s\n", $row['Field'], $row['Type'], $row['Null'], var_export($row['Default'], true));
}
echo "\nMigration complete.\n";
