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
    <!-- header logo: style can be found in header.less -->
    <?php 
    include "../connection.php";
    include('../header.php');
    ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <?php include('../sidebar-left.php'); ?>

        <!-- Right side column. Contains the navbar and content of the page -->
       <!-- Right side column. Contains the navbar and content of the page -->
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Activity Logs</h1>
        <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
    </section>
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        Statistics
                                    </div>
                                   
                  
                        <div class="box-body table-responsive">
                            <form method="post">
                                <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch logs including user information
                                        $squery = mysqli_query($con, "SELECT * FROM tbllogs ORDER BY logdate DESC");
                                        while ($row = mysqli_fetch_array($squery)) {
                                            echo '
                                            <tr>
                                                <td>' . htmlspecialchars($row['user']) . '</td>
                                                <td>' . htmlspecialchars($row['logdate']) . '</td>
                                                <td>' . htmlspecialchars($row['action']) . '</td>
                                            </tr>
                                            ';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </form>
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div><!-- /.row -->
            </section><!-- /.content -->
        </aside><!-- /.right-side -->
    </div><!-- ./wrapper -->
    <!-- jQuery 2.0.2 -->
    <?php include "../footer.php"; ?>
    <script type="text/javascript">
        $(function () {
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
     .header-date-time {
        font-size: 16px; /* Adjust font size as needed */
        color: #555; /* Optional: Change color for better visibility */
        margin-left: 20px; /* Optional: Add some space between title and date/time */
    }
</style>
</body>
</html>
