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
                    <img src="icons/municipality_2.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                        <p class="header-address">LGU Penetration Rate</p>
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
                                    </div>
                                    
                                    <div class="box-body table-responsive">
                                    <form method="post">
                                        <table id="table" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                    <th>NO.</th>
                                                    <th>Municipality/City</th>
                                                    <th>Barangay Count</th>
                                                    <th>Formula</th>
                                                    <th>Penetration Rate (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                             $counter = 1;
                                             $totalMunicipalities = 0;
                                             $totalBarangaysWithAccess = 0;
                                             $totalBarangayCount = 0;

                                             // Single query: all municipalities with distinct barangay access counts
                                             $accessMap = [];
                                             $accessQuery = mysqli_query($con, "SELECT locality, COUNT(DISTINCT barangay) AS barangays_with_access FROM tblfwfa WHERE locality != '' GROUP BY locality");
                                             while ($aRow = mysqli_fetch_assoc($accessQuery)) {
                                                 $accessMap[$aRow['locality']] = $aRow['barangays_with_access'];
                                             }

                                             $totalLGUWithAccess = count($accessMap);

                                             $tableQuery = "SELECT * FROM tblsdn WHERE municipality != ''";
                                             $result = mysqli_query($con, $tableQuery);

                                             if (!$result) {
                                                 die('Error: ' . mysqli_error($con));
                                             }

                                             while ($row = mysqli_fetch_assoc($result)) {
                                                 $barangayCount = $row['barangay_count'];
                                                 $municipality = $row['municipality'];
                                                 $barangaysWithAccess = isset($accessMap[$municipality]) ? $accessMap[$municipality] : 0;

                                                 $penetrationRate = ($barangayCount > 0) ? ($barangaysWithAccess / $barangayCount) * 100 : 0;

                                                 $totalMunicipalities++;
                                                 $totalBarangaysWithAccess += $barangaysWithAccess;
                                                 $totalBarangayCount += $barangayCount;

                                                 echo '
                                                    <tr>
                                                        <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $row['id'] . '" /></td>
                                                        <td>' . $counter++ . '</td>
                                                        <td>' . htmlspecialchars($municipality) . '</td>
                                                        <td>' . htmlspecialchars($barangayCount) . '</td>
                                                        <td>Total No. Barangays with Access Point / Total Barangay Count x 100%</td>
                                                        <td>' . number_format($penetrationRate, 2) . '%</td>
                                                    </tr>
                                                 ';
                                             }
                                            ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" style="text-align: right;"><strong>Total LGU Penetration Rate:</strong></td>
                                                    <td>
                                                        <?php
                                                        $totalMunicipalitiesCountQuery = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblsdn WHERE municipality != ''");
                                                        $totalMunicipalitiesCountRow = mysqli_fetch_assoc($totalMunicipalitiesCountQuery);
                                                        $totalMunicipalitiesCount = $totalMunicipalitiesCountRow['cnt'];
                                                        $totalLGUPenetrationRate = ($totalMunicipalitiesCount > 0) ? ($totalLGUWithAccess / $totalMunicipalitiesCount) * 100 : 0;
                                                        echo number_format($totalLGUPenetrationRate, 2) . '%';
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
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
            <?php include "../footer.php"; ?>
            <script type="text/javascript">
                $(function () {
                    $("#table").DataTable({
                        "aoColumnDefs": [{ "bSortable": false, "aTargets": [0] }],
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
                table th:nth-child(1) { width: 30px; }
                table th:nth-child(2) { width: 50px; }
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
            </style>
        </body>
    </html>