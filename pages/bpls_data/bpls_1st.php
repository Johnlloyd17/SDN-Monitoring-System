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
                    <img src="icons/first_district1.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkwhite; font-weight: bold;">1st District Count</h3>
                        <p class="header-address">Overview of Local Government Units in District 1</p>
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
                                        Datatables
                                    </div>
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                           
                                        </form>
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
                                                <th>City/Municipality</th>
                                                <th>LGU Name</th>
                                                <th>Class</th>
                                                <th>district Provider</th>
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
                                                 
                                                 $tableQuery = "SELECT * FROM tblbpls WHERE district = '1st District'";
                                                
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
                                                                     <td>' . $row['district'] . '</td>
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