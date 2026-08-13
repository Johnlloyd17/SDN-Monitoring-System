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
        /* Other styles remain unchanged */
           
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
                                    <th 
                                    <th>GOVNET</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch all municipalities
                                $municipalitiesQuery = mysqli_query($con, "SELECT DISTINCT municity FROM tblproject ORDER BY municity");
                                $municipalities = [];
                                while ($row = mysqli_fetch_assoc($municipalitiesQuery)) {
                                    $municipalities[] = $row['municity'];
                                }

                                // Define the list of projects
                                $projects = ['Cybersecurity', 'eLGU BPLS', 'FWFA', 'IIDB', 'ILCDB', 'GECS', 'DREAM', 'GOVNET'];

                                // Display each municipality with counts for each project
                                foreach ($municipalities as $municipality) {
                                    echo '<tr>';
                                    echo '<td>' . $municipality . '</td>';
                                    foreach ($projects as $project) {
                                        $q = mysqli_query($con, "SELECT COUNT(*) AS total FROM tblactivity WHERE municipality = '$municipality' AND project = '$project'");
                                        $result = mysqli_fetch_assoc($q);
                                        echo '<td>' . $result['total'] . '</td>';
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
    </script>
</body>
</html>