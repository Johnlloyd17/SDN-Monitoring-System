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
    $query = "SELECT ps.item_description, ps.qty, ps.unit, ps.pullout_date, ps.purpose, ps.requested_by_out, ps.inspected_by_out, ps.approved_by_out, ps.pass_slip_no, ps.status,
              i.property AS property_no, i.serial AS serial_no, i.ics AS ics_no
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
    $pullout_date = mysqli_real_escape_string($con, $_POST['pullout_date']);
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

        if ($qty <= 0) {
            echo "<script>alert('Invalid quantity for row " . ($index + 1) . ".'); window.history.back();</script>";
            exit;
        }

        $checkQuery = "SELECT quantity FROM inventory WHERE id = '$inv_id'";
        $checkResult = mysqli_query($con, $checkQuery);
        $item = mysqli_fetch_assoc($checkResult);

        if (!$item || $item['quantity'] < $qty) {
            echo "<script>alert('Insufficient quantity for: $item_desc. Available: " . ($item['quantity'] ?? 0) . "); window.history.back();</script>";
            exit;
        }

        $insertQuery = "INSERT INTO pass_slip (pass_slip_no, inventory_id, item_description, qty, unit,
                        pullout_date, requested_by_out, inspected_by_out, approved_by_out,
                        purpose, condition_out, remarks, status, created_by)
                        VALUES ('$pass_slip_no', '$inv_id', '$item_desc', '$qty', '$unit',
                        '$pullout_date', '$requested_by_out', '$inspected_by_out', '$approved_by_out',
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
?>