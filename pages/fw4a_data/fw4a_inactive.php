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
                    <img src="icons/deactivated_2.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                        <p class="header-address">Inactive Access Points</p>
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
                                                        <label for="localitySelect">Select Locality</label>
                                                        <select id="localitySelect" name="locality" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Localities</option>
                                                            <?php
                                                            // Fetching distinct localities
                                                            $localitiesQuery = mysqli_query($con, "SELECT DISTINCT locality FROM tblfwfa WHERE locality != '' and status = 'Inactive'");
                                                            while ($locality = mysqli_fetch_assoc($localitiesQuery)) {
                                                                echo '<option value="' . htmlspecialchars($locality['locality']) . '"' . (isset($_POST['locality']) && $_POST['locality'] == $locality['locality'] ? ' selected' : '') . '>' . htmlspecialchars($locality['locality']) . '</option>';
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
                                                            // Fetching distinct barangays based on selected locality
                                                            $barangayQuery = "SELECT DISTINCT barangay FROM tblfwfa WHERE barangay != '' and status = 'Inactive'";
                                                            if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                                                $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                                                $barangayQuery .= " AND locality = '$locality'";
                                                            }
                                                            $barangayResult = mysqli_query($con, $barangayQuery);
                                                            while ($barangay = mysqli_fetch_assoc($barangayResult)) {
                                                                echo '<option value="' . htmlspecialchars($barangay['barangay']) . '"' . (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') . '>' . htmlspecialchars($barangay['barangay']) . '</option>';
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
                                                        <th>NO.</th>
                                                        <th>Locality</th>
                                                        <th>Barangay</th>
                                                        <th>District</th>
                                                        <th>Locations</th>
                                                        <th>Site Type</th>
                                                        <th>Site Code</th>
                                                        <th>Strategy</th>
                                                        <th>Status</th>
                                                        <th>Reason for Outage</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                    $counter = 1;  // Initialize counter
                                                    $localityFilter = isset($_POST['locality']) ? $_POST['locality'] : '';
                                                    $barangayFilter = isset($_POST['barangay']) ? $_POST['barangay'] : '';
                                                    
                                                    // Dynamic query based on selected filters
                                                    $tableQuery = "SELECT * FROM tblfwfa WHERE locality != '' AND status = 'Inactive'";
                                                    
                                                    if ($localityFilter) {
                                                        $tableQuery .= " AND locality = '" . mysqli_real_escape_string($con, $localityFilter) . "'";
                                                    }
                                                    
                                                    if ($barangayFilter) {
                                                        $tableQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $barangayFilter) . "'";
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
                                                                    <td>' . $row['district'] . '</td>
                                                                    <td>' . $row['locations'] . '</td>
                                                                    <td>' . $row['type'] . '</td>
                                                                    <td>' . $row['code'] . '</td>
                                                                    <td>' . $row['strategy'] . '</td>
                                                                    <td>' . $row['status'] . '</td>
                                                                    <td>' . $row['reason'] . '</td>
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