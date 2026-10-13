<?php
require_once 'pages/connection.php';

echo "=== Phase 2 Database Migration ===\n\n";

// 1. Create backup
echo "1. Creating backup table...\n";
$sql = "CREATE TABLE tblfwfa_backup_20261013 AS SELECT * FROM tblfwfa";
if (mysqli_query($con, $sql)) {
    $count = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa_backup_20261013");
    echo "   ✓ Backup created: " . mysqli_fetch_assoc($count)['c'] . " rows\n";
} else {
    echo "   ✗ Backup failed: " . mysqli_error($con) . "\n";
    exit(1);
}

// 2. Add source_sheet column
echo "2. Adding source_sheet column...\n";
$sql = "ALTER TABLE tblfwfa ADD COLUMN source_sheet VARCHAR(100) NULL AFTER remarks";
if (mysqli_query($con, $sql)) {
    echo "   ✓ Column added\n";
} else {
    echo "   ✗ Failed: " . mysqli_error($con) . "\n";
    exit(1);
}

// 3. Update existing rows with default
echo "3. Setting default source_sheet for existing rows...\n";
$sql = "UPDATE tblfwfa SET source_sheet = 'Legacy Import' WHERE source_sheet IS NULL OR source_sheet = ''";
if (mysqli_query($con, $sql)) {
    echo "   ✓ " . mysqli_affected_rows($con) . " rows updated\n";
} else {
    echo "   ✗ Failed: " . mysqli_error($con) . "\n";
    exit(1);
}

// 4. Add composite unique index
echo "4. Adding composite unique index (site_code, source_sheet)...\n";
// First check for conflicts
$conflicts = mysqli_query($con, "
    SELECT site_code, source_sheet, COUNT(*) as cnt 
    FROM tblfwfa 
    WHERE site_code IS NOT NULL AND site_code != ''
    GROUP BY site_code, source_sheet 
    HAVING COUNT(*) > 1
");
if (mysqli_num_rows($conflicts) > 0) {
    echo "   ⚠ Found duplicate (site_code, source_sheet) pairs:\n";
    while ($row = mysqli_fetch_assoc($conflicts)) {
        echo "      - {$row['site_code']} / {$row['source_sheet']} = {$row['cnt']} rows\n";
    }
    echo "   Cannot create unique index. Resolve duplicates first.\n";
    exit(1);
} else {
    echo "   No conflicts found\n";
}

$sql = "ALTER TABLE tblfwfa ADD UNIQUE KEY uk_site_sheet (site_code, source_sheet)";
if (mysqli_query($con, $sql)) {
    echo "   ✓ Unique index created\n";
} else {
    echo "   ✗ Failed: " . mysqli_error($con) . "\n";
    exit(1);
}

// 5. Verify
echo "5. Verification...\n";
$cols = mysqli_query($con, "SHOW COLUMNS FROM tblfwfa LIKE 'source_sheet'");
echo "   Column: " . (mysqli_num_rows($cols) ? "EXISTS" : "MISSING") . "\n";

$idx = mysqli_query($con, "SHOW INDEX FROM tblfwfa WHERE Key_name = 'uk_site_sheet'");
echo "   Index: " . (mysqli_num_rows($idx) ? "EXISTS" : "MISSING") . "\n";

$count = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa");
echo "   Total rows: " . mysqli_fetch_assoc($count)['c'] . "\n";

$legacy = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa WHERE source_sheet = 'Legacy Import'");
echo "   Legacy rows: " . mysqli_fetch_assoc($legacy)['c'] . "\n";

echo "\n=== Migration Complete ===\n";