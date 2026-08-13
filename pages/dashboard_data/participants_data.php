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
    <?php include "../connection.php"; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/total_participants.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>Total Participants</h3>
                        <p class="header-address">Overview of Participants in All Activities</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                    </div>
            
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <!-- Existing box with table -->
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        Participants Data
                                    </div>
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                            <div class="row" style="display: flex; align-items: center;">
                                                <!-- Filter by Mode -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="modeSelect">Select Mode of Implementation</label>
                                                        <select id="modeSelect" name="mode" class="form-control">
                <option value="">All Mode of Implementation</option>
                <option value="Face-to-Face" <?php echo (isset($_POST['mode']) && $_POST['mode'] == 'Face-to-Face') ? 'selected' : ''; ?>>Face-to-Face</option>
                <option value="Virtual" <?php echo (isset($_POST['mode']) && $_POST['mode'] == 'Virtual') ? 'selected' : ''; ?>>Virtual</option>
            </select>
                                                    </div>
                                                </div>

                                                <!-- Year Filter -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="yearSelect">Select Year</label>
                                                        <select id="yearSelect" name="year" class="form-control" onchange="document.getElementById('filterForm').submit()">
                                                            <option value="">Select Year</option>
                                                            <?php
                // Fetch distinct years from the database
                $yearQuery = mysqli_query($con, "SELECT DISTINCT YEAR(start) as year FROM tblparticipant WHERE start != '' ORDER BY year DESC");
                while ($row = mysqli_fetch_assoc($yearQuery)) {
                    echo '<option value="' . $row['year'] . '" ' . (isset($_POST['year']) && $_POST['year'] == $row['year'] ? 'selected' : '') . '>' . $row['year'] . '</option>';
                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Month Filter -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="monthSelect">Select Month</label>
                                                        <select id="monthSelect" name="month" class="form-control" onchange="document.getElementById('filterForm').submit()">
                                                            <option value="">Select Month</option>
                                                            <?php
                // Fetch distinct months from the database based on the selected year
                if (isset($_POST['year']) && $_POST['year'] != '') {
                    $year = mysqli_real_escape_string($con, $_POST['year']);
                    $monthQuery = mysqli_query($con, "SELECT DISTINCT MONTH(start) as month FROM tblparticipant WHERE YEAR(start) = '$year' AND start != '' ORDER BY month ASC");
                } else {
                    // If no year is selected, show all months
                    $monthQuery = mysqli_query($con, "SELECT DISTINCT MONTH(start) as month FROM tblparticipant WHERE start != '' ORDER BY month ASC");
                }

                while ($row = mysqli_fetch_assoc($monthQuery)) {
                    $monthName = date("F", mktime(0, 0, 0, $row['month'], 10));
                    echo '<option value="' . $row['month'] . '" ' . (isset($_POST['month']) && $_POST['month'] == $row['month'] ? 'selected' : '') . '>' . $monthName . '</option>';
                }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="box-body table-responsive">
                                        <table id="table" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Activity Name</th>
                                                    <th>Fullname</th>
                                                    <th>Sex</th>
                                                    <th>Contact</t>
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
                                                $modeFilter = isset($_POST['mode']) ? mysqli_real_escape_string($con, $_POST['mode']) : '';
                                                $yearFilter = isset($_POST['year']) ? mysqli_real_escape_string($con, $_POST['year']) : '';
                                                $monthFilter = isset($_POST['month']) ? mysqli_real_escape_string($con, $_POST['month']) : '';
                                                
                                                // Build query with filters
                                                $query = "SELECT * FROM tblparticipant WHERE 1=1";
                                                if ($modeFilter) {
                                                    $query .= " AND mode = '$modeFilter'";
                                                }
                                                if ($yearFilter) {
                                                    $query .= " AND YEAR(start) = '$yearFilter'";
                                                }
                                                if ($monthFilter) {
                                                    $query .= " AND MONTH(start) = '$monthFilter'";
                                                }

                                                $squery = mysqli_query($con, $query);
                                                $counter = 1;
                                                while ($row = mysqli_fetch_array($squery)) {
                                                    echo '
                                                    <tr>
                                                        <td>' . $counter++ . '</td>
                                                        <td>' . $row['start'] . '</td>
                                                        <td>' . $row['end'] . '</td>
                                                        <td>' . $row['activity'] . '</td>
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

                                    </div><!-- /.box-body -->
                                </div><!-- /.panel -->
                            </div><!-- /.col-md-12 -->
                        </div><!-- /.box -->

                        <?php include "../edit_notif.php"; ?>
                        <?php include "../added_notif.php"; ?>
                        <?php include "../delete_notif.php"; ?>
                        <?php include "../duplicate_error.php"; ?>
                    </div><!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
        <?php }
        include "../footer.php"; ?>
        <script type="text/javascript">
            $(function () {
                $("#table").dataTable({
                    "aoColumnDefs": [{ "bSortable": false, "aTargets": [13] }], "aaSorting": []
                });
                $(".select2").select2();
            });

            // Update table when mode or year/month is selected
            document.getElementById('modeSelect').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
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
            .header-title {
            display: flex;
            align-items: center; /* Align items vertically */
            justify-content: space-between; /* Space between logo/title and date/time */
            width: 100%; /* Full width for proper alignment */
        }
        .header-logo {
            height: 50px; /* Adjust the size as needed */
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
        .header-date-time {
            font-size: 16px; /* Adjust font size as needed */
            color: #555; /* Optional: Change color for better visibility */
            margin-left: auto; /* Push the date/time to the right */
        }
        /* Other styles remain unchanged */
        </style>
    </body>
</html>