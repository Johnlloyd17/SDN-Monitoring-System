<?php
/**
 * Migration: Finalize tblfwfa schema to match fw4a_data.php column spec
 * Columns A–AC (29 data columns) + internal auto-increment id PK
 *
 * Run: php migrations/006_finalize_fw4a_schema.php
 *
 * Idempotent: safe to re-run. Backup table preserved if it already exists.
 */
include __DIR__ . '/../pages/connection.php';

echo "=== Migration 006: Finalize tblfwfa schema ===\n\n";

function hasColumn($con, $table, $col) {
    $r = mysqli_query($con, "SHOW COLUMNS FROM `$table` LIKE '" . mysqli_real_escape_string($con, $col) . "'");
    return $r && mysqli_num_rows($r) > 0;
}

function hasTable($con, $table) {
    $r = mysqli_query($con, "SHOW TABLES LIKE '$table'");
    return $r && mysqli_num_rows($r) > 0;
}

function step($label, $ok, $err) {
    echo ($ok ? "    OK: " : "    SKIP/ERR: ") . $label . ($ok ? "\n" : " — $err\n");
}

// ── Step 0: Backup (preserve original if already created) ──
echo "[0] Creating backup table tblfwfa_backup ...\n";
if (!hasTable($con, 'tblfwfa_backup')) {
    $r = mysqli_query($con, "CREATE TABLE tblfwfa_backup AS SELECT * FROM tblfwfa");
    step("backup created", $r, mysqli_error($con));
    echo "    (" . mysqli_affected_rows($con) . " rows copied).\n";
} else {
    echo "    Backup already exists — kept as-is.\n";
}

// ── Step 1a: Rename locations → site_locations ──
echo "\n[1] Renaming legacy columns ...\n";
if (hasColumn($con, 'tblfwfa', 'locations') && !hasColumn($con, 'tblfwfa', 'site_locations')) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa CHANGE COLUMN `locations` `site_locations` VARCHAR(255) DEFAULT NULL");
    step("locations → site_locations", $r, mysqli_error($con));
} else {
    echo "    site_locations already present or locations missing.\n";
}

// ── Step 1b: Rename code → site_code ──
if (hasColumn($con, 'tblfwfa', 'code') && !hasColumn($con, 'tblfwfa', 'site_code')) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa CHANGE COLUMN `code` `site_code` VARCHAR(255) DEFAULT NULL");
    step("code → site_code", $r, mysqli_error($con));
} else {
    echo "    site_code already present or code missing.\n";
}

// ── Step 1c: Rename type → site_type ──
if (hasColumn($con, 'tblfwfa', 'type') && !hasColumn($con, 'tblfwfa', 'site_type')) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa CHANGE COLUMN `type` `site_type` VARCHAR(255) DEFAULT NULL");
    step("type → site_type", $r, mysqli_error($con));
} else {
    echo "    site_type already present or type missing.\n";
}

// ── Step 2: Change lat/lng from decimal → varchar (only if still decimal) ──
echo "\n[2] Changing latitude/longitude from decimal(10,7) → VARCHAR(255) ...\n";
$latType = mysqli_fetch_assoc(mysqli_query($con, "SHOW COLUMNS FROM tblfwfa LIKE 'latitude'"))['Type'] ?? '';
if (stripos($latType, 'decimal') !== false) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa MODIFY COLUMN `latitude` VARCHAR(255) DEFAULT NULL");
    step("latitude → varchar", $r, mysqli_error($con));
} else {
    echo "    latitude already type: $latType\n";
}
$lngType = mysqli_fetch_assoc(mysqli_query($con, "SHOW COLUMNS FROM tblfwfa LIKE 'longitude'"))['Type'] ?? '';
if (stripos($lngType, 'decimal') !== false) {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa MODIFY COLUMN `longitude` VARCHAR(255) DEFAULT NULL");
    step("longitude → varchar", $r, mysqli_error($con));
} else {
    echo "    longitude already type: $lngType\n";
}

// ── Step 3: Change status from varchar → ENUM (only if not already the 9-option ENUM) ──
echo "\n[3] Changing status from varchar(50) → ENUM (9 options, nullable) ...\n";
$statusType = mysqli_fetch_assoc(mysqli_query($con, "SHOW COLUMNS FROM tblfwfa LIKE 'status'"))['Type'] ?? '';
$targetStatusEnum = "enum('Active','Inactive','Ongoing','Assist','Terminated','Deactivated','Ongoing Acceptance','For Installation','For Transfer')";
if (strtolower(trim($statusType)) === $targetStatusEnum) {
    echo "    status already the 9-option ENUM.\n";
} else {
    $r = mysqli_query($con, "ALTER TABLE tblfwfa MODIFY COLUMN `status` ENUM('Active','Inactive','Ongoing','Assist','Terminated','Deactivated','Ongoing Acceptance','For Installation','For Transfer') DEFAULT NULL");
    step("status → ENUM (was $statusType)", $r, mysqli_error($con));
}

// ── Step 4: Add all 13 new columns (each guarded) ──
echo "\n[4] Adding 13 new columns ...\n";
$adds = [
    "ADD COLUMN `item_no` INT DEFAULT NULL AFTER `id`",
    "ADD COLUMN `transfer_new_locations` VARCHAR(255) DEFAULT NULL AFTER `site_locations`",
    "ADD COLUMN `procurement_initiative` ENUM('Centrally Procured','Regional Procured') DEFAULT NULL AFTER `longitude`",
    "ADD COLUMN `installation_type` ENUM('Region Initiated','Manage Service') DEFAULT NULL AFTER `procurement_initiative`",
    "ADD COLUMN `uat` TINYINT(1) DEFAULT NULL AFTER `installation_type`",
    "ADD COLUMN `conforme` TINYINT(1) DEFAULT NULL AFTER `uat`",
    "ADD COLUMN `link_type` VARCHAR(255) DEFAULT NULL AFTER `status`",
    "ADD COLUMN `replacement_form_file` TEXT DEFAULT NULL AFTER `link_type`",
    "ADD COLUMN `conforme_file` TEXT DEFAULT NULL AFTER `replacement_form_file`",
    "ADD COLUMN `uat_file` TEXT DEFAULT NULL AFTER `conforme_file`",
    "ADD COLUMN `additional_uat` TEXT DEFAULT NULL AFTER `uat_file`",
    "ADD COLUMN `site_coordinator_name` VARCHAR(255) DEFAULT NULL AFTER `additional_uat`",
    "ADD COLUMN `contact_details` VARCHAR(255) DEFAULT NULL AFTER `site_coordinator_name`",
];
foreach ($adds as $add) {
    if (preg_match('/ADD COLUMN `([^`]+)`/', $add, $m)) {
        $col = $m[1];
        if (hasColumn($con, 'tblfwfa', $col)) {
            echo "    SKIP: $col already exists\n";
            continue;
        }
        $r = mysqli_query($con, "ALTER TABLE tblfwfa $add");
        step($col, $r, mysqli_error($con));
    }
}

// ── Print current schema ──
echo "\n=== Current schema (DESCRIBE tblfwfa) ===\n";
$r5 = mysqli_query($con, "DESCRIBE tblfwfa");
$colNum = 0;
while ($row = mysqli_fetch_assoc($r5)) {
    $colNum++;
    printf("    %2d. %-30s %s %s %s\n", $colNum, $row['Field'], $row['Type'], $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL', $row['Default'] !== null ? 'DEFAULT ' . $row['Default'] : ($row['Extra'] ?: ''));
}
echo "\nTotal columns: $colNum\n";
echo "\n=== Migration 006 complete ===\n";
?>