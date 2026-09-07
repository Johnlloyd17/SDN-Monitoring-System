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
                            <h3 style="color: darkwhite; font-weight: bold;">Purchase Request</h3>
                            <p class="header-address">......................</p>
                        </div>
                        <div class="header-date-time" id="dateTime"></div>
                    </div>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-body">

                                    <div style="display: flex; justify-content: center; align-items: center; min-height: 400px; text-align: center; padding: 40px 20px;">
                                        <div>
                                            <i class="fa fa-wrench" style="font-size: 80px; color: #d4a017; margin-bottom: 20px;"></i>
                                            <h2 style="font-weight: bold; color: #333; margin-bottom: 10px;">Page Under Maintenance</h2>
                                            <p style="font-size: 16px; color: #777; margin-bottom: 8px;">We are currently performing scheduled maintenance on this page.</p>
                                            <p style="font-size: 16px; color: #777; margin-bottom: 25px;">Please check back later. We apologize for the inconvenience.</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- /.content -->
            </aside>
            <!-- /.right-side -->
        </div>
        <!-- ./wrapper -->

        <!-- jQuery 2.0.2 -->
    <?php
}
include "../scripts.php";
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