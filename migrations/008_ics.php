<?php
/**
 * Migration: Add Inventory Custodian Slip (ICS) tables
 *
 * Creates the `ics` (header) and `ics_items` (itemized detail) tables.
 *
 * Run from project root:  php migrations/008_ics.php
 */

require __DIR__ . '/../pages/connection.php';

$db = mysqli_query($con, "SELECT DATABASE()");
$dbName = mysqli_fetch_row($db)[0];
echo "Database: $dbName\n";

$sql = "CREATE TABLE IF NOT EXISTS `ics` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ics_no` VARCHAR(50) NOT NULL,
  `date_issued` DATE NOT NULL,
  `received_by` VARCHAR(255) DEFAULT NULL,
  `received_by_position` VARCHAR(255) DEFAULT NULL,
  `received_from` VARCHAR(255) DEFAULT NULL,
  `received_from_position` VARCHAR(255) DEFAULT NULL,
  `date_received` DATE DEFAULT NULL,
  `total` DECIMAL(12,2) DEFAULT 0.00,
  `created_by` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ics_no` (`ics_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($con, $sql)) {
    echo "OK: created table `ics`\n";
} else {
    echo "FAILED (ics): " . mysqli_error($con) . "\n";
    exit(1);
}

$sql2 = "CREATE TABLE IF NOT EXISTS `ics_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ics_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL,
  `unit` VARCHAR(50) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `unit_cost` DECIMAL(12,2) DEFAULT 0.00,
  `date_acquired` DATE DEFAULT NULL,
  `inventory_item_no` VARCHAR(100) DEFAULT NULL,
  `estimated_useful_life` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ics_id` (`ics_id`),
  FOREIGN KEY (`ics_id`) REFERENCES `ics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($con, $sql2)) {
    echo "OK: created table `ics_items`\n";
} else {
    echo "FAILED (ics_items): " . mysqli_error($con) . "\n";
    exit(1);
}

echo "\n=== Post schema ===\n";
foreach (['ics', 'ics_items'] as $t) {
    echo "-- $t --\n";
    $r = mysqli_query($con, "SHOW COLUMNS FROM $t");
    while ($row = mysqli_fetch_assoc($r)) {
        printf("%-24s | %-24s | Null=%s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? 'NO' : 'YES');
    }
}
echo "\nMigration complete.\n";