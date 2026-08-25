<?php if (!isset($con)) include "../connection.php";
// Add Item
if (isset($_POST['btn_add'])) {
    $province = mysqli_real_escape_string($con, $_POST['txt_province']);
    $district = mysqli_real_escape_string($con, $_POST['txt_district']);
    $municipality = mysqli_real_escape_string($con, $_POST['txt_municipality']);
    $lgu = mysqli_real_escape_string($con, $_POST['txt_lgu']);
    $class = mysqli_real_escape_string($con, $_POST['txt_class']);
    $system = mysqli_real_escape_string($con, $_POST['txt_system']);
    $action = mysqli_real_escape_string($con, $_POST['txt_action']);
    $businessyn = mysqli_real_escape_string($con, $_POST['txt_businessyn']);
    $businessstatus = mysqli_real_escape_string($con, $_POST['txt_businessstatus']);
    $barangayyn = mysqli_real_escape_string($con, $_POST['txt_barangayyn']);
    $barangaystatus = mysqli_real_escape_string($con, $_POST['txt_barangaystatus']);
    $buildingyn = mysqli_real_escape_string($con, $_POST['txt_buildingyn']);
    $buildingstatus = mysqli_real_escape_string($con, $_POST['txt_buildingstatus']);
    $workingyn = mysqli_real_escape_string($con, $_POST['txt_workingyn']);
    $workingstatus = mysqli_real_escape_string($con, $_POST['txt_workingstatus']);
    $bfpyn = mysqli_real_escape_string($con, $_POST['txt_bfpyn']);
    $bplyn = mysqli_real_escape_string($con, $_POST['txt_bplyn']);
    $bplstatus = mysqli_real_escape_string($con, $_POST['txt_bplstatus']);
    $ecedulayn = mysqli_real_escape_string($con, $_POST['txt_ecedulayn']);
    $ecedulastatus = mysqli_real_escape_string($con, $_POST['txt_ecedulastatus']);
    $elcryn = mysqli_real_escape_string($con, $_POST['txt_elcryn']);
    $elcrstatus = mysqli_real_escape_string($con, $_POST['txt_elcrstatus']);
    $enewsyn = mysqli_real_escape_string($con, $_POST['txt_enewsyn']);
    $enewsstatus = mysqli_real_escape_string($con, $_POST['txt_enewsstatus']);
    $remark = mysqli_real_escape_string($con, $_POST['txt_remark']);  // Capture remark field

    // Log activity if user role exists
    if (isset($_SESSION['role'])) {
        $action_log = 'Added Item: ' . $lgu;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action_log . "')");
    }

    // Insert query with remark field
    $query = "INSERT INTO tblbpls (
        province, district, municipality, lgu, class, system, action, businessyn, businessstatus, 
        barangayyn, barangaystatus, buildingyn, buildingstatus, workingyn, workingstatus, 
        bfpyn, bplyn, bplstatus, ecedulayn, ecedulastatus, elcryn, elcrstatus, enewsyn, enewsstatus, remark
    ) VALUES (
        '$province', '$district', '$municipality', '$lgu', '$class', '$system', '$action', '$businessyn', '$businessstatus', 
        '$barangayyn', '$barangaystatus', '$buildingyn', '$buildingstatus', '$workingyn', '$workingstatus', 
        '$bfpyn', '$bplyn', '$bplstatus', '$ecedulayn', '$ecedulastatus', '$elcryn', '$elcrstatus', '$enewsyn', '$enewsstatus', '$remark'
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
    $province = mysqli_real_escape_string($con, $_POST['txt_edit_province']);
    $district = mysqli_real_escape_string($con, $_POST['txt_edit_district']);
    $municipality = mysqli_real_escape_string($con, $_POST['txt_edit_municipality']);
    $lgu = mysqli_real_escape_string($con, $_POST['txt_edit_lgu']);
    $class = mysqli_real_escape_string($con, $_POST['txt_edit_class']);
    $system = mysqli_real_escape_string($con, $_POST['txt_edit_system']);
    $action = mysqli_real_escape_string($con, $_POST['txt_edit_action']);
    $businessyn = mysqli_real_escape_string($con, $_POST['txt_edit_businessyn']);
    $businessstatus = mysqli_real_escape_string($con, $_POST['txt_edit_businessstatus']);
    $barangayyn = mysqli_real_escape_string($con, $_POST['txt_edit_barangayyn']);
    $barangaystatus = mysqli_real_escape_string($con, $_POST['txt_edit_barangaystatus']);
    $buildingyn = mysqli_real_escape_string($con, $_POST['txt_edit_buildingyn']);
    $buildingstatus = mysqli_real_escape_string($con, $_POST['txt_edit_buildingstatus']);
    $workingyn = mysqli_real_escape_string($con, $_POST['txt_edit_workingyn']);
    $workingstatus = mysqli_real_escape_string($con, $_POST['txt_edit_workingstatus']);
    $bfpyn = mysqli_real_escape_string($con, $_POST['txt_edit_bfpyn']);
    $bplyn = mysqli_real_escape_string($con, $_POST['txt_edit_bplyn']);
    $bplstatus = mysqli_real_escape_string($con, $_POST['txt_edit_bplstatus']);
    $ecedulayn = mysqli_real_escape_string($con, $_POST['txt_edit_ecedulayn']);
    $ecedulastatus = mysqli_real_escape_string($con, $_POST['txt_edit_ecedulastatus']);
    $elcryn = mysqli_real_escape_string($con, $_POST['txt_edit_elcryn']);
    $elcrstatus = mysqli_real_escape_string($con, $_POST['txt_edit_elcrstatus']);
    $enewsyn = mysqli_real_escape_string($con, $_POST['txt_edit_enewsyn']);
    $enewsstatus = mysqli_real_escape_string($con, $_POST['txt_edit_enewsstatus']);
    $remark = mysqli_real_escape_string($con, $_POST['txt_edit_remark']);  // Capture remark field

    // Update query with remark field
    $update_query = mysqli_query($con, "UPDATE tblbpls SET 
         province = '$province', 
         district = '$district', 
         municipality = '$municipality', 
         lgu = '$lgu', 
         class = '$class', 
         system = '$system', 
         action = '$action', 
         businessyn = '$businessyn', 
         businessstatus = '$businessstatus', 
         barangayyn = '$barangayyn', 
         barangaystatus = '$barangaystatus', 
         buildingyn = '$buildingyn', 
         buildingstatus = '$buildingstatus', 
         workingyn = '$workingyn', 
         workingstatus = '$workingstatus', 
         bfpyn = '$bfpyn', 
         bplyn = '$bplyn', 
         bplstatus = '$bplstatus', 
         ecedulayn = '$ecedulayn', 
         ecedulastatus = '$ecedulastatus', 
         elcryn = '$elcryn', 
         elcrstatus = '$elcrstatus', 
         enewsyn = '$enewsyn', 
         enewsstatus = '$enewsstatus',
         remark = '$remark'   
        WHERE id = '$id'") or die('Error: ' . mysqli_error($con));

    if (isset($_SESSION['role'])) {
        $action_log = 'Updated Item ' . $lgu;  // Logging activity
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
            $lguQuery = mysqli_query($con, "SELECT lgu FROM tblbpls WHERE id = '$value'");
            $lguRow = mysqli_fetch_assoc($lguQuery);
            $lguName = $lguRow['lgu'];

            $delete_query = mysqli_query($con, "DELETE FROM tblbpls WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
            // Logging the deletion of each item
            if (isset($_SESSION['role'])) {
                $action = 'Deleted Item: ' . $lguName;  // Log the deleted activity name
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
