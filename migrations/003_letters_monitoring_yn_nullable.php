<?php
/**
 * Migration: Letters Monitoring - FW4A nullable + For Response? ENUM('Y','N')
 *
 * Changes:
 *   1. fw4a becomes nullable with NULL default (blank "none" allowed).
 *   2. for_response ENUM changes from ('Yes','No') to ('Y','N') to match the
 *      source spreadsheet format. Kept NOT NULL with default 'Y'.
 *
 * Table is empty (all rows were batch-deleted at 12:20 + user is re-importing),
 * so the ENUM/null changes are non-destructive - no existing 'Yes'/'No' values
 * need converting.
 *
 * Run from project root:  php migrations/003_letters_monitoring_yn_nullable.php
 */

require __DIR__ . '/../pages/connection.php';

$table = 'letters_monitoring';

echo "=== Pre-state ===\n";
$res = mysqli_query($con, "SELECT COUNT(*) c FROM $table");
echo 'rows: ' . mysqli_fetch_assoc($res)['c'] . "\n";

$sqls = array(
    "ALTER TABLE $table MODIFY fw4a ENUM('Request','Provision') NULL DEFAULT NULL",
    "ALTER TABLE $table MODIFY for_response ENUM('Y','N') NOT NULL DEFAULT 'Y'",
);

foreach ($sqls as $sql) {
    if (mysqli_query($con, $sql)) {
        echo "OK: $sql\n";
    } else {
        echo "ALTER FAILED: " . mysqli_error($con) . "\n";
        exit(1);
    }
}

echo "\n=== Post schema ===\n";
$r = mysqli_query($con, "SHOW FULL COLUMNS FROM $table");
while ($row = mysqli_fetch_assoc($r)) {
    printf("%-32s | %-30s | Null=%-4s | Default=%s\n", $row['Field'], $row['Type'], $row['Null'], var_export($row['Default'], true));
}
echo "\nMigration complete.\n";
