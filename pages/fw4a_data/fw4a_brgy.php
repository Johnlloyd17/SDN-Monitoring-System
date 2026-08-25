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
                <img src="icons/barangays_2.png" alt="Logo" class="header-logo" />
                <div class="header-info">
                    <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                    <p class="header-address">Barangay Penetration Rate</p>
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
                                    Barangay Access Points and Penetration Rate
                                </div>
                                <div class="panel-body">
                                    <h5 style="margin: 0; font-weight: bold;">BARANGAY PENETRATION RATE:</h5>
                                    <span id="penetrationRate" style="font-size: 36px; font-weight: bold; color: darkblue;"></span>
                                    <p style="margin: 5px 0; font-size: 14px;">
                                        Total No. of Barangays with Access Point / Total No. of Barangays in SDN
                                    </p>
                                </div>
                                
                                <div class="box-body table-responsive">
                                    <table id="table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>NO.</th>
                                                <th>Municipality/City</th>
                                                <th>Barangay</th>
                                                <th>Barangay with Access Points</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                         $counter = 1;
                                         $totalBarangays = 0;
                                         $totalBarangaysWithAccess = 0;

                                         // Single query: LEFT JOIN to find barangays with access
                                         $accessMap = [];
                                         $accessQuery = mysqli_query($con, "SELECT barangay, locality, COUNT(*) AS access_count FROM tblfwfa WHERE locality != '' GROUP BY barangay, locality");
                                         while ($aRow = mysqli_fetch_assoc($accessQuery)) {
                                             $accessMap[$aRow['barangay'] . '|' . $aRow['locality']] = $aRow['access_count'];
                                         }

                                         $tableQuery = "SELECT * FROM tblbrgy ORDER BY municipality, barangay";
                                         $result = mysqli_query($con, $tableQuery);

                                         if (!$result) {
                                             die('Error: ' . mysqli_error($con));
                                         }

                                         while ($row = mysqli_fetch_assoc($result)) {
                                             $barangay = $row['barangay'];
                                             $locality = $row['municipality'];
                                             $key = $barangay . '|' . $locality;
                                             $hasAccess = isset($accessMap[$key]) && $accessMap[$key] > 0;

                                             $totalBarangays++;
                                             if ($hasAccess) {
                                                 $totalBarangaysWithAccess++;
                                                 $accessCell = '<td style="background-color: rgba(0, 0, 255, 0.1);">Yes</td>';
                                             } else {
                                                 $accessCell = '<td style="background-color: rgba(255, 0, 0, 0.1);">No</td>';
                                             }

                                             echo '
                                                <tr>
                                                    <td>' . $counter++ . '</td>
                                                    <td>' . htmlspecialchars($locality) . '</td>
                                                    <td>' . htmlspecialchars($barangay) . '</td>
                                                    ' . $accessCell . '
                                                </tr>
                                             ';
                                         }
                                        ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" style="text-align: right;"><strong>Total Barangays:</strong></td>
                                                <td><?php echo $totalBarangays; ?></td>
                                                <td><?php echo $totalBarangaysWithAccess; ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
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
        <?php include "../footer.php"; ?>
        <script type="text/javascript">
            $(function () {
                $("#table").DataTable({
                    "aaSorting": [],
                    "pageLength": 10,
                    "lengthMenu": [10, 25, 50, 100]
                });
            });

            // Function to update the date and time
            function updateDateTime() {
                const now = new Date();
                const options = { 
                    year: 'numeric', 
                    month: 'long ', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit', 
                    hour12: true 
                };
                document.getElementById('dateTime').innerText = now.toLocaleString('en-US', options);
            }

            // Calculate and display the barangay penetration rate
            function calculatePenetrationRate(totalBarangaysWithAccess, totalBarangays) {
                const penetrationRate = (totalBarangays > 0) ? (totalBarangaysWithAccess / totalBarangays) * 100 : 0;
                document.getElementById('penetrationRate').innerText = `${totalBarangaysWithAccess} / ${totalBarangays} = ${penetrationRate.toFixed(2)}%`;
            }

            // Call the function to calculate the penetration rate
            calculatePenetrationRate(<?php echo $totalBarangaysWithAccess; ?>, <?php echo $totalBarangays; ?>);

            // Update the date and time every second
            setInterval(updateDateTime, 1000);
            updateDateTime(); // Initial call to display immediately
        </script>
        <style>
            table {
                table-layout: auto;
                width: 100%;
            }
            table th {
                white-space: normal;
                text-align: center;
                word-wrap: break-word;
                overflow-wrap: break-word;
                max-width: 200px;
            }
            table td {
                white-space: nowrap;
            }
            table th, table td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .header-title {
                display: flex;
                align-items: center;
            }
            .header-logo {
                height: 50px;
                width: auto;
                margin-right: 10px;
            }
            .header-info {
                display: flex;
                flex-direction: column;
            }
            h3 {
                margin: 0;
                font-weight: 600;
            }
            .header-address {
                margin: 0;
                font-size: 14px;
                color: #555;
            }
            .header-date-time {
                font-size: 16px;
                color: #555;
                margin-left: auto;
            }
            .info-box {
                background-color: #f8f9fa;
                border: 1px solid #ced4da;
                padding: 10px;
                margin-bottom: 15px;
                border-radius: 5px;
            }
        </style>
    </body>
</html>