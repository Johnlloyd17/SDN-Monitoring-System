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
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/elgu.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>eLGU BPLS</h3>
                        <p class="header-address">Monitoring Status</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                    </div>
            
            </section>
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                    Summary of DICT Engagement with LGUs
                                    </div>
                                    <div class="panel-body">
    <div class="row">
        <!-- Total No. of LGUs -->
        <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="../bpls_data/bpls_lgu.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="icons/municipality.png" alt="Total LGUs" style="width: auto; height: 56px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total No. of LGUs</span>
                    <span class="info-box-number">
                        <?php
                        $query = "
                            SELECT 
                                (SELECT COUNT(*) FROM tblbpls WHERE system = 'DICT (eLGU BPLS)') +
                                (SELECT COUNT(*) FROM tblbpls WHERE system = 'DICT (eLGU)') +
                                (SELECT COUNT(*) FROM tblbpls WHERE system = 'For Training') +
                                (SELECT COUNT(*) FROM tblbpls WHERE system = 'With own system/manual') AS total_lgu_stats
                        ";
                        $result = mysqli_query($con, $query);
                        $data = mysqli_fetch_assoc($result);
                        echo $data['total_lgu_stats'];
                        ?>
                    </span>
                </div>
            </div>
        </div>
        </a>
        <!-- Total LGUs Availing DICT -->
        <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="../bpls_data/bpls_dict.php"><div class="info-box">
                <span class="info-box-icon bg-red">
                    <img src="icons/municipality_dict.png" alt="Total DICT LGUs" style="width: 50px; height: 50px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total LGUs Availing DICT</span>
                    <span class="info-box-number">
                        <?php
                        $q = mysqli_query($con, "SELECT 
                            (SELECT COUNT(*) FROM tblbpls WHERE system = 'DICT (eLGU BPLS)') +
                            (SELECT COUNT(*) FROM tblbpls WHERE system = 'DICT (eLGU)') AS total_dict_lgus");
                        $result = mysqli_fetch_assoc($q);
                        echo $result['total_dict_lgus'];
                        ?>
                    </span>
                </div>
            </div>
        </div>
 </a>
        <!-- Implementation Rate -->
        <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="../bpls_data/bpls_implem.php"><div class="info-box">
                <span class="info-box-icon bg-yellow">
                    <img src="icons/implementation_rate.png" alt="Implementation Rate" style="width: 44px; height: 44px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Implementation Rate (%)</span>
                    <span class="info-box-number">
                        <?php
                        $q_total = mysqli_query($con, "SELECT COUNT(*) AS total_lgus FROM tblbpls");
                        $total_lgus = mysqli_fetch_assoc($q_total)['total_lgus'];

                        $q_implemented = mysqli_query($con, "SELECT COUNT(*) AS implemented_lgus FROM tblbpls WHERE system IN ('DICT (eLGU BPLS)', 'DICT (eLGU)')");
                        $implemented_lgus = mysqli_fetch_assoc($q_implemented)['implemented_lgus'];

                        $implementation_rate = $total_lgus > 0 ? ($implemented_lgus / $total_lgus) * 100 : 0;
                        echo number_format($implementation_rate, 2) . '%';
                        ?></a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- District 1 Count -->
        <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="../bpls_data/bpls_1st.php"><div class="info-box">
                <span class="info-box-icon bg-red">
                    <img src="icons/first_district.png" alt="1st District Count" style="width: auto; height: 60px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">1st District Count</span>
                    <span class="info-box-number">
                        <?php
                        $q_district1 = mysqli_query($con, "SELECT COUNT(*) AS district1_count FROM tblbpls WHERE district = '1st District'");
                        $district1_count = mysqli_fetch_assoc($q_district1)['district1_count'];
                        echo $district1_count;
                        ?>
                    </span>
                </div>
            </div>
        </div>
        </a>
        <!-- District 2 Count -->
        <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="../bpls_data/bpls_2nd.php"><div class="info-box">
                <span class="info-box-icon bg-yellow">
                    <img src="icons/second_district.png" alt="2nd District Count" style="width: auto; height: 52px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">2nd District Count</span>
                    <span class="info-box-number">
                        <?php
                        $q_district2 = mysqli_query($con, "SELECT COUNT(*) AS district2_count FROM tblbpls WHERE district = '2nd District'");
                        $district2_count = mysqli_fetch_assoc($q_district2)['district2_count'];
                        echo $district2_count;
                        ?>
                    </span>
                </div>
            </div>
        </div>
        </a>
        <!-- Engagement Rate -->
        <div class="col-md-4 col-sm-6 col-xs-12">
        <a href="../bpls_data/bpls_engage.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="icons/engagement_rate.png" alt="Engagement Rate" style="width: 52px; height: 52px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Engagement Rate (%)</span>
                    <span class="info-box-number">
                        <?php
                        $q_engagement = mysqli_query($con, "SELECT 
                            (SELECT COUNT(*) FROM tblbpls WHERE system = 'DICT (eLGU BPLS)') +
                            (SELECT COUNT(*) FROM tblbpls WHERE system = 'DICT (eLGU)') +
                            (SELECT COUNT(*) FROM tblbpls WHERE system = 'For Training') +
                            (SELECT COUNT(*) FROM tblbpls WHERE system = 'With own system/manual') AS total_engagement");
                        $engagement_lgus = mysqli_fetch_assoc($q_engagement)['total_engagement'];
                        
                        $engagement_rate = $total_lgus > 0 ? ($engagement_lgus / $total_lgus) * 100 : 0;
                        echo number_format($engagement_rate, 2) . '%';
                        ?></a>
                    </span>
                </div>
            </div>
        </div>           
                            </div><!-- /.col-md-12 -->
                        </div>
                        <div class="panel-body">
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
            $color = ''; // Default color
            if ($count > 0) {
                $color = 'style="background-color: #F56954; color: white;"'; // Default red color
            }

            // Display the count for the system
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
                </div><!-- /.col-md-12 -->

            </section><!-- /.content -->
        </aside><!-- /.right-side -->
    </div><!-- ./wrapper -->

    <!-- Include footer and other necessary scripts -->
    <?php include "../footer.php"; ?>

    <!-- jQuery -->
    <script src="../../js/jquery-1.12.3.js" type="text/javascript"></script>
    <!-- Bootstrap JS -->
    <script src="../../js/bootstrap.min.js" type="text/javascript"></script>
    <!-- DataTables JS -->
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
            color: inherit; /* Inherit the color from parent element */
            text-decoration: none; /* Remove underline from links */
        }
        .info-box-number a:hover {
            text-decoration: underline; /* Add underline on hover for better UX */
        }
        .header-title {
            display: flex;
            align-items: center; /* Align items vertically */
        }
        .header-logo {
            height: 50px; /* Adjust the size as needed */
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
        .info-box-icon {
            background-color: white; /* Change the background to white */
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4); /* Add an inner shadow */
            border-radius: 5px; /* Optional: Adjust the border-radius if needed */
            padding: 10px; /* Optional: Add padding to make the icon fit better */
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 300px; /* Set a fixed height for the charts */
            margin-bottom: 20px;
        }
        .chart-container canvas {
            width: 100% !important; /* Ensure canvas takes up full width */
            height: 100% !important; /* Ensure canvas takes up full height */
        }
        .panel-body {
            padding: 15px; /* Add padding to the panel body */
        }
        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px; /* Adjust height as needed */
            width: 80px; /* Adjust width as needed */
            font-size: 40px; /* Adjust icon size */
        }
        .header-date-time {
            font-size: 16px; /* Adjust font size as needed */
            color: #555; /* Optional: Change color for better visibility */
            margin-left: auto; /* Push the date/time to the right */
        }
        /* Other styles remain unchanged */
</style>

    </style>
    </body>
</html>