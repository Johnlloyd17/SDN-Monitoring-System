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
                    <img src="img/logo/elgu.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>eLGU BPLS</h3>
                        <p class="header-address">Activities Conducted</p>
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
                                    Monitoring and Accomplishments
                                    </div>
                                    <div class="panel-body">
    <form method="post" id="filterForm">
        <div class="row">
            <!-- Total Activities -->
            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/activities_data_elgu.php"><div class="info-box">
                    <span class="info-box-icon bg-blue">
                        <img src="img/icons/activity-1.png" alt="Total Activities" style="width: auto; height: 50px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Activities</span>
                        <span class="info-box-number" id="totalParticipants">
                            <?php
                            $filterQuery = "SELECT COUNT(*) AS total_activities FROM tblactivity WHERE project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $result = mysqli_query($con, $filterQuery);
                            $data = mysqli_fetch_assoc($result);
                            echo $data['total_activities'];
                            ?></a>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Municipality -->
            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/municipalities_data_elgu.php"><div class="info-box">
                    <span class="info-box-icon bg-red">
                        <img src="img/icons/municipality-1.png" alt="Total Activities" style="width: auto; height: 50px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Municipalities</span>
                        <span class="info-box-number">
                            <?php
                            $filterQuery = "SELECT COUNT(DISTINCT municipality) AS total_municipalities FROM tblactivity WHERE project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $q = mysqli_query($con, $filterQuery);
                            $result = mysqli_fetch_assoc($q);
                            echo $result['total_municipalities'];
                            ?>
                            </a>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Barangay -->
            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/barangays_data_elgu.php"><div class="info-box">
                    <span class="info-box-icon bg-yellow">
                        <img src="img/icons/barangay-1.png" alt="Total Activities" style="width: auto; height: 60px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Barangay</span>
                        <span class="info-box-number">
                            <?php
                            $filterQuery = "SELECT COUNT(DISTINCT barangay) AS total_barangays FROM tblactivity WHERE barangay IS NOT NULL AND barangay != '' AND project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $q = mysqli_query($con, $filterQuery);
                            $result = mysqli_fetch_assoc($q);
                            echo $result['total_barangays'];
                            ?>
                            </a>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Sectors -->
            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/sectors_data_elgu.php"><div class="info-box">
                    <span class="info-box-icon bg-blue">
                        <img src="img/icons/sector-1.png" alt="Total Activities" style="width: auto; height: 58px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Sectors</span>
                        <span class="info-box-number">
                            <?php
                            $filterQuery = "SELECT COUNT(DISTINCT sector) AS total_sectors FROM tblactivity WHERE sector IS NOT NULL AND sector != '' AND project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $q = mysqli_query($con, $filterQuery);
                            $result = mysqli_fetch_assoc($q);
                            echo $result['total_sectors'];
                            ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div> <!-- End of first row -->

        <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="../planned_activities/planned_activities_elgu.php">
            <div class="info-box">
                <span class="info-box-icon bg-white">
                    <img src="img/logo/dict.png" alt="Total Activities" style="width: auto; height: 58px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-weight: bold;">Planned Activities - eLGU</span>
                    <p>
                     Targets and Initiatives in Surigao del Norte
                        <span style="padding-left: 5px;"></span> <!-- Added padding here -->
                      
                    </p>
                </div>
            </div>
        </a>
    </div>

            <!-- Total Completers -->
            <div class="col-md-3 col-sm-6 col -xs-12">
            <div class="info-box">
                    <span class="info-box-icon bg-yellow">
                        <img src="img/icons/completers-1.png" alt="Total Activities" style="width: auto; height: 55px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Completers</span>
                        <span class="info-box-number">
                            <?php
                            $filterQuery = "SELECT SUM(completers) AS total_completers FROM tblactivity WHERE project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $q = mysqli_query($con, $filterQuery);
                            $result = mysqli_fetch_assoc($q);
                            echo $result['total_completers'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Male Completers -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-blue">
                        <img src="img/icons/male-1.png" alt="Total Activities" style="width: auto; height: 53px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Male Completers</span>
                        <span class="info-box-number">
                            <?php
                            $filterQuery = "SELECT SUM(male) AS total_male_completers FROM tblactivity WHERE project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $q = mysqli_query($con, $filterQuery);
                            $result = mysqli_fetch_assoc($q);
                            echo $result['total_male_completers'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Female Completers -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red">
                        <img src="img/icons/female-1.png" alt="Total Activities" style="width: auto; height: 53px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Female Completers</span>
                        <span class="info-box-number">
                            <?php
                            $filterQuery = "SELECT SUM(female) AS total_female_completers FROM tblactivity WHERE project = 'eLGU BPLS'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                            }
                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                            }
                            $q = mysqli_query($con, $filterQuery);
                            $result = mysqli_fetch_assoc($q);
                            echo $result['total_female_completers'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div> <!-- End of second row -->

        <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="projectSelect">Select Sector</label>
        <select id="projectSelect" name="project" class="form-control" onchange="this.form.submit()">
            <option value="">All Sectors</option>
            <?php
            $sectorQuery = "SELECT DISTINCT sector FROM tblactivity WHERE project = 'eLGU BPLS' AND sector IS NOT NULL AND sector != ''";
            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                $sectorQuery .= " AND mode = '$mode'";
            }
            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                $sectorQuery .= " AND municipality = '$municipality'";
            }
            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                $sectorQuery .= " AND barangay = '$barangay'";
            }
            $sectorsQuery = mysqli_query($con, $sectorQuery);
            if ($sectorsQuery) {
                while ($sector = mysqli_fetch_assoc($sectorsQuery)) {
                    echo '<option value="' . htmlspecialchars($sector['sector']) . '"' . (isset($_POST['project']) && $_POST['project'] == $sector['sector'] ? ' selected' : '') . '>' . htmlspecialchars($sector['sector']) . '</option>';
                }
            } else {
                echo "Error fetching sectors: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>

<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="modeSelect">Select Mode of Implementation</label>
        <select id="modeSelect" name="mode" class="form-control" onchange="this.form.submit()">
            <option value="">All Mode of Implementation</option>
            <?php
            $modeQuery = "SELECT DISTINCT mode FROM tblactivity WHERE project = 'eLGU BPLS' AND mode IS NOT NULL AND mode != ''";
            if (isset($_POST['project']) && $_POST['project'] != '') {
                $sector = mysqli_real_escape_string($con, $_POST['project']);
                $modeQuery .= " AND sector = '$sector'";
            }
            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                $modeQuery .= " AND municipality = '$municipality'";
            }
            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                $modeQuery .= " AND barangay = '$barangay'";
            }
            $modesQuery = mysqli_query($con, $modeQuery);
            if ($modesQuery) {
                while ($mode = mysqli_fetch_assoc($modesQuery)) {
                    echo '<option value="' . htmlspecialchars($mode['mode']) . '"' . (isset($_POST['mode']) && $_POST['mode'] == $mode['mode'] ? ' selected' : '') . '>' . htmlspecialchars($mode['mode']) . '</option>';
                }
            } else {
                echo "Error fetching modes: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>

<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="municipalitySelect">Select Municipality</label>
        <select id="municipalitySelect" name="municipality" class="form-control" onchange="this.form.submit()">
            <option value="">All Municipalities</option>
            <?php
            $municipalityQuery = "SELECT DISTINCT municipality FROM tblactivity WHERE project = 'eLGU BPLS' AND municipality IS NOT NULL AND municipality != ''";
            if (isset($_POST['project']) && $_POST['project'] != '') {
                $sector = mysqli_real_escape_string($con, $_POST['project']);
                $municipalityQuery .= " AND sector = '$sector'";
            }
            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                $municipalityQuery .= " AND mode = '$mode'";
            }
            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                $municipalityQuery .= " AND barangay = '$barangay'";
            }
            $municipalitiesQuery = mysqli_query($con, $municipalityQuery);
            if ($municipalitiesQuery) {
                while ($municipality = mysqli_fetch_assoc($municipalitiesQuery)) {
                    echo '<option value="' . htmlspecialchars($municipality['municipality']) . '"' . (isset($_POST['municipality']) && $_POST['municipality'] == $municipality['municipality'] ? ' selected' : '') . '>' . htmlspecialchars($municipality['municipality']) . '</option>';
                }
            } else {
                echo "Error fetching municipalities: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>

<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="barangaySelect">Select Barangay</label>
        <select id="barangaySelect" name="barangay" class="form-control" onchange="this.form.submit()">
            <option value="">All Barangays</option>
            <?php
            $barangayQuery = "SELECT DISTINCT barangay FROM tblactivity WHERE project = 'eLGU BPLS' AND barangay IS NOT NULL AND barangay != ''";
            if (isset($_POST['project']) && $_POST['project'] != '') {
                $sector = mysqli_real_escape_string($con, $_POST['project']);
                $barangayQuery .= " AND sector = '$sector'";
            }
            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                $barangayQuery .= " AND mode = '$mode'";
            }
            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                $barangayQuery .= " AND municipality = '$municipality'";
            }
            $barangaysQuery = mysqli_query($con, $barangayQuery);
            if ($barangaysQuery) {
                while ($barangay = mysqli_fetch_assoc($barangaysQuery)) {
                    echo '<option value="' . htmlspecialchars($barangay['barangay']) . '"' . (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') . '>' . htmlspecialchars($barangay['barangay']) . '</option>';
                }
            } else {
                echo "Error fetching barangays: " . mysqli_error($con);
            }
            ?>
        </select>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
</div>
    
                <!-- DataTables Table -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            DataTables
                        </div>
                        
<div style="padding:10px; display: flex; justify-content: space-between;">
                                            <div>
                                            <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                        
                                                    <?php } elseif ($_SESSION['username'] === 'elgusdn') { ?>
                                                        <!-- Limited access for specific user -->
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    <?php } ?>
                                            </div>
                                            <div>
                                            <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                <!-- Import Button -->
                                                <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i> Import</button>
                                                <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
                                                <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload" aria-hidden="true"></i> Export</button>
                                                
                                                <?php } elseif ($_SESSION['username'] === 'elgusdn') { ?>
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
                                            <?php 
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'elgusdn') {
                                            ?>
                                                <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                <th>No.</th>
                                            <?php 
                                            }
                                            ?>
                                                            <th>Start Date</th>
                                                            <th>End Date</th>
                                                            <th>Bureau</th>
                                                            <th>Project</th>
                                                            <th>Indicator</th>
                                                            <th>Activity Name</th>
                                                            <th>Training Venue</th>
                                                            <th>Municipality/City</th>
                                                            <th>Barangay</th>
                                                            <th>District</th>
                                                            <th>Requesting Agency</th>
                                                            <th>Mode of Implementation</th>
                                                            <th>Target Sector</th>
                                                            <th>Responsible Person</th>
                                                            <th>Name of Resource Person</th>
                                                            <th>No. of Participants</th>
                                                            <th>No. of Completers</th>
                                                            <th>Male</th>
                                                            <th>Female</th>
                                                            <th>Approved Activity Design</th>
                                                            <th>Link to MOVs</th>
                                                            <th>Remarks</th>
                                                            <?php 
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'elgusdn') {
                                            ?>
                                                <th style="width: 40px !important;">Option</th>
                                            <?php 
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                        <tbody>
                                        <?php
                                                  $counter = 1;  // Initialize counter
                                                  $tableQuery = "SELECT * FROM tblactivity WHERE project = 'eLGU BPLS'";
                                                  
                                                  if (isset($_POST['project']) && $_POST['project'] != '') {
                                                      $tableQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                  }
                                                  
                                                  if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                      $tableQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                  }
                                                  
                                                  if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                                                      $tableQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                                                  }
                                                  
                                                  if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                      $tableQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                                                  }
                                                  
                                                  $tableQuery .= " ORDER BY start DESC"; // Order by start date
                                                  $result = mysqli_query($con, $tableQuery);
                                                  
                                                  if (!$result) {
                                                      die('Error: ' . mysqli_error($con));
                                                  }
                                                  
                                                  while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<tr>';
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'elgusdn') {
                                                echo '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="'.$row['id'].'" /></td>';
                                                echo ' <td>' . $counter++ . '</td>'; // Assuming 'id' is the primary key
                                            }
                                            echo '
                                                <td>' . $row['start'] . '</td>
                                                                <td>' . $row['end'] . '</td>
                                                                <td>' . $row['project'] . '</td>
                                                                <td>' . $row['subproject'] . '</td>
                                                                 <td>' . $row['indicator'] . '</td>
                                                                <td>' . $row['activity'] . '</td>
                                                                <td>' . $row['training'] . '</td>
                                                                <td>' . $row['municipality'] . '</td>
                                                                <td>' . $row['barangay'] . '</td>
                                                                <td>' . $row['district'] . '</td>
                                                                <td>' . $row['agency'] . '</td>
                                                                <td>' . $row['mode'] . '</td>
                                                                <td>' . $row['sector'] . '</td>
                                                                <td>' . $row['person'] . '</td>
                                                                <td>' . $row['resource'] . '</td>
                                                                <td>' . $row['participants'] . '</td>
                                                                <td>' . $row['completers'] . '</td>
                                                                <td>' . $row['male'] . '</td>
                                                                <td>' . $row['female'] . '</td>
                                                                <td>' . $row['approved'] . '</td>
                                                                <td>' . $row['mov'] . '</td>
                                                                <td>' . $row['remarks'] . '</td>';
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'elgusdn') {
                                                echo '<td>
                                                    <button class="btn btn-primary btn-sm btn-edit-activity" data-id="'.$row['id'].'" data-activity="'.htmlspecialchars($row['activity'], ENT_QUOTES).'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button>
                                                    <button class="btn btn-primary btn-sm btn-view-activity" data-id="'.$row['id'].'" data-activity="'.htmlspecialchars($row['activity'], ENT_QUOTES).'"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
                                                </td>';
                                            }
                                            echo '</tr>';
                                        }
                                        ?>
                                                </table>


                                    <?php include "../deleteModal.php"; ?>
                                    <?php include "edit_modal.php"; ?>
                                    <?php include "view_modal.php"; ?>

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
        <!-- jQuery 2.0.2 -->
        <?php }
        include "../footer.php"; ?>
<script type="text/javascript">

var select_all = document.getElementById("cbxMainphoto"); //select all checkbox
var checkboxes = document.getElementsByClassName("chk_deletephoto"); //checkbox items

//select all checkboxes
select_all.addEventListener("change", function(e){
    for (i = 0; i < checkboxes.length; i++) { 
        checkboxes[i].checked = select_all.checked;
    }
});


for (var i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener('change', function(e){ //".checkbox" change 
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if(this.checked == false){
            select_all.checked = false;
        }
        //check "select all" if all checkbox items are checked
        if(document.querySelectorAll('.checkbox:checked').length == checkboxes.length){
            select_all.checked = true;
        }
    });
}
    $(function() {
        $("#table").dataTable({
           "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,3 ] } ],"aaSorting": []
        });

        // Edit Activity: fetch data via AJAX and populate modal
        $(document).on('click', '.btn-edit-activity', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.getJSON('../../ajax/activity_get_item.php?action=item&id=' + id, function(data) {
                $('#edit_hidden_id').val(data.id);
                $('#edit_start').val(data.start || '');
                $('#edit_end').val(data.end || '');
                $('#edit_project').val(data.project || '');
                $('#edit_subproject').val(data.subproject || '');
                $('#edit_indicator').val(data.indicator || '');
                $('#edit_activity').val(data.activity || '');
                $('#edit_training').val(data.training || '');
                $('#edit_municipality').val(data.municipality || '');
                $('#edit_barangay').val(data.barangay || '');
                $('#edit_district').val(data.district || '');
                $('#edit_agency').val(data.agency || '');
                $('#edit_mode').val(data.mode || '');
                $('#edit_sector').val(data.sector || '');
                $('#edit_person').val(data.person || '');
                $('#edit_resource').val(data.resource || '');
                $('#edit_participants').val(data.participants || '');
                $('#edit_completers').val(data.completers || '');
                $('#edit_male').val(data.male || '');
                $('#edit_female').val(data.female || '');
                $('#edit_approved').val(data.approved || '');
                $('#edit_mov').val(data.mov || '');
                $('#edit_remarks').val(data.remarks || '');
                $('#editModal').modal('show');
            });
        });

        // View Activity: fetch photos via AJAX and populate modal
        $(document).on('click', '.btn-view-activity', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var activityName = $(this).data('activity');
            $('#view_hidden_id').val(id);
            $('#view_activity_title').text(activityName);
            $('#photoGrid').html('<p class="text-muted">Loading photos...</p>');
            $('#viewModal').modal('show');

            $.getJSON('../../ajax/activity_get_item.php?action=photos&id=' + id, function(photos) {
                if (photos.length === 0) {
                    $('#photoGrid').html('<p class="text-muted">No photos found.</p>');
                    return;
                }
                var html = '';
                for (var i = 0; i < photos.length; i++) {
                    var p = photos[i];
                    var filePath = 'photo/' + p.filename;
                    var ext = p.filename.split('.').pop().toLowerCase();
                    html += '<div class="col-md-4">';
                    html += '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />';
                    html += '<div class="file-item">';
                    if (['jpg','jpeg','png','gif'].indexOf(ext) !== -1) {
                        html += '<img src="' + filePath + '" alt="' + p.filename + '" class="file-thumbnail"/>';
                    } else if (ext === 'pdf') {
                        html += '<div class="file-thumbnail-pdf"><embed src="' + filePath + '" type="application/pdf" width="100%" height="100%" /></div>';
                    } else {
                        html += '<div class="file-thumbnail">File type not previewable</div>';
                    }
                    html += '<div class="file-info"><span class="filename">' + p.filename.replace(/\d+/g, '') + '</span>';
                    html += '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a></div></div></div>';
                }
                $('#photoGrid').html(html);
                bindPhotoCheckboxes();
            });
        });

        function bindPhotoCheckboxes() {
            var selectAll = document.getElementById("cbxMainphoto");
            var cbs = document.getElementsByClassName("chk_deletephoto");
            if (selectAll) {
                selectAll.checked = false;
                selectAll.onchange = function() {
                    for (var j = 0; j < cbs.length; j++) cbs[j].checked = selectAll.checked;
                };
            }
        }
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
            
        document.getElementById('importBtn').addEventListener('click', function() {
                document.getElementById('importFile').click();
            });

            document.getElementById('importFile').addEventListener('change', function() {
                var formData = new FormData();
                formData.append('file', this.files[0]);

                fetch('import.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        showToast('Data imported successfully!', 'success');
                        location.reload();
                    } else {
                        console.error('[Activity Import Error]', data.error);
                        showToast(data.error || 'Import failed.', 'error');
                    }
                }).catch(function(error) {
                    console.error('[Activity Import Error]', error);
                    showToast('Import failed. Check console for details.', 'error');
                });
            });
// Add event listener for the export button
document.getElementById('exportBtn').addEventListener('click', function() {
    // Get the selected values from the filters
    var selectedMode = document.getElementById('modeSelect').value; // Assuming there's a mode select element
    var selectedSector = document.getElementById('projectSelect').value;
    var selectedMunicipality = document.getElementById('municipalitySelect').value; // Assuming there's a municipality select element
    var selectedBarangay = document.getElementById('barangaySelect').value; // Assuming there's a barangay select element

    // Construct the URL for the export.php script with the selected filters as GET parameters
    var exportUrl = 'exportelgu.php?mode=' + encodeURIComponent(selectedMode) +
                    '&sector=' + encodeURIComponent(selectedSector) +
                    '&municipality=' + encodeURIComponent(selectedMunicipality) +
                    '&barangay=' + encodeURIComponent(selectedBarangay);

    // whiteirect to the constructed URL
    window.location.href = exportUrl;
});

</script>

<style>
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
            height: 55px; /* Adjust the size as needed */
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
        /* Adjust table layout to auto for column width based on content */
        table {
            table-layout: auto;
            width: 100%;
        }

        /* Ensure header text wraps to two lines */
        table th {
            white-space: normal;
            text-align: center; /* Center-align headers if needed */
            word-wrap: break-word; /* Allow long words to break */
            overflow-wrap: break-word; /* Ensure long words break in modern browsers */
            max-width: 200px; /* Example max-width to limit header width */
        }

        table td {
            white-space: nowrap; /* Ensure cell text does not wrap */
        }

        /* Optional: Adjust column widths if necessary */
        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        /* Optional: Adjust specific column widths */
        table th:nth-child(1) { width: 30px; } /* Example width for checkbox column */
        table th:nth-child(2) { width: 50px; } /* Example width for No. column */
        /* Add more specific widths as needed */

   
            /* Ensure header text does not wrap */
            table th {
                white-space: nowrap;
                text-align: center; /* Center-align headers if needed */
            }

            /* Optional: Adjust column widths if necessary */
            table th, table td {
                padding: 8px;
                border: 1px solid #ddd;
            }

            /* Optional: Adjust specific column widths */
            table th:nth-child(1) { width: 30px; } /* Example width for checkbox column */
            table th:nth-child(2) { width: 50px; } /* Example width for No. column */
            /* Add more specific widths as needed */
            <style>
    /* File item container */
    .file-item {
        position: relative;
        text-align: center;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        background-color: #f9f9f9;
        width: 100%;
        height: 250px; /* Fixed height for uniformity */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease; /* Smooth transition for hover effects */
    }

    /* Thumbnail styling (uniform for all file types) */
    .file-thumbnail,
    .file-thumbnail-pdf,
    .file-thumbnail-office {
        width: 150px; /* Fixed width */
        height: 150px; /* Fixed height */
        object-fit: cover; /* Ensures images and other content fill the area */
        margin-bottom: 15px; /* Uniform space between thumbnail and filename */
        transition: transform 0.3s ease; /* Smooth transition for hover effect */
    }

    /* Hover effect only on the file thumbnail */
    .file-thumbnail:hover, 
    .file-thumbnail-pdf:hover, 
    .file-thumbnail-office:hover {
        transform: scale(1.05); /* Slight zoom on hover for thumbnails */
    }

    /* For PDFs - embed PDF into the same size container */
    .file-thumbnail-pdf {
        background-color: #f4f4f4;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40px;
        color: #444;
    }

    /* For Office files like Word, Excel, PowerPoint */
    .file-thumbnail-office {
        background-color: #e6e6e6;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40px;
        color: #444;
    }

    /* Filename styling */
    .filename {
        font-size: 12px; /* Smaller font size for filename */
        color: #333;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis; /* Truncate long filenames */
        white-space: nowrap;
        max-width: 100px; /* Limit the width for better alignment */
        text-align: center; /* Center the filename text */
        margin-bottom: 5px; /* Consistent space between filename and download icon */
    }

    /* Container for the filename and download button */
    .file-info {
        display: flex;
        align-items: center; /* Align text and icon vertically */
        justify-content: center;
        margin-top: 0; /* Remove any additional top margin */
    }

    /* Download icon styling */
    .download-btn {
        font-size: 12px; /* Smaller size for the download icon */
        color: #007bff;
        text-decoration: none;
        padding: 0 5px;
        vertical-align: middle; /* Align it with the text */
    }

    .download-btn i {
        font-size: 14px; /* Matching the icon size with filename size */
        vertical-align: middle; /* Align it with the text */
    }

    /* Checkbox styling */
    .file-item input[type="checkbox"] {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
    }
    .header-title {
            display: flex;
            align-items: center; /* Align items vertically */
            justify-content: space-between; /* Space between logo/title and date/time */
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