<?php if (!isset($con)) include "../connection.php";
if (isset($_POST['btn_add'])) {
    $locality = mysqli_real_escape_string($con, $_POST['txt_locality']);
    $barangay = mysqli_real_escape_string($con, $_POST['txt_barangay']);
    $district = mysqli_real_escape_string($con, $_POST['txt_district']);
    $transport_location = mysqli_real_escape_string($con, $_POST['txt_transport_location']);
    $transport_type = mysqli_real_escape_string($con, $_POST['txt_transport_type']);
    $locations = mysqli_real_escape_string($con, $_POST['txt_locations']);
    $type = mysqli_real_escape_string($con, $_POST['txt_type']);
    $code = mysqli_real_escape_string($con, $_POST['txt_code']);
    $nationwide_id = mysqli_real_escape_string($con, $_POST['txt_nationwide_id']);
    $date_of_activation = !empty($_POST['txt_date_of_activation']) ? "'" . mysqli_real_escape_string($con, $_POST['txt_date_of_activation']) . "'" : 'NULL';
    $current_date_of_acceptance = !empty($_POST['txt_current_date_of_acceptance']) ? "'" . mysqli_real_escape_string($con, $_POST['txt_current_date_of_acceptance']) . "'" : 'NULL';
    $latitude = $_POST['txt_latitude'] !== '' ? (float)$_POST['txt_latitude'] : 'NULL';
    $longitude = $_POST['txt_longitude'] !== '' ? (float)$_POST['txt_longitude'] : 'NULL';
    $strategy = mysqli_real_escape_string($con, $_POST['txt_strategy']);
    $status = mysqli_real_escape_string($con, $_POST['txt_status']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks']);

    if (isset($_SESSION['role'])) {
        $action_log = 'Added Item ' . $locality;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action_log . "')");
    }

    $query = "INSERT INTO tblfwfa (
        locality, barangay, district, transport_location, transport_type,
        locations, type, code, nationwide_id, date_of_activation,
        current_date_of_acceptance, latitude, longitude, strategy, status, remarks
    ) VALUES (
        '$locality', '$barangay', '$district', '$transport_location', '$transport_type',
        '$locations', '$type', '$code', '$nationwide_id', $date_of_activation,
        $current_date_of_acceptance, $latitude, $longitude, '$strategy', '$status', '$remarks'
    )";


    $query_result = mysqli_query($con, $query) or die('Error: ' . mysqli_error($con));

    $id = mysqli_insert_id($con);
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {

            $target = "photo/";

            // Ensure the photo directory exists, create if it doesn't
            if (!file_exists($target)) {
                mkdir($target, 0777, true); // Create the directory with write permissions
            }

            // Sanitize file name (optional, but recommended)
            $milliseconds = round(microtime(true) * 1000);
            $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['files']['name'][$key]);
            $target = $target . $name;

            if (move_uploaded_file($tmp_name, $target)) {
                mysqli_query($con, "INSERT INTO tblbplsphoto (activityid, filename) 
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

if (isset($_POST['btn_save'])) {
    $id = $_POST['hidden_id'];
    $locality = mysqli_real_escape_string($con, $_POST['txt_edit_locality']);
    $barangay = mysqli_real_escape_string($con, $_POST['txt_edit_barangay']);
    $district = mysqli_real_escape_string($con, $_POST['txt_edit_district']);
    $transport_location = mysqli_real_escape_string($con, $_POST['txt_edit_transport_location']);
    $transport_type = mysqli_real_escape_string($con, $_POST['txt_edit_transport_type']);
    $locations = mysqli_real_escape_string($con, $_POST['txt_edit_locations']);
    $type = mysqli_real_escape_string($con, $_POST['txt_edit_type']);
    $code = mysqli_real_escape_string($con, $_POST['txt_edit_code']);
    $nationwide_id = mysqli_real_escape_string($con, $_POST['txt_edit_nationwide_id']);
    $date_of_activation = !empty($_POST['txt_edit_date_of_activation']) ? "'" . mysqli_real_escape_string($con, $_POST['txt_edit_date_of_activation']) . "'" : 'NULL';
    $current_date_of_acceptance = !empty($_POST['txt_edit_current_date_of_acceptance']) ? "'" . mysqli_real_escape_string($con, $_POST['txt_edit_current_date_of_acceptance']) . "'" : 'NULL';
    $latitude = $_POST['txt_edit_latitude'] !== '' ? (float)$_POST['txt_edit_latitude'] : 'NULL';
    $longitude = $_POST['txt_edit_longitude'] !== '' ? (float)$_POST['txt_edit_longitude'] : 'NULL';
    $strategy = mysqli_real_escape_string($con, $_POST['txt_edit_strategy']);
    $status = mysqli_real_escape_string($con, $_POST['txt_edit_status']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks']);

    $update_query = mysqli_query($con, "UPDATE tblfwfa SET 
        locality = '$locality', 
        barangay = '$barangay', 
        district = '$district',
        transport_location = '$transport_location',
        transport_type = '$transport_type',
        locations = '$locations', 
        type = '$type', 
        code = '$code', 
        nationwide_id = '$nationwide_id',
        date_of_activation = $date_of_activation,
        current_date_of_acceptance = $current_date_of_acceptance,
        latitude = $latitude,
        longitude = $longitude,
        strategy = '$strategy', 
        status = '$status', 
        remarks = '$remarks'
        WHERE id = '$id'") or die('Error: ' . mysqli_error($con));

    if (isset($_SESSION['role'])) {
        $action_log = 'Updated Item ' . $locality;  // Logging activity
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
            $locationsQuery = mysqli_query($con, "SELECT locations FROM tblfwfa WHERE id = '$value'");
            $locationsRow = mysqli_fetch_assoc($locationsQuery);
            $locationsName = $locationsRow['locations'];

            $delete_query = mysqli_query($con, "DELETE FROM tblfwfa WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
            // Logging the deletion of each item
            if (isset($_SESSION['role'])) {
                $action = 'Deleted Item: ' . $locationsName;  // Log the deleted activity name
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
