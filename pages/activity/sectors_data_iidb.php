<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else
    ob_start();
    include('../head_css.php');
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
                    <img src="img/icons/sector.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkwhite; font-weight: bold;">Total Sectors</h3>
                        <p class="header-address">Total Counts of Sectors (Distinct)</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div>
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
                                                        <label for="sectorSelect">Select Sectors</label>
                                                        <select id="sectorSelect" name="sector" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Sectors</option>
                                                            <?php
                                                            // Fetching distinct municipalities
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT sector FROM tblactivity WHERE sector != '' and project = 'IIDB'");
                                                            while ($sector = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $sector['sector'] . '"' . (isset($_POST['sector']) && $_POST['sector'] == $sector['sector'] ? ' selected' : '') . '>' . $sector['sector'] . '</option>';
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
                                                            $yearQuery = "SELECT DISTINCT YEAR(start) AS year FROM tblactivity WHERE sector != '' AND project = 'IIDB'";
                                                            if (isset($_POST['sector']) && $_POST['sector'] != '') {
                                                                $sector = mysqli_real_escape_string($con, $_POST['sector']);
                                                                $yearQuery .= " AND sector = '$sector'";
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

                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="monthSelect">Select Month</label>
                                                        <select id="monthSelect" name="month" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Months</option>
                                                            <?php
                                                            // Fetching distinct months based on selected filters
                                                            $monthQuery = "SELECT DISTINCT MONTH(start) AS month FROM tblactivity WHERE sector != '' AND project = 'IIDB'";
                                                            if (isset($_POST['sector']) && $_POST['sector'] != '') {
                                                                $sector = mysqli_real_escape_string($con, $_POST['sector']);
                                                                $monthQuery .= " AND sector = '$sector'";
                                                            }
                                                            if (isset($_POST['year']) && $_POST['year'] != '') {
                                                                $year = mysqli_real_escape_string($con, $_POST['year']);
                                                                $monthQuery .= " AND YEAR(start) = '$year'";
                                                            }
                                                            $monthQuery .= " ORDER BY month ASC";
                                                            $monthResult = mysqli_query($con, $monthQuery);
                                                            if ($monthResult) {
                                                                while ($row = mysqli_fetch_assoc($monthResult)) {
                                                                    $monthName = date("F", mktime(0, 0, 0, $row['month'], 10));
                                                                    echo '<option value="' . $row['month'] . '"' . (isset($_POST['month']) && $_POST['month'] == $row['month'] ? ' selected' : '') . '>' . $monthName . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching months: " . mysqli_error($con);
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="box-body table-responsive 
                                    <form method="post">
                                        <table id="table" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                    <th>No.</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Bureau</th>
                                                    <th>Project</th>
                                                    <th>Indicator</th>
                                                    <th>Activity Name</th>
                                                    <th>Training Venue</th>
                                                    <th>Municipality/City</th>
                                                    <th>Barangay</th>
                                                    <th>District</th>
                                                    <th>Requesting Agency</th>
                                                    <th>Mode of Implementation</th>
                                                    <th>Target Sector</th>
                                                    <th>Responsible Person</th>
                                                    <th>Name of Resource Person</th>
                                                    <th>No. of Participants</th>
                                                    <th>No. of Completers</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th>Approved Activity Design</th>
                                                    <th>Link to MOVs</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                             $counter = 1;  // Initialize counter
                                             $sectorFilter = isset($_POST['sector']) ? $_POST['sector'] : '';
                                             $yearFilter = isset($_POST['year']) ? $_POST['year'] : '';
                                             $monthFilter = isset($_POST['month']) ? $_POST['month'] : '';


                                             // Dynamic query based on selected filters
                                             $tableQuery = "SELECT * FROM tblactivity WHERE sector != '' and project = 'IIDB'";
                                             
                                             if ($sectorFilter) {
                                                 $tableQuery .= " AND sector = '" . mysqli_real_escape_string($con, $sectorFilter) . "'";
                                             }
                                             if ($yearFilter) {
                                                $tableQuery .= " AND YEAR(start) = '" . mysqli_real_escape_string($con, $yearFilter) . "'";
                                            }
                                            
                                            if ($monthFilter) {
                                                $tableQuery .= " AND MONTH(start) = '" . mysqli_real_escape_string($con, $monthFilter) . "'";
                                            }
                                             
                                            
                                             
                                             $tableQuery .= " ORDER BY start DESC";
                                             $result = mysqli_query($con, $tableQuery);

                                             if (!$result) {
                                                 die('Error: ' . mysqli_error($con));
                                             }
                                             
                                             while ($row = mysqli_fetch_assoc($result)) {
                                                 echo '
                                                    <tr>
                                                        <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $row['id'] . '" /></td>
                                                        <td>' . $counter++ . '</td>
                                                        <td>' . $row['start'] . '</td>
                                                        <td>' . $row['end'] . '</td>
                                                        <td>' . $row['project'] . '</td>
                                                        <td>' . $row['subproject'] . '</td>
                                                                        <td>' . $row['indicator'] . '</td>
                                                        <td>' . $row['activity'] . '</td>
                                                        <td>' . $row['training'] . '</td>
                                                        <td>' . $row['municipality'] . '</td>
                                                        <td>' . $row['barangay'] . '</td>
                                                        <td>' . $row['district'] . '</td>
                                                        <td>' . $row['agency'] . '</td>
                                                        <td>' . $row['mode'] . '</td>
                                                        <td>' . $row['sector'] . '</td>
                                                        <td>' . $row['person'] . '</td>
                                                        <td>' . $row['resource'] . '</td>
                                                        <td>' . $row['participants'] . '</td>
                                                        <td>' . $row['completers'] . '</td>
                                                        <td>' . $row['male'] . '</td>
                                                        <td>' . $row['female'] . '</td>
                                                        <td>' . $row['approved'] . '</td>
                                                        <td>' . $row['mov'] . '</td>
                                                        <td>' . $row['remarks'] . '</td>
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
        /* Other styles remain unchanged */
        
            </style>
    </body>
</html>