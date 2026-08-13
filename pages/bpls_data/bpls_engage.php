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
    <?php include "../connection.php"; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/engagement_rate1.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>Engagement Rate (%)</h3>
                        <p class="header-address">Assessing Participation in Local Governance Initiatives</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div>
                </div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="box"></div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Municipality/City Data
                            </div>
                            <div class="panel-body">
                                <!-- Info Box for Engagement Rate -->
                                <div class="info-box">
                                   
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span class="info-box-number" id="engagementRate" style="font-size: 24px; font-weight: bold; color: #0072B5;"></span>
                                            <div id="engagementFormula" style="font-size: 14px; margin-left: 10px;"></div>
                                        </div>
                                        <span class="info-box-text">Engagement Rate</span>
                                        <p class="header-rate">(Sum of DICT (eLGU BPLS) + DICT (eLGU) + For Training + With Own System) / Total No. of LGUs</pv>
                                    </div>
                            
                               
                                <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Municipality/City</th>
                                            <th>DICT eLGU BPLS (Version 1)</th>
                                            <th>DICT eLGU (Version 2)</th>
                                            <th>(For Training) With Complete Documentary Requirements</th>
                                            <th>With Own System or Manual Process</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
    <?php
    // Fetch all municipalities
    $municipalitiesQuery = mysqli_query($con, "SELECT DISTINCT municipality FROM tblbplsmonitoring ORDER BY municipality");
    $municipalities = [];
    while ($row = mysqli_fetch_assoc($municipalitiesQuery)) {
        $municipalities[] = $row['municipality'];
    }

    // Initialize totals for the "Total No. of LGUs" column
    $totals = [
        'DICT (eLGU BPLS)' => 0,
        'DICT (eLGU)' => 0,
        'For Training' => 0,
        'With own system/manual' => 0
    ];

    // Display each municipality with counts for each project
    foreach ($municipalities as $municipality) {
        echo '<tr>';
        echo '<td>' . $municipality . '</td>';
        $systems = [
            'DICT (eLGU BPLS)' => 0,
            'DICT (eLGU)' => 0,
            'For Training' => 0,
            'With own system/manual' => 0
        ];

        // Query the data for the 'system' column
        $q = mysqli_query($con, "SELECT * FROM tblbpls WHERE lgu = '$municipality'");
        while ($row = mysqli_fetch_assoc($q)) {
            // Count for 'system' column (DICT eLGU BPLS, DICT eLGU, etc.)
            foreach (array_keys($systems) as $project) {
                if ($row['system'] == $project) {
                    $systems[$project]++;
                    $totals[$project]++;
                }
            }
        }

        // Loop through each system to display counts and apply coloring
        foreach ($systems as $project => $count) {
            $color = $count > 0 ? 'style="background-color: #0072B5; color: white;"' : '';
            echo '<td ' . $color . '>' . $count . '</td>';
        }

        echo '</tr>';
    }
    ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <?php
                                            foreach ($totals as $total) {
                                                echo '<th>' . $total . '</th>';
                                            }
                                            ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <?php include "../footer.php"; ?>

    <script src="../../js/jquery-1.12.3.js" type="text/javascript"></script>
    <script src="../../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../../js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="../../js/dataTables.buttons.min.js" type="text/javascript"></script>
    <script src="../../js/buttons.print.min.js" type="text/javascript"></script>
    <script src="../../js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {
            $("#table").dataTable({
                "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
                "aaSorting": [],
                "dom": '<"search"f><"top"l>rt<"bottom"ip><"clear">'
            });

            // Calculate and display engagement rate
            function calculateEngagementRate() {
                const totalLGUs = <?php echo count($municipalities); ?>;
                const totalBPLS = <?php echo $totals['DICT (eLGU BPLS)']; ?>;
                const totalELGU = <?php echo $totals['DICT (eLGU)']; ?>;
                const totalTraining = <?php echo $totals['For Training']; ?>;
                const totalManual = <?php echo $totals['With own system/manual']; ?>;
                const totalEngaged = totalBPLS + totalELGU + totalTraining + totalManual;
                const engagementRate = (totalEngaged / totalLGUs) * 100;

                // Display the engagement rate
                document.getElementById('engagementRate').innerHTML = 
                    '<strong>' + engagementRate.toFixed(2) + '%</strong>';
                document.getElementById('engagementFormula').innerHTML = 
                    'Engagement Rate = <strong>' + totalEngaged + '</strong> / <strong>' + totalLGUs + '</strong> = <strong>' + engagementRate.toFixed(2) + '%</strong>';
            }

            calculateEngagementRate(); // Initial calculation
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
        .info-box-number a {
            color: inherit;
            text-decoration: none;
        }
        .info-box-number a:hover {
            text-decoration: underline;
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
        .header-rate {
            margin: 0;
            font-size: 12px;
            color: black;
        }
        .info-box-icon {
            background-color: white;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }
        .panel-body {
            padding: 15px;
        }
        .header-date-time {
            font-size: 16px;
            color: #555;
            margin-left: auto;
        }
    </style>
</body>