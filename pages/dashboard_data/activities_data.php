<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
    exit();
} else {
    ob_start();
    include('../head_css.php');
}
?>
<body class="skin-black">
    <?php include "../connection.php"; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/total_activities.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>Total Activities</h3>
                        <p class="header-address">Overview of Activities Conducted</p>
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
                                        Statistics
                                    </div>
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                            <div class="row">
                                               <!-- Project Filter -->
<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="projectSelect">Select Project</label>
        <select id="projectSelect" name="project" class="form-control" onchange="document.getElementById('filterForm').submit()">
            <option value="" <?php echo empty($projectFilter) ? 'selected' : ''; ?>>All Projects</option>
            <?php
            // Fetching distinct projects dynamically based on other filters
            $projectQuery = "SELECT DISTINCT project FROM tblactivity WHERE project != ''";
            
            if (!empty($_POST['district'])) {
                $district = mysqli_real_escape_string($con, $_POST['district']);
                $projectQuery .= " AND district = '$district'";
            }
            if (!empty($_POST['status'])) {
                $status = mysqli_real_escape_string($con, $_POST['status']);
                $projectQuery .= " AND status = '$status'";
            }
            $projectResult = mysqli_query($con, $projectQuery);

            while ($project = mysqli_fetch_assoc($projectResult)) {
                echo '<option value="' . $project['project'] . '"' . (isset($_POST['project']) && $_POST['project'] == $project['project'] ? ' selected' : '') . '>' . $project['project'] . '</option>';
            }
            ?>
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
            // Fetching distinct years dynamically based on other filters
            $yearQuery = "SELECT DISTINCT YEAR(start) as year FROM tblactivity WHERE start != ''";
            
            if (!empty($_POST['project'])) {
                $project = mysqli_real_escape_string($con, $_POST['project']);
                $yearQuery .= " AND project = '$project'";
            }
            $yearQuery .= " ORDER BY year DESC";
            $yearResult = mysqli_query($con, $yearQuery);

            while ($row = mysqli_fetch_assoc($yearResult)) {
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
            // Fetching distinct months dynamically based on other filters
            $monthQuery = "SELECT DISTINCT MONTH(start) as month FROM tblactivity WHERE start != ''";
            
            if (!empty($_POST['project'])) {
                $project = mysqli_real_escape_string($con, $_POST['project']);
                $monthQuery .= " AND project = '$project'";
            }
            if (!empty($_POST['year'])) {
                $year = mysqli_real_escape_string($con, $_POST['year']);
                $monthQuery .= " AND YEAR(start) = '$year'";
            }
            $monthQuery .= " ORDER BY month ASC";
            $monthResult = mysqli_query($con, $monthQuery);

            while ($row = mysqli_fetch_assoc($monthResult)) {
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
                                        <form method="post">
                                            <table id="table" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Bureau</th>
                                                        <th>Project</th>
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
                                                   $projectFilter = isset($_POST['project']) ? mysqli_real_escape_string($con, $_POST['project']) : '';
                                                   $yearFilter = isset($_POST['year']) ? mysqli_real_escape_string($con, $_POST['year']) : '';
                                                   $monthFilter = isset($_POST['month']) ? mysqli_real_escape_string($con, $_POST['month']) : '';
                                                   
                                                   // Build the query
                                                   $query = "SELECT * FROM tblactivity WHERE 1=1";
                                                   
                                                   if ($projectFilter) {
                                                       $query .= " AND project = '$projectFilter'";
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
                                                            <td>' . $row['project'] . '</td>
                                                            <td>' . $row['subproject'] . '</td>
                                                            <td>' . $row['activity'] . '</td>
                                                            <td>' . $row['training'] . '</td>
                                                            <td>' . $row['project'] . '</td>
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php include "../edit_notif.php"; ?>
                        <?php include "../added_notif.php"; ?>
                        <?php include "../delete_notif.php"; ?>
                        <?php include "../duplicate_error.php"; ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <?php include "../footer.php"; ?>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            $(function () {
                $("#table").DataTable({
                    "aoColumnDefs": [{ "bSortable": false, "aTargets": [21] }],
                    "aaSorting": []
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
                        showToast('Data imported successfully!', 'success');
                        location.reload();
                    } else {
                        console.error('[Import Error]', data.error);
                        showToast(data.error || 'Import failed.', 'error');
                    }
                }).catch(function(error) {
                    console.error('[Import Error]', error);
                    showToast('Import failed. Check console for details.', 'error');
                });
            });

            document.getElementById('exportBtn').addEventListener('click', function() {
                window.location.href = 'export.php';
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
<style>        table {
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
            .header-title {
            display: flex;
            align-items: center; /* Align items vertically */
            justify-content: space-between; /* Space between logo/title and date/time */
            width: 100%; /* Full width for proper alignment */
        }
        .header-logo {
            height: 48px; /* Adjust the size as needed */
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
