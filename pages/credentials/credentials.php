<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!DOCTYPE html>
<html>
<?php

if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
?>
<body class="skin-black">
    <!-- header logo: style can be found in header.less -->
    <?php
    include "../connection.php";
    ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <?php include('../sidebar-left.php'); ?>
<!-- Right side column. Contains the navbar and content of the page -->
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">User Credentials</h1>
        <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
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

                                    <div style="padding:10px;">
                                        <?php require_once __DIR__ . '/credentials_rows.php'; ?>
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Credential</button>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
                                    </div>

                                <div class="box-body table-responsive">
                                    <table id="table" class="table table-bordered table-striped">
                                        <thead>
                                            <?php echo cred_render_headers(cred_can_manage()); ?>
                                        </thead>
                                        <tbody>
                                            <?php echo cred_render_rows(cred_fetch_rows($con), cred_can_manage()); ?>
                                        </tbody>
                                    </table>

                                    <?php include "edit_modal.php"; ?>

                                    <?php include "../deleteModal.php"; ?>

                                    <?php include "add_modal.php"; ?>

                    </div>   <!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <?php }
        include "../scripts.php";
        ?>
<?php include "credentials_list_js.php"; ?>
<script type="text/javascript">
    $(function() {
        if ($.fn.DataTable.isDataTable('#table')) {
            $('#table').DataTable().destroy();
        }
        $("#table").dataTable({
           "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,3 ] } ],"aaSorting": []
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