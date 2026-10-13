<?php
require_once 'pages/connection.php';

echo "=== Phase 2 Database Migration (No Unique Index) ===\n\n";

// 1. Backup already created - verify
echo "1. Verifying backup...\n";
$count = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa_backup_20261013");
echo "   Backup rows: " . mysqli_fetch_assoc($count)['c'] . "\n";

// 2. source_sheet column already added - verify
echo "2. Verifying source_sheet column...\n";
$cols = mysqli_query($con, "SHOW COLUMNS FROM tblfwfa LIKE 'source_sheet'");
if (mysqli_num_rows($cols)) {
    echo "   ✓ Column exists\n";
} else {
    echo "   ✗ Column missing - adding...\n";
    mysqli_query($con, "ALTER TABLE tblfwfa ADD COLUMN source_sheet VARCHAR(100) NULL AFTER remarks");
}

// 3. Verify existing rows have source_sheet
echo "3. Verifying source_sheet values...\n";
$legacy = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa WHERE source_sheet = 'Legacy Import'");
echo "   Legacy Import: " . mysqli_fetch_assoc($legacy)['c'] . " rows\n";
$null = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa WHERE source_sheet IS NULL OR source_sheet = ''");
echo "   NULL/empty: " . mysqli_fetch_assoc($null)['c'] . " rows\n";

// 4. SKIP unique index per user request
echo "4. Skipping unique index (per user: keep legacy data as-is)\n";
echo "   Duplicate prevention will be handled in application layer\n";

// 5. Final verification
echo "5. Final state:\n";
$total = mysqli_query($con, "SELECT COUNT(*) as c FROM tblfwfa");
echo "   Total rows: " . mysqli_fetch_assoc($total)['c'] . "\n";

$struct = mysqli_query($con, "DESCRIBE tblfwfa");
echo "   Columns: " . mysqli_num_rows($struct) . "\n";

echo "\n=== Migration Complete ===\n";