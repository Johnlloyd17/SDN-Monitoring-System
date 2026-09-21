<?php
/**
 * Migration: Add ICS attachment table
 *
 * Creates the `ics_attachments` table for storing scanned copies of
 * Inventory Custodian Slips.
 *
 * Run from project root:  php migrations/009_ics_attachments.php
 */

require __DIR__ . '/../pages/connection.php';

$db = mysqli_query($con, "SELECT DATABASE()");
$dbName = mysqli_fetch_row($db)[0];
echo "Database: $dbName\n";

$sql = "CREATE TABLE IF NOT EXISTS `ics_attachments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ics_no` VARCHAR(50) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `uploaded_by` VARCHAR(255) DEFAULT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ics_no` (`ics_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($con, $sql)) {
    echo "OK: created table `ics_attachments`\n";
} else {
    echo "FAILED (ics_attachments): " . mysqli_error($con) . "\n";
    exit(1);
}

echo "\n=== Post schema ===\n";
$r = mysqli_query($con, "SHOW COLUMNS FROM ics_attachments");
while ($row = mysqli_fetch_assoc($r)) {
    printf("%-24s | %-24s | Null=%s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? 'NO' : 'YES');
}
echo "\nMigration complete.\n";