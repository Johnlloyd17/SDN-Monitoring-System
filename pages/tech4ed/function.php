<?php
if (isset($_POST['btn_add'])) {
    $region = $_POST['txt_region'];
    $province = $_POST['txt_province'];
    $district = $_POST['txt_district'];
    $municipality = $_POST['txt_municipality'];
    $barangay = $_POST['txt_barangay'];
    $street = $_POST['txt_street'];
    $location = $_POST['txt_location'];
    $cname = $_POST['txt_cname'];
    $host = $_POST['txt_host'];
    $category = $_POST['txt_category'];
    $longitude = $_POST['txt_longitude'];
    $latitude = $_POST['txt_latitude'];
    $cmanager = $_POST['txt_cmanager'];
    $cemail = $_POST['txt_cemail'];
    $cmobile = $_POST['txt_cmobile'];
    $clandline = $_POST['txt_clandline'];
    $cgender = $_POST['txt_cgender'];
    $amanager = $_POST['txt_amanager'];
    $aemail = $_POST['txt_aemail'];
    $amobile = $_POST['txt_amobile'];
    $alandline = $_POST['txt_alandline'];
    $agender = $_POST['txt_agender'];
    $launch = $_POST['txt_launch'];
    $registration = $_POST['txt_registration'];
    $operation = $_POST['txt_operation'];
    $visited = $_POST['txt_visited'];
    $desktop = $_POST['txt_desktop'];
    $laptop = $_POST['txt_laptop'];
    $printer = $_POST['txt_printer'];
    $scanner = $_POST['txt_scanner'];
    $status = $_POST['txt_status'];
    $network = $_POST['txt_network'];
    $connectivity = $_POST['txt_connectivity'];
    $speed = $_POST['txt_speed'];
    $cmtmale = $_POST['txt_cmtmale'];
    $cmtfemale = $_POST['txt_cmtfemale'];
    $straining = $_POST['txt_straining'];
    $etraining = $_POST['txt_etraining'];
    $signing = $_POST['txt_signing'];
    $partner = $_POST['txt_partner'];
    $expiration = $_POST['txt_expiration'];
    $donation = $_POST['txt_donation'];
    $datedonation = $_POST['txt_datedonation'];
    $tcms = $_POST['txt_tcms'];
    $key_one = $_POST['txt_key_one'];
    $identifier = $_POST['txt_identifier'];

    if (isset($_SESSION['role'])) {
        $action = 'Added Item ' . $region;  // Logging activity
        $iquery = mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '" . $action . "')");
    }

    $query = "INSERT INTO tbltech4ed (
        region, province, district, municipality, barangay, street, location, cname, host, category, longitude, latitude,
        cmanager, cemail, cmobile, clandline, cgender, amanager, aemail, amobile, alandline, agender, launch, registration, operation, visited,
        desktop, laptop, printer, scanner, status, network, connectivity, speed, cmtmale, cmtfemale, straining, etraining,
        signing, partner, expiration, donation, datedonation, tcms, key_one, identifier
    ) VALUES (
         '$region', '$province', '$district', '$municipality', '$barangay', '$street', '$location', '$cname', '$host', '$category', '$longitude', '$latitude',
        '$cmanager', '$cemail', '$cmobile', '$clandline', '$cgender', '$amanager', '$aemail', '$amobile', '$alandline', '$agender', '$launch', '$registration', '$operation', '$visited',
        '$desktop', '$laptop', '$printer', '$scanner', '$status', '$network', '$connectivity', '$speed', '$cmtmale', '$cmtfemale', '$straining', '$etraining',
        '$signing', '$partner', '$expiration', '$donation', '$datedonation', '$tcms', '$key_one', '$identifier'
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
    $region = $_POST['txt_edit_region'];
    $province = $_POST['txt_edit_province'];
    $district = $_POST['txt_edit_district'];
    $municipality = $_POST['txt_edit_municipality'];
    $barangay = $_POST['txt_edit_barangay'];
    $street = $_POST['txt_edit_street'];
    $location = $_POST['txt_edit_location'];
    $cname = $_POST['txt_edit_cname'];
    $host = $_POST['txt_edit_host'];
    $category = $_POST['txt_edit_category'];
    $longitude = $_POST['txt_edit_longitude'];
    $latitude = $_POST['txt_edit_latitude'];
    $cmanager = $_POST['txt_edit_cmanager'];
    $cemail = $_POST['txt_edit_cemail'];
    $cmobile = $_POST['txt_edit_cmobile'];
    $clandline = $_POST['txt_edit_clandline'];
    $cgender = $_POST['txt_edit_cgender'];
    $amanager = $_POST['txt_edit_amanager'];
    $aemail = $_POST['txt_edit_aemail'];
    $amobile = $_POST['txt_edit_amobile'];
    $alandline = $_POST['txt_edit_alandline'];
    $agender = $_POST['txt_edit_agender'];
    $launch = $_POST['txt_edit_launch'];
    $registration = $_POST['txt_edit_registration'];
    $operation = $_POST['txt_edit_operation'];
    $visited = $_POST['txt_edit_visited'];
    $desktop = $_POST['txt_edit_desktop'];
    $laptop = $_POST['txt_edit_laptop'];
    $printer = $_POST['txt_edit_printer'];
    $scanner = $_POST['txt_edit_scanner'];
    $status = $_POST['txt_edit_status'];
    $network = $_POST['txt_edit_network'];
    $connectivity = $_POST['txt_edit_connectivity'];
    $speed = $_POST['txt_edit_speed'];
    $cmtmale = $_POST['txt_edit_cmtmale'];
    $cmtfemale = $_POST['txt_edit_cmtfemale'];
    $straining = $_POST['txt_edit_straining'];
    $etraining = $_POST['txt_edit_etraining'];
    $signing = $_POST['txt_edit_signing'];
    $partner = $_POST['txt_edit_partner'];
    $expiration = $_POST['txt_edit_expiration'];
    $donation = $_POST['txt_edit_donation'];
    $datedonation = $_POST['txt_edit_datedonation'];
    $tcms = $_POST['txt_edit_tcms'];
    $key_one = $_POST['txt_edit_key_one'];
    $identifier = $_POST['txt_edit_identifier'];

    $update_query = mysqli_query($con, "UPDATE tbltech4ed SET 
        region = '$region',
province = '$province',
district = '$district',
municipality = '$municipality',
barangay = '$barangay',
street = '$street',
location = '$location',
cname = '$cname',
host = '$host',
category = '$category',
longitude = '$longitude',
latitude = '$latitude',
cmanager = '$cmanager',
cemail = '$cemail',
cmobile = '$cmobile',
clandline = '$clandline',
cgender = '$cgender',
amanager = '$amanager',
aemail = '$aemail',
amobile = '$amobile',
alandline = '$alandline',
agender = '$agender',
launch = '$launch',
registration = '$registration',
operation = '$operation',
visited = '$visited',
desktop = '$desktop',
laptop = '$laptop',
printer = '$printer',
scanner = '$scanner',
status = '$status',
network = '$network',
connectivity = '$connectivity',
speed = '$speed',
cmtmale = '$cmtmale',
cmtfemale = '$cmtfemale',
straining = '$straining',
etraining = '$etraining',
signing = '$signing',
partner = '$partner',
expiration = '$expiration',
donation = '$donation',
datedonation = '$datedonation',
tcms = '$tcms',
key_one = '$key_one',
identifier = '$identifier'
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
            $delete_query = mysqli_query($con, "DELETE FROM tbltech4ed WHERE id = '$value'") or die('Error: ' . mysqli_error($con));
            
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
