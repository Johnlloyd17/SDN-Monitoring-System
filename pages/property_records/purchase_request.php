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

                                            </div>
                                            <div class="text-center">
                                                <img src="img/dict_logo.png" class="img-fluid mw-75 mh-75" alt="Responsive image" width="400" height="160">
                                            </div>
                                            <div style="padding:10px; display: flex; justify-content: center;">

                                                <div>
                                                    <h4 style="font-family: sans-serif; font-weight: bold; color: black; font-size: 20px; text-transform: capitalize;" class="text-center">Purchase Request</h4>

                                                </div>

                                            </div>



                                            <div class="box-body table-responsive">
                                                <form method="post">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>

                                                                <tr>
                                                                    <td colspan="6" style="padding: 0;">
                                                                        <table class="table table-bordered mb-0" style="border: none;">
                                                                            <tr style="border-bottom: none;">
                                                                                <td style="border:none; border-top: none;">Department: ________________</td>
                                                                                <td style="border:none; border-top: none;" colspan="2">PR No. ________________</td>
                                                                            </tr>
                                                                            <tr style="border-top: none;">
                                                                                <td style="border:none;">Section: ________________</td>
                                                                                <td style="border:none;">SAI No.: ________________</td>
                                                                                <td style="border:none;">Date: ________________</td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>


                                                                <tr>
                                                                    <th>Stock No.</th>
                                                                    <th>Unit</th>
                                                                    <th>Item Description</th>
                                                                    <th>Qty</th>
                                                                    <th>Unity Cost</th>
                                                                    <th>Total Cost</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- tr1 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr1 -->


                                                                <!-- tr2 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr2 -->

                                                                <!-- tr4 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr4 -->

                                                                <!-- tr5 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr5 -->
                                                                <!-- tr6 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr6 -->
                                                                <!-- tr7 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr7 -->
                                                                <!-- tr8 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr8 -->
                                                                <!-- tr9 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr9 -->
                                                                <!-- tr10 -->
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td colspan="2" style="font-weight: bold; " class="text-center">Total</td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr10 -->
                                                                <!-- tr10 -->
                                                                <tr>
                                                                    <td colspan="6">
                                                                        Purpose:
                                                                    </td>
                                                                </tr>
                                                                <!-- tr10 -->


                                                                <!-- tr11 -->
                                                                <tr>
                                                                    <td colspan="6">

                                                                    </td>
                                                                </tr>
                                                                <!-- tr11 -->





                                                                <!-- tr12 -->

                                                                <tr style="font-weight: bold;">
                                                                    <td colspan="2">Signature: (Printed Name Designation)</td>
                                                                    <td colspan="2">Requested by:</td>
                                                                    <td>Recommending Approved:</td>
                                                                    <td>Approved by:</td>
                                                                </tr>
                                                                <!-- tr12 -->

                                                                <!-- tr13 -->

                                                                <tr style="height: 100px;">
                                                                    <td colspan="2"></td>
                                                                    <td colspan="2"></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>

                                                                <!-- tr13 -->





                                                            </tbody>
                                                        </table>
                                                    </div>


                                                </form>
                                            </div><!-- /.box-body -->
                                            <div class="panel-heading ">
                                                <a href="property.php" class="btn btn-default btn-sm " style="color: black; "><i class="fa fa-out" aria-hidden="true"></i> back</a>

                                                <a href="canvas_form1.php" class="btn btn-primary btn-sm" style="color: white; float: right; margin-left: 10px;"><i class="fa fa-user-plus" aria-hidden="true"></i> Next</a>
                                                <a href="#" class="btn btn-success btn-sm" style="color: white; float: right;"><i class="fa fa-download" aria-hidden="true"></i> Print</a>

                                            </div>


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