<?php if (!isset($con)) include "../connection.php";
// Add Item
if (isset($_POST['btn_add'])) {
    $locality = mysqli_real_escape_string($con, $_POST['txt_locality']);
    $barangay = mysqli_real_escape_string($con, $_POST['txt_barangay']);
    $district = mysqli_real_escape_string($con, $_POST['txt_district']);
    $location = mysqli_real_escape_string($con, $_POST['txt_location']);
    $date = mysqli_real_escape_string($con, $_POST['txt_date']);
    $year = mysqli_real_escape_string($con, $_POST['txt_year']);
    $type = mysqli_real_escape_string($con, $_POST['txt_type']);
    $status = mysqli_real_escape_string($con, $_POST['txt_status']);
    $accomplished = mysqli_real_escape_string($con, $_POST['txt_accomplished']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks']);


    if (isset($_SESSION['role'])) {
        $action_log = 'Added Item: ' . $locality;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action_log . "')");
    }

    $query = "INSERT INTO locationrequests (
        locality, barangay, district, location, date, year, type, status, accomplished, remarks
    ) VALUES (
        '$locality', '$barangay', '$district', '$location', '$date', '$year', '$type', '$status', '$accomplished', '$remarks'
    )";
    

    $query_result = mysqli_query($con, $query) or die('Error: ' . mysqli_error($con));

    $id = mysqli_insert_id($con);

    // Handle file upload
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $target = "photo/";

            // Ensure the photo directory exists, create if it doesn't
            if (!file_exists($target)) {
                mkdir($target, 0777, true);
            }

            // Sanitize file name (optional)
            $milliseconds = round(microtime(true) * 1000);
            $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['files']['name'][$key]);
            $target = $target . $name;

            if (move_uploaded_file($tmp_name, $target)) {
                mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) 
                    VALUES ('$id', '" . $name . "')") or die('Error: ' . mysqli_error($con));
            } else {
                echo "Error uploading file: " . $_FILES['files']['name'][$key];
            }
        }
    }

    if ($query_result) {
        $_SESSION['added'] = 1;
        header("location: " . $_SERVER['REQUEST_URI']);
    }
}

// Update Item
if (isset($_POST['btn_save'])) {
    $id = $_POST['hidden_id'];
    $locality = mysqli_real_escape_string($con, $_POST['txt_edit_locality']);
    $barangay = mysqli_real_escape_string($con, $_POST['txt_edit_barangay']);
    $district = mysqli_real_escape_string($con, $_POST['txt_edit_district']);
    $location = mysqli_real_escape_string($con, $_POST['txt_edit_location']);
    $date = mysqli_real_escape_string($con, $_POST['txt_edit_date']);
    $year = mysqli_real_escape_string($con, $_POST['txt_edit_year']);
    $type = mysqli_real_escape_string($con, $_POST['txt_edit_type']);
    $status = mysqli_real_escape_string($con, $_POST['txt_edit_status']);
    $accomplished = mysqli_real_escape_string($con, $_POST['txt_edit_accomplished']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks']);


    // Update query with remark field
    $update_query = mysqli_query($con, "UPDATE locationrequests SET 
         locality = '$locality', 
        barangay = '$barangay', 
        district = '$district',
        location = '$location', 
        date = '$date',
        year = '$year',
        type = '$type',  
        status = '$status', 
        accomplished = '$accomplished', 
        remarks = '$remarks' 
        WHERE id = '$id'") or die('Error: ' . mysqli_error($con));

    if (isset($_SESSION['role'])) {
        $action_log = 'Updated Item ' . $item_no;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action_log . "')");
    }

    if ($update_query) {
        $_SESSION['edited'] = 1;
        header("Location: " . $_SERVER['REQUEST_URI']);
    }
}

if (isset($_POST['btn_delete'])) {
    if (isset($_POST['chk_delete'])) {
        foreach ($_POST['chk_delete'] as $value) {
            // First, retrieve the activity name before deletion
            $locationQuery = mysqli_query($con, "SELECT location FROM locationrequests WHERE id = '$value'");
            $locationRow = mysqli_fetch_assoc($locationQuery);
            $locationName = $locationRow['location'];

            $delete_query = mysqli_query($con, "DELETE FROM locationrequests WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
            // Logging the deletion of each item
            if (isset($_SESSION['role'])) {
                $action = 'Deleted Item: ' . $locationName;  // Log the deleted activity name
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
