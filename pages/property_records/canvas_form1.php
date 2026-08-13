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
                                            <div class="panel-heading"></div>
                                            <div style="padding: 20px;">
                                                <div class="text-center">

                                                    <img src="img/dict_logo.png" class="img-fluid mw-75 mh-75 lign-self-center " alt="Responsive image" width="400" height="160">
                                                </div>

                                                <h4 style="font-family: sans-serif; font-weight: bold; color: black; font-size: 20px; text-transform: capitalize;" class="text-center">Request For Price Quotation</h4>
                                                <h4 style="font-family: sans-serif; font-weight: normal; color: black; font-size: 18px; text-transform: capitalize;" class="text-center">Canvas Form</h4>


                                                <div class="row align-items-center">
                                                    <div class="col-md-7">
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-addon">Purchase Request No.:</span>
                                                            <input id="purchaseRequestNo" type="text" class="form-control" name="purchaseRequestNo" placeholder="Enter Purchase Request No.">
                                                        </div>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-addon">Approved Budget for the Contract (ABC):</span>
                                                            <input id="approvedBudget" type="text" class="form-control" name="approvedBudget" placeholder="Enter Budget">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-addon">Canvass No.:</span>
                                                            <input id="canvassNo" type="text" class="form-control" name="canvassNo" placeholder="Enter Canvass No.">
                                                        </div>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-addon">Date:</span>
                                                            <input id="date" type="date" class="form-control" name="date">
                                                        </div>
                                                    </div>
                                                </div>


                                                <div>
                                                    <h5 style="font-weight: bold; color: black; font-family: Arial, Helvetica, sans-serif; margin-top: 30px; margin-bottom: 10px;   ">GENTLEMEN: </h5>
                                                    <p style="text-align: justify">Please quote your lowest prices for the following item(s) specified below including the total amount in legible style
                                                        (preferably typewritten)and return this duly accomplished and signed by the company's authorized representative to the
                                                        Procurement Planning and Management Division Deadline of submission of bids is
                                                        on <span style="text-decoration: underline; font-weight: bold;">_____________________</span>. </p>

                                                </div>
                                                <style>
                                                    .table-borderless>tbody>tr>td,
                                                    .table-borderless>tbody>tr>th,
                                                    .table-borderless>tfoot>tr>td,
                                                    .table-borderless>tfoot>tr>th,
                                                    .table-borderless>thead>tr>td,
                                                    .table-borderless>thead>tr>th {
                                                        border: none;
                                                    }
                                                </style>
                                                <div style="margin-top: 40px; margin-bottom: 20px;">
                                                    <div class="table-responsive">
                                                        <table class="table table-borderless">

                                                            <tbody>
                                                                <tr>
                                                                    <th style="text-align: center; font-weight: bold; text-transform: capitalize;">_____________________</th>
                                                                    <th style="text-align: center; font-weight: bold; text-transform: capitalize;">_____________________</th>
                                                                </tr>

                                                                <tr>
                                                                    <th style="text-align: center;">CANVASSER</th>

                                                                    <th style="text-align: center;">CHAIRMAN, BIRDS AND AWARDS</th>
                                                                </tr>


                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h5 style="font-weight: bold; color: black; font-family: Arial, Helvetica, sans-serif; margin-top: 30px; margin-bottom: 10px;   ">IMPORTANT: </h5>
                                                    <p style="text-align: justify">
                                                        <br>1. The total price quoted is subject to withholding tax;
                                                        <br>2. Bids should be valid for at least 45 days (from the deadline of submission);
                                                        <br>3. Delivery Period; 5 to 7 working Days (please state the delivery period if beyond the required 10 working days);
                                                        <br>4. In case of failure to deliver make full delivery within the time specified above, penalty of one tenth(1/10) of one
                                                        percent(1%) for everyday of delay shall be imposed;
                                                        <br>5. Terms of payment; Fifteen (15) to thirty (30) days after the inspection and acceptance of goods;
                                                        <br>6. Bidders should provide the following requirements:
                                                        <br>&emsp;a. Valid PhilGEPS Certificate of Registration;
                                                        <br>&emsp;b. BIR Certificate of Registration; and
                                                        <br>&emsp;c. Business/Mayor's Permit.
                                                    </p>

                                                </div>
                                            </div>


                                            <div class="box-body table-responsive">
                                                <form method="post">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>



                                                                <tr>
                                                                    <th>Item No.</th>
                                                                    <th>Qty</th>
                                                                    <th>Unity</th>
                                                                    <th>Item Description</th>
                                                                    <th>Unity Cost</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Brand</th>
                                                                    <th>Model</th>
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
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                                <!-- tr1 -->





                                                            </tbody>
                                                        </table>
                                                    </div>


                                                </form>
                                            </div><!-- /.box-body -->

                                            <div style="padding: 20px;">
                                                <div>
                                                    <p style="text-align: justify">
                                                        <br>After having carefully read and accepted your General Conditions, I/We quote you on the(s) at prices indicated above
                                                    </p>

                                                    <div class="input-group mb-3">
                                                        <span class="input-group-addon">Supplier's Representative</span>
                                                        <input id="suppliersRepresentative" type="text" class="form-control" name="suppliersRepresentative">
                                                    </div>

                                                    <div class="input-group mb-3">
                                                        <span class="input-group-addon">Company Name</span>
                                                        <input id="companyName" type="text" class="form-control" name="companyName">
                                                    </div>

                                                    <div class="input-group mb-3">
                                                        <span class="input-group-addon">Address</span>
                                                        <input id="address" type="text" class="form-control" name="address">
                                                    </div>

                                                    <div class="input-group mb-3">
                                                        <span class="input-group-addon">Telephone No.</span>
                                                        <input id="telephoneNo" type="text" class="form-control" name="telephoneNo">
                                                    </div>

                                                    <div class="input-group mb-3">
                                                        <span class="input-group-addon">Email</span>
                                                        <input id="email" type="text" class="form-control" name="email">
                                                    </div>

                                                    <div class="input-group mb-3">
                                                        <span class="input-group-addon">Date of Quotation</span>
                                                        <input id="dateOfQuotation" type="text" class="form-control" name="dateOfQuotation">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="panel-heading ">
                                                <a href="#" class="btn btn-default btn-sm " style="color: black; "><i class="fa fa-out" aria-hidden="true"></i> back</a>

                                                <a href="#" class="btn btn-primary btn-sm" style="color: white; float: right; margin-left: 10px;"><i class="fa fa-user-plus" aria-hidden="true"></i> Next</a>
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