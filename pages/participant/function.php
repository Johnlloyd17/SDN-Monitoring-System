<?php
if (isset($_POST['btn_add'])) {
    $start = mysqli_real_escape_string($con, $_POST['txt_start']);
    $end = mysqli_real_escape_string($con, $_POST['txt_end']);
    $activity = mysqli_real_escape_string($con, $_POST['txt_activity']);
    $indicator = mysqli_real_escape_string($con, $_POST['txt_indicator']);
    $fullname = mysqli_real_escape_string($con, $_POST['txt_fullname']);
    $sex = mysqli_real_escape_string($con, $_POST['txt_sex']);
    $contact = mysqli_real_escape_string($con, $_POST['txt_contact']);
    $email = mysqli_real_escape_string($con, $_POST['txt_email']);
    $mode = mysqli_real_escape_string($con, $_POST['txt_mode']);
    $agency = mysqli_real_escape_string($con, $_POST['txt_agency']);
    $sector = mysqli_real_escape_string($con, $_POST['txt_sector']);
    $project = mysqli_real_escape_string($con, $_POST['txt_project']);
    $person = mysqli_real_escape_string($con, $_POST['txt_person']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks']);

    if (isset($_SESSION['role'])) {
        $action = 'Added Item: ' . $fullname;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action . "')");
    }

    $query = "INSERT INTO tblparticipant (
        start, end, activity, indicator, fullname, sex, contact, email, mode, agency, sector, 
        project, person, remarks
    ) VALUES (
        '$start', '$end', '$activity', '$indicator', '$fullname', '$sex', '$contact', '$email', '$mode', '$agency', '$sector', 
        '$project', '$person', '$remarks'
    )";

    echo $query; // Output the query to debug
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

if (isset($_POST['btn_save'])) {
    $id = $_POST['hidden_id'];
    $start = mysqli_real_escape_string($con, $_POST['txt_edit_start']);
    $end = mysqli_real_escape_string($con, $_POST['txt_edit_end']);
    $activity = mysqli_real_escape_string($con, $_POST['txt_edit_activity']);
    $indicator = mysqli_real_escape_string($con, $_POST['txt_edit_indicator']);
    $fullname = mysqli_real_escape_string($con, $_POST['txt_edit_fullname']);
    $sex = mysqli_real_escape_string($con, $_POST['txt_edit_sex']);
    $contact = mysqli_real_escape_string($con, $_POST['txt_edit_contact']);
    $email = mysqli_real_escape_string($con, $_POST['txt_edit_email']);
    $mode = mysqli_real_escape_string($con, $_POST['txt_edit_mode']);
    $agency = mysqli_real_escape_string($con, $_POST['txt_edit_agency']);
    $sector = mysqli_real_escape_string($con, $_POST['txt_edit_sector']);
    $project = mysqli_real_escape_string($con, $_POST['txt_edit_project']);
    $person = mysqli_real_escape_string($con, $_POST['txt_edit_person']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks']);

    $update_query = mysqli_query($con, "UPDATE tblparticipant SET 
         start = '$start', 
         end = '$end', 
         activity = '$activity', 
         indicator = '$indicator', 
         fullname = '$fullname', 
         sex = '$sex', 
         contact = '$contact', 
         email = '$email', 
         mode = '$mode', 
         agency = '$agency', 
         sector = '$sector', 
         project = '$project', 
         person = '$person', 
         remarks = '$remarks' 
        WHERE id = '$id'") or die('Error: ' . mysqli_error($con));

    if (isset($_SESSION['role'])) {
        $action = 'Updated Item ' . $item_no;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action . "')");
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
            $fullnameQuery = mysqli_query($con, "SELECT fullname FROM tblparticipant WHERE id = '$value'");
            $fullnameRow = mysqli_fetch_assoc($fullnameQuery);
            $fullnameName = $fullnameRow['fullname'];

            $delete_query = mysqli_query($con, "DELETE FROM tblparticipant WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
            // Logging the deletion of each item
            if (isset($_SESSION['role'])) {
                $action = 'Deleted Item: ' . $fullnameName;  // Log the deleted activity name
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
