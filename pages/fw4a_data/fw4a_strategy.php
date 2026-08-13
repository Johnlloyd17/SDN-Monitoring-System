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

     <!-- Right side column. Contains the navbar and content of the page -->
     <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="fwfa.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                        <p class="header-address">Strategy</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                </div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
</div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Internet Access Distribution
                        </div>
        <div class="panel-body">
        <table id="table1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Municipality/City</th> <!-- No center alignment for Municipality/City -->
                <th class="text-center">PICS MUN</th>
                <th class="text-center">PICS-PP</th>
                <th class="text-center">PICS-SUC</th>
                <th class="text-center">RIS-WISPS</th>
                <th class="text-center">RIS-PICS MUN</th>
                <th class="text-center">CoRe-FW4A _ UNDP-VSAT</th>
                <th class="text-center">CoRe-FW4A _ Phase 4</th>
                <th class="text-center">Total</th> <!-- New Column for Row Total -->
            </tr>
        </thead>
        <tbody>
            <?php
            // Initialize an array to store column totals for the first table
            $column_totals_1 = [
                'PICS MUN' => 0,
                'PICS-PP' => 0,
                'PICS-SUC' => 0,
                'RIS-WISPS' => 0,
                'RIS-PICS MUN' => 0,
                'CoRe-FW4A _ UNDP-VSAT' => 0,
                'CoRe-FW4A _ Phase 4' => 0
            ];

            // Fetch municipalities and display project statistics
            $municipalitiesQuery = mysqli_query($con, "SELECT * FROM tbllocality ORDER BY locality");
            while ($row = mysqli_fetch_assoc($municipalitiesQuery)) {
                $locality = $row['locality'];
                $row_total = 0;  // Variable to calculate the row total
                
                echo '<tr>';
                echo '<td>' . $locality . '</td>';  // No centering for locality
                
                // Loop through each project and display counts for each
                $projects = ['PICS MUN', 'PICS-PP', 'PICS-SUC', 'RIS-WISPS', 'RIS-PICS MUN', 'CoRe-FW4A _ UNDP-VSAT', 'CoRe-FW4A _ Phase 4'];
                foreach ($projects as $project) {
                    $q = mysqli_query($con, "SELECT COUNT(*) AS total FROM tblfwfa WHERE locality = '$locality' AND strategy = '$project'");
                    $result = mysqli_fetch_assoc($q);
                    $project_total = $result['total'];
                    echo '<td class="text-center">' . $project_total . '</td>';  // Centered values

                    // Update column totals for the first table
                    $column_totals_1[$project] += $project_total;
                    $row_total += $project_total;  // Add to row total
                }

                // Display row total
                echo '<td class="text-center">' . $row_total . '</td>';  // Centered row total
                echo '</tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-left">Total</th> <!-- Left-align the "Total" in the first column -->
                <?php
                // Output column totals for the first table, these are centered
                foreach ($projects as $project) {
                    echo '<th class="text-center">' . $column_totals_1[$project] . '</th>';
                }
                ?>
                <th class="text-center"></th> <!-- Empty cell for the last column -->
            </tr>
        </tfoot>
    </table>
</div>

<!-- Second Table (Copy of the First Table) -->
<div class="panel-body">
    <table id="table2" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Site Type</th> <!-- No center alignment for Site Type -->
                <th class="text-center">PICS MUN</th>
                <th class="text-center">PICS-PP</th>
                <th class="text-center">PICS-SUC</th>
                <th class="text-center">RIS-WISPS</th>
                <th class="text-center">RIS-PICS MUN</th>
                <th class="text-center">CoRe-FW4A _ UNDP-VSAT</th>
                <th class="text-center">CoRe-FW4A _ Phase 4</th>
                <th class="text-center">Total</th> <!-- New Column for Row Total -->
            </tr>
        </thead>
        <tbody>
            <?php
            // Initialize an array to store column totals for the second table
            $column_totals_2 = [
                'PICS MUN' => 0,
                'PICS-PP' => 0,
                'PICS-SUC' => 0,
                'RIS-WISPS' => 0,
                'RIS-PICS MUN' => 0,
                'CoRe-FW4A _ UNDP-VSAT' => 0,
                'CoRe-FW4A _ Phase 4' => 0
            ];

            // Fetch site types and display project statistics for the second table
            $municipalitiesQuery = mysqli_query($con, "SELECT * FROM tbltype ORDER BY type");
            while ($row = mysqli_fetch_assoc($municipalitiesQuery)) {
                $type = $row['type'];
                $row_total = 0;  // Variable to calculate the row total
                
                echo '<tr>';
                echo '<td>' . $type . '</td>';  // No centering for site type
                
                // Loop through each project and display counts for each
                foreach ($projects as $project) {
                    $q = mysqli_query($con, "SELECT COUNT(*) AS total FROM tblfwfa WHERE type = '$type' AND strategy = '$project'");
                    $result = mysqli_fetch_assoc($q);
                    $project_total = $result['total'];
                    echo '<td class="text-center">' . $project_total . '</td>';  // Centered values

                    // Update column totals for the second table
                    $column_totals_2[$project] += $project_total;
                    $row_total += $project_total;  // Add to row total
                }

                // Display row total
                echo '<td class="text-center">' . $row_total . '</td>';  // Centered row total
                echo '</tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-left">Total</th> <!-- Left-align the "Total" in the first column -->
                <?php
                // Output column totals for the second table, these are centered
                foreach ($projects as $project) {
                    echo '<th class="text-center">' . $column_totals_2[$project] . '</th>';
                }
                ?>
                <th class="text-center"></th> <!-- Empty cell for the last column -->
            </tr>
        </tfoot>
    </table>
</div>


<!-- DataTables Initialization -->
<script>
    $(document).ready(function() {
        // Initialize the first DataTable
        $('#table1').DataTable({
            "paging": true,       // Enable pagination
            "searching": true,    // Enable searching
            "ordering": true,     // Enable column sorting
            "lengthChange": true, // Allow changing the number of entries per page
            "info": true,         // Show information (e.g., "Showing 1 to 10 of 50 entries")
            "autoWidth": false    // Disable auto width for the table columns
        });

        // Initialize the second DataTable
        $('#table2').DataTable({
            "paging": true,       // Enable pagination
            "searching": true,    // Enable searching
            "ordering": true,     // Enable column sorting
            "lengthChange": true, // Allow changing the number of entries per page
            "info": true,         // Show information (e.g., "Showing 1 to 10 of 50 entries")
            "autoWidth": false    // Disable auto width for the table columns
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

    $(function() {
        $("#table").dataTable({
           "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,3 ] } ],"aaSorting": []
        });
    });
            

</script>

<style>
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
            .header-title {
            display: flex;
            align-items: center; /* Aligns items vertically */
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
        .header-date-time {
            font-size: 16px; /* Adjust font size as needed */
            color: #555; /* Optional: Change color for better visibility */
            margin-left: auto; /* Push the date/time to the right */
        }
        /* Other styles remain unchanged */
    </style>
    </body>
</html>