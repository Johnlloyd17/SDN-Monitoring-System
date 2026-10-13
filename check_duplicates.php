<?php
require_once 'pages/connection.php';

echo "=== Checking Duplicate Details ===\n\n";

$duplicates = mysqli_query($con, "
    SELECT site_code, source_sheet, COUNT(*) as cnt, GROUP_CONCAT(id) as ids
    FROM tblfwfa 
    WHERE site_code IS NOT NULL AND site_code != ''
    GROUP BY site_code, source_sheet 
    HAVING COUNT(*) > 1
    ORDER BY cnt DESC
");

echo "Duplicate groups:\n";
while ($row = mysqli_fetch_assoc($duplicates)) {
    echo "\n{$row['site_code']} (x{$row['cnt']}): IDs {$row['ids']}\n";
    
    // Check if rows are identical
    $ids = explode(',', $row['ids']);
    $firstId = $ids[0];
    $detail = mysqli_query($con, "SELECT * FROM tblfwfa WHERE id IN ({$row['ids']})");
    $rows = [];
    while ($r = mysqli_fetch_assoc($detail)) {
        $rows[] = $r;
    }
    
    // Compare all columns except id
    $cols = array_keys($rows[0]);
    $cols = array_diff($cols, ['id']);
    $allSame = true;
    for ($i = 1; $i < count($rows); $i++) {
        foreach ($cols as $col) {
            if ($rows[0][$col] !== $rows[$i][$col]) {
                $allSame = false;
                echo "   DIFF in $col: '{$rows[0][$col]}' vs '{$rows[$i][$col]}'\n";
            }
        }
    }
    if ($allSame) {
        echo "   → All columns identical (exact duplicates)\n";
    }
}