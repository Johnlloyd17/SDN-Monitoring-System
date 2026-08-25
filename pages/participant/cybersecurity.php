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

        <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/cyber.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">Cybersecurity</h3>
                        <p class="header-address">Activity Participants</p>
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
                                        Statistics
                                    </div>
                                
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                            <div class="row">
                                                <!-- Total Participants -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                <a href="../participant/cyber_participant.php"><div class="info-box">
                                                        <span class="info-box-icon bg-red">
                                                            <img src="icons/participants_1.png" alt="Total Activities" style="width: 54px; height: 54px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Participants</span>
                                                            <span class="info-box-number" id="totalParticipants">
                                                            <?php
                            $filterQuery = "SELECT COUNT(*) AS total_participants FROM tblparticipant WHERE project = 'Cybersecurity'";
                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                            }
                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                            }
                            if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                            }
                            if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                            }
                            $result = mysqli_query($con, $filterQuery);
                            $data = mysqli_fetch_assoc($result);
                            echo $data['total_participants'];
                            ?>
                        </span></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Total Male -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_male.php"><div class="info-box">
                                                        <span class="info-box-icon bg-blue">
                                                            <img src="icons/male_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Male</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_male FROM tblparticipant WHERE sex = 'Male' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_male'];
                                                                ?></a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Total Female -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_female.php"><div class="info-box">
                                                        <span class="info-box-icon bg-red">
                                                            <img src="icons/female_1.png" alt="Total Activities" style="width: 60px; height: 60px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Female</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_female FROM tblparticipant WHERE sex = 'Female' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_female'];
                                                                ?></a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Total Face-to-Face -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_facetoface.php"><div class="info-box">
                                                        <span class="info-box-icon bg-yellow">
                                                            <img src="icons/face-to-face_1.png" alt="Total Activities" style="width: 53px; height: 53px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Face-to-Face</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_face_to_face FROM tblparticipant WHERE mode = 'Face-to-Face' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_face_to_face'];
                                                                ?></a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> <!-- End of first row -->

                                            <div class="row">
                                                <!-- Total Virtual -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_virtual.php"><div class="info-box">
                                                        <span class="info-box-icon bg-blue">
                                                            <img src="icons/virtual_1.png" alt="Total Activities" style="width: 46px; height: 46px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Virtual</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_virtual FROM tblparticipant WHERE mode = 'Virtual' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_virtual'];
                                                                ?></a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Total Awareness -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_awareness.php"><div class="info-box">
                                                        <span class="info-box-icon bg-red">
                                                            <img src="icons/awareness_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Awareness</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_awareness FROM tblparticipant WHERE indicator = 'Awareness' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_awareness'];
                                                                ?></a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Total Training -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_training.php"><div class="info-box">
                                                        <span class="info-box-icon bg-blue">
                                                            <img src="icons/training_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Training</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_training FROM tblparticipant WHERE indicator = 'Training' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_training'];
                                                                ?></a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Total Orientation -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../participant/cyber_orientation.php"><div class="info-box">
                                                        <span class="info-box-icon bg-yellow">
                                                            <img src="icons/orientation_1.png" alt="Total Activities" style="width: 60px; height: 60px;">
                                                        </span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Total Orientation</span>
                                                            <span class="info-box-number">
                                                                <?php
                                                                $filterQuery = "SELECT COUNT(*) AS total_orientation FROM tblparticipant WHERE indicator = 'Orientation' AND project = 'Cybersecurity'";
                                                                if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                    $filterQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                                }
                                                                if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                    $filterQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                                }
                                                                if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                    $filterQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                                }
                                                                if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                    $filterQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                                }
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['total_orientation'];
                                                                ?></a>
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
                                                            $sectorQuery = "SELECT DISTINCT sector FROM tblparticipant WHERE project = 'Cybersecurity'";
                                                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                                                                $sectorQuery .= " AND mode = '$mode'";
                                                            }
                                                            if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                                $indicator = mysqli_real_escape_string($con, $_POST['indicator']);
                                                                $sectorQuery .= " AND indicator = '$indicator'";
                                                            }
                                                            if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                $sex = mysqli_real_escape_string($con, $_POST['sex']);
                                                                $sectorQuery .= " AND sex = '$sex'";
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
                                                </div><!-- End of sector filter -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="modeSelect">Select Mode of Implementation</label>
                                                        <select id="modeSelect" name="mode" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Modes of Implementation</option>
                                                            <?php
                                                            $modeQuery = "SELECT DISTINCT mode FROM tblparticipant WHERE project = 'Cybersecurity'";
                                                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                $project = mysqli_real_escape_string($con, $_POST['project']);
                                                                $modeQuery .= " AND sector = '$project'";
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
                                                </div><!-- End of mode filter -->
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="indicatorSelect">Select Indicator</label>
                                                        <select id="indicatorSelect" name="indicator" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Indicators</option>
                                                            <?php
                                                           $indicatorQuery = "SELECT DISTINCT indicator FROM tblparticipant WHERE indicator != '' AND project = 'Cybersecurity'";

                                                            if (isset($_POST['project']) && $_POST['project'] != '') {
                                                                $project = mysqli_real_escape_string($con, $_POST['project']);
                                                                $indicatorQuery .= " AND sector = '$project'";
                                                            }
                                                            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                                                                $indicatorQuery .= " AND mode = '$mode'";
                                                            }
                                                            if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                                $sex = mysqli_real_escape_string($con, $_POST['sex']);
                                                                $indicatorQuery .= " AND sex = '$sex'";
                                                            }
                                                            $indicatorsQuery = mysqli_query($con, $indicatorQuery);
                                                            if ($indicatorsQuery) {
                                                                while ($indicator = mysqli_fetch_assoc($indicatorsQuery)) {
                                                                    echo '<option value="' . htmlspecialchars($indicator['indicator']) . '"' . (isset($_POST['indicator']) && $_POST['indicator'] == $indicator['indicator'] ? ' selected' : '') . '>' . htmlspecialchars($indicator['indicator']) . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching indicators: " . mysqli_error($con);
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                               
                                                <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="sexSelect">Select Sex</label>
        <select id="sexSelect" name="sex" class="form-control" onchange="this.form.submit()">
            <option value="">All Sexes</option>
            <?php 
            $sexQuery = "SELECT DISTINCT sex FROM tblparticipant WHERE project = 'Cybersecurity' AND sex IS NOT NULL AND TRIM(sex) != ''";
            
            if (isset($_POST['project']) && $_POST['project'] != '') {
                $project = mysqli_real_escape_string($con, $_POST['project']);
                $sexQuery .= " AND sector = '$project'";
            }
            if (isset($_POST['mode']) && $_POST['mode'] != '') {
                $mode = mysqli_real_escape_string($con, $_POST['mode']);
                $sexQuery .= " AND mode = '$mode'";
            }
            if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                $indicator = mysqli_real_escape_string($con, $_POST['indicator']);
                $sexQuery .= " AND indicator = '$indicator'";
            }
            
            $sexsQuery = mysqli_query($con, $sexQuery);
            if ($sexsQuery) {
                while ($sex = mysqli_fetch_assoc($sexsQuery)) {
                    echo '<option value="' . htmlspecialchars($sex['sex']) . '"' . 
                        (isset($_POST['sex']) && $_POST['sex'] == $sex['sex'] ? ' selected' : '') . 
                        '>' . htmlspecialchars($sex['sex']) . '</option>';
                }
            } else {
                echo "Error fetching sexes: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>

                                                </div>
                                            </div> <!-- End of filter row -->
                                        </form>
                                
                                        <div style="padding:10px; display: flex; justify-content: space-between;">
                                            <div>
                                            <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Participant</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                        
                                                    <?php } elseif ($_SESSION['username'] === 'cybersecuritysdn') { ?>
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
                                                
                                                <?php } elseif ($_SESSION['username'] === 'cybersecuritysdn') { ?>
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
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'cybersecuritysdn') {
                                            ?>
                                                <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                <th>No.</th>
                                            <?php 
                                            }
                                            ?>
                                                                <th>Start Date</th>
                                                                <th>End Date</th>
                                                                <th>Activity Name</th>
                                                                <th>Indicators</th>
                                                                <th>Fullname</th>
                                                                <th>Sex</th>
                                                                <th>Contact</th>
                                                                <th>Email Address</th>
                                                                <th>Mode of Implementation</th>
                                                                <th>Agency</th>
                                                                <th>Target Sector</th>
                                                                <th>Project</th>
                                                                <th>Responsible Person</th>
                                                                <th>Remarks</th>
                                                                <?php 
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'cybersecuritysdn') {
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
                                                        $tableQuery = "SELECT * FROM tblparticipant WHERE project = 'Cybersecurity'";
                                                        
                                                        if (isset($_POST['project']) && $_POST['project'] != '') {
                                                            $tableQuery .= " AND sector = '" . mysqli_real_escape_string($con, $_POST['project']) . "'";
                                                        }
                                                        
                                                        if (isset($_POST['mode']) && $_POST['mode'] != '') {
                                                            $tableQuery .= " AND mode = '" . mysqli_real_escape_string($con, $_POST['mode']) . "'";
                                                        }
                                                        
                                                        if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                            $tableQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                        }
                                                        
                                                        if (isset($_POST['sex']) && $_POST['sex'] != '') {
                                                            $tableQuery .= " AND sex = '" . mysqli_real_escape_string($con, $_POST['sex']) . "'";
                                                        }
                                                        
                                                        $tableQuery .= " ORDER BY start DESC"; // Order by start date
                                                        $result = mysqli_query($con, $tableQuery);
                                                        
                                                        if (!$result) {
                                                            die('Error: ' . mysqli_error($con));
                                                        }
                                                        
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            echo '<tr>';
                                                    if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'cybersecuritysdn') {
                                                        echo '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="'.$row['id'].'" /></td>';
                                                        echo ' <td>' . $counter++ . '</td>'; // Assuming 'id' is the primary key
                                                    }
                                                    echo '
                                                        <td>' . $row['start'] . '</td>
                                                                    <td>' . $row['end'] . '</td>
                                                                    <td>' . $row['activity'] . '</td>
                                                                    <td>' . $row['indicator'] . '</td>
                                                                    <td>' . $row['fullname'] . '</td>
                                                                    <td>' . $row['sex'] . '</td>
                                                                    <td>' . $row['contact'] . '</td>
                                                                    <td>' . $row['email'] . '</td>
                                                                    <td>' . $row['mode'] . '</td>
                                                                    <td>' . $row['agency'] . '</td>
                                                                    <td>' . $row['sector'] . '</td>
                                                                    <td>' . $row['project'] . '</td>
                                                                    <td>' . $row['person'] . '</td>
                                                                     <td>' . $row['remarks'] . '</td>';
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'cybersecuritysdn') {
                                                echo '<td>
                                                    <button class="btn btn-primary btn-sm btn-edit-item" data-id="'.$row['id'].'" data-name="'.htmlspecialchars($row['fullname'], ENT_QUOTES).'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button>
                                                    <button class="btn btn-primary btn-sm btn-view-item" data-id="'.$row['id'].'" data-name="'.htmlspecialchars($row['fullname'], ENT_QUOTES).'"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
                                                </td>';
                                            }
                                            echo '</tr>';
                                        }
                                        ?>
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

            <?php include "edit_modal.php"; ?>
            <?php include "view_modal.php"; ?>

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

        $(document).on('click', '.btn-edit-item', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: '../../ajax/participant_get_item.php?action=item&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#edit_hidden_id').val(data.id);
                    $('#edit_start').val(data.start);
                    $('#edit_end').val(data.end);
                    $('#edit_activity').val(data.activity);
                    $('#edit_indicator').val(data.indicator);
                    $('#edit_fullname').val(data.fullname);
                    $('#edit_sex').val(data.sex);
                    $('#edit_contact').val(data.contact);
                    $('#edit_email').val(data.email);
                    $('#edit_mode').val(data.mode);
                    $('#edit_agency').val(data.agency);
                    $('#edit_sector').val(data.sector);
                    $('#edit_project').val(data.project);
                    $('#edit_person').val(data.person);
                    $('#edit_remarks').val(data.remarks);
                    $('#editModal').modal('show');
                }
            });
        });

        $(document).on('click', '.btn-view-item', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#view_item_title').text(name);
            $('#view_hidden_id').val(id);
            $.ajax({
                url: '../../ajax/participant_get_item.php?action=photos&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(photos) {
                    var html = '';
                    if (photos.length === 0) {
                        html = '<div class="col-md-12 text-center"><p>No photos found.</p></div>';
                    } else {
                        for (var i = 0; i < photos.length; i++) {
                            var p = photos[i];
                            var filePath = 'photo/' + p.filename;
                            var ext = p.filename.split('.').pop().toLowerCase();
                            var thumb = '';
                            if (['jpg','jpeg','png','gif'].indexOf(ext) !== -1) {
                                thumb = '<img src="' + filePath + '" alt="' + p.filename + '" class="file-thumbnail"/>';
                            } else if (ext === 'pdf') {
                                thumb = '<div class="file-thumbnail-pdf"><embed src="' + filePath + '" type="application/pdf" width="100%" height="100%" /></div>';
                            } else if (['docx','xlsx','pptx'].indexOf(ext) !== -1) {
                                thumb = '<div class="file-thumbnail-office"><i class="fas fa-file-word"></i></div>';
                            } else {
                                thumb = '<div class="file-thumbnail">File type not previewable</div>';
                            }
                            var nameWithoutNums = p.filename.replace(/\d+/g, '');
                            html += '<div class="col-md-4">' +
                                '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />' +
                                '<div class="file-item">' + thumb +
                                '<div class="file-info"><span class="filename">' + nameWithoutNums + '</span>' +
                                '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                                '</div></div></div>';
                        }
                    }
                    $('#photoGrid').html(html);
                    $('#viewModal').modal('show');
                }
            });
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
                        location.reload(); // Reload the page to show updated data
                    } else {
                        alert(data.error);
                    }
                }).catch(error => console.error('Error:', error));
            });

         // Export functionality with multiple filters
document.getElementById('exportBtn').addEventListener('click', function() {
    // Get values from the filters
    var selectedProject = document.getElementById('projectSelect').value;
    var selectedMode = document.getElementById('modeSelect').value;
    var selectedIndicator = document.getElementById('indicatorSelect').value;
    var selectedSex = document.getElementById('sexSelect').value;

    // Construct query parameters
    var queryParams = [];
    if (selectedProject) queryParams.push('project=' + encodeURIComponent(selectedProject));
    if (selectedMode) queryParams.push('mode=' + encodeURIComponent(selectedMode));
    if (selectedIndicator) queryParams.push('indicator=' + encodeURIComponent(selectedIndicator));
    if (selectedSex) queryParams.push('sex=' + encodeURIComponent(selectedSex));

    // Redirect to the export.php script with the selected filters as GET parameters
    window.location.href = 'exportcyber.php?' + queryParams.join('&');
});

// Update statistics dynamically when any filter is changed
document.querySelectorAll('.filterSelect').forEach(function(filter) {
    filter.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

            

</script>

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