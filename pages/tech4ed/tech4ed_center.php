<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
}
?>
<body class="skin-black">
    <?php
    include "../connection.php";
    include('../header.php');
    ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/center_2.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">Tech4ED DTC</h3>
                        <p class="header-address">Total DICT Provincial Training Centers</p>
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
                                        Filters
                                    </div>
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                            <div class="row">
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="municipalitySelect">Select Municipality</label>
                                                        <select id="municipalitySelect" name="municipality" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Municipalities</option>
                                                            <?php
                                                            // Fetching distinct municipalities
                                                            $municipalitiesQuery = mysqli_query($con, "SELECT DISTINCT municipality FROM tbltech4ed WHERE municipality != '' AND category = 'DICT Provincial Training Center'");
                                                            while ($municipality = mysqli_fetch_assoc($municipalitiesQuery)) {
                                                                echo '<option value="' . htmlspecialchars($municipality['municipality']) . '"' . (isset($_POST['municipality']) && $_POST['municipality'] == $municipality['municipality'] ? ' selected' : '') . '>' . htmlspecialchars($municipality['municipality']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="barangaySelect">Select Barangay</label>
                                                        <select id="barangaySelect" name="barangay" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Barangays</option>
                                                            <?php
                                                            // Fetching distinct barangays based on selected municipality
                                                            $barangayQuery = "SELECT DISTINCT barangay FROM tbltech4ed WHERE barangay != '' AND category = 'DICT Provincial Training Center'";
                                                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                                                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                                                                $barangayQuery .= " AND municipality = '$municipality'";
                                                            }
                                                            $barangayResult = mysqli_query($con, $barangayQuery);
                                                            while ($barangay = mysqli_fetch_assoc($barangayResult)) {
                                                                echo '<option value="' . htmlspecialchars($barangay['barangay']) . '"' . (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') . '>' . htmlspecialchars($barangay['barangay']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="yearSelect">Select Year</label>
                                                        <select id="yearSelect" name="year" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Years</option>
                                                            <?php
                                                            // Fetching distinct years based on selected filters
                                                            $yearQuery = "SELECT DISTINCT YEAR(launch) AS year FROM tbltech4ed WHERE category = 'DICT Provincial Training Center'";
                                                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                                                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                                                                $yearQuery .= " AND municipality = '$municipality'";
                                                            }
                                                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                                                $yearQuery .= " AND barangay = '$barangay'";
                                                            }
                                                            $yearQuery .= " ORDER BY year DESC";
                                                            $yearResult = mysqli_query($con, $yearQuery);
                                                            if ($yearResult) {
                                                                while ($year = mysqli_fetch_assoc($yearResult)) {
                                                                    echo '<option value="' . $year['year'] . '"' . (isset($_POST['year']) && $_POST['year'] == $year['year'] ? ' selected' : '') . '>' . $year['year'] . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching years: " . mysqli_error($con);
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                               
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="box-body table-responsive">
                                        <form method="post">
                                            <table id="table" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                        <th>No.</th>
                                                    <th>Region</th>
                                                            <th>Province</th>
                                                            <th>Congressional District</th>
                                                            <th>Municipality/City</th>
                                                            <th>Barangay</th>
                                                            <th>Street Address</th>
                                                            <th>Specific Center Location</th>
                                                            <th>Center Name</th>
                                                            <th>Host</th>
                                                            <th>Category</th>
                                                            <th>Longitude</th>
                                                            <th>Latitude</th>
                                                            <th>Center Manager's Name</th>
                                                            <th>Email</th>
                                                            <th>Mobile</th>
                                                            <th>Landline</th>
                                                            <th>Gender</th>
                                                            <th>Assistant Center Manager's Name</th>
                                                            <th>Email</th>
                                                            <th>Mobile</th>
                                                            <th>Landline</th>
                                                            <th>Gender</th>
                                                            <th>Date of Launching</th>
                                                            <th>Date of Platform Registration</th>
                                                            <th>Operational Status</th>
                                                            <th>Date Last Visited</th>
                                                            <th># of functional desktop units</th>
                                                            <th># of functional laptop units</th>
                                                            <th># of functional printer</th>
                                                            <th># of functional scanner/copier</th>
                                                            <th>Status</th>
                                                            <th>Types of Network</th>
                                                            <th>Internet Connectivity</th>
                                                            <th>Internet Speed</th>
                                                            <th>CMT (# of pax) Male</th>
                                                            <th>CMT (# of pax) Female</th>
                                                            <th>Start Date of Training</th>
                                                            <th>End Date of Training</th>
                                                            <th>Date of Signing</th>
                                                            <th>Partner</th>
                                                            <th>Expiration</th>
                                                            <th>Type of Donation</th>
                                                            <th>Date of Donation</th>
                                                            <th>TCMS</th>
                                                            <th>Key</th>
                                                            <th>Identifier</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    $counter = 1;  // Initialize counter
                                                    $municipalityFilter = isset($_POST['municipality']) ? $_POST['municipality'] : '';
                                                    $barangayFilter = isset($_POST['barangay']) ? $_POST['barangay'] : '';
                                                    $yearFilter = isset($_POST['year']) ? $_POST['year'] : '';
                                                    
                                                    // Dynamic query based on selected filters
                                                    $tableQuery = "SELECT * FROM tbltech4ed WHERE category = 'DICT Provincial Training Center'";
                                                    
                                                    if ($municipalityFilter) {
                                                        $tableQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $municipalityFilter) . "'";
                                                    }
                                                    if ($barangayFilter) {
                                                        $tableQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $barangayFilter) . "'";
                                                    }
                                                    if ($yearFilter) {
                                                        $tableQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $yearFilter) . "'";
                                                    }
                                                    $tableQuery .= " ORDER BY municipality ASC";
                                                    $result = mysqli_query($con, $tableQuery);
                                                    
                                                    if (!$result) {
                                                        die('Error: ' . mysqli_error($con));
                                                    }
                                                    
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo '
                                                           <tr>
                                                                    <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $row['id'] . '" /></td>
                                                                   <td>' . $counter++ . '</td>
                                                                <td>' . $row['region'] . '</td>
                                                                <td>' . $row['province'] . '</td>
                                                                <td>' . $row['district'] . '</td>
                                                                <td>' . $row['municipality'] . '</td>
                                                                <td>' . $row['barangay'] . '</td>
                                                                <td>' . $row['street'] . '</td>
                                                                <td>' . $row['location'] . '</td>
                                                                <td>' . $row['cname'] . '</td>
                                                                <td>' . $row['host'] . '</td>
                                                                <td>' . $row['category'] . '</td>
                                                                <td>' . $row['longitude'] . '</td>
                                                                <td>' . $row['latitude'] . '</td>
                                                                <td>' . $row['cmanager'] . '</td>
                                                                <td>' . $row['cemail'] . '</td>
                                                                <td>' . $row['cmobile'] . '</td>
                                                                <td>' . $row['clandline'] . '</td>
                                                                <td>' . $row['cgender'] . '</td>
                                                                <td>' . $row['amanager'] . '</td>
                                                                <td>' . $row['aemail'] . '</td>
                                                                <td>' . $row['amobile'] . '</td>
                                                                <td>' . $row['alandline'] . '</td>
                                                                <td>' . $row['agender'] . '</td>
                                                                <td>' . $row['launch'] . '</td>
                                                                <td>' . $row['registration'] . '</td>
                                                                <td>' . $row['operation'] . '</td>
                                                                <td>' . $row['visited'] . '</td>
                                                                <td>' . $row['desktop'] . '</td>
                                                                <td>' . $row['laptop'] . '</td>
                                                                <td>' . $row['printer'] . '</td>
                                                                <td>' . $row['scanner'] . '</td>
                                                                <td>' . $row['status'] . '</td>
                                                                <td>' . $row['network'] . '</td>
                                                                <td>' . $row['connectivity'] . '</td>
                                                                <td>' . $row['speed'] . '</td>
                                                                <td>' . $row['cmtmale'] . '</td>
                                                                <td>' . $row['cmtfemale'] . '</td>
                                                                <td>' . $row['straining'] . '</td>
                                                                <td>' . $row['etraining'] . '</td>
                                                                <td>' . $row['signing'] . '</td>
                                                                <td>' . $row['partner'] . '</td>
                                                                <td>' . $row['expiration'] . '</td>
                                                                <td>' . $row['donation'] . '</td>
                                                                <td>' . $row['datedonation'] . '</td>
                                                                <td>' . $row['tcms'] . '</td>
                                                                <td>' . $row['key_one'] . '</td>
                                                                <td>' . $row['identifier'] . '</td>
                                                        </tr>
                                                        ';
                                                    }
                                                ?>
                                                </tbody>
                                            </table>
                                            <?php include "../deleteModal.php"; ?>
                                        </form>
                                    </div><!-- /.box-body -->
                                </div><!-- /.box -->
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </section><!-- /.content -->
                </aside><!-- /.right-side -->
            </div><!-- ./wrapper -->
            <?php include "../footer.php"; ?>
            <script type="text/javascript">
                $(function() {
                    $("#table").dataTable({
                        "aoColumnDefs": [{ "bSortable": false, "aTargets": [0] }],
                        "aaSorting": []
                    });
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

                .header-date-time {
                    font-size: 16px; /* Adjust font size as needed */
                    color: #555; /* Optional: Change color for better visibility */
                    margin-left: auto; /* Push the date/time to the right */
                }
            </style>
        </body>
    </html>