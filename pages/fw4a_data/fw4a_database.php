<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
?>
<body class="skin-black">
    <!-- header logo: style can be found in header.less -->
    <?php
    include "../connection.php";
    ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <?php include('../sidebar-left.php'); ?>

        <!-- Right side column. Contains the navbar and content of the page -->
        <!-- Right side column. Contains the navbar and content of the page -->
        <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="fwfa.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                        <p class="header-address">Monitoring</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                </div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                <div class="panel panel-default">
                                <div class="panel-heading">
                                        Overview of Internet Access Points
                                    </div>
                                    <div class="panel-body">
    <form method="post" id="filterForm">
        
    <div class="row">
    <!-- LGU Penetration Rate -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../fw4a_data/fw4a_penetration.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="municipality.png" alt="Total Participants" style="width: 50px; height: 50px;">
                </span>
        <div class="info-box-content">
            <span class="info-box-text">LGU Penetration Rate</span>
            <span class="info-box-number">
                <?php
                if (isset($_POST['locality']) && $_POST['locality'] != '') {
                    $locality = mysqli_real_escape_string($con, $_POST['locality']);
                    $barangayWithWifiQuery = "SELECT COUNT(DISTINCT barangay) AS barangay_with_wifi FROM tblfwfa WHERE locality = '$locality' AND status = 'Active'";
                    $wifiResult = mysqli_query($con, $barangayWithWifiQuery);
                    $wifiCount = mysqli_fetch_assoc($wifiResult)['barangay_with_wifi'];

                    $totalBarangayQuery = "SELECT barangay_count FROM tblsdn WHERE municipality = '$locality'";
                    $totalResult = mysqli_query($con, $totalBarangayQuery);
                    $totalCount = mysqli_fetch_assoc($totalResult)['barangay_count'];

                    if ($totalCount > 0) {
                        $penetrationRate = ($wifiCount / $totalCount) * 100;
                        echo number_format($penetrationRate, 2) . '%';
                    } else {
                        echo 'N/A';
                    }
                } else {
                    $totalMunicipalitiesQuery = "SELECT COUNT(*) AS total_municipalities FROM tblsdn";
                    $totalMunicipalitiesResult = mysqli_query($con, $totalMunicipalitiesQuery);
                    $totalMunicipalitiesCount = mysqli_fetch_assoc($totalMunicipalitiesResult)['total_municipalities'];

                    $activeMunicipalitiesQuery = "SELECT COUNT(DISTINCT locality) AS active_municipalities FROM tblfwfa WHERE status = 'Active'";
                    $activeMunicipalitiesResult = mysqli_query($con, $activeMunicipalitiesQuery);
                    $activeMunicipalitiesCount = mysqli_fetch_assoc($activeMunicipalitiesResult)['active_municipalities'];

                    if ($totalMunicipalitiesCount > 0) {
                        $overallPenetrationRate = ($activeMunicipalitiesCount / $totalMunicipalitiesCount) * 100;
                        echo number_format($overallPenetrationRate, 2) . '%';
                    } else {
                        echo 'N/A';
                    }
                }
                ?></a>
            </span>
        </div>
    </div>
</div>

 <!-- Barangay Penetration Rate -->
<div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../fw4a_data/fw4a_brgy.php">
        <div class="info-box">
            <span class="info-box-icon bg-red">
                <img src="barangays.png" alt="Total Participants" style="width: 60px; height: 60px;">
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Barangay Penetration Rate</span>
                <span class="info-box-number">
                    <?php
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        echo 'N/A';
                    } else {
                        $totalBarangaysWithWifiQuery = "SELECT COUNT(DISTINCT barangay, locality) AS barangays_with_wifi FROM tblfwfa WHERE status = 'Active'";
                        $wifiCountResult = mysqli_query($con, $totalBarangaysWithWifiQuery);
                        $wifiCount = mysqli_fetch_assoc($wifiCountResult)['barangays_with_wifi'];

                        $totalBarangayCountQuery = "SELECT SUM(barangay_count) AS total_barangay_count FROM tblsdn";
                        $totalCountResult = mysqli_query($con, $totalBarangayCountQuery);
                        $totalCount = mysqli_fetch_assoc($totalCountResult)['total_barangay_count'];

                        if ($totalCount > 0) {
                            $barangayPenetrationRate = ($wifiCount / $totalCount) * 100;
                            echo number_format($barangayPenetrationRate, 2) . '%';
                        } else {
                            echo 'N/A';
                        }
                    }
                    ?>
                </span>
            </div>
        </div>
    </a>
</div>
    <!-- Active Access Points -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../fw4a_data/fw4a_active.php"><div class="info-box">
                <span class="info-box-icon bg-yellow">
                    <img src="active.png" alt="Total Participants" style="width: 50px; height: 50px;">
                </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Active Access Points</span>
                                                        <span class="info-box-number">
                                                            <?php
                                                            $strategyFilter = !empty($_POST['strategy']) ? " AND strategy = '" . mysqli_real_escape_string($con, $_POST['strategy']) . "'" : '';
                                                            $typeFilter = !empty($_POST['type']) ? " AND type = '" . mysqli_real_escape_string($con, $_POST['type']) . "'" : '';
                                                            $localityFilter = !empty($_POST['locality']) ? " AND locality = '" . mysqli_real_escape_string($con, $_POST['locality']) . "'" : '';
                                                            $barangayFilter = !empty($_POST['barangay']) ? " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'" : '';

                                                            $filterQuery = "SELECT COUNT(*) AS active_access_points FROM tblfwfa WHERE status = 'Active'" . $strategyFilter . $typeFilter . $localityFilter . $barangayFilter;
                                                            $q = mysqli_query($con, $filterQuery);
                                                            $result = mysqli_fetch_assoc($q);
                                                            echo $result['active_access_points'];
                                                            ?></a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

    <!-- Deactivated Access Points -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../fw4a_data/fw4a_deactivated.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="deacti.png" alt="Total Participants" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Deactivated Access Points</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS deactivated_access_points FROM tblfwfa WHERE status = 'Deactivated'" . $strategyFilter . $typeFilter . $localityFilter . $barangayFilter;
                    $q = mysqli_query($con, $filterQuery);
                    $result = mysqli_fetch_assoc($q);
                    echo $result['deactivated_access_points'];
                    ?></a>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="strategySelect">Select Strategy</label>
            <select id="strategySelect" name="strategy" class="form-control full-width-select" onchange="this.form.submit()">
                <option value="">All Strategy</option>
                <?php
                // Fetching distinct strategies based on selected filters
                $strategyQuery = "SELECT DISTINCT strategy FROM tblfwfa WHERE strategy != ''";
                if (isset($_POST['locality']) && $_POST['locality'] != '') {
                    $locality = mysqli_real_escape_string($con, $_POST['locality']);
                    $strategyQuery .= " AND locality = '$locality'";
                }
                if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                    $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                    $strategyQuery .= " AND barangay = '$barangay'";
                }
                if (isset($_POST['status']) && $_POST['status'] != '') {
                    $status = mysqli_real_escape_string($con, $_POST['status']);
                    $strategyQuery .= " AND status = '$status'";
                }
                if (isset($_POST['type']) && $_POST['type'] != '') {
                    $type = mysqli_real_escape_string($con, $_POST['type']);
                    $strategyQuery .= " AND type = '$type'";
                }

                $strategyQuery .= " ORDER BY strategy ASC";
                $agenciesQuery = mysqli_query($con, $strategyQuery);
                while ($strategy = mysqli_fetch_assoc($agenciesQuery)) {
                    echo '<option value="' . $strategy['strategy'] . '"' . (isset($_POST['strategy']) && $_POST['strategy'] == $strategy['strategy'] ? ' selected' : '') . '>' . $strategy['strategy'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="typeSelect">Select Type</label>
            <select id="typeSelect" name="type" class="form-control full-width-select" onchange="this.form.submit()">
                <option value="">All Type</option>
                <?php
                // Fetching distinct types based on selected filters
                $typeQuery = "SELECT DISTINCT type FROM tblfwfa WHERE type != ''";
                if (isset($_POST['locality']) && $_POST['locality'] != '') {
                    $locality = mysqli_real_escape_string($con, $_POST['locality']);
                    $typeQuery .= " AND locality = '$locality'";
                }
                if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                    $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                    $typeQuery .= " AND barangay = '$barangay'";
                }
                if (isset($_POST['status']) && $_POST['status'] != '') {
                    $status = mysqli_real_escape_string($con, $_POST['status']);
                    $typeQuery .= " AND status = '$status'";
                }
                if (isset($_POST['strategy']) && $_POST['strategy'] != '') {
                    $strategy = mysqli_real_escape_string($con, $_POST['strategy']);
                    $typeQuery .= " AND strategy = '$strategy'";
                }
                $typeQuery .= " ORDER BY type ASC";
                $agenciesQuery = mysqli_query($con, $typeQuery);
                while ($type = mysqli_fetch_assoc($agenciesQuery)) {
                    echo '<option value="' . $type['type'] . '"' . (isset($_POST['type']) && $_POST['type'] == $type['type'] ? ' selected' : '') . '>' . $type['type'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="localitySelect">Select Municipality</label>
            <select id ="localitySelect" name="locality" class="form-control full-width-select" onchange="this.form.submit()">
                <option value="">All Municipalities</option>
                <?php
                // Fetching distinct localities based on selected filters
                $localityQuery = "SELECT DISTINCT locality FROM tblfwfa WHERE locality != ''";
                if (isset($_POST['strategy']) && $_POST['strategy'] != '') {
                    $strategy = mysqli_real_escape_string($con, $_POST['strategy']);
                    $localityQuery .= " AND strategy = '$strategy'";
                }
                if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                    $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                    $localityQuery .= " AND barangay = '$barangay'";
                }
                if (isset($_POST['status']) && $_POST['status'] != '') {
                    $status = mysqli_real_escape_string($con, $_POST['status']);
                    $localityQuery .= " AND status = '$status'";
                }
                if (isset($_POST['type']) && $_POST['type'] != '') {
                    $type = mysqli_real_escape_string($con, $_POST['type']);
                    $localityQuery .= " AND type = '$type'";
                }
                $localityQuery .= " ORDER BY locality ASC";

                $localitysQuery = mysqli_query($con, $localityQuery);
                while ($locality = mysqli_fetch_assoc($localitysQuery)) {
                    echo '<option value="' . $locality['locality'] . '"' . (isset($_POST['locality']) && $_POST['locality'] == $locality['locality'] ? ' selected' : '') . '>' . $locality['locality'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="form-group">
            <label for="barangaySelect">Select Barangay</label>
            <select id="barangaySelect" name="barangay" class="form-control full-width-select" onchange="this.form.submit()">
                <option value="">All Barangay</option>
                <?php
                // Fetching distinct barangays based on selected filters in ascending order
                $barangayQuery = "SELECT DISTINCT barangay FROM tblfwfa WHERE barangay != ''";

                if (isset($_POST['locality']) && $_POST['locality'] != '') {
                    $locality = mysqli_real_escape_string($con, $_POST['locality']);
                    $barangayQuery .= " AND locality = '$locality'";
                }
                if (isset($_POST['strategy']) && $_POST['strategy'] != '') {
                    $strategy = mysqli_real_escape_string($con, $_POST['strategy']);
                    $barangayQuery .= " AND strategy = '$strategy'";
                }
                if (isset($_POST['status']) && $_POST['status'] != '') {
                    $status = mysqli_real_escape_string($con, $_POST['status']);
                    $barangayQuery .= " AND status = '$status'";
                }
                if (isset($_POST['type']) && $_POST['type'] != '') {
                    $type = mysqli_real_escape_string($con, $_POST['type']);
                    $barangayQuery .= " AND type = '$type'";
                }
                // Add ORDER BY clause to sort the results in ascending order
                $barangayQuery .= " ORDER BY barangay ASC";

                $barangaysQuery = mysqli_query($con, $barangayQuery);
                while ($barangay = mysqli_fetch_assoc($barangaysQuery)) {
                    echo '<option value="' . $barangay['barangay'] . '"' . 
                        (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') . 
                        '>' . $barangay['barangay'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

   
</div>
        </div>
    


    
                                        <div style="padding:10px; display: flex; justify-content: space-between;">
                                            <div>
                                                <?php if ($_SESSION['role'] !== 'staff') { ?>
                                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Data</button>

                                                    <?php if ($_SESSION['role'] !== 'staff') { ?>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <div>
                                                <!-- Import Button -->
                                                <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i> Import</button>
                                                <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />

                                                <!-- Export Button -->
                                                <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload" aria-hidden="true"></i> Export</button>
                                            </div>
                                        </div>
                                
                                <div class="box-body table-responsive">
                                <form method="post">
                                    <table id="table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                <th>NO.</th>
                                                <th>Locality</th>
                                                    <th>Barangay</th>
                                                    <th>Locations</th>
                                                    <th>Site Type</th>
                                                    <th>Site Code</th>
                                                    <th>Strategy</th>
                                                    <th>Status</th>
                                                    <th>Downtime Logs Link</th>
                                                    <th>Remarks</th>
                                                    <th>Contact Person</th>
                                                <th style="width: 40px !important;">Option</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                                 $counter = 1;  // Initialize counter
                                                 $localityFilter = isset($_POST['locality']) ? $_POST['locality'] : '';
                                                 $typeFilter = isset($_POST['type']) ? $_POST['type'] : '';
                                                 $barangayFilter = isset($_POST['barangay']) ? $_POST['barangay'] : '';
                                                 $strategyFilter = isset($_POST['strategy']) ? $_POST['strategy'] : '';
                                               
                                                 // Dynamic query based on selected filters
                                                 $tableQuery = "SELECT * FROM tblfwfa WHERE locality != ''";
                                                 
                                                 if ($localityFilter) {
                                                     $tableQuery .= " AND locality = '" . mysqli_real_escape_string($con, $localityFilter) . "'";
                                                 }
                                                 
                                                 if ($barangayFilter) {
                                                     $tableQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $barangayFilter) . "'";
                                                 }
                                                 if ($strategyFilter) {
                                                     $tableQuery .= " AND strategy = '" . mysqli_real_escape_string($con, $strategyFilter) . "'";
                                                 }
                                                 if ($typeFilter) {
                                                    $tableQuery .= " AND type = '" . mysqli_real_escape_string($con, $typeFilter) . "'";
                                                }
                                                
                                                 $tableQuery .= " ORDER BY locality ASC";
                                                 $result = mysqli_query($con, $tableQuery);
                                                 
                                                 if (!$result) {
                                                     die('Error: ' . mysqli_error($con));
                                                 }
                                                 
                                                 while ($row = mysqli_fetch_assoc($result)) {
                                                     echo '
                                                    
                                                                <tr>
                                                                    <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $row['id'] . '" /></td>
                                                                   <td>' . $counter++ . '</td>
                                                                    <td>' . $row['locality'] . '</td>
                                                                    <td>' . $row['barangay'] . '</td>
                                                                    <td>' . $row['locations'] . '</td>
                                                                    <td>' . $row['type'] . '</td>
                                                                    <td>' . $row['code'] . '</td>
                                                                    <td>' . $row['strategy'] . '</td>
                                                                    <td>' . $row['status'] . '</td>
                                                                    <td>' . $row['downtime'] . '</td>
                                                                    <td>' . $row['remarks'] . '</td>
                                                                    <td>' . $row['contact'] . '</td>

                                                                <td class="option-buttons">
                                                                    <div style="display: flex; gap: 5px;">
                                                                      <button class="btn btn-primary btn-sm" data-target="#editModal' . $row['id'] . '" data-toggle="modal"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button>
                                                                        <button class="btn btn-primary btn-sm" data-target="#viewModal' . $row['id'] . '" data-toggle="modal"><i class="fa fa-eye" aria-hidden="true"></i> Files</button>
                                                                    </div>
                                                                </td>
                                                                </tr>
                                                                ';

                                                                include "edit_modal.php";
                                                                include "view_modal.php";
                                                            
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>


                                    <?php include "../deleteModal.php"; ?>

                                    </form>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->

                            <?php include "../edit_notif.php"; ?>

                            <?php include "../added_notif.php"; ?>

                            <?php include "../delete_notif.php"; ?>

                            <?php include "../duplicate_error.php"; ?>

            <?php include "add_modal.php"; ?>

            <?php include "function.php"; ?>


                    </div>   <!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
        <?php }
        include "../footer.php"; ?>
 <script type="text/javascript">
    var select_all = document.getElementById("cbxMainphoto"); //select all checkbox
var checkboxes = document.getElementsByClassName("chk_deletephoto"); //checkbox items

//select all checkboxes
select_all.addEventListener("change", function(e){
    for (i = 0; i < checkboxes.length; i++) { 
        checkboxes[i].checked = select_all.checked;
    }
});

for (var i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener('change', function(e){ //".checkbox" change 
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if(this.checked == false){
            select_all.checked = false;
        }
        //check "select all" if all checkbox items are checked
        if(document.querySelectorAll('.checkbox:checked').length == checkboxes.length){
            select_all.checked = true;
        }
    });
}

                    $(function () {
                        $("#table").DataTable({
                            "aoColumnDefs": [{ "bSortable": false, "aTargets": [0] }], // Update index if needed
                            "aaSorting": [],
                            "pageLength": 10, // Set default rows per page
                            "lengthMenu": [10, 25, 50, 100] // Options for rows per page
                        });
                        $(".select2").select2();
                    });
                    document.getElementById('importBtn').addEventListener('click', function() {
                document.getElementById('importFile').click();
            });

            document.getElementById('importFile').addEventListener('change', function() {
                var formData = new FormData();
                formData.append('file', this.files[0]);

                fetch('import.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        location.reload(); // Reload the page to show updated data
                    } else {
                        alert(data.error);
                    }
                }).catch(error => console.error('Error:', error));
            });

            document.getElementById('exportBtn').addEventListener('click', function() {
    // Get the selected filter values
    var selectedLocality = document.getElementById('localitySelect').value;
    var selectedBarangay = document.getElementById('barangaySelect').value;
    var selectedType = document.getElementById('typeSelect').value; // Ensure you have a typeSelect dropdown
    var selectedStrategy = document.getElementById('strategySelect').value; // Ensure you have a strategySelect dropdown

    // Build the URL with all selected filters as GET parameters
    var exportUrl = 'export.php?' +
        'locality=' + encodeURIComponent(selectedLocality) +
        '&barangay=' + encodeURIComponent(selectedBarangay) +
        '&type=' + encodeURIComponent(selectedType) +
        '&strategy=' + encodeURIComponent(selectedStrategy);

    // Redirect to the export.php script with the filters
    window.location.href = exportUrl;
});

   
     // Function to update the date and time
     function updateDateTime() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: true 
            };
            document.getElementById('dateTime').innerText = now.toLocaleString('en-US', options);
        }

        // Update the date and time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call to display immediately
                </script>
               
               <style>
                
    .info-box-icon {
            background-color: white; /* Change the background to white */
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, .4); /* Add an inner shadow */
            border-radius: 5px; /* Optional: Adjust the border-radius if needed */
            padding: 10px; /* Optional: Add padding to make the icon fit better */
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 300px; /* Set a fixed height for the charts */
            margin-bottom: 20px;
        }
        .chart-container canvas {
            width: 100% !important; /* Ensure canvas takes up full width */
            height: 100% !important; /* Ensure canvas takes up full height */
        }
        .panel-body {
            padding: 15px; /* Add padding to the panel body */
        }
        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px; /* Adjust height as needed */
            width: 80px; /* Adjust width as needed */
            font-size: 40px; /* Adjust icon size */
        }
        .info-box-number a {
            color: inherit; /* Inherit the color from parent element */
            text-decoration: none; /* Remove underline from links */
        }
        .info-box-number a:hover {
            text-decoration: underline; /* Add underline on hover for better UX */
        }
         .header-title {
            display: flex;
            align-items: center; /* Align items vertically */
        }
        .header-logo {
            height: 55px; /* Adjust the size as needed */
            width: auto; /* Maintain aspect ratio */
            margin-right: 10px; /* Space between logo and title */
        }
        .header-info {
            display: flex;
            flex-direction: column; /* Stack title and address vertically */
        }
        h3 {
            margin: 0; /* Remove default margin */
            font-weight: 600; /* Set to semi-bold */
        }
        .header-address {
            margin: 0; /* Remove default margin */
            font-size: 14px; /* Adjust font size as needed */
            color: #555; /* Optional: Change color for better visibility */
        }
        /* Adjust table layout to auto for column width based on content */
        table {
            table-layout: auto;
            width: 100%;
        }

        /* Ensure header text wraps to two lines */
        table th {
            white-space: normal;
            text-align: center; /* Center-align headers if needed */
            word-wrap: break-word; /* Allow long words to break */
            overflow-wrap: break-word; /* Ensure long words break in modern browsers */
            max-width: 200px; /* Example max-width to limit header width */
        }

        table td {
            white-space: nowrap; /* Ensure cell text does not wrap */
        }

        /* Optional: Adjust column widths if necessary */
        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        /* Optional: Adjust specific column widths */
        table th:nth-child(1) { width: 30px; } /* Example width for checkbox column */
        table th:nth-child(2) { width: 50px; } /* Example width for No. column */
        /* Add more specific widths as needed */

   
            /* Ensure header text does not wrap */
            table th {
                white-space: nowrap;
                text-align: center; /* Center-align headers if needed */
            }

            /* Optional: Adjust column widths if necessary */
            table th, table td {
                padding: 8px;
                border: 1px solid #ddd;
            }

            /* Optional: Adjust specific column widths */
            table th:nth-child(1) { width: 30px; } /* Example width for checkbox column */
            table th:nth-child(2) { width: 50px; } /* Example width for No. column */
            /* Add more specific widths as needed */
            <style>
    /* File item container */
    .file-item {
        position: relative;
        text-align: center;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        background-color: #f9f9f9;
        width: 100%;
        height: 250px; /* Fixed height for uniformity */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease; /* Smooth transition for hover effects */
    }

    /* Thumbnail styling (uniform for all file types) */
    .file-thumbnail,
    .file-thumbnail-pdf,
    .file-thumbnail-office {
        width: 150px; /* Fixed width */
        height: 150px; /* Fixed height */
        object-fit: cover; /* Ensures images and other content fill the area */
        margin-bottom: 15px; /* Uniform space between thumbnail and filename */
        transition: transform 0.3s ease; /* Smooth transition for hover effect */
    }

    /* Hover effect only on the file thumbnail */
    .file-thumbnail:hover, 
    .file-thumbnail-pdf:hover, 
    .file-thumbnail-office:hover {
        transform: scale(1.05); /* Slight zoom on hover for thumbnails */
    }

    /* For PDFs - embed PDF into the same size container */
    .file-thumbnail-pdf {
        background-color: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40px;
        color: #444;
    }

    /* For Office files like Word, Excel, PowerPoint */
    .file-thumbnail-office {
        background-color: #e6e6e6;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40px;
        color: #444;
    }

    /* Filename styling */
    .filename {
        font-size: 12px; /* Smaller font size for filename */
        color: #333;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis; /* Truncate long filenames */
        white-space: nowrap;
        max-width: 100px; /* Limit the width for better alignment */
        text-align: center; /* Center the filename text */
        margin-bottom: 5px; /* Consistent space between filename and download icon */
    }

    /* Container for the filename and download button */
    .file-info {
        display: flex;
        align-items: center; /* Align text and icon vertically */
        justify-content: center;
        margin-top: 0; /* Remove any additional top margin */
    }

    /* Download icon styling */
    .download-btn {
        font-size: 12px; /* Smaller size for the download icon */
        color: #007bff;
        text-decoration: none;
        padding: 0 5px;
        vertical-align: middle; /* Align it with the text */
    }

    .download-btn i {
        font-size: 14px; /* Matching the icon size with filename size */
        vertical-align: middle; /* Align it with the text */
    }

    /* Checkbox styling */
    .file-item input[type="checkbox"] {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
    }
    .header-title {
            display: flex;
            align-items: center; /* Align items vertically */
            justify-content: space-between; /* Space between logo/title and date/time */
        }
        .header-date-time {
            font-size: 16px; /* Adjust font size as needed */
            color: #555; /* Optional: Change color for better visibility */
            margin-left: auto; /* Push the date/time to the right */
        }
        /* Other styles remain unchanged */
        
</style>

    </style>
    </body>
</html>