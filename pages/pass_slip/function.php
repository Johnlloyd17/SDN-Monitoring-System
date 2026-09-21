<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "../connection.php";

// ============================================================
// ACTION: Get Pass Slip History (AJAX from Property Records)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'history') {
    header('Content-Type: application/json');
    
    $tableCheck = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0");
    if (!$tableCheck) {
        echo json_encode([]);
        exit;
    }
    
    $inventory_id = mysqli_real_escape_string($con, $_GET['inventory_id']);
    
    $query = "SELECT pass_slip_no, pullout_date, return_date, requested_by_out, qty, unit, status 
              FROM pass_slip 
              WHERE inventory_id = '$inventory_id' 
              ORDER BY pullout_date DESC";
    $result = mysqli_query($con, $query);
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode($data);
    exit;
}

// ============================================================
// ACTION: Get Item Details by Pass Slip No (AJAX)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'item_details') {
    header('Content-Type: application/json');
    
    $pass_slip_no = mysqli_real_escape_string($con, $_GET['pass_slip_no']);
    $query = "SELECT ps.item_description, ps.qty, ps.unit, ps.serial_no, ps.pullout_date, ps.purpose, ps.requested_by_out, ps.inspected_by_out, ps.approved_by_out, ps.pass_slip_no, ps.status,
              i.property AS property_no, i.serial AS serial_no_inv, i.ics AS ics_no
              FROM pass_slip ps 
              LEFT JOIN inventory i ON ps.inventory_id = i.id 
              WHERE ps.pass_slip_no = '$pass_slip_no' ORDER BY ps.id ASC";
    $result = mysqli_query($con, $query);
    
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode($data);
    exit;
}

// ============================================================
// ACTION: Get Distinct Inspector Names (AJAX for dropdown)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'inspector_list') {
    header('Content-Type: application/json');
    $query = "SELECT DISTINCT inspected_by_out AS name FROM pass_slip WHERE inspected_by_out IS NOT NULL AND inspected_by_out != '' ORDER BY inspected_by_out ASC";
    $result = mysqli_query($con, $query);
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row['name'];
    }
    echo json_encode($data);
    exit;
}

// ============================================================
// ACTION: Create New Pass Slip (supports multiple items)
// ============================================================
if (isset($_POST['create_pass_slip'])) {
    $purpose = mysqli_real_escape_string($con, $_POST['purpose']);
    $condition_out = mysqli_real_escape_string($con, $_POST['condition_out']);
    $requested_by_out = mysqli_real_escape_string($con, $_POST['requested_by_out']);
    $inspected_by_out = mysqli_real_escape_string($con, $_POST['inspected_by_out']);
    $approved_by_out = mysqli_real_escape_string($con, $_POST['approved_by_out']);
    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);

    $inventory_ids = $_POST['inventory_id'] ?? [];
    $descriptions = $_POST['item_description'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];
    $serial_nos = $_POST['serial_no'] ?? [];
    $pullout_dates = $_POST['pullout_date'] ?? [];
    $return_dates = $_POST['return_date'] ?? [];

    if (empty($inventory_ids) || count($inventory_ids) == 0) {
        echo "<script>alert('Please add at least one item.'); window.history.back();</script>";
        exit;
    }

    foreach ($inventory_ids as $index => $inv_id) {
        if (empty($inv_id)) {
            echo "<script>alert('Please select an item for row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
    }

    if (!empty($_POST['pass_slip_no'])) {
        $pass_slip_no = mysqli_real_escape_string($con, $_POST['pass_slip_no']);
    } else {
        $year = date('Y');
        $getLast = mysqli_query($con, "SELECT pass_slip_no FROM pass_slip WHERE pass_slip_no LIKE 'PS-$year-%' ORDER BY id DESC LIMIT 1");
        if ($getLast && mysqli_num_rows($getLast) > 0) {
            $lastRow = mysqli_fetch_assoc($getLast);
            $lastNum = intval(substr($lastRow['pass_slip_no'], -4)) + 1;
            $pass_slip_no = "PS-$year-" . str_pad($lastNum, 4, '0', STR_PAD_LEFT);
        } else {
            $pass_slip_no = "PS-$year-0001";
        }
    }

    $insertedCount = 0;
    $itemSummaries = array();

    foreach ($inventory_ids as $index => $inv_id) {
        $inv_id = mysqli_real_escape_string($con, $inv_id);
        $item_desc = mysqli_real_escape_string($con, $descriptions[$index] ?? '');
        $qty = intval($qtys[$index]);
        $unit = mysqli_real_escape_string($con, $units[$index] ?? '');
        $serial_no = mysqli_real_escape_string($con, $serial_nos[$index] ?? '');
        $item_pullout_date = mysqli_real_escape_string($con, $pullout_dates[$index] ?? date('Y-m-d'));
        $item_return_date = !empty($return_dates[$index]) ? "'" . mysqli_real_escape_string($con, $return_dates[$index]) . "'" : "NULL";

        if ($qty <= 0) {
            echo "<script>alert('Invalid quantity for row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }

        $checkQuery = "SELECT quantity, status, item_type FROM inventory WHERE id = '$inv_id'";
        $checkResult = mysqli_query($con, $checkQuery);
        $item = mysqli_fetch_assoc($checkResult);

        if (!$item) {
            echo "<script>alert('Item not found for row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }

        if ($item['item_type'] === 'consumable') {
            echo "<script>alert('Cannot create pass slip for consumable item: $item_desc. Only equipment items are allowed.'); window.history.back();</script>";
            exit;
        }

        if (!in_array($item['status'] ?? '', ['Available', 'For Deployment'])) {
            echo "<script>alert('Cannot create pass slip for item: $item_desc. Status is \"" . ($item['status'] ?? 'Unknown') . "\" — only Available or For Deployment items can be borrowed.'); window.history.back();</script>";
            exit;
        }

        if ($item['quantity'] < $qty) {
            echo "<script>alert('Insufficient quantity for: $item_desc. Available: " . ($item['quantity'] ?? 0) . "); window.history.back();</script>";
            exit;
        }

        $insertQuery = "INSERT INTO pass_slip (pass_slip_no, inventory_id, item_description, qty, unit, serial_no,
                        pullout_date, return_date, requested_by_out, inspected_by_out, approved_by_out,
                        purpose, condition_out, remarks, status, created_by)
                        VALUES ('$pass_slip_no', '$inv_id', '$item_desc', '$qty', '$unit', '$serial_no',
                        '$item_pullout_date', $item_return_date, '$requested_by_out', '$inspected_by_out', '$approved_by_out',
                        '$purpose', '$condition_out', '$remarks', 'borrowed', '" . ($_SESSION['username'] ?? 'admin') . "')";

        if (mysqli_query($con, $insertQuery)) {
            $newQty = $item['quantity'] - $qty;
            mysqli_query($con, "UPDATE inventory SET quantity = $newQty WHERE id = '$inv_id'");
            $insertedCount++;
            $itemSummaries[] = "$item_desc ($qty $unit)";
        } else {
            echo "<script>alert('Error creating pass slip for: $item_desc - " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
    }

    if ($insertedCount > 0) {
        $summary = implode(', ', $itemSummaries);
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Created Pass Slip: $pass_slip_no - $summary')");

        echo "<script>alert('Pass Slip $pass_slip_no created successfully with $insertedCount item(s)!'); window.location.href = 'pass_slip.php';</script>";
    } else {
        echo "<script>alert('Error: No items were added.'); window.history.back();</script>";
    }
    exit;
}

// ============================================================
// ACTION: Create Inventory Custodian Slip (ICS) with multiple items
// ============================================================
if (isset($_POST['create_ics'])) {
    $date_issued = mysqli_real_escape_string($con, $_POST['date_issued']);
    $received_by = mysqli_real_escape_string($con, $_POST['received_by'] ?? '');
    $received_by_position = mysqli_real_escape_string($con, $_POST['received_by_position'] ?? '');
    $received_from = mysqli_real_escape_string($con, $_POST['received_from'] ?? '');
    $received_from_position = mysqli_real_escape_string($con, $_POST['received_from_position'] ?? '');
    $date_received = !empty($_POST['date_received']) ? "'" . mysqli_real_escape_string($con, $_POST['date_received']) . "'" : "NULL";

    $qtys = $_POST['qty'] ?? [];
    $units = $_POST['unit'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $costs = $_POST['unit_cost'] ?? [];
    $date_acquireds = $_POST['date_acquired'] ?? [];
    $inv_item_nos = $_POST['inventory_item_no'] ?? [];
    $useful_lives = $_POST['estimated_useful_life'] ?? [];

    if (empty($date_issued)) {
        echo "<script>alert('Date Issued is required.'); window.history.back();</script>";
        exit;
    }

    if (empty($qtys) || count($qtys) == 0) {
        echo "<script>alert('Please add at least one item.'); window.history.back();</script>";
        exit;
    }

    // Auto-generate ICS number if left empty (e.g., 13202409-099-1)
    if (!empty($_POST['ics_no'])) {
        $ics_no = mysqli_real_escape_string($con, $_POST['ics_no']);
    } else {
        $prefix = '13' . date('Y') . date('m');
        $getLast = mysqli_query($con, "SELECT ics_no FROM ics WHERE ics_no LIKE '$prefix-%' ORDER BY id DESC LIMIT 1");
        if ($getLast && mysqli_num_rows($getLast) > 0) {
            $lastRow = mysqli_fetch_assoc($getLast);
            $parts = explode('-', $lastRow['ics_no']);
            $lastSeq = intval($parts[count($parts) - 2] ?? 0) + 1;
            $ics_no = $prefix . '-' . str_pad($lastSeq, 3, '0', STR_PAD_LEFT) . '-1';
        } else {
            $ics_no = $prefix . '-001-1';
        }
    }

    // Validate items and compute total
    $total = 0.00;
    foreach ($qtys as $index => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            echo "<script>alert('Invalid quantity for row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
        if (empty($descriptions[$index])) {
            echo "<script>alert('Please enter a description for row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }
        $cost = floatval($costs[$index] ?? 0);
        $total += $qty * $cost;
    }
    $total = number_format($total, 2, '.', '');

    // Duplicate check
    $dupCheck = mysqli_query($con, "SELECT id FROM ics WHERE ics_no = '$ics_no'");
    if ($dupCheck && mysqli_num_rows($dupCheck) > 0) {
        echo "<script>alert('ICS No. $ics_no already exists. Please use a different number.'); window.history.back();</script>";
        exit;
    }

    $insertHeader = "INSERT INTO ics (ics_no, date_issued, received_by, received_by_position,
                      received_from, received_from_position, date_received, total, created_by)
                      VALUES ('$ics_no', '$date_issued', '$received_by', '$received_by_position',
                      '$received_from', '$received_from_position', $date_received, '$total',
                      '" . ($_SESSION['username'] ?? 'admin') . "')";

    if (!mysqli_query($con, $insertHeader)) {
        echo "<script>alert('Error saving ICS: " . mysqli_error($con) . "'); window.history.back();</script>";
        exit;
    }

    $ics_id = mysqli_insert_id($con);
    $insertedCount = 0;
    $itemSummaries = array();

    foreach ($qtys as $index => $qty) {
        $qty = intval($qty);
        $unit = mysqli_real_escape_string($con, $units[$index] ?? '');
        $description = mysqli_real_escape_string($con, $descriptions[$index]);
        $cost = number_format(floatval($costs[$index] ?? 0), 2, '.', '');
        $date_acquired = !empty($date_acquireds[$index]) ? "'" . mysqli_real_escape_string($con, $date_acquireds[$index]) . "'" : "NULL";
        $inv_item_no = !empty($inv_item_nos[$index]) ? mysqli_real_escape_string($con, $inv_item_nos[$index]) : $ics_no;
        $useful_life = mysqli_real_escape_string($con, $useful_lives[$index] ?? '');

        $insertItem = "INSERT INTO ics_items (ics_id, qty, unit, description, unit_cost, date_acquired,
                       inventory_item_no, estimated_useful_life)
                       VALUES ($ics_id, $qty, '$unit', '$description', '$cost', $date_acquired,
                       '$inv_item_no', '$useful_life')";

        if (mysqli_query($con, $insertItem)) {
            $insertedCount++;
            $itemSummaries[] = "$description ($qty $unit)";
        } else {
            echo "<script>alert('Error saving ICS item for row " . ($index + 1) . ": " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
    }

    if ($insertedCount > 0) {
        $summary = implode(', ', $itemSummaries);
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Created Inventory Custodian Slip: $ics_no - Total: $total')");

        $_SESSION['added'] = 1;
        $_SESSION['new_ics'] = $ics_id;
        header("Location: pass_slip.php");
        exit;
    } else {
        echo "<script>alert('Error: No items were added.'); window.history.back();</script>";
        exit;
    }
}

// ============================================================
// ACTION: Delete ICS Records (Batch, by ids from datatable)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $icsUploadDir = dirname(__DIR__) . '/../../uploads/ics/';
    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $icsRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$icsRow) continue;
        $icsNo = $icsRow['ics_no'];

        // ICS attachment rows + their files
        $attQuery = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNo'");
        if ($attQuery) {
            while ($att = mysqli_fetch_assoc($attQuery)) {
                $filePath = $icsUploadDir . $att['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNo'");

        // Cascade items + the record itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");

        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS records: " . mysqli_real_escape_string($con, $icsNo) . "')");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete ICS Records (Batch, from ICS Records table)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $deleted = 0;
    $uploadDir = __DIR__ . '/../../uploads/ics/';
    foreach ($ids as $rawId) {
        $icsNo = mysqli_real_escape_string($con, $rawId);

        // Delete attachment files + rows for this ICS
        $attRows = @mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNo'");
        if ($attRows) {
            while ($att = mysqli_fetch_assoc($attRows)) {
                $filePath = $uploadDir . $att['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNo'");

        // Delete item rows, then the ICS record itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_no = '$icsNo'");
        mysqli_query($con, "DELETE FROM ics WHERE ics_no = '$icsNo'");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (Batch, from records datatable)
// Mirrors Pass Slip records batch delete (chk_delete[]).
// ICS create does NOT decrement inventory, so no quantity restore.
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $icsIds = isset($_POST['ids']) ? $_POST['ids'] : [];
    $icsIds = array_filter(array_map('intval', $icsIds));
    if (empty($icsIds)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $icsTableCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$icsTableCheck || mysqli_num_rows($icsTableCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    $deletedCount = 0;  // not the row count, used for toast
    $icsCounter = 0;

    foreach ($icsIds as $icsId) {
        if ($icsId <= 0) continue;

        $icsRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $icsId"));
        if (!$icsRow) {
            continue;
        }
        $icsNo = $icsRow['ics_no'];

        // 1) Remove attachment rows + their files (scanned copies)
        $attResult = @mysqli_query($con, "SHOW TABLES LIKE 'ics_attachments'");
        if ($attResult && mysqli_num_rows($attResult) > 0) {
            $attQuery = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNo'");
            if ($attQuery) {
                while ($att = mysqli_fetch_assoc($attQuery)) {
                    $attFile = $icsUploadDir . $att['filename'];
                    if (file_exists($attFile)) {
                        @unlink($attFile);
                    }
                }
            }
            mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNo'");
        }

        // 2) Remove item rows (cascade to ics_items)
        @mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $icsId");

        // 3) Remove the ICS record itself
        mysqli_query($con, "DELETE FROM ics WHERE id = $icsId");
        $icsCounter++;
    }

    $logRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
    $logIds = join(', ', $icsIds);
    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('$logRole', NOW(), 'Deleted selected ICS record(s): $logIds')");

    echo json_encode(['success' => $icsCounter > 0, 'deleted' => $icsCounter]);
    exit;
}

// ============================================================
// ACTION: Delete ICS Records (Batch, from ICS Records table)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT id, ics_no FROM ics WHERE id = $id"));
        if (!$row) continue;
        $icsNoRec = $row['ics_no'];

        // Delete ICS attachment rows + their files
        $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoRec'");
        if ($attRows) {
            while ($att = mysqli_fetch_assoc($attRows)) {
                $filePath = $icsUploadDir . $att['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoRec'");

        // Delete item rows + the ICS header itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no) — batch/chk_delete[]
// ============================================================
// ACTION: Delete ICS Records (Batch, from #icsTable checkboxes)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $uploadDir = __DIR__ . '/../../uploads/ics/';
    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$row) continue;
        $icsNo = $row['ics_no'];

        // 1) Delete attachment rows + files for this ICS
        $attQuery = @mysqli_query($con, "SHOW TABLES LIKE 'ics_attachments'");
        if ($attQuery && mysqli_num_rows($attQuery) > 0) {
            $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNo'");
            if ($attRows) {
                while ($att = mysqli_fetch_assoc($attRows)) {
                    $filePath = $uploadDir . $att['filename'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
            mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNo'");
        }

        // 2) Delete item rows, then the ICS record
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");
        $deleted++;
    }

    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES (
        '" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted " . $deleted . " ICS Record(s) from batch selection')");

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => $deleted . ' ICS record(s) deleted.']);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (batch, from ICS records DataTable)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $icsIds = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($icsIds) || !is_array($icsIds)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $deleted = 0;
    $deletedCounter = 0; // ICS record counter for logging (unused reserved)

    foreach ($icsIds as $rawId) {
        $icsId = intval($rawId);
        if ($icsId <= 0) continue;

        // Load ICS number (single record header), then delete cascading rows
        $icsHdr = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $icsId"));
        if (!$icsHdr) continue;

        $icsNoDelete = $icsHdr['ics_no'];

        // Remove attachment rows + their files (mirror pass slip records delete)
        $attQ = @mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoDelete'");
        if ($attQ) {
            while ($att = mysqli_fetch_assoc($attQ)) {
                $attFile = __DIR__ . '/../../uploads/ics/' . $att['filename'];
                if (file_exists($attFile)) {
                    @unlink($attFile);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoDelete'");
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $icsId");
        mysqli_query($con, "DELETE FROM ics WHERE id = $icsId");
        $deleted++;
    }

    $icsCount = 0;
    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (batch, from datatable checkboxes)
// Deletes ics + cascade ics_items + ics_attachments (rows+files).
// NOTE: ICS creation never decrements inventory (custody slip only),
//       so no inventory restore is needed.
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $deleted = 0;
    $uploadDirIcs = __DIR__ . '/../../uploads/ics/';
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $icsRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$icsRow) continue;
        $icsNoRec = mysqli_real_escape_string($con, $icsRow['ics_no']);

        // 1) Remove attached files + attachment rows
        $attTbl = @mysqli_query($con, "SHOW TABLES LIKE 'ics_attachments'");
        if ($attTbl && mysqli_num_rows($attTbl) > 0) {
            $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoRec'");
            if ($attRows) {
                while ($att = mysqli_fetch_assoc($attRows)) {
                    $filePath = $uploadDirIcs . $att['filename'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
            mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoRec'");
        }

        // 2) Items + header
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");

        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS: $icsNoRec')");
        $deleted++;
    }

    echo json_encode(['success' => true, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (Batch, by ics id)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    $deleted = 0;

    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$row) continue;
        $icsNo = $row['ics_no'];

        // Delete attachment rows + their files
        $attResult = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNo'");
        if ($attResult) {
            while ($attRow = mysqli_fetch_assoc($attResult)) {
                $filePath = $icsUploadDir . $attRow['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNo'");

        // Delete item rows, then the ICS record
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");
        $deleted++;

        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS Record: $icsNo')");
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (Batch, by ics_no)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $icsNos = isset($_POST['ics_nos']) ? $_POST['ics_nos'] : [];
    if (empty($icsNos) || !is_array($icsNos)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $deleted = 0;
    $uploadIcsDir = __DIR__ . '/../../uploads/ics/';

    foreach ($icsNos as $rawIcsNo) {
        $icsNoDel = mysqli_real_escape_string($con, $rawIcsNo);

        // Delete attachment rows + their files for this ICS record
        $attAtt = @mysqli_query($con, "SELECT id, filename FROM ics_attachments WHERE ics_no = '$icsNoDel'");
        if ($attAtt) {
            while ($attR = mysqli_fetch_assoc($attAtt)) {
                $attFile = $uploadIcsDir . $attR['filename'];
                if (file_exists($attFile)) {
                    @unlink($attFile);
                }
                mysqli_query($con, "DELETE FROM ics_attachments WHERE id = " . intval($attR['id']));
            }
        }

        $icsDelRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT id FROM ics WHERE ics_no = '$icsNoDel' LIMIT 1"));
        if ($icsDelRow) {
            $icsDelId = intval($icsDelRow['id']);
            mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $icsDelId");
            mysqli_query($con, "DELETE FROM ics WHERE id = $icsDelId");
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
                VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS Records (Batch): $icsNoDel')");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (Batch, via selected checkboxes)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.', 'deleted' => 0]);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.', 'deleted' => 0]);
        exit;
    }

    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    $deleted = 0;

    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        // Resolve ics_no for this record (needed to remove its attachment rows + files)
        $res = mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id");
        if (!$res) continue;
        $rec = mysqli_fetch_assoc($res);
        if (!$rec) continue; // already gone
        $icsNoRec = mysqli_real_escape_string($con, $rec['ics_no']);

        // Delete attachment rows + their files (all for this ics_no)
        $attResult = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoRec'");
        if ($attResult) {
            while ($att = mysqli_fetch_assoc($attResult)) {
                $fPath = $icsUploadDir . $att['filename'];
                if (file_exists($fPath)) {
                    @unlink($fPath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoRec'");

        // Cascade item rows, then the ICS record itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");

        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS Record: $icsNoRec')");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted, 'message' => $deleted . ' ICS record(s) deleted.']);
    exit;
}

// ============================================================
// ACTION: Delete ICS Records (batch, by ics id)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $icsRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$icsRow) continue;
        $icsNoRecord = $icsRow['ics_no'];

        // Delete ICS attachments (rows + files) for this ICS no
        $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoRecord'");
        if ($attRows) {
            while ($att = mysqli_fetch_assoc($attRows)) {
                $filePath = $icsUploadDir . $att['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoRecord'");

        // Remove ICS items rows, then the ICS record itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no) — single/batch
// ============================================================
// ACTION: Delete ICS Records (Batch, by ids from datatable)
// Mirrors the Pass Slip records batch delete, but for ICS:
//  - removes ics row + its ics_items + ics_attachments (rows + files)
//  - does NOT touch inventory (ICS create never decrements stock)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $icsRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$icsRow) continue;
        $icsNoRec = mysqli_real_escape_string($con, $icsRow['ics_no']);

        // 1) Delete ICS attachment rows (+ their files in uploads/ics/)
        $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoRec'");
        if ($attRows) {
            while ($att = mysqli_fetch_assoc($attRows)) {
                $filePath = __DIR__ . '/../../uploads/ics/' . $att['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoRec'");

        // 2) Delete ICS items, then the ICS record itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS Records (Batch): $icsNoRec')");

        $deleted++;
    }

    echo json_encode(['success' => true, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (Batch, from ICS Records datatable)
// Mirrors Pass Slip records batch delete. ICS issuance never
// touches inventory, so deleting an ICS = remove ics,
// cascade ics_items + ics_attachments (rows + scanned files).
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $icsTableCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$icsTableCheck || mysqli_num_rows($icsTableCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    if (!is_dir($icsUploadDir)) {
        @mkdir($icsUploadDir, 0777, true);
    }

    $deletedCount = 0;
    foreach ($ids as $rawId) {
        $icsId = intval($rawId);
        if ($icsId <= 0) continue;

        // Grab ics_no to cascade attachments (files + rows) before header delete
        $icsRow = mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $icsId");
        $icsNoVal = null;
        if ($icsRow && ($icsRec = mysqli_fetch_assoc($icsRow))) {
            $icsNoVal = $icsRec['ics_no'];
        }

        // Delete ICS attachment rows + their scanned files for this record
        if (!is_null($icsNoVal)) {
            $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoVal'");
            if ($attRows) {
                while ($att = mysqli_fetch_assoc($attRows)) {
                    $attPath = $icsUploadDir . $att['filename'];
                    if (file_exists($attPath)) {
                        @unlink($attPath);
                    }
                }
                mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoVal'");
            }
            mysqli_query($con, "DELETE FROM ics_items WHERE ics_no = '$icsNoVal'");
        }

        // Delete ICS row itself
        mysqli_query($con, "DELETE FROM ics WHERE id = $icsId");
        $deletedCount++;

        if (!is_null($icsNoVal)) {
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
                VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS: $icsNoVal')");
        }
    }

    echo json_encode(['success' => $deletedCount > 0, 'deleted' => $deletedCount]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
// ACTION: Delete ICS Records (Batch, from ICS Records table)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $deleted = 0;
    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT ics_no FROM ics WHERE id = $id"));
        if (!$row) continue;
        $icsNoRec = $row['ics_no'];

        // Delete attachment rows + their files for this ICS record
        $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsNoRec'");
        if ($attRows) {
            while ($att = mysqli_fetch_assoc($attRows)) {
                $filePath = $icsUploadDir . $att['filename'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsNoRec'");

        // Delete item rows, then the ICS record itself
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $id");
        mysqli_query($con, "DELETE FROM ics WHERE id = $id");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Batch Delete ICS Records (ids[] = ics record ids)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_records') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No ICS records selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'ICS table not found.']);
        exit;
    }

    $deleted = 0;
    $icsUploadDir = __DIR__ . '/../../uploads/ics/';
    foreach ($ids as $rawId) {
        $aid = intval($rawId);
        if ($aid <= 0) continue;

        $icsRow = mysqli_fetch_assoc(mysqli_query($con, "SELECT id, ics_no FROM ics WHERE id = $aid"));
        if (!$icsRow) continue;
        $icsRecId = intval($icsRow['id']);
        $icsRecNo = $icsRow['ics_no'];

        // 1) Remove attachment rows + files for this ICS
        $attRows = mysqli_query($con, "SELECT filename FROM ics_attachments WHERE ics_no = '$icsRecNo'");
        if ($attRows) {
            while ($att = mysqli_fetch_assoc($attRows)) {
                $attPath = $icsUploadDir . $att['filename'];
                if (file_exists($attPath)) {
                    @unlink($attPath);
                }
            }
        }
        mysqli_query($con, "DELETE FROM ics_attachments WHERE ics_no = '$icsRecNo'");

        // 2) Items under this ICS record
        mysqli_query($con, "DELETE FROM ics_items WHERE ics_id = $icsRecId");

        // 3) The ICS record itself
        mysqli_query($con, "DELETE FROM ics WHERE id = $icsRecId");

        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action)
            VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted ICS Records (Batch): " . mysqli_real_escape_string($con, $icsRecNo) . "')");
        $deleted++;
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Delete Pass Slip (by pass_slip_no)
// ============================================================
if (isset($_POST['btn_delete'])) {
    // Single delete by pass_slip_no
    if (isset($_POST['delete_pass_slip_no']) && !empty($_POST['delete_pass_slip_no'])) {
        $pass_slip_no = mysqli_real_escape_string($con, $_POST['delete_pass_slip_no']);
        $psItems = mysqli_query($con, "SELECT id, inventory_id, qty, status FROM pass_slip WHERE pass_slip_no = '$pass_slip_no'");
        if ($psItems) {
            while ($psRow = mysqli_fetch_assoc($psItems)) {
                if ($psRow['status'] === 'borrowed') {
                    mysqli_query($con, "UPDATE inventory SET quantity = quantity + " . intval($psRow['qty']) . " WHERE id = " . intval($psRow['inventory_id']));
                }
            }
            mysqli_query($con, "DELETE FROM pass_slip WHERE pass_slip_no = '$pass_slip_no'");
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
                VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted Pass Slip: $pass_slip_no')");
        }
        $_SESSION['delete'] = 1;
        header("Location: pass_slip.php");
        exit;
    }

    // Legacy single delete by id
    if (isset($_POST['delete_single_id']) && !empty($_POST['delete_single_id'])) {
        $id = intval($_POST['delete_single_id']);
        $psQuery = mysqli_query($con, "SELECT pass_slip_no, item_description, inventory_id, qty, status FROM pass_slip WHERE id = $id");
        if ($psRow = mysqli_fetch_assoc($psQuery)) {
            if ($psRow['status'] === 'borrowed') {
                mysqli_query($con, "UPDATE inventory SET quantity = quantity + " . intval($psRow['qty']) . " WHERE id = " . intval($psRow['inventory_id']));
            }
            mysqli_query($con, "DELETE FROM pass_slip WHERE id = $id");
            mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
                VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted Pass Slip: " . $psRow['pass_slip_no'] . " - " . $psRow['item_description'] . "')");
        }
        $_SESSION['delete'] = 1;
        header("Location: pass_slip.php");
        exit;
    }

    // Batch delete (from checkboxes) - values are now pass_slip_no strings
    if (isset($_POST['chk_delete']) && is_array($_POST['chk_delete'])) {
        foreach ($_POST['chk_delete'] as $pass_slip_no) {
            $pass_slip_no = mysqli_real_escape_string($con, $pass_slip_no);
            $psItems = mysqli_query($con, "SELECT inventory_id, qty, status FROM pass_slip WHERE pass_slip_no = '$pass_slip_no'");
            if ($psItems) {
                while ($psRow = mysqli_fetch_assoc($psItems)) {
                    if ($psRow['status'] === 'borrowed') {
                        mysqli_query($con, "UPDATE inventory SET quantity = quantity + " . intval($psRow['qty']) . " WHERE id = " . intval($psRow['inventory_id']));
                    }
                }
                mysqli_query($con, "DELETE FROM pass_slip WHERE pass_slip_no = '$pass_slip_no'");
                mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
                    VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Deleted Pass Slip: $pass_slip_no')");
            }
        }
        $_SESSION['delete'] = 1;
        header("Location: pass_slip.php");
        exit;
    }
}

// ============================================================
// ACTION: Process Return (all items by pass_slip_no)
// ============================================================
if (isset($_POST['process_return'])) {
    $pass_slip_no = mysqli_real_escape_string($con, $_POST['pass_slip_no']);
    $return_date = mysqli_real_escape_string($con, $_POST['return_date']);
    $condition_return = mysqli_real_escape_string($con, $_POST['condition_return']);
    $requested_by_return = mysqli_real_escape_string($con, $_POST['requested_by_return']);
    $inspected_by_return = mysqli_real_escape_string($con, $_POST['inspected_by_return']);
    $approved_by_return = mysqli_real_escape_string($con, $_POST['approved_by_return']);
    $remarks_return = mysqli_real_escape_string($con, $_POST['remarks_return']);

    // Get all items in this pass slip
    $psItems = mysqli_query($con, "SELECT * FROM pass_slip WHERE pass_slip_no = '$pass_slip_no' AND status = 'borrowed'");
    if (!$psItems || mysqli_num_rows($psItems) == 0) {
        echo "<script>alert('No borrowed items found for this pass slip.'); window.history.back();</script>";
        exit;
    }

    $itemCount = 0;
    while ($ps = mysqli_fetch_assoc($psItems)) {
        // Update each item with return details
        $updateQuery = "UPDATE pass_slip SET 
            return_date = '$return_date',
            requested_by_return = '$requested_by_return',
            inspected_by_return = '$inspected_by_return',
            approved_by_return = '$approved_by_return',
            condition_return = '$condition_return',
            remarks = IF('$remarks_return' != '', '$remarks_return', remarks),
            status = 'returned'
            WHERE id = " . intval($ps['id']);

        if (mysqli_query($con, $updateQuery)) {
            mysqli_query($con, "UPDATE inventory SET quantity = quantity + " . intval($ps['qty']) . " WHERE id = " . intval($ps['inventory_id']));
            $itemCount++;
        }
    }

    mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) 
        VALUES ('" . ($_SESSION['role'] ?? 'admin') . "', NOW(), 'Processed Return: $pass_slip_no - $itemCount item(s) returned')");

    echo "<script>alert('$itemCount item(s) returned successfully!'); window.location.href = 'pass_slip.php';</script>";
    exit;
}

// ============================================================
// ACTION: Mark Overdue (can be run via cron or manual trigger)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'mark_overdue') {
    $query = "UPDATE pass_slip SET status = 'overdue' 
              WHERE status = 'borrowed' AND return_date < CURDATE()";
    mysqli_query($con, $query);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Overdue items updated.']);
    exit;
}

// ============================================================
// ACTION: Upload Scanned File for Pass Slip
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'upload_attachment') {
    ob_start();
    header('Content-Type: application/json');

    $pass_slip_no = isset($_POST['pass_slip_no']) ? mysqli_real_escape_string($con, $_POST['pass_slip_no']) : '';
    if (empty($pass_slip_no)) {
        echo json_encode(['success' => false, 'message' => 'Missing pass slip number.']);
        ob_end_flush();
        exit;
    }

    $tableExists = false;
    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'pass_slip_attachments'");
    if ($tCheck && mysqli_num_rows($tCheck) > 0) {
        $tableExists = true;
    }
    if (!$tableExists) {
        echo json_encode(['success' => false, 'message' => 'Attachments table not found. Please run migration_pass_slip_attachments.sql first.']);
        ob_end_flush();
        exit;
    }

    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $uploadDir = __DIR__ . '/../../uploads/pass_slip/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory. Check permissions.']);
            ob_end_flush();
            exit;
        }
    }

    $uploadedCount = 0;
    $errors = [];

    if (isset($_FILES['attachments'])) {
        $fileCount = count($_FILES['attachments']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $_FILES['attachments']['tmp_name'][$i];
            $originalName = $_FILES['attachments']['name'][$i];
            $fileType = $_FILES['attachments']['type'][$i];
            $fileSize = $_FILES['attachments']['size'][$i];
            $fileError = $_FILES['attachments']['error'][$i];

            if ($fileError !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
                    UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
                    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.'
                ];
                $errors[] = "Error uploading $originalName: " . ($errorMessages[$fileError] ?? 'Unknown upload error (#' . $fileError . ')');
                continue;
            }

            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Invalid file type: $originalName (only PDF, JPG, PNG allowed)";
                continue;
            }

            if ($fileSize > 10 * 1024 * 1024) {
                $errors[] = "File too large: $originalName (max 10MB)";
                continue;
            }

            $safeName = round(microtime(true) * 1000) . '_' . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $originalName);
            $target = $uploadDir . $safeName;

            if (move_uploaded_file($tmpName, $target)) {
                $uploadedBy = $_SESSION['username'] ?? 'admin';
                $insQuery = "INSERT INTO pass_slip_attachments (pass_slip_no, filename, uploaded_by) 
                             VALUES ('$pass_slip_no', '" . mysqli_real_escape_string($con, $safeName) . "', '$uploadedBy')";
                if (mysqli_query($con, $insQuery)) {
                    $uploadedCount++;
                } else {
                    $errors[] = "DB error for: $originalName - " . mysqli_error($con);
                }
            } else {
                $errors[] = "Failed to move file: $originalName to $target (check directory permissions)";
            }
        }
    } else {
        $errors[] = 'No files received by server.';
    }

    ob_end_clean();
    echo json_encode([
        'success' => $uploadedCount > 0,
        'uploaded' => $uploadedCount,
        'errors' => $errors
    ]);
    exit;
}

// ============================================================
// ACTION: Get Attachment List for Pass Slip (AJAX)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'attachment_list') {
    header('Content-Type: application/json');

    $pass_slip_no = isset($_GET['pass_slip_no']) ? mysqli_real_escape_string($con, $_GET['pass_slip_no']) : '';
    if (empty($pass_slip_no)) {
        echo json_encode([]);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'pass_slip_attachments'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode([]);
        exit;
    }

    $query = "SELECT id, filename, uploaded_by, uploaded_at 
              FROM pass_slip_attachments 
              WHERE pass_slip_no = '$pass_slip_no' 
              ORDER BY uploaded_at DESC";
    $result = mysqli_query($con, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

// ============================================================
// ACTION: Delete Attachments (Batch, AJAX)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_attachments') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No attachments selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'pass_slip_attachments'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'Attachments table not found.']);
        exit;
    }

    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT filename FROM pass_slip_attachments WHERE id = $id"));
        if ($row) {
            $filePath = __DIR__ . '/../../uploads/pass_slip/' . $row['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            mysqli_query($con, "DELETE FROM pass_slip_attachments WHERE id = $id");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}

// ============================================================
// ACTION: Upload Scanned Copy for ICS (AJAX)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'upload_attachment_ics') {
    ob_start();
    header('Content-Type: application/json');

    $icsNo = isset($_POST['ics_no']) ? mysqli_real_escape_string($con, $_POST['ics_no']) : '';
    if (empty($icsNo)) {
        echo json_encode(['success' => false, 'message' => 'Missing ICS number.']);
        ob_end_flush();
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics_attachments'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'Attachments table not found. Please run the ICS attachments migration first.']);
        ob_end_flush();
        exit;
    }

    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $uploadDir = __DIR__ . '/../../uploads/ics/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory. Check permissions.']);
            ob_end_flush();
            exit;
        }
    }

    $uploadedCount = 0;
    $errors = [];

    if (isset($_FILES['attachments'])) {
        $fileCount = count($_FILES['attachments']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $_FILES['attachments']['tmp_name'][$i];
            $originalName = $_FILES['attachments']['name'][$i];
            $fileType = $_FILES['attachments']['type'][$i];
            $fileSize = $_FILES['attachments']['size'][$i];
            $fileError = $_FILES['attachments']['error'][$i];

            if ($fileError !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
                    UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
                    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.'
                ];
                $errors[] = "Error uploading $originalName: " . ($errorMessages[$fileError] ?? 'Unknown upload error (#' . $fileError . ')');
                continue;
            }

            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "Invalid file type: $originalName (only PDF, JPG, PNG allowed)";
                continue;
            }

            if ($fileSize > 10 * 1024 * 1024) {
                $errors[] = "File too large: $originalName (max 10MB)";
                continue;
            }

            $safeName = round(microtime(true) * 1000) . '_' . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $originalName);
            $target = $uploadDir . $safeName;

            if (move_uploaded_file($tmpName, $target)) {
                $uploadedBy = $_SESSION['username'] ?? 'admin';
                $insQuery = "INSERT INTO ics_attachments (ics_no, filename, uploaded_by) 
                             VALUES ('$icsNo', '" . mysqli_real_escape_string($con, $safeName) . "', '$uploadedBy')";
                if (mysqli_query($con, $insQuery)) {
                    $uploadedCount++;
                } else {
                    $errors[] = "DB error for: $originalName - " . mysqli_error($con);
                    @unlink($target);
                }
            } else {
                $errors[] = "Failed to move file: $originalName to $target (check directory permissions)";
            }
        }
    } else {
        $errors[] = 'No files received by server.';
    }

    ob_end_clean();
    echo json_encode([
        'success' => $uploadedCount > 0,
        'uploaded' => $uploadedCount,
        'errors' => $errors
    ]);
    exit;
}

// ============================================================
// ACTION: Get ICS Attachment List (AJAX)
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'ics_attachment_list') {
    header('Content-Type: application/json');

    $icsNo = isset($_GET['ics_no']) ? mysqli_real_escape_string($con, $_GET['ics_no']) : '';
    if (empty($icsNo)) {
        echo json_encode([]);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics_attachments'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode([]);
        exit;
    }

    $query = "SELECT id, filename, uploaded_by, uploaded_at 
              FROM ics_attachments 
              WHERE ics_no = '$icsNo' 
              ORDER BY uploaded_at DESC";
    $result = mysqli_query($con, $query);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}

// ============================================================
// ACTION: Delete ICS Attachments (Batch, AJAX)
// ============================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_ics_attachments') {
    header('Content-Type: application/json');

    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No attachments selected.']);
        exit;
    }

    $tCheck = @mysqli_query($con, "SHOW TABLES LIKE 'ics_attachments'");
    if (!$tCheck || mysqli_num_rows($tCheck) == 0) {
        echo json_encode(['success' => false, 'message' => 'Attachments table not found.']);
        exit;
    }

    $deleted = 0;
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id <= 0) continue;

        $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT filename FROM ics_attachments WHERE id = $id"));
        if ($row) {
            $filePath = __DIR__ . '/../../uploads/ics/' . $row['filename'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            mysqli_query($con, "DELETE FROM ics_attachments WHERE id = $id");
            $deleted++;
        }
    }

    echo json_encode(['success' => $deleted > 0, 'deleted' => $deleted]);
    exit;
}
?>