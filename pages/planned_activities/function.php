<?php
if (isset($_POST['btn_add'])) {
    // Sanitize input data
    $start = mysqli_real_escape_string($con, $_POST['txt_start']);
    $end = mysqli_real_escape_string($con, $_POST['txt_end']);
    $project = mysqli_real_escape_string($con, $_POST['txt_project']);
    $subproject = mysqli_real_escape_string($con, $_POST['txt_subproject']);
    $indicator = mysqli_real_escape_string($con, $_POST['txt_indicator']);
    $activity = mysqli_real_escape_string($con, $_POST['txt_activity']);
    $training = mysqli_real_escape_string($con, $_POST['txt_training']);
    $municipality = mysqli_real_escape_string($con, $_POST['txt_municipality']);
    $barangay = mysqli_real_escape_string($con, $_POST['txt_barangay']);
    $district = mysqli_real_escape_string($con, $_POST['txt_district']);
    $agency = mysqli_real_escape_string($con, $_POST['txt_agency']);
    $mode = mysqli_real_escape_string($con, $_POST['txt_mode']);
    $sector = mysqli_real_escape_string($con, $_POST['txt_sector']);
    $person = mysqli_real_escape_string($con, $_POST['txt_person']);
    $resource = mysqli_real_escape_string($con, $_POST['txt_resource']);
    $participants = mysqli_real_escape_string($con, $_POST['txt_participants']);
    $completers = mysqli_real_escape_string($con, $_POST['txt_completers']);
    $male = mysqli_real_escape_string($con, $_POST['txt_male']);
    $female = mysqli_real_escape_string($con, $_POST['txt_female']);
    $approved = mysqli_real_escape_string($con, $_POST['txt_approved']);
    $mov = mysqli_real_escape_string($con, $_POST['txt_mov']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_remarks']);
    $type = mysqli_real_escape_string($con, $_POST['txt_type']);

    if (isset($_SESSION['role'])) {
        $action = 'Added Item: ' . $activity;  // Logging activity
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action')");
    }

    // Insert query with new data columns
    $query = "INSERT INTO targets_initiatives (
        start, end, project, subproject, indicator, activity, training, municipality, barangay, district, agency, mode, sector, person, resource, participants, completers, male, female, approved, mov, remarks, type
    ) VALUES (
        '$start', '$end', '$project', '$subproject', '$indicator', '$activity', '$training', '$municipality', '$barangay', '$district', '$agency', '$mode', '$sector', '$person', '$resource', '$participants', '$completers', '$male', '$female', '$approved', '$mov', '$remarks', '$type'
    )";

    $query_result = mysqli_query($con, $query) or die('Error: ' . mysqli_error($con));

    // Check if indicator has value and insert into tblactivity
    if (!empty($remarks)) {
        $activity_query = "INSERT INTO tblactivity (
            start, end, project, subproject, indicator, activity, training, municipality, barangay, district, agency, mode, sector, person, resource, participants, completers, male, female, approved, mov, remarks
        ) VALUES (
            '$start', '$end', '$project', '$subproject', '$type', '$activity', '$training', '$municipality', '$barangay', '$district', '$agency', '$mode', '$sector', '$person', '$resource', '$participants', '$completers', '$male', '$female', '$approved', '$mov', '$remarks'
        )";
        mysqli_query($con, $activity_query) or die('Error: ' . mysqli_error($con));
    }

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
    $start = mysqli_real_escape_string($con, $_POST['txt_edit_start']);
    $end = mysqli_real_escape_string($con, $_POST['txt_edit_end']);
    $project = mysqli_real_escape_string($con, $_POST['txt_edit_project']);
    $subproject = mysqli_real_escape_string($con, $_POST['txt_edit_subproject']);
    $indicator = mysqli_real_escape_string($con, $_POST['txt_edit_indicator']);
    $activity = mysqli_real_escape_string($con, $_POST['txt_edit_activity']);
    $training = mysqli_real_escape_string($con, $_POST['txt_edit_training']);
    $municipality = mysqli_real_escape_string($con, $_POST['txt_edit_municipality']);
    $barangay = mysqli_real_escape_string($con, $_POST['txt_edit_barangay']);
    $district = mysqli_real_escape_string($con, $_POST['txt_edit_district']);
    $agency = mysqli_real_escape_string($con, $_POST['txt_edit_agency']);
    $mode = mysqli_real_escape_string($con, $_POST['txt_edit_mode']);
    $sector = mysqli_real_escape_string($con, $_POST['txt_edit_sector']);
    $person = mysqli_real_escape_string($con, $_POST['txt_edit_person']);
    $resource = mysqli_real_escape_string($con, $_POST['txt_edit_resource']);
    $participants = mysqli_real_escape_string($con, $_POST['txt_edit_participants']);
    $completers = mysqli_real_escape_string($con, $_POST['txt_edit_completers']);
    $male = mysqli_real_escape_string($con, $_POST['txt_edit_male']);
    $female = mysqli_real_escape_string($con, $_POST['txt_edit_female']);
    $approved = mysqli_real_escape_string($con, $_POST['txt_edit_approved']);
    $mov = mysqli_real_escape_string($con, $_POST['txt_edit_mov']);
    $remarks = mysqli_real_escape_string($con, $_POST['txt_edit_remarks']);
    $type = mysqli_real_escape_string($con, $_POST['txt_edit_type']);

    $update_query = mysqli_query($con, "UPDATE targets_initiatives SET 
    start = '$start', 
    end = '$end', 
    project = '$project', 
    subproject = '$subproject', 
    indicator = '$indicator',
    activity = '$activity', 
    training = '$training', 
    municipality = '$municipality', 
    barangay = '$barangay', 
    district = '$district', 
    agency = '$agency', 
    mode = '$mode', 
    sector = '$sector', 
    person = '$person', 
    resource = '$resource', 
    participants = '$participants', 
    completers = '$completers', 
    male = '$male', 
    female = '$female', 
    approved = '$approved', 
    mov = '$mov', 
    remarks = '$remarks',
    type = '$type'
        WHERE id = '$id'") or die('Error: ' . mysqli_error($con));

    // Check if indicator has value and insert into tblactivity
    if (!empty($remarks)) {
        $activity_query = "INSERT INTO tblactivity (
            start, end, project, subproject, indicator, activity, training, municipality, barangay, district, agency, mode, sector, person, resource, participants, completers, male, female, approved, mov, remarks
        ) VALUES (
            '$start', '$end', '$project', '$subproject', '$indicator', '$activity', '$training', '$municipality', '$barangay', '$district', '$agency', '$mode', '$sector', '$person', '$resource', '$participants', '$completers', '$male', '$female', '$approved', '$mov', '$remarks'
        )";
        mysqli_query($con, $activity_query) or die('Error: ' . mysqli_error($con));
    }

    if (isset($_SESSION['role'])) {
        $action = 'Updated Item ' . $activity;  // Logging activity
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
            $activityQuery = mysqli_query($con, "SELECT activity FROM targets_initiatives WHERE id = '$value'");
            $activityRow = mysqli_fetch_assoc($activityQuery);
            $activityName = $activityRow['activity'];

            $delete_query = mysqli_query($con, "DELETE FROM targets_initiatives WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
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
