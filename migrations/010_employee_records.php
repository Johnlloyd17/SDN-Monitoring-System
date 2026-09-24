<?php
/**
 * Migration: Employee Records + Pass Slip employee linking
 *
 * 1. Creates the `employees` table (DICT worker directory).
 * 2. Adds 6 nullable FK columns to `pass_slip` so Requested/Inspected/
 *    Approved By (Pull-Out and Return) can reference an employee record.
 *
 * Deleting an employee never removes a pass slip: the FK is ON DELETE
 * SET NULL, so the typed name text is always preserved in the slip.
 *
 * Run from project root:  php migrations/010_employee_records.php
 */

require __DIR__ . '/../pages/connection.php';

$db = mysqli_query($con, "SELECT DATABASE()");
$dbName = mysqli_fetch_row($db)[0];
echo "Database: $dbName\n";

$sql = "CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `full_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($con, $sql)) {
    echo "OK: created table `employees`\n";
} else {
    echo "FAILED (employees): " . mysqli_error($con) . "\n";
    exit(1);
}

// Columns + FK constraints to add to pass_slip
$linkCols = array(
    'requested_by_out_emp_id',
    'inspected_by_out_emp_id',
    'approved_by_out_emp_id',
    'requested_by_return_emp_id',
    'inspected_by_return_emp_id',
    'approved_by_return_emp_id',
);

foreach ($linkCols as $col) {
    $chk = mysqli_query($con, "SELECT 1 FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'pass_slip' AND column_name = '$col'");
    if ($chk && mysqli_num_rows($chk) > 0) {
        echo "SKIP (pass_slip.$col already exists)\n";
        continue;
    }

    $alt = "ALTER TABLE `pass_slip` ADD COLUMN `$col` INT(11) DEFAULT NULL";
    if (mysqli_query($con, $alt)) {
        echo "OK: added pass_slip.$col\n";
    } else {
        echo "FAILED (pass_slip.$col): " . mysqli_error($con) . "\n";
        exit(1);
    }
}

foreach ($linkCols as $col) {
    $fk = "fk_pass_slip_{$col}";
    $chk = mysqli_query($con, "SELECT 1 FROM information_schema.table_constraints
        WHERE table_schema = DATABASE() AND table_name = 'pass_slip' AND constraint_name = '$fk'");
    if ($chk && mysqli_num_rows($chk) > 0) {
        echo "SKIP (constraint $fk already exists)\n";
        continue;
    }

    $alt = "ALTER TABLE `pass_slip`
            ADD CONSTRAINT `$fk` FOREIGN KEY (`$col`) REFERENCES `employees`(`id`)
            ON DELETE SET NULL ON UPDATE CASCADE";
    if (mysqli_query($con, $alt)) {
        echo "OK: added constraint $fk\n";
    } else {
        echo "FAILED ($fk): " . mysqli_error($con) . "\n";
        exit(1);
    }
}

echo "\n=== Post schema ===\n";

echo "-- employees --\n";
$r = mysqli_query($con, "SHOW COLUMNS FROM employees");
while ($row = mysqli_fetch_assoc($r)) {
    printf("%-24s | %-24s | Null=%s\n", $row['Field'], $row['Type'], $row['Null'] === 'NO' ? 'NO' : 'YES');
}

echo "-- pass_slip (link columns) --\n";
foreach ($linkCols as $col) {
    $r = mysqli_query($con, "SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'pass_slip' AND column_name = '$col'");
    $row = mysqli_fetch_assoc($r);
    printf("%-32s | %-24s | Null=%s\n", $col, $row['COLUMN_TYPE'], strtoupper($row['IS_NULLABLE']));
}

echo "\nMigration complete.\n";