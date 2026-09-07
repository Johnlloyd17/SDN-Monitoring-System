<?php
/**
 * Migration: Letters Monitoring - revise column data types
 *
 * Changes applied:
 *  - for_response: ENUM('Y','N') -> ENUM('Yes','No'), mapping existing Y/N to Yes/No
 *  - fw4a: DROP COLUMN (per user decision; existing data in this column is lost)
 *  - link_incoming: varchar(500) -> TEXT
 *  - link_outgoing: varchar(500) -> TEXT
 *  - post_activity_report: varchar(500) -> TEXT
 *
 * Run from project root:  php migrations/001_letters_monitoring_types.php
 */

require __DIR__ . '/../pages/connection.php';

$table = 'letters_monitoring';

echo "=== Pre-migration data state ===\n";
$r = mysqli_query($con, "SELECT for_response, COUNT(*) c FROM $table GROUP BY for_response");
while ($row = mysqli_fetch_assoc($r)) {
    echo "for_response " . var_export($row['for_response'], true) . " => {$row['c']}\n";
}

echo "\n=== Step 1: map for_response Y/N -> Yes/No ===\n";
mysqli_query($con, "UPDATE $table SET for_response = 'Yes' WHERE for_response = 'Y'");
echo 'Y->Yes affected: ' . mysqli_affected_rows($con) . "\n";
mysqli_query($con, "UPDATE $table SET for_response = 'No' WHERE for_response = 'N'");
echo 'N->No affected: ' . mysqli_affected_rows($con) . "\n";

echo "\n=== Step 2: ALTER TABLE ===\n";
$sql = "ALTER TABLE $table
    DROP COLUMN fw4a,
    MODIFY COLUMN for_response ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
    MODIFY COLUMN link_incoming TEXT NULL DEFAULT NULL,
    MODIFY COLUMN link_outgoing TEXT NULL DEFAULT NULL,
    MODIFY COLUMN post_activity_report TEXT NULL DEFAULT NULL";

if (mysqli_query($con, $sql)) {
    echo "ALTER succeeded\n";
} else {
    echo "ALTER FAILED: " . mysqli_error($con) . "\n";
    exit(1);
}

echo "\n=== Post-migration schema ===\n";
$r = mysqli_query($con, "SHOW FULL COLUMNS FROM $table");
while ($row = mysqli_fetch_assoc($r)) {
    printf("%-32s | %-28s | Null=%-4s | Default=%s\n", $row['Field'], $row['Type'], $row['Null'], var_export($row['Default'], true));
}

echo "\n=== Post-migration data check ===\n";
$r = mysqli_query($con, "SELECT COUNT(*) c FROM $table");
echo 'total rows: ' . mysqli_fetch_assoc($r)['c'] . "\n";
$r = mysqli_query($con, "SELECT for_response, COUNT(*) c FROM $table GROUP BY for_response");
while ($row = mysqli_fetch_assoc($r)) echo "for_response " . var_export($row['for_response'], true) . " => {$row['c']}\n";

echo "\nMigration complete.\n";
