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
                    <img src="icons/female_2.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkwhite; font-weight: bold;">Total Female</h3>
                        <p class="header-address">Count of Female Participants Who Attended</p>
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
                                    <form method="post">
                                        <div class="row">
                                        <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="projectSelect">Select Sector</label>
                                                        <select id="projectSelect" name="project" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Sectors</option>
                                                            <?php
                                                            $sectorQuery = "SELECT DISTINCT sector FROM tblparticipant WHERE project = 'Cybersecurity' and sex = 'Female'";
                                                            
                                                            if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                $indicator = mysqli_real_escape_string($con, $_POST['indicator']);
                                                                $sectorQuery .= " AND indicator = '$indicator'";
                                                            }
                                                            if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                $sex = mysqli_real_escape_string($con, $_POST['sex']);
                                                                $sectorQuery .= " AND sex = '$sex'";
                                                            }
                                                            $sectorsQuery = mysqli_query($con, $sectorQuery);
                                                            if ($sectorsQuery) {
                                                                while ($sector = mysqli_fetch_assoc($sectorsQuery)) {
                                                                    echo '<option value="' . htmlspecialchars($sector['sector']) . '"' . (isset($_POST['project']) && $_POST['project'] == $sector['sector'] ? ' selected' : '') . '>' . htmlspecialchars($sector['sector']) . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching sectors: " . mysqli_error($con);
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div><!-- End of sector filter -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="modeSelect">Select Mode of Implementation</label>
                                                        <select id="modeSelect" name="mode" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Modes of Implementation</option>
                                                            <?php
                                                            $modeQuery = "SELECT DISTINCT mode FROM tblparticipant WHERE project = 'Cybersecurity' and sex = 'Female'";
                                                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                $project = mysqli_real_escape_string($con, $_POST['project']);
                                                                $modeQuery .= " AND sector = '$project'";
                                                            }
                                                            $modesQuery = mysqli_query($con, $modeQuery);
                                                            if ($modesQuery) {
                                                                while ($mode = mysqli_fetch_assoc($modesQuery)) {
                                                                    echo '<option value="' . htmlspecialchars($mode['mode']) . '"' . (isset($_POST['mode']) && $_POST['mode'] == $mode['mode'] ? ' selected' : '') . '>' . htmlspecialchars($mode['mode']) . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching modes: " . mysqli_error($con);
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div><!-- End of mode filter -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="indicatorSelect">Select Indicator</label>
                                                        <select id="indicatorSelect" name="indicator" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Indicators</option>
                                                            <?php
                                                            $indicatorQuery = "SELECT DISTINCT indicator FROM tblparticipant WHERE project = 'Cybersecurity' and sex = 'Female'";
                                                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                $project = mysqli_real_escape_string($con, $_POST['project']);
                                                                $indicatorQuery .= " AND sector = '$project'";
                                                            }
                                                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                                                                $indicatorQuery .= " AND mode = '$mode'";
                                                            }
                                                            
                                                            $indicatorsQuery = mysqli_query($con, $indicatorQuery);
                                                            if ($indicatorsQuery) {
                                                                while ($indicator = mysqli_fetch_assoc($indicatorsQuery)) {
                                                                    echo '<option value="' . htmlspecialchars($indicator['indicator']) . '"' . (isset($_POST['indicator']) && $_POST['indicator'] == $indicator['indicator'] ? ' selected' : '') . '>' . htmlspecialchars($indicator['indicator']) . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching indicators: " . mysqli_error($con);
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                               

                                        </form>
                                    </div> <!-- End of panel body -->
                                </div> <!-- End of panel -->
                 
                                
                                <div class="box-body table-responsive">
                                    <form method="post">
                                        <table id="table" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                    <th>No.</th>
                                                                <th>Start Date</th>
                                                                <th>End Date</th>
                                                                <th>Activity Name</th>
                                                                <th>Indicators</th>
                                                                <th>Fullname</th>
                                                                <th>Sex</th>
                                                                <th>Contact</th>
                                                                <th>Email Address</th>
                                                                <th>Mode of Implementation</th>
                                                                <th>Agency</th>
                                                                <th>Target Sector</th>
                                                                <th>Project</th>
                                                                <th>Responsible Person</th>
                                                                <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                        $counter = 1;  // Initialize counter
                                                        $tableQuery = "SELECT * FROM tblparticipant WHERE project = 'Cybersecurity' and sex = 'Female'";
                                                        
                                                        if (isset($_POST['project']) && $_POST['project'] != '') {
                                                            $tableQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                        }
                                                        
                                                        if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                            $tableQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                        }
                                                        
                                                        if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                            $tableQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                        }
                                                       
                                                        
                                                        $tableQuery .= " ORDER BY start DESC"; // Order by start date
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
                                                                    <td>' . $row['activity'] . '</td>
                                                                    <td>' . $row['indicator'] . '</td>
                                                                    <td>' . $row['fullname'] . '</td>
                                                                    <td>' . $row['sex'] . '</td>
                                                                    <td>' . $row['contact'] . '</td>
                                                                    <td>' . $row['email'] . '</td>
                                                                    <td>' . $row['mode'] . '</td>
                                                                    <td>' . $row['agency'] . '</td>
                                                                    <td>' . $row['sector'] . '</td>
                                                                    <td>' . $row['project'] . '</td>
                                                                    <td>' . $row['person'] . '</td>
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