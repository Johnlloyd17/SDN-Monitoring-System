<?php
if (isset($_POST['btn_add'])) {
    // Sanitize input data
    $project = mysqli_real_escape_string($con, $_POST['txt_project']);
    $item = mysqli_real_escape_string($con, $_POST['txt_item']);
    $classification = mysqli_real_escape_string($con, $_POST['txt_classification']);
    $quantity = mysqli_real_escape_string($con, $_POST['txt_quantity']);
    $unit = mysqli_real_escape_string($con, $_POST['txt_unit']);
    $description = mysqli_real_escape_string($con, $_POST['txt_description']);
    $received = mysqli_real_escape_string($con, $_POST['txt_received']);
    $property = mysqli_real_escape_string($con, $_POST['txt_property']);
    $ics = mysqli_real_escape_string($con, $_POST['txt_ics']);
    $serial = mysqli_real_escape_string($con, $_POST['txt_serial']);
    $date = mysqli_real_escape_string($con, $_POST['txt_date']);
    $officer = mysqli_real_escape_string($con, $_POST['txt_officer']);
    $cost = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_cost']));
    $life = mysqli_real_escape_string($con, $_POST['txt_life']);
    $transferred = mysqli_real_escape_string($con, $_POST['txt_transferred']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks']);

    if (isset($_SESSION['role'])) {
        $action = 'Added Item:' . $description;  // Logging activity
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action')");
    }

    // Insert query with new data columns
    $query = "INSERT INTO inventory  (
        project, item, classification, quantity, unit, description, received, property, ics, serial, date, officer, cost, life, transferred, remarks
    ) VALUES (
        '$project', '$item', '$classification', '$quantity', '$unit', '$description', '$received', '$property', '$ics', '$serial', '$date', '$officer', '$cost', '$life', '$transferred', '$remarks'
    )";

    $query_result = mysqli_query($con, $query) or die('Error: ' . mysqli_error($con));

    // File upload logic
    $id = mysqli_insert_id($con);
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $target = "photo/";

            if (!file_exists($target)) {
                mkdir($target, 0777, true);
            }

            $milliseconds = round(microtime(true) * 1000);
            $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['files']['name'][$key]);
            $target .= $name;

            if (move_uploaded_file($tmp_name, $target)) {
                mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES ('$id', '$name')") or die('Error: ' . mysqli_error($con));
            }
        }
    }

    if ($query_result) {
        $_SESSION['added'] = 1;
        header("location: " . $_SERVER['REQUEST_URI']);
    }
}

// Update logic for btn_save
if (isset($_POST['btn_save'])) {
    $id = $_POST['hidden_id'];
    $project = mysqli_real_escape_string($con, $_POST['txt_edit_project']);
    $item = mysqli_real_escape_string($con, $_POST['txt_edit_item']);
    $classification = mysqli_real_escape_string($con, $_POST['txt_edit_classification']);
    $quantity = mysqli_real_escape_string($con, $_POST['txt_edit_quantity']);
    $unit = mysqli_real_escape_string($con, $_POST['txt_edit_unit']);
    $description = mysqli_real_escape_string($con, $_POST['txt_edit_description']);
    $received = mysqli_real_escape_string($con, $_POST['txt_edit_received']);
    $property = mysqli_real_escape_string($con, $_POST['txt_edit_property']);
    $ics = mysqli_real_escape_string($con, $_POST['txt_edit_ics']);
    $serial = mysqli_real_escape_string($con, $_POST['txt_edit_serial']);
    $date = mysqli_real_escape_string($con, $_POST['txt_edit_date']);
    $officer = mysqli_real_escape_string($con, $_POST['txt_edit_officer']);
    $cost = str_replace(',', '', mysqli_real_escape_string($con, $_POST['txt_edit_cost']));
    $life = mysqli_real_escape_string($con, $_POST['txt_edit_life']);
    $transferred = mysqli_real_escape_string($con, $_POST['txt_edit_transferred']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks']);

    $update_query = mysqli_query($con, "UPDATE inventory  SET 
        project = '$project', 
        item = '$item',
        classification = '$classification',
        quantity = '$quantity',
        unit = '$unit',
        description = '$description',
        received = '$received',
        property = '$property',
        ics = '$ics',
        serial = '$serial',
        date = '$date',
        officer = '$officer',
        cost = '$cost',
        life = '$life',
        transferred = '$transferred',
        remarks = '$remarks' 
        WHERE id = '$id'") or die('Error: ' . mysqli_error($con));

    if ($update_query) {
        $_SESSION['edited'] = 1;
        header("Location: " . $_SERVER['REQUEST_URI']);
    }
}

if (isset($_POST['btn_delete'])) {
    if (isset($_POST['chk_delete'])) {
        foreach ($_POST['chk_delete'] as $value) {
            // First, retrieve the activity name before deletion
            $itemQuery = mysqli_query($con, "SELECT item FROM inventory WHERE id = '$value'");
            $itemRow = mysqli_fetch_assoc($itemQuery);
            $itemName = $itemRow['item'];

            $delete_query = mysqli_query($con, "DELETE FROM inventory WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
            // Logging the deletion of each item
            if (isset($_SESSION['role'])) {
                $action = 'Deleted Item: ' . $activityName;  // Log the deleted activity name
                $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action . "')");
            }
            
            if ($delete_query) {
                $_SESSION['delete'] = 1;
                header("Location: " . $_SERVER['REQUEST_URI']);
            }
        }
    }
}



if (isset($_POST['btn_addimage'])) {
    $id = $_POST['hidden_id'];

    if (isset($_FILES['photos'])) {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {

            $target = "photo/";

            // Ensure the photo directory exists, create if it doesn't
            if (!file_exists($target)) {
                mkdir($target, 0777, true);
            }

            // Sanitize file name
            $milliseconds = round(microtime(true) * 1000);
            $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['photos']['name'][$key]);
            $target = $target . $name;

            if (move_uploaded_file($tmp_name, $target)) {
                $query = mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) 
                    VALUES ('$id', '" . $name . "')") or die('Error: ' . mysqli_error($con));
                if ($query == true) {
                    $_SESSION['added'] = 1;
                    header("location: " . $_SERVER['REQUEST_URI']);
                }
            }
        }
    }
}

if (isset($_POST['btn_remove'])) {
    if (isset($_POST['chk_deletephoto'])) {
        foreach ($_POST['chk_deletephoto'] as $value) {
            $delete_query = mysqli_query($con, "DELETE from tblactivityphoto where id = '$value'") or die('Error: ' . mysqli_error($con));
                    
            if ($delete_query == true) {
                $_SESSION['delete'] = 1;
                header("location: " . $_SERVER['REQUEST_URI']);
            }
        }
    }
}
?>
