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
        <?php
        include "../connection.php";
        include('../header.php');
        ?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <?php include('../sidebar-left.php'); ?>

            <aside class="right-side">
                <section class="content-header">
                    <div class="header-title">
                        <img src="report.png" alt="Logo" class="header-logo" />
                        <div class="header-info">
                            <h3 style="color: darkwhite; font-weight: bold;">Procurement</h3>
                            <p class="header-address">......................</p>
                        </div>
                        <div class="header-date-time" id="dateTime"></div>
                    </div>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="box">
                            <div class="box-header">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <br>



                                    <div class="box-body table-responsive">

                                        <!------------------------------------------------ ADD CONTENT IN HERE ------------------------------------------------>

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                DataTables
                                            </div>

                                            <div style="padding:10px; display: flex; justify-content: space-between;">
                                                <div>
                                                    <a href="purchase_request.php" class="btn btn-primary btn-sm" style="color: white;"><i class="fa fa-user-plus" aria-hidden="true"></i> Create Purchase Request</a>
                                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"> Delete</button>




                                                    <!---------------------------------------------- UNCOMMENT THIS LATER  ---------------------------------------------->
                                                    <!-- <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Create Purchase Request</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>

                                                    <?php } elseif ($_SESSION['username'] === 'cybersecuritysdn') { ?>

                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    <?php } ?> -->
                                                    <!----------------------------------------------/ UNCOMMENT THIS LATER  ---------------------------------------------->

                                                </div>
                                                <div>
                                                    <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                        <!-- Import Button -->
                                                        <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i> Import</button>
                                                        <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
                                                        <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload" aria-hidden="true"></i> Export</button>

                                                    <?php } elseif ($_SESSION['username'] === 'cybersecuritysdn') { ?>
                                                        <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i> Import</button>
                                                        <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
                                                        <!-- Export Button -->
                                                        <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload" aria-hidden="true"></i> Export</button>
                                                    <?php } ?>
                                                </div>
                                                
                                            </div>

                                            <div class="box-body table-responsive">
                                                <form method="post">
                                                    <table id="table" class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>

                                                                <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)" /></th>
                                                                <th>#</th>

                                                                <th>Product Code</th>
                                                                <th>Item Description</th>
                                                                <th>UOM</th>
                                                                <th>Unity Cost</th>
                                                                <th>Quantity</th>
                                                                <th>Remarks</th>




                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>


                                                    <?php include "../deleteModal.php"; ?>

                                                </form>
                                            </div><!-- /.box-body -->


                                        </div><!-- /.box -->

                                        <!------------------------------------------------ ADD CONTENT IN HERE ------------------------------------------------>



                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                </section>
                <!-- /.content -->
            </aside>
            <!-- /.right-side -->
        </div>
        <!-- ./wrapper -->

        <!-- jQuery 2.0.2 -->
    <?php
}
include "../footer.php";
    ?>

    <script type="text/javascript">
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
            font-size: 16px;
            /* Adjust font size as needed */
            color: #555;
            /* Optional: Change color for better visibility */
            margin-left: auto;
            /* Push the date/time to the right */
        }

        .header-title {
            display: flex;
            align-items: center;
            /* Align items vertically */
        }

        .header-logo {
            height: 55px;
            /* Adjust the size as needed */
            width: auto;
            /* Maintain aspect ratio */
            margin-right: 10px;
            /* Space between logo and title */
        }

        .header-info {
            display: flex;
            flex-direction: column;
            /* Stack title and address vertically */
        }

        h3 {
            margin: 0;
            /* Remove default margin */
            font-weight: 600;
            /* Set to semi-bold */
        }

        .header-address {
            margin: 0;
            /* Remove default margin */
            font-size: 14px;
            /* Adjust font size as needed */
            color: #555;
            /* Optional: Change color for better visibility */
        }
    </style>
    </body>

</html>