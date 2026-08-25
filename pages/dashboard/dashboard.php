<!DOCTYPE html>
<html lang="en">
<head>
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
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .info-box-icon {
            background-color: white; /* Change the background to white */
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2); /* Add an inner shadow */
            border-radius: 5px; /* Optional: Adjust the border-radius if needed */
            padding: 10px; /* Optional: Add padding to make the icon fit better */
        }

        .panel-body {
            padding: 15px;
        }
        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            width: 80px;
            font-size: 40px;
        }
        .dataTables_filter input {
            padding-top: 20px;
            padding-bottom: 20px;
            width: 350px; /* Adjust width for the search bar */
            display: inline-block;
        }
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
            justify-content: space-between; /* Space between logo/title and date/time */
            width: 100%; /* Full width for proper alignment */
        }
        .header-logo {
            height: 60px; /* Adjust the size as needed */
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
        .header-date-time {
            font-size: 16px; /* Adjust font size as needed */
            color: #555; /* Optional: Change color for better visibility */
            margin-left: auto; /* Push the date/time to the right */
        }
        /* Chart panels */
        .chart-panel {
            margin-bottom: 0;
        }
        .chart-panel .panel-body {
            padding: 10px 15px 5px;
        }
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
            margin-bottom: 10px;
        }
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
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
        <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/dict_logo.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>DICT Surigao del Norte Provincial Office</h3>
                        <p class="header-address">Ferdinand M. Ortiz St., Brgy. Washington, Surigao City</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                    </div>
            
            </section>
    
            <!-- Statistics panel -->
            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Statistics
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/activities_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <img src="icons/activities.png" alt="Total Activities" style="width: 50px; height: 50px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Activities</span>
                                        <span class="info-box-number">        
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(*) AS total_trainings FROM tblactivity");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['total_trainings'];
                                                ?>
                                           
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/participants_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <img src="icons/participants.png" alt="Total Participants" style="width: 52px; height: 52px;">
                                    </span> </a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Participants</span>
                                        <span class="info-box-number">                                      
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(*) AS total_participants FROM tblparticipant");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['total_participants'];
                                                ?>
                                   
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/sectors_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-yellow">
                                        <img src="icons/sectors.png" alt="Total Sectors" style="width: 60px; height: 60px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Sectors</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(*) AS total_sector FROM (SELECT DISTINCT sector FROM tblactivity WHERE sector IS NOT NULL AND sector != '') AS sectors");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['total_sector'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/agencies_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <img src="icons/agencies.png" alt="Total Agencies" style="width: 50px; height: 50px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Agencies</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(DISTINCT agency) AS total_agencies FROM tblactivity WHERE agency IS NOT NULL AND agency != ''");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['total_agencies'];
                                                ?>
                                         
                                        </span>
 </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/district1_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <img src="icons/district_1.png" alt="District 1" style="width: 60px; height: 60px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">District 1</span>
                                        <span class="info-box-number">
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(*) AS district_1_count FROM tblactivity WHERE district = 'District 1' OR district = 'District 1 (Siargao)'");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['district_1_count'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/district2_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-yellow">
                                        <img src="icons/district_2.png" alt="District 2" style="width: 55px; height: 55px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">District 2</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(*) AS district_2_count FROM tblactivity WHERE district = 'District 2' OR district = 'District 2 (Mainland)'");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['district_2_count'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/municipalities_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <img src="icons/municipalities.png" alt="Total Municipality" style="width: auto; height: 58px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Municipalities</span>
                                        <span class="info-box-number">
                                         
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(DISTINCT municipality) AS total_municipality FROM tblactivity WHERE municipality IS NOT NULL AND municipality != ''");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['total_municipality'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/barangays_data.php"><div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <img src="icons/barangays.png" alt="Total Barangays" style="width: 60px; height: 60px;">
                                    </span></a>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Barangays</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                $q = mysqli_query($con, "SELECT COUNT(DISTINCT barangay) AS total_barangay FROM tblactivity WHERE barangay IS NOT NULL AND barangay != ''");
                                                $result = mysqli_fetch_assoc($q);
                                                echo $result['total_barangay'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.row -->
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->
            </div>

            <!-- Charts Section -->
            <div class="col-md-12 col-sm-12 col-xs-12">
                <?php
                    // Chart 1: Activities by District (Doughnut)
                    $districtQuery = mysqli_query($con, "SELECT 
                        SUM(CASE WHEN district = 'District 1' OR district = 'District 1 (Siargao)' THEN 1 ELSE 0 END) AS district1,
                        SUM(CASE WHEN district = 'District 2' OR district = 'District 2 (Mainland)' THEN 1 ELSE 0 END) AS district2
                    FROM tblactivity");
                    $districtRow = mysqli_fetch_assoc($districtQuery);

                    // Chart 2: Activities per Project (Horizontal Bar)
                    $projectQuery = mysqli_query($con, "SELECT project, COUNT(*) AS total FROM tblactivity WHERE project IS NOT NULL AND project != '' GROUP BY project ORDER BY total DESC");
                    $projectLabels = [];
                    $projectData = [];
                    while ($row = mysqli_fetch_assoc($projectQuery)) {
                        $projectLabels[] = $row['project'];
                        $projectData[] = (int)$row['total'];
                    }

                    // Chart 3: Top 10 Municipalities (Bar)
                    $topMuniQuery = mysqli_query($con, "SELECT municipality, COUNT(*) AS total FROM tblactivity WHERE municipality IS NOT NULL AND municipality != '' GROUP BY municipality ORDER BY total DESC LIMIT 10");
                    $muniLabels = [];
                    $muniData = [];
                    while ($row = mysqli_fetch_assoc($topMuniQuery)) {
                        $muniLabels[] = $row['municipality'];
                        $muniData[] = (int)$row['total'];
                    }

                    // Chart 4: Activities by Sector (Pie)
                    $sectorQuery = mysqli_query($con, "SELECT sector, COUNT(*) AS total FROM tblactivity WHERE sector IS NOT NULL AND sector != '' GROUP BY sector ORDER BY total DESC");
                    $sectorLabels = [];
                    $sectorData = [];
                    while ($row = mysqli_fetch_assoc($sectorQuery)) {
                        $sectorLabels[] = $row['sector'];
                        $sectorData[] = (int)$row['total'];
                    }
                ?>

                <!-- Row 1: District Doughnut + Projects Bar -->
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="panel panel-default chart-panel">
                            <div class="panel-heading">Activities by District</div>
                            <div class="panel-body">
                                <div class="chart-container">
                                    <canvas id="districtChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="panel panel-default chart-panel">
                            <div class="panel-heading">Activities per Project</div>
                            <div class="panel-body">
                                <div class="chart-container">
                                    <canvas id="projectChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Top Municipalities Bar + Sector Pie -->
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="panel panel-default chart-panel">
                            <div class="panel-heading">Top 10 Municipalities by Activity</div>
                            <div class="panel-body">
                                <div class="chart-container">
                                    <canvas id="municipalityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="panel panel-default chart-panel">
                            <div class="panel-heading">Activities by Sector</div>
                            <div class="panel-body">
                                <div class="chart-container">
                                    <canvas id="sectorChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTables Table -->
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Municipality/City Data
                    </div>
                    <div class="panel-body">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Municipality/City</th>
                                    <th>Cybersecurity</th>
                                    <th>eLGU</th>
                                    <th>FWFA</th>
                                    <th>IIDB</th>
                                    <th>ILCDB</th>
                                    <th>GECS</th>
                                    <th>DREAM</th>
                                    <th>GOVNET</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Single query to get all municipality × project counts
                                $crossTabQuery = mysqli_query($con, "
                                    SELECT municipality, project, COUNT(*) AS total
                                    FROM tblactivity
                                    WHERE municipality != '' AND project != ''
                                    GROUP BY municipality, project
                                ");
                                $crossTabData = [];
                                while ($row = mysqli_fetch_assoc($crossTabQuery)) {
                                    $crossTabData[$row['municipality']][$row['project']] = $row['total'];
                                }

                                // Fetch all municipalities
                                $municipalitiesQuery = mysqli_query($con, "SELECT DISTINCT municipality FROM tblactivity WHERE municipality != '' ORDER BY municipality ASC");
                                $projects = ['Cybersecurity', 'eLGU BPLS', 'FWFA', 'IIDB', 'ILCDB', 'GECS', 'DREAM', 'GOVNET'];

                                while ($mRow = mysqli_fetch_assoc($municipalitiesQuery)) {
                                    $municipality = $mRow['municipality'];
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($municipality) . '</td>';
                                    foreach ($projects as $project) {
                                        $count = isset($crossTabData[$municipality][$project]) ? $crossTabData[$municipality][$project] : 0;
                                        echo '<td>' . $count . '</td>';
                                    }
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /.col-md-12 -->

        </aside><!-- /.right-side -->
    </div><!-- ./wrapper -->

    <!-- Include footer and other necessary scripts -->
    <?php include "../footer.php"; ?>

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

        // --- CHARTS ---

        // Chart 1: Activities by District (Doughnut)
        var districtCtx = document.getElementById('districtChart').getContext('2d');
        new Chart(districtCtx, {
            type: 'doughnut',
            data: {
                labels: ['District 1 (Siargao)', 'District 2 (Mainland)'],
                datasets: [{
                    data: [<?php echo (int)$districtRow['district1']; ?>, <?php echo (int)$districtRow['district2']; ?>],
                    backgroundColor: ['#3498db', '#e67e22'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, font: { size: 13 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Chart 2: Activities per Project (Horizontal Bar)
        var projectCtx = document.getElementById('projectChart').getContext('2d');
        var projectColors = ['#3498db','#27ae60','#e74c3c','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e'];
        new Chart(projectCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($projectLabels); ?>,
                datasets: [{
                    label: 'Activities',
                    data: <?php echo json_encode($projectData); ?>,
                    backgroundColor: projectColors.slice(0, <?php echo count($projectLabels); ?>),
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.raw + ' activities'; }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false }, ticks: { font: { size: 12 } } }
                }
            }
        });

        // Chart 3: Top 10 Municipalities (Bar)
        var muniCtx = document.getElementById('municipalityChart').getContext('2d');
        new Chart(muniCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($muniLabels); ?>,
                datasets: [{
                    label: 'Activities',
                    data: <?php echo json_encode($muniData); ?>,
                    backgroundColor: '#3498db',
                    hoverBackgroundColor: '#2980b9',
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.raw + ' activities'; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45, minRotation: 30 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
                }
            }
        });

        // Chart 4: Activities by Sector (Pie)
        var sectorCtx = document.getElementById('sectorChart').getContext('2d');
        var sectorColors = ['#3498db','#27ae60','#e74c3c','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e','#d35400','#16a085'];
        new Chart(sectorCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($sectorLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($sectorData); ?>,
                    backgroundColor: sectorColors.slice(0, <?php echo count($sectorLabels); ?>),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 15, font: { size: 12 } } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>