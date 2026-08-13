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
                    <img src="icons/municipality_dict1.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkwhite; font-weight: bold;">Total LGU Availing DICT</h3>
                        <p class="header-address">Count of Local Government Units Utilizing DICT Services</p>
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
                                                        <label for="systemSelect">Select systems</label>
                                                        <select id="systemSelect" name="system" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All system</option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT system FROM tblbpls WHERE (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($system = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $system['system'] . '"' . (isset($_POST['system']) && $_POST['system'] == $system['system'] ? ' selected' : '') . '>' . $system['system'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="businessynSelect">Select Business Permit </label>
                                                        <select id="businessynSelect" name="businessyn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Business Permit </option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT businessyn FROM tblbpls WHERE businessyn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($businessyn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $businessyn['businessyn'] . '"' . (isset($_POST['businessyn']) && $_POST['businessyn'] == $businessyn['businessyn'] ? ' selected' : '') . '>' . $businessyn['businessyn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="barangayynSelect">Select Barangay Clearance </label>
                                                        <select id="barangayynSelect" name="barangayyn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Barangay Clearance </option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT barangayyn FROM tblbpls WHERE barangayyn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($barangayyn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $barangayyn['barangayyn'] . '"' . (isset($_POST['barangayyn']) && $_POST['barangayyn'] == $barangayyn['barangayyn'] ? ' selected' : '') . '>' . $barangayyn['barangayyn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="buildingynSelect">Select Building Permit </label>
                                                        <select id="buildingynSelect" name="buildingyn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Building Permit </option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT buildingyn FROM tblbpls WHERE buildingyn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($buildingyn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $buildingyn['buildingyn'] . '"' . (isset($_POST['buildingyn']) && $_POST['buildingyn'] == $buildingyn['buildingyn'] ? ' selected' : '') . '>' . $buildingyn['buildingyn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                </div>
                                                <div class="row">
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="workingynSelect">Select Working Permit</label>
                                                        <select id="workingynSelect" name="workingyn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Working Permit</option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT workingyn FROM tblbpls WHERE workingyn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($workingyn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $workingyn['workingyn'] . '"' . (isset($_POST['workingyn']) && $_POST['workingyn'] == $workingyn['workingyn'] ? ' selected' : '') . '>' . $workingyn['workingyn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                     
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="bplynSelect">Select Business Permit and Licensing System</label>
                                                        <select id="bplynSelect" name="bplyn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Business Permit and Licensing System</option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT bplyn FROM tblbpls WHERE bplyn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($bplyn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $bplyn['bplyn'] . '"' . (isset($_POST['bplyn']) && $_POST['bplyn'] == $bplyn['bplyn'] ? ' selected' : '') . '>' . $bplyn['bplyn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="ecedulaynSelect">Select Electronic Community Tax Certificate</label>
                                                        <select id="ecedulaynSelect" name="ecedulayn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Electronic Community Tax Certificate</option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT ecedulayn FROM tblbpls WHERE ecedulayn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($ecedulayn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $ecedulayn['ecedulayn'] . '"' . (isset($_POST['ecedulayn']) && $_POST['ecedulayn'] == $ecedulayn['ecedulayn'] ? ' selected' : '') . '>' . $ecedulayn['ecedulayn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="elcrynSelect">Select Electronic Local Civil Registry </label>
                                                        <select id="elcrynSelect" name="elcryn" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Electronic Local Civil Registry </option>
                                                            <?php
                                                            // Fetching distinct requesting agencies
                                                            $agenciesQuery = mysqli_query($con, "SELECT DISTINCT elcryn FROM tblbpls WHERE elcryn != '' and (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')");
                                                            while ($elcryn = mysqli_fetch_assoc($agenciesQuery)) {
                                                                echo '<option value="' . $elcryn['elcryn'] . '"' . (isset($_POST['elcryn']) && $_POST['elcryn'] == $elcryn['elcryn'] ? ' selected' : '') . '>' . $elcryn['elcryn'] . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                        </form>
                                    </div>
                                    </div>        
                                
                
                                    <div class="box-body table-responsive">
                                        <form method="post">
                                            <table id="table" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                        <th>NO.</th>
                                                <th>Province</th>
                                                <th>Congressional District</th>
                                                <th>City/lgu</th>
                                                <th>LGU Name</th>
                                                <th>Class</th>
                                                <th>System Provider</th>
                                                <th>Remarks / Action Items</th>
                                                <th>(BP) Y/N</th>
                                                <th>(BP) Status</th>
                                                <th>Integration of (BC) Y/N</th>
                                                <th>Integration of (BC) Status</th>
                                                <th>(BPCO) Y/N</th>
                                                <th>(BPCO) Status</th>
                                                <th>(WP) Y/N</th>
                                                <th>(WP) Status</th>
                                                <th>Integration of (FSIC)</th>
                                                <th>(BPLS) Y/N</th>
                                                <th>(BPLS) Status</th>
                                                <th>(eCEDULA) Y/N</th>
                                                <th>(eCEDULA) Status</th>
                                                <th>(eLCR) Y/N</th>
                                                <th>(eLCR) Status</th>
                                                <th>eNEWS Y/N</th>
                                                <th>eNEWS Status</th>
                                                <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                 $counter = 1;  // Initialize counter
                                                 $systemFilter = isset($_POST['system']) ? $_POST['system'] : '';
                                                 $businessynFilter = isset($_POST['businessyn']) ? $_POST['businessyn'] : '';
                                                 $barangayynFilter = isset($_POST['barangayyn']) ? $_POST['barangayyn'] : '';
                                                 $buildingynFilter = isset($_POST['buildingyn']) ? $_POST['buildingyn'] : '';
                                                 $workingynFilter = isset($_POST['workingyn']) ? $_POST['workingyn'] : '';
                                                 $bplynFilter = isset($_POST['bplyn']) ? $_POST['bplyn'] : '';
                                                 $ecedulaynFilter = isset($_POST['ecedulayn']) ? $_POST['ecedulayn'] : '';
                                                 $elcrynFilter = isset($_POST['elcryn']) ? $_POST['elcryn'] : '';
                                                
                                                 $tableQuery = "SELECT * FROM tblbpls WHERE (system = 'DICT (eLGU BPLS)' OR system = 'DICT (eLGU)')";
                                                 if ($systemFilter) {
                                                     $tableQuery .= " AND system = '" . mysqli_real_escape_string($con, $systemFilter) . "'";
                                                 }
                                                 if ($businessynFilter) {
                                                    $tableQuery .= " AND businessyn = '" . mysqli_real_escape_string($con, $businessynFilter) . "'";
                                                }
                                                if ($barangayynFilter) {
                                                    $tableQuery .= " AND barangayyn = '" . mysqli_real_escape_string($con, $barangayynFilter) . "'";
                                                }
                                                if ($buildingynFilter) {
                                                    $tableQuery .= " AND buildingyn = '" . mysqli_real_escape_string($con, $buildingynFilter) . "'";
                                                }

                                                if ($workingynFilter) {
                                                    $tableQuery .= " AND workingyn = '" . mysqli_real_escape_string($con, $workingynFilter) . "'";
                                                }
                                                if ($bplynFilter) {
                                                   $tableQuery .= " AND bplyn = '" . mysqli_real_escape_string($con, $bplynFilter) . "'";
                                               }
                                               if ($ecedulaynFilter) {
                                                   $tableQuery .= " AND ecedulayn = '" . mysqli_real_escape_string($con, $ecedulaynFilter) . "'";
                                               }
                                               if ($elcrynFilter) {
                                                   $tableQuery .= " AND elcryn = '" . mysqli_real_escape_string($con, $elcrynFilter) . "'";
                                               }
                                                 $tableQuery .= " ORDER BY lgu ASC";
                                                 $result = mysqli_query($con, $tableQuery);
                                                 if (!$result) {
                                                     die('Error: ' . mysqli_error($con));
                                                 }
                                                 while ($row = mysqli_fetch_assoc($result)) {
                                                     echo '
                                                     
                                                                 <tr>
                                                                     <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $row['id'] . '" /></td>
                                                                    <td>' . $counter++ . '</td>
                                                                     <td>' . $row['province'] . '</td>
                                                                     <td>' . $row['district'] . '</td>
                                                                     <td>' . $row['municipality'] . '</td>
                                                                     <td>' . $row['lgu'] . '</td>
                                                                     <td>' . $row['class'] . '</td>
                                                                     <td>' . $row['system'] . '</td>
                                                                     <td>' . $row['action'] . '</td>
                                                                     <td>' . $row['businessyn'] . '</td>
                                                                     <td>' . $row['businessstatus'] . '</td>
                                                                     <td>' . $row['barangayyn'] . '</td>
                                                                     <td>' . $row['barangaystatus'] . '</td>
                                                                     <td>' . $row['buildingyn'] . '</td>
                                                                     <td>' . $row['buildingstatus'] . '</td>
                                                                     <td>' . $row['workingyn'] . '</td>
                                                                     <td>' . $row['workingstatus'] . '</td>
                                                                     <td>' . $row['bfpyn'] . '</td>
                                                                     <td>' . $row['bplyn'] . '</td>
                                                                     <td>' . $row['bplstatus'] . '</td>
                                                                     <td>' . $row['ecedulayn'] . '</td>
                                                                     <td>' . $row['ecedulastatus'] . '</td>
                                                                     <td>' . $row['elcryn'] . '</td>
                                                                     <td>' . $row['elcrstatus'] . '</td>
                                                                     <td>' . $row['enewsyn'] . '</td>
                                                                     <td>' . $row['enewsstatus'] . '</td>
                                                                     <td>' . $row['remark'] . '</td>
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