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
                    <img src="icons/total_barangays.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>Total Barangays</h3>
                        <p class="header-address">Count of Distinct Barangays</p>
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
                                        Barangay Data
                                    </div>
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                            <div class="row">
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="barangaySelect">Select Barangays</label>
                                                        <select id="barangaySelect" name="barangay" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Barangay</option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT barangay FROM tblactivity WHERE barangay != '' ORDER BY barangay ASC");
                                                            while ($barangay = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $barangay['barangay'] . '"' . (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') . '>' . $barangay['barangay'] . '</option>';
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
                                                $counter = 1;  // Initialize counter
                                                $barangayFilter = isset($_POST['barangay']) ? $_POST['barangay'] : '';
                                                $tableQuery = "SELECT * FROM tblactivity WHERE barangay != ''";
                                                if ($barangayFilter) {
                                                    $tableQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $barangayFilter) . "'";
                                                }
                                                $tableQuery .= " ORDER BY start DESC";
                                                $result = mysqli_query($con, $tableQuery);
                                                if (!$result) {
                                                    die('Error: ' . mysqli_error($con));
                                                }
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '
                                                    <tr>
                                                        <td>' . $counter++ . '</td>
                                                        <td>' . $row['start'] . '</td>
                                                        <td>' . $row['end'] . '</td>
                                                        <td>' . $row['project'] . '</td>
                                                        <td>' . $row['subproject'] . '</td>
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
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification includes removed -->
                        <!-- Modal includes removed -->
                        <!-- Add modal and functions removed -->
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <?php include "../footer.php"; ?>
    <script type="text/javascript">
        $(function () {
            $("#table").DataTable({
                "aoColumnDefs": [{ "bSortable": false, "aTargets": [21] }], // Update index if needed
                "aaSorting": []
            });
            $(".select2").select2();
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
            </style>
</body>
</html>
