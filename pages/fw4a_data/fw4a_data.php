<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
    $isAdmin = ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'fwfasdn');
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
                        <img src="icons/fwfa_logo.png" alt="Logo" class="header-logo" />
                        <div class="header-info">
                            <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                            <p class="header-address">Monitoring</p>
                        </div>
                        <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                    </div>
                </section>
                <section class="content">
                    <div id="ajaxToast" class="alert alert-success" style="position: fixed; top: 1em; right: 1em; z-index: 9999; display: none;"></div>
                    <div class="row">
                        <div class="box">
                            <div class="box-header">
                                <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            Overview of Internet Access Points
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <a href="../fw4a_data/fw4a_penetration.php">
                                                        <div class="info-box">
                                                            <span class="info-box-icon bg-blue">
                                                                <img src="icons/municipality_1.png" alt="Total Participants" style="width: 50px; height: 50px;">
                                                            </span>
                                                            <div class="info-box-content">
                                                                <span class="info-box-text">LGU Penetration Rate</span>
                                                                <span class="info-box-number"><span id="statPenetration">
                                                                        <?php
                                                                        if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                                                            $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                                                            $barangayWithWifiQuery = "SELECT COUNT(DISTINCT barangay) AS barangay_with_wifi FROM tblfwfa WHERE locality = '$locality' AND status = 'Active'";
                                                                            $wifiResult = mysqli_query($con, $barangayWithWifiQuery);
                                                                            $wifiCount = mysqli_fetch_assoc($wifiResult)['barangay_with_wifi'];

                                                                            $totalBarangayQuery = "SELECT barangay_count FROM tblsdn WHERE municipality = '$locality'";
                                                                            $totalResult = mysqli_query($con, $totalBarangayQuery);
                                                                            $totalCount = mysqli_fetch_assoc($totalResult)['barangay_count'];

                                                                            if ($totalCount > 0) {
                                                                                $penetrationRate = ($wifiCount / $totalCount) * 100;
                                                                                echo number_format($penetrationRate, 2) . '%';
                                                                            } else {
                                                                                echo 'N/A';
                                                                            }
                                                                        } else {
                                                                            $totalMunicipalitiesQuery = "SELECT COUNT(*) AS total_municipalities FROM tblsdn";
                                                                            $totalMunicipalitiesResult = mysqli_query($con, $totalMunicipalitiesQuery);
                                                                            $totalMunicipalitiesCount = mysqli_fetch_assoc($totalMunicipalitiesResult)['total_municipalities'];

                                                                            $activeMunicipalitiesQuery = "SELECT COUNT(DISTINCT locality) AS active_municipalities FROM tblfwfa WHERE status = 'Active'";
                                                                            $activeMunicipalitiesResult = mysqli_query($con, $activeMunicipalitiesQuery);
                                                                            $activeMunicipalitiesCount = mysqli_fetch_assoc($activeMunicipalitiesResult)['active_municipalities'];

                                                                            if ($totalMunicipalitiesCount > 0) {
                                                                                $overallPenetrationRate = ($activeMunicipalitiesCount / $totalMunicipalitiesCount) * 100;
                                                                                echo number_format($overallPenetrationRate, 2) . '%';
                                                                            } else {
                                                                                echo 'N/A';
                                                                            }
                                                                        }
                                                                        ?></span>
                                                    </a>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Barangay Penetration Rate -->
                                        <div class="col-md-3 col-sm-6 col-xs-12">
                                            <a href="../fw4a_data/fw4a_brgy.php">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-red">
                                                        <img src="icons/barangays_1.png" alt="Total Participants" style="width: 60px; height: 60px;">
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Barangay Penetration Rate</span>
                                                        <span class="info-box-number"><span id="statBrgy">
                                                                <?php
                                                                if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                                    echo 'N/A';
                                                                } else {
                                                                    $totalBarangaysWithWifiQuery = "SELECT COUNT(DISTINCT barangay, locality) AS barangays_with_wifi FROM tblfwfa WHERE status = 'Active'";
                                                                    $wifiCountResult = mysqli_query($con, $totalBarangaysWithWifiQuery);
                                                                    $wifiCount = mysqli_fetch_assoc($wifiCountResult)['barangays_with_wifi'];

                                                                    $totalBarangayCountQuery = "SELECT SUM(barangay_count) AS total_barangay_count FROM tblsdn";
                                                                    $totalCountResult = mysqli_query($con, $totalBarangayCountQuery);
                                                                    $totalCount = mysqli_fetch_assoc($totalCountResult)['total_barangay_count'];

                                                                    if ($totalCount > 0) {
                                                                        $barangayPenetrationRate = ($wifiCount / $totalCount) * 100;
                                                                        echo number_format($barangayPenetrationRate, 2) . '%';
                                                                    } else {
                                                                        echo 'N/A';
                                                                    }
                                                                }
                                                                ?>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- Active Access Points -->
                                        <div class="col-md-3 col-sm-6 col-xs-12">
                                            <a href="../fw4a_data/fw4a_active.php">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-yellow">
                                                        <img src="icons/active_1.png" alt="Total Participants" style="width: 50px; height: 50px;">
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Active Access Points</span>
                                                        <span class="info-box-number"><span id="statActive">
                                                                <?php
                                                                $strategyFilter = !empty($_POST['strategy']) ? " AND strategy = '" . mysqli_real_escape_string($con, $_POST['strategy']) . "'" : '';
                                                                $typeFilter = !empty($_POST['type']) ? " AND type = '" . mysqli_real_escape_string($con, $_POST['type']) . "'" : '';
                                                                $localityFilter = !empty($_POST['locality']) ? " AND locality = '" . mysqli_real_escape_string($con, $_POST['locality']) . "'" : '';
                                                                $barangayFilter = !empty($_POST['barangay']) ? " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'" : '';

                                                                $filterQuery = "SELECT COUNT(*) AS active_access_points FROM tblfwfa WHERE status = 'Active'" . $strategyFilter . $typeFilter . $localityFilter . $barangayFilter;
                                                                $q = mysqli_query($con, $filterQuery);
                                                                $result = mysqli_fetch_assoc($q);
                                                                echo $result['active_access_points'];
                                                                ?></span>
                                            </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inactive Access Points -->
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <a href="../fw4a_data/fw4a_inactive.php">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-blue">
                                                <img src="icons/deactivated_1.png" alt="Total Participants" style="width: 50px; height: 50px;">
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Inactive Access Points</span>
                                                <span class="info-box-number"><span id="statInactive">
                                                        <?php
                                                        $filterQuery = "SELECT COUNT(*) AS Inactive_access_points FROM tblfwfa WHERE status = 'Inactive'" . $strategyFilter . $typeFilter . $localityFilter . $barangayFilter;
                                                        $q = mysqli_query($con, $filterQuery);
                                                        $result = mysqli_fetch_assoc($q);
                                                        echo $result['Inactive_access_points'];
                                                        ?></span>
                                    </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="strategySelect">Select Strategy</label>
                                <select id="strategySelect" name="strategy" class="form-control full-width-select">
                                    <option value="">All Strategy</option>
                                    <?php
                                    // Fetching distinct strategies based on selected filters
                                    $strategyQuery = "SELECT DISTINCT strategy FROM tblfwfa WHERE strategy != ''";
                                    if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                        $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                        $strategyQuery .= " AND locality = '$locality'";
                                    }
                                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                        $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                        $strategyQuery .= " AND barangay = '$barangay'";
                                    }
                                    if (isset($_POST['status']) && $_POST['status'] != '') {
                                        $status = mysqli_real_escape_string($con, $_POST['status']);
                                        $strategyQuery .= " AND status = '$status'";
                                    }
                                    if (isset($_POST['type']) && $_POST['type'] != '') {
                                        $type = mysqli_real_escape_string($con, $_POST['type']);
                                        $strategyQuery .= " AND type = '$type'";
                                    }

                                    $strategyQuery .= " ORDER BY strategy ASC";
                                    $agenciesQuery = mysqli_query($con, $strategyQuery);
                                    while ($strategy = mysqli_fetch_assoc($agenciesQuery)) {
                                        echo '<option value="' . $strategy['strategy'] . '"' . (isset($_POST['strategy']) && $_POST['strategy'] == $strategy['strategy'] ? ' selected' : '') . '>' . $strategy['strategy'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="typeSelect">Select Type</label>
                                <select id="typeSelect" name="type" class="form-control full-width-select">
                                    <option value="">All Type</option>
                                    <?php
                                    // Fetching distinct types based on selected filters
                                    $typeQuery = "SELECT DISTINCT type FROM tblfwfa WHERE type != ''";
                                    if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                        $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                        $typeQuery .= " AND locality = '$locality'";
                                    }
                                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                        $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                        $typeQuery .= " AND barangay = '$barangay'";
                                    }
                                    if (isset($_POST['status']) && $_POST['status'] != '') {
                                        $status = mysqli_real_escape_string($con, $_POST['status']);
                                        $typeQuery .= " AND status = '$status'";
                                    }
                                    if (isset($_POST['strategy']) && $_POST['strategy'] != '') {
                                        $strategy = mysqli_real_escape_string($con, $_POST['strategy']);
                                        $typeQuery .= " AND strategy = '$strategy'";
                                    }
                                    $typeQuery .= " ORDER BY type ASC";
                                    $agenciesQuery = mysqli_query($con, $typeQuery);
                                    while ($type = mysqli_fetch_assoc($agenciesQuery)) {
                                        echo '<option value="' . $type['type'] . '"' . (isset($_POST['type']) && $_POST['type'] == $type['type'] ? ' selected' : '') . '>' . $type['type'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="localitySelect">Select Municipality</label>
                                <select id="localitySelect" name="locality" class="form-control full-width-select">
                                    <option value="">All Municipalities</option>
                                    <?php
                                    // Fetching distinct localities based on selected filters
                                    $localityQuery = "SELECT DISTINCT locality FROM tblfwfa WHERE locality != ''";
                                    if (isset($_POST['strategy']) && $_POST['strategy'] != '') {
                                        $strategy = mysqli_real_escape_string($con, $_POST['strategy']);
                                        $localityQuery .= " AND strategy = '$strategy'";
                                    }
                                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                        $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                        $localityQuery .= " AND barangay = '$barangay'";
                                    }
                                    if (isset($_POST['status']) && $_POST['status'] != '') {
                                        $status = mysqli_real_escape_string($con, $_POST['status']);
                                        $localityQuery .= " AND status = '$status'";
                                    }
                                    if (isset($_POST['type']) && $_POST['type'] != '') {
                                        $type = mysqli_real_escape_string($con, $_POST['type']);
                                        $localityQuery .= " AND type = '$type'";
                                    }
                                    $localityQuery .= " ORDER BY locality ASC";

                                    $localitysQuery = mysqli_query($con, $localityQuery);
                                    while ($locality = mysqli_fetch_assoc($localitysQuery)) {
                                        echo '<option value="' . $locality['locality'] . '"' . (isset($_POST['locality']) && $_POST['locality'] == $locality['locality'] ? ' selected' : '') . '>' . $locality['locality'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label for="barangaySelect">Select Barangay</label>
                                <select id="barangaySelect" name="barangay" class="form-control full-width-select">
                                    <option value="">All Barangay</option>
                                    <?php
                                    // Fetching distinct barangays based on selected filters in ascending order
                                    $barangayQuery = "SELECT DISTINCT barangay FROM tblfwfa WHERE barangay != ''";

                                    if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                        $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                        $barangayQuery .= " AND locality = '$locality'";
                                    }
                                    if (isset($_POST['strategy']) && $_POST['strategy'] != '') {
                                        $strategy = mysqli_real_escape_string($con, $_POST['strategy']);
                                        $barangayQuery .= " AND strategy = '$strategy'";
                                    }
                                    if (isset($_POST['status']) && $_POST['status'] != '') {
                                        $status = mysqli_real_escape_string($con, $_POST['status']);
                                        $barangayQuery .= " AND status = '$status'";
                                    }
                                    if (isset($_POST['type']) && $_POST['type'] != '') {
                                        $type = mysqli_real_escape_string($con, $_POST['type']);
                                        $barangayQuery .= " AND type = '$type'";
                                    }
                                    // Add ORDER BY clause to sort the results in ascending order
                                    $barangayQuery .= " ORDER BY barangay ASC";

                                    $barangaysQuery = mysqli_query($con, $barangayQuery);
                                    while ($barangay = mysqli_fetch_assoc($barangaysQuery)) {
                                        echo '<option value="' . $barangay['barangay'] . '"' .
                                            (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') .
                                            '>' . $barangay['barangay'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
        </div>

        <!-- ========================= MAP SECTION ========================= -->
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center; cursor:pointer; padding:10px 15px;"
                        data-toggle="collapse" data-target="#mapCollapse">
                        <span><i class="fa fa-map-marker"></i> Access Points Map</span>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <button class="btn btn-xs btn-default" id="mapToggleView" title="Toggle Heatmap/Locators" onclick="event.stopPropagation();">
                                <i class="fa fa-fire"></i> Heatmap
                            </button>
                            <i class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                    <div id="mapCollapse" class="collapse in">
                        <div class="panel-body" style="padding:0;">
                            <div id="fw4aMap" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ========================= END MAP SECTION ========================= -->

        <!-- Toolbar: Add + Delete + Row Filter (left) | Search + Import + Export (right) -->
        <div style="padding:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <?php if ($isAdmin) { ?>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus"></i> Add Activity</button>
                    <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete</button>
                <?php } ?>
                <label style="margin:0; font-weight:normal;">Show </label>
                <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="150">150</option>
                    <option value="200">200</option>
                </select>
                <label style="margin:0; font-weight:normal;"> entries</label>
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <div class="input-group" style="width:300px;">
                    <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search access points..." />
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                        <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                    </span>
                </div>
                <?php if ($isAdmin) { ?>
                    <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Import</button>
                    <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
                    <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> Export</button>
                <?php } ?>
            </div>
        </div>

        <div class="box-body table-responsive">
            <table id="table" class="table table-bordered">
                <thead>
                    <tr>
                        <?php if ($isAdmin) { ?>
                            <th rowspan="2" style="width: 20px !important;"><input type="checkbox" id="cbxMain" /></th>
                            <th rowspan="2">Item No.</th>
                        <?php } ?>
                        <th rowspan="2">Locality</th>
                        <th rowspan="2">Barangay</th>
                        <th rowspan="2">District</th>
                        <th rowspan="2">Transport Location</th>
                        <th rowspan="2">Transport Type</th>
                        <th rowspan="2">Site Locations</th>
                        <th rowspan="2">Site Code</th>
                        <th rowspan="2">Nationwide ID</th>
                        <th rowspan="2">Site Type</th>
                        <th colspan="2">Date of Activation</th>
                        <th colspan="2">Coordinates</th>
                        <th rowspan="2">Strategy</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Remarks</th>
                        <?php if ($isAdmin) { ?>
                            <th rowspan="2" style="width: 80px !important;">Option</th>
                        <?php } ?>
                    </tr>
                    <tr>
                        <th>Date of Activation</th>
                        <th>Current Date of Acceptance</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="19" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div><!-- /.box-body -->

        <!-- Bottom bar: Info text (left) + Pagination (right) -->
        <div style="padding:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
            <div id="paginationInfo" class="text-muted"></div>
            <ul class="pagination" style="margin:0;" id="pagination"></ul>
        </div>
        </div><!-- /.box -->

        <?php include "../edit_notif.php"; ?>
        <?php include "../added_notif.php"; ?>
        <?php include "../delete_notif.php"; ?>
        <?php include "../duplicate_error.php"; ?>

        <?php include "add_modal.php"; ?>

        <!-- ========================= EDIT MODAL (Single Dynamic) ======================= -->
        <div id="editModal" class="modal fade">
            <form id="editForm">
                <div class="modal-dialog modal-lg" style="width:750px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Edit Access Point</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="editAlert" style="display:none;"></div>
                            <input type="hidden" name="hidden_id" id="edit_hidden_id" />
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group"><label>Locality:</label><input type="text" name="txt_edit_locality" id="edit_locality" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Barangay:</label><input type="text" name="txt_edit_barangay" id="edit_barangay" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>District:</label><input type="text" name="txt_edit_district" id="edit_district" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Transport Location:</label><input type="text" name="txt_edit_transport_location" id="edit_transport_location" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Transport Type:</label><input type="text" name="txt_edit_transport_type" id="edit_transport_type" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Locations:</label><input type="text" name="txt_edit_locations" id="edit_locations" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Site Type:</label><input type="text" name="txt_edit_type" id="edit_type" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Site Code:</label><input type="text" name="txt_edit_code" id="edit_code" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Nationwide ID:</label><input type="text" name="txt_edit_nationwide_id" id="edit_nationwide_id" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Date of Activation:</label><input type="date" name="txt_edit_date_of_activation" id="edit_date_of_activation" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Current Date of Acceptance:</label><input type="date" name="txt_edit_current_date_of_acceptance" id="edit_current_date_of_acceptance" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Latitude:</label><input type="text" name="txt_edit_latitude" id="edit_latitude" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Longitude:</label><input type="text" name="txt_edit_longitude" id="edit_longitude" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Strategy:</label><input type="text" name="txt_edit_strategy" id="edit_strategy" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Status:</label>
                                        <select name="txt_edit_status" id="edit_status" class="form-control input-sm">
                                            <option value="">-- Select Status --</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Ongoing">Ongoing</option>
                                            <option value="Terminated">Terminated</option>
                                            <option value="Deactivated">Deactivated</option>
                                            <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                            <option value="For Installation">For Installation</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>Remarks:</label><textarea name="txt_edit_remarks" id="edit_remarks" class="form-control input-sm"></textarea></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="editSubmitBtn">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================= VIEW FILES MODAL (Single Dynamic) ======================= -->
        <div id="viewModal" class="modal fade" role="dialog">
            <form id="photoForm" enctype="multipart/form-data">
                <div class="modal-dialog modal-lg" style="width:850px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">View Files for Activity</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <input type="hidden" name="hidden_id" id="view_hidden_id" value="" />
                            <input type="checkbox" id="viewSelectAll" /> <label>Select All</label>
                            <div class="row" id="photoGrid">
                                <div class="col-md-12 text-center text-muted">No files found.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="col-md-6 text-left">
                                <input name="photos[]" id="photoFileInput" class="form-control input-sm" type="file" multiple />
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" id="addPhotoBtn"><i class="fa fa-plus"></i> Add</button>
                            <button type="button" class="btn btn-danger btn-sm" id="removePhotoBtn"><i class="fa fa-trash"></i> Remove Selected</button>
                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================= DELETE CONFIRMATION MODAL ======================= -->
        <div id="deleteModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Delete Confirmation</h4>
                    </div>
                    <div class="modal-body">
                        <p id="deleteConfirmText">Are you sure you want to delete the selected item(s)?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary btn-sm" id="confirmDeleteBtn">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        </div> <!-- /.row -->
        </section><!-- /.content -->
        </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
    <?php }
include "../footer.php"; ?>
    <script type="text/javascript">
        (function() {
            var basePath = '../../ajax/';
            var currentPage = 1;
            var perPage = 5;
            var totalPages = 1;
            var searchTimeout = null;
            var isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
            var selectedIds = {};
            var selectAllActive = false;


            function getFilters() {
                return {
                    strategy: document.getElementById('strategySelect').value,
                    type: document.getElementById('typeSelect').value,
                    locality: document.getElementById('localitySelect').value,
                    barangay: document.getElementById('barangaySelect').value,
                    search: document.getElementById('searchInput').value
                };
            }

            function loadFilters() {
                var f = getFilters();
                var params = 'strategy=' + encodeURIComponent(f.strategy) +
                    '&type=' + encodeURIComponent(f.type) +
                    '&locality=' + encodeURIComponent(f.locality) +
                    '&barangay=' + encodeURIComponent(f.barangay);
                $.ajax({
                    url: basePath + 'fw4a_filters.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                    var s = document.getElementById('strategySelect');
                    var t = document.getElementById('typeSelect');
                    var l = document.getElementById('localitySelect');
                    var b = document.getElementById('barangaySelect');

                    var sv = s.value,
                        tv = t.value,
                        lv = l.value,
                        bv = b.value;

                    s.innerHTML = '<option value="">All Strategy</option>';
                    data.strategies.forEach(function(v) {
                        s.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>';
                    });
                    s.value = sv;

                    t.innerHTML = '<option value="">All Type</option>';
                    data.types.forEach(function(v) {
                        t.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>';
                    });
                    t.value = tv;

                    l.innerHTML = '<option value="">All Municipalities</option>';
                    data.localities.forEach(function(v) {
                        l.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>';
                    });
                    l.value = lv;

                    b.innerHTML = '<option value="">All Barangay</option>';
                    data.barangays.forEach(function(v) {
                        b.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>';
                    });
                    b.value = bv;
                    }
                });
            }

            function loadStats() {
                var f = getFilters();
                var params = 'strategy=' + encodeURIComponent(f.strategy) +
                    '&type=' + encodeURIComponent(f.type) +
                    '&locality=' + encodeURIComponent(f.locality) +
                    '&barangay=' + encodeURIComponent(f.barangay);
                $.ajax({
                    url: basePath + 'fw4a_stats.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        document.getElementById('statPenetration').textContent = res.penetration;
                        document.getElementById('statBrgy').textContent = res.barangay;
                        document.getElementById('statActive').textContent = res.active;
                        document.getElementById('statInactive').textContent = res.inactive;
                    }
                });
            }

            function loadData(page) {
                currentPage = page || 1;
                var f = getFilters();
                var colspan = isAdmin ? 19 : 16;
                var params = 'page=' + currentPage +
                    '&per_page=' + perPage +
                    '&search=' + encodeURIComponent(f.search) +
                    '&strategy=' + encodeURIComponent(f.strategy) +
                    '&type=' + encodeURIComponent(f.type) +
                    '&locality=' + encodeURIComponent(f.locality) +
                    '&barangay=' + encodeURIComponent(f.barangay);

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.ajax({
                    url: basePath + 'fw4a_data.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        totalPages = res.total_pages;
                        renderTable(res.data);
                        renderPagination(res.page, res.total_pages, res.total);
                        syncHeaderCheckbox();
                        updateDeleteBtn();
                    },
                    error: function() {
                        tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center text-danger">Failed to load data.</td></tr>';
                    }
                });
            }

            function renderTable(rows) {
                var tbody = document.getElementById('tableBody');
                var colspan = isAdmin ? 19 : 16;
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    var id = parseInt(row.id);
                    html += '<tr>';
                    if (isAdmin) {
                        html += '<td><input type="checkbox" class="chk_delete" data-id="' + id + '"' + (selectedIds[id] ? ' checked' : '') + ' /></td>';
                        html += '<td>' + row.row_num + '</td>';
                    }
                    html += '<td>' + escHtml(row.locality) + '</td>' +
                        '<td>' + escHtml(row.barangay) + '</td>' +
                        '<td>' + escHtml(row.district) + '</td>' +
                        '<td>' + escHtml(row.transport_location) + '</td>' +
                        '<td>' + escHtml(row.transport_type) + '</td>' +
                        '<td>' + escHtml(row.locations) + '</td>' +
                        '<td>' + escHtml(row.code) + '</td>' +
                        '<td>' + escHtml(row.nationwide_id) + '</td>' +
                        '<td>' + escHtml(row.type) + '</td>' +
                        '<td>' + escHtml(row.date_of_activation) + '</td>' +
                        '<td>' + escHtml(row.current_date_of_acceptance) + '</td>' +
                        '<td>' + escHtml(row.latitude) + '</td>' +
                        '<td>' + escHtml(row.longitude) + '</td>' +
                        '<td>' + escHtml(row.strategy) + '</td>' +
                        '<td>' + escHtml(row.status) + '</td>' +
                        '<td>' + escHtml(row.remarks) + '</td>';
                    if (isAdmin) {
                        html += '<td class="option-buttons">' +
                            '<div style="display:flex;gap:5px;flex-wrap:wrap;">' +
                            '<button class="btn btn-primary btn-xs editBtn" data-id="' + id + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                            '<button class="btn btn-info btn-xs viewBtn" data-id="' + id + '" title="Files"><i class="fa fa-eye"></i></button>' +
                            '</div></td>';
                    }
                    html += '</tr>';
                });
                tbody.innerHTML = html;
            }

            function renderPagination(page, total, count) {
                var pag = document.getElementById('pagination');
                var info = document.getElementById('paginationInfo');

                var start = count > 0 ? (page - 1) * perPage + 1 : 0;
                var end = Math.min(page * perPage, count);
                info.textContent = 'Showing ' + start + ' to ' + end + ' of ' + count + ' entries';

                if (total <= 1) {
                    pag.innerHTML = '';
                    return;
                }

                var html = '';
                html += '<li' + (page <= 1 ? ' class="disabled"' : '') + '><a href="#" data-page="' + (page - 1) + '">&laquo;</a></li>';

                var startPage = Math.max(1, page - 2);
                var endPage = Math.min(total, page + 2);

                if (startPage > 1) {
                    html += '<li><a href="#" data-page="1">1</a></li>';
                    if (startPage > 2) html += '<li class="disabled"><a>&hellip;</a></li>';
                }
                for (var i = startPage; i <= endPage; i++) {
                    html += '<li' + (i === page ? ' class="active"' : '') + '><a href="#" data-page="' + i + '">' + i + '</a></li>';
                }
                if (endPage < total) {
                    if (endPage < total - 1) html += '<li class="disabled"><a>&hellip;</a></li>';
                    html += '<li><a href="#" data-page="' + total + '">' + total + '</a></li>';
                }

                html += '<li' + (page >= total ? ' class="disabled"' : '') + '><a href="#" data-page="' + (page + 1) + '">&raquo;</a></li>';
                pag.innerHTML = html;
            }

            function updateDeleteBtn() {
                if (!isAdmin) return;
                var count = Object.keys(selectedIds).length;
                var btn = document.getElementById('deleteSelectedBtn');
                if (btn) {
                    btn.disabled = count === 0;
                    btn.innerHTML = '<i class="fa fa-trash"></i> Delete' + (count > 0 ? ' (' + count + ')' : '');
                }
            }

            function syncHeaderCheckbox() {
                if (!isAdmin) return;
                var cbx = document.getElementById('cbxMain');
                if (!cbx) return;
                var all = document.querySelectorAll('.chk_delete').length;
                var checked = document.querySelectorAll('.chk_delete:checked').length;
                cbx.checked = selectAllActive || (all > 0 && all === checked);
                cbx.indeterminate = !selectAllActive && checked > 0 && checked < all;
            }

            function clearSelection() {
                selectedIds = {};
                selectAllActive = false;
                var cbx = document.getElementById('cbxMain');
                if (cbx) {
                    cbx.checked = false;
                    cbx.indeterminate = false;
                }
                document.querySelectorAll('.chk_delete').forEach(function(cb) {
                    cb.checked = false;
                });
                updateDeleteBtn();
            }

            function selectAllMatching(cb) {
                var f = getFilters();
                var params = 'get_ids=1' +
                    '&search=' + encodeURIComponent(f.search) +
                    '&strategy=' + encodeURIComponent(f.strategy) +
                    '&type=' + encodeURIComponent(f.type) +
                    '&locality=' + encodeURIComponent(f.locality) +
                    '&barangay=' + encodeURIComponent(f.barangay);
                $.ajax({
                    url: basePath + 'fw4a_data.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        if (res.ids) {
                            res.ids.forEach(function(id) {
                                selectedIds[id] = true;
                            });
                            selectAllActive = true;
                        }
                        document.querySelectorAll('.chk_delete').forEach(function(c) {
                            c.checked = true;
                        });
                        syncHeaderCheckbox();
                        updateDeleteBtn();
                        if (typeof cb === 'function') cb();
                    }
                });
            }

            function escHtml(str) {
                if (str === null || str === undefined) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(String(str)));
                return div.innerHTML;
            }

            // Filter change handlers
            $('#strategySelect, #typeSelect, #localitySelect, #barangaySelect').on('change', function() {
                clearSelection();
                loadData(1);
                loadFilters();
                loadStats();
                loadMapData();
            });

            // Per-page selector
            $('#perPageSelect').on('change', function() {
                perPage = parseInt(this.value);
                clearSelection();
                loadData(1);
            });

            // Search
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    clearSelection();
                    loadData(1);
                }, 400);
            });
            $('#searchBtn').on('click', function() {
                clearSelection();
                loadData(1);
            });
            $('#clearSearchBtn').on('click', function() {
                document.getElementById('searchInput').value = '';
                clearSelection();
                loadData(1);
            });

            // Pagination clicks
            $('#pagination').on('click', 'a[data-page]', function(e) {
                e.preventDefault();
                var pg = parseInt($(this).attr('data-page'));
                if (pg >= 1 && pg <= totalPages) loadData(pg);
            });

            // Select all checkbox (selects matching records across ALL pages)
            if (isAdmin) {
                $('#cbxMain').on('change', function() {
                    if (this.checked) {
                        selectAllMatching();
                    } else {
                        selectedIds = {};
                        selectAllActive = false;
                        document.querySelectorAll('.chk_delete').forEach(function(cb) {
                            cb.checked = false;
                        });
                        syncHeaderCheckbox();
                        updateDeleteBtn();
                    }
                });
                $(document).on('change', '.chk_delete', function() {
                    var id = parseInt(this.getAttribute('data-id'));
                    if (this.checked) {
                        selectedIds[id] = true;
                    } else {
                        delete selectedIds[id];
                        selectAllActive = false;
                    }
                    var all = document.querySelectorAll('.chk_delete').length;
                    var checked = document.querySelectorAll('.chk_delete:checked').length;
                    document.getElementById('cbxMain').checked = selectAllActive || (all > 0 && all === checked);
                    document.getElementById('cbxMain').indeterminate = !selectAllActive && checked > 0 && checked < all;
                    updateDeleteBtn();
                });
            }

            // ========== ADD ==========
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('addSubmitBtn');
                btn.disabled = true;
                btn.value = 'Adding...';
                var formData = new FormData(this);
                formData.append('action', 'add');
                $.ajax({
                    url: basePath + 'fw4a_crud.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#addModal').modal('hide');
                            showToast(res.message, 'success');
                            loadData(currentPage);
                            loadFilters();
                            loadStats();
                            loadMapData();
                            document.getElementById('addForm').reset();
                        } else {
                            $('#addAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                        }
                    },
                    error: function() {
                        $('#addAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                    },
                    complete: function() {
                        btn.disabled = false;
                        btn.value = 'Add Item';
                    }
                });
            });

            // ========== EDIT ==========
            $(document).on('click', '.editBtn', function() {
                var id = $(this).attr('data-id');
                $('#editAlert').hide();
                $.getJSON(basePath + 'fw4a_get_item.php?action=item&id=' + id, function(item) {
                    $('#edit_hidden_id').val(item.id);
                    $('#edit_locality').val(item.locality);
                    $('#edit_barangay').val(item.barangay);
                    $('#edit_district').val(item.district);
                    $('#edit_transport_location').val(item.transport_location);
                    $('#edit_transport_type').val(item.transport_type);
                    $('#edit_locations').val(item.locations);
                    $('#edit_type').val(item.type);
                    $('#edit_code').val(item.code);
                    $('#edit_nationwide_id').val(item.nationwide_id);
                    $('#edit_date_of_activation').val(item.date_of_activation || '');
                    $('#edit_current_date_of_acceptance').val(item.current_date_of_acceptance || '');
                    $('#edit_latitude').val(item.latitude);
                    $('#edit_longitude').val(item.longitude);
                    $('#edit_strategy').val(item.strategy);
                    var knownStatuses = ['Active', 'Inactive', 'Ongoing', 'Terminated', 'Deactivated', 'Ongoing Acceptance', 'For Installation'];
                    if (item.status && knownStatuses.indexOf(item.status) === -1) {
                        $('#edit_status').val('Other');
                    } else {
                        $('#edit_status').val(item.status);
                    }
                    $('#edit_remarks').val(item.remarks);
                    $('#editModal').modal('show');
                }).fail(function() {
                    showToast('Failed to load item data.', 'danger');
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('editSubmitBtn');
                btn.disabled = true;
                btn.value = 'Saving...';
                $.ajax({
                    url: basePath + 'fw4a_crud.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=edit',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#editModal').modal('hide');
                            showToast(res.message, 'success');
                            loadData(currentPage);
                            loadFilters();
                            loadStats();
                            loadMapData();
                        } else {
                            $('#editAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                        }
                    },
                    error: function() {
                        $('#editAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                    },
                    complete: function() {
                        btn.disabled = false;
                        btn.value = 'Save';
                    }
                });
            });

            // ========== VIEW FILES ==========
            $(document).on('click', '.viewBtn', function() {
                var id = $(this).attr('data-id');
                document.getElementById('view_hidden_id').value = id;
                loadPhotos(id);
                $('#viewModal').modal('show');
            });

            function loadPhotos(inventoryId) {
                var grid = document.getElementById('photoGrid');
                grid.innerHTML = '<div class="col-md-12 text-center"><i class="fa fa-spinner fa-spin"></i> Loading files...</div>';

                $.getJSON(basePath + 'fw4a_get_item.php?action=photos&id=' + inventoryId, function(photos) {
                    if (!photos || photos.length === 0) {
                        grid.innerHTML = '<div class="col-md-12 text-center text-muted">No files found.</div>';
                        return;
                    }
                    var html = '';
                    photos.forEach(function(photo) {
                        var ext = photo.type.toLowerCase();
                        var preview = '';
                        if (['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) >= 0) {
                            preview = '<img src="' + photo.filepath + '" alt="" class="file-thumbnail" style="cursor:pointer;" />';
                        } else if (ext === 'pdf') {
                            preview = '<div class="file-thumbnail-pdf"><embed src="' + photo.filepath + '" type="application/pdf" width="100%" height="100%" /></div>';
                        } else if (['docx', 'xlsx', 'pptx'].indexOf(ext) >= 0) {
                            preview = '<div class="file-thumbnail-office"><i class="fas fa-file-word"></i></div>';
                        } else {
                            preview = '<div class="file-thumbnail">File type not previewable</div>';
                        }
                        var nameClean = photo.filename.replace(/\d+/g, '');
                        html += '<div class="col-md-4">' +
                            '<input type="checkbox" class="photoChk" value="' + photo.id + '" />' +
                            '<div class="file-item">' + preview +
                            '<div class="file-info"><span class="filename">' + escHtml(nameClean) + '</span>' +
                            '<a href="' + photo.filepath + '" download class="download-btn" title="Download"><i class="fas fa-download"></i></a>' +
                            '</div></div></div>';
                    });
                    grid.innerHTML = html;
                }).fail(function() {
                    grid.innerHTML = '<div class="col-md-12 text-center text-danger">Failed to load files.</div>';
                });
            }

            $('#viewSelectAll').on('change', function() {
                var c = this.checked;
                document.querySelectorAll('.photoChk').forEach(function(cb) {
                    cb.checked = c;
                });
            });

            $('#addPhotoBtn').on('click', function() {
                var inventoryId = document.getElementById('view_hidden_id').value;
                var fileInput = document.getElementById('photoFileInput');
                if (!fileInput.files || fileInput.files.length === 0) {
                    showToast('Please select files to upload.', 'warning');
                    return;
                }
                var fd = new FormData();
                fd.append('action', 'add_photo');
                fd.append('hidden_id', inventoryId);
                for (var i = 0; i < fileInput.files.length; i++) {
                    fd.append('photos[]', fileInput.files[i]);
                }
                $.ajax({
                    url: basePath + 'fw4a_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            showToast(res.message, 'success');
                            loadPhotos(inventoryId);
                            fileInput.value = '';
                        } else {
                            showToast(res.error || 'Upload failed.', 'danger');
                        }
                    },
                    error: function() {
                        showToast('Network error.', 'danger');
                    }
                });
            });

            $('#removePhotoBtn').on('click', function() {
                var ids = [];
                document.querySelectorAll('.photoChk:checked').forEach(function(cb) {
                    ids.push(cb.value);
                });
                if (ids.length === 0) {
                    showToast('No files selected.', 'warning');
                    return;
                }
                if (!confirm('Remove selected files?')) return;

                var fd = new FormData();
                fd.append('action', 'remove_photo');
                ids.forEach(function(id) {
                    fd.append('photo_ids[]', id);
                });

                $.ajax({
                    url: basePath + 'fw4a_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        showToast(res.message, 'success');
                        loadPhotos(document.getElementById('view_hidden_id').value);
                    },
                    error: function() {
                        showToast('Network error.', 'danger');
                    }
                });
            });

            // ========== DELETE ==========
            $('#deleteSelectedBtn').on('click', function() {
                var count = Object.keys(selectedIds).length;
                if (count === 0) {
                    showToast('No items selected.', 'warning');
                    return;
                }
                document.getElementById('deleteConfirmText').textContent =
                    'Are you sure you want to delete ' + count + ' selected item(s)?';
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                var btn = $(this);
                if (btn.hasClass('disabled')) return;
                btn.addClass('disabled').prop('disabled', true).text('Deleting...');

                var ids = Object.keys(selectedIds);
                var fd = new FormData();
                fd.append('action', 'delete');
                ids.forEach(function(id) {
                    fd.append('ids[]', id);
                });

                $.ajax({
                    url: basePath + 'fw4a_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        $('#deleteModal').modal('hide');
                        btn.removeClass('disabled').prop('disabled', false).text('OK');
                        showToast(res.message, 'success');
                        clearSelection();
                        loadData(currentPage);
                        loadFilters();
                        loadStats();
                        loadMapData();
                    },
                    error: function(xhr, status, err) {
                        console.error('[Delete Error]', status, err, xhr.responseText);
                        btn.removeClass('disabled').prop('disabled', false).text('OK');
                        showToast('Network error.', 'danger');
                    }
                });
            });

            // ========== IMPORT ==========
            $('#importBtn').on('click', function() {
                document.getElementById('importFile').click();
            });
            $('#importFile').on('change', function() {
                var formData = new FormData();
                formData.append('file', this.files[0]);

                fetch('import.php', {
                    method: 'POST',
                    body: formData
                }).then(function(response) {
                    return response.text().then(function(text) {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Import raw response:', text);
                            throw e;
                        }
                    });
                }).then(data => {
                    if (data.success) {
                        var msg = data.inserted + ' row(s) inserted';
                        if (data.skipped > 0) {
                            msg += ', ' + data.skipped + ' skipped';
                        }
                        console.log('[Import Result]', msg, data);
                        showToast(msg + '. Reloading...', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'Import failed.', 'danger');
                    }
                }).catch(() => showToast('Import error.', 'danger'));
            });

            // ========== EXPORT ==========
            $('#exportBtn').on('click', function() {
                var f = getFilters();
                var url = 'export.php?locality=' + encodeURIComponent(f.locality) +
                    '&barangay=' + encodeURIComponent(f.barangay) +
                    '&type=' + encodeURIComponent(f.type) +
                    '&strategy=' + encodeURIComponent(f.strategy);
                window.location.href = url;
            });

            // ========== DATE/TIME ==========
            function updateDateTime() {
                var now = new Date();
                document.getElementById('dateTime').innerText = now.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            }
            setInterval(updateDateTime, 1000);
            updateDateTime();

            // ========== MAP ==========
            var fw4aMap = null;
            var mapMarkersLayer = null;
            var mapHeatLayer = null;
            var mapMode = 'markers';
            var mapLegend = null;
            var currentMapPoints = [];
            var searchHighlightMarker = null;
            var searchDebounceTimer = null;
            var activeStatusFilter = 'All';

            var statusColors = {
                'Active':             '#27ae60',
                'Inactive':           '#7f8c8d',
                'Ongoing':            '#3498db',
                'Terminated':         '#c0392b',
                'Deactivated':        '#e67e22',
                'Ongoing Acceptance': '#9b59b6',
                'For Installation':   '#1abc9c',
                'Other':              '#bdc3c7'
            };

            function getStatusColor(status) {
                return statusColors[status] || statusColors['Other'];
            }

            function initMap() {
                fw4aMap = L.map('fw4aMap', {
                    center: [12.8797, 121.7740],
                    zoom: 7,
                    scrollWheelZoom: true
                });

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(fw4aMap);

                mapMarkersLayer = L.layerGroup();
                mapHeatLayer = L.heatLayer([], {
                    radius: 25,
                    blur: 15,
                    maxZoom: 17,
                    gradient: {
                        0.2: 'blue',
                        0.4: 'cyan',
                        0.6: 'lime',
                        0.8: 'yellow',
                        1.0: 'red'
                    }
                });

                new SearchControl().addTo(fw4aMap);
                new StatusFilterControl().addTo(fw4aMap);
                addMapLegend();
                loadMapData();
            }

            function addMapLegend() {
                if (mapLegend) fw4aMap.removeControl(mapLegend);
                var html = '<div class="map-legend"><b>Status Legend</b><br>';
                for (var status in statusColors) {
                    html += '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:' + statusColors[status] + ';margin-right:5px;vertical-align:middle;"></span>' + status + '<br>';
                }
                html += '</div>';
                mapLegend = L.control({
                    position: 'bottomleft'
                });
                mapLegend.onAdd = function() {
                    var div = L.DomUtil.create('div');
                    div.innerHTML = html;
                    return div;
                };
                mapLegend.addTo(fw4aMap);
            }

            // ========== MAP SEARCH ==========
            var SearchControl = L.Control.extend({
                options: { position: 'topright' },
                onAdd: function(map) {
                    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control fw4a-search-control');
                    container.innerHTML =
                        '<div class="fw4a-search-wrapper">' +
                            '<input type="text" id="mapSearchInput" class="fw4a-search-input" placeholder="Search places or records..." autocomplete="off" />' +
                            '<div id="mapSearchResults" class="fw4a-search-results" style="display:none;"></div>' +
                        '</div>';
                    L.DomEvent.disableClickPropagation(container);
                    L.DomEvent.disableScrollPropagation(container);

                    var input = container.querySelector('#mapSearchInput');
                    var results = container.querySelector('#mapSearchResults');

                    input.addEventListener('input', function() {
                        var query = this.value.trim();
                        clearTimeout(searchDebounceTimer);
                        if (query.length < 2) {
                            results.style.display = 'none';
                            results.innerHTML = '';
                            return;
                        }
                        searchDebounceTimer = setTimeout(function() {
                            performSearch(query, results);
                        }, 350);
                    });

                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            results.style.display = 'none';
                            results.innerHTML = '';
                            input.blur();
                        }
                    });

                    input.addEventListener('focus', function() {
                        if (results.children.length > 0 && results.innerHTML.trim() !== '') {
                            results.style.display = 'block';
                        }
                    });

                    document.addEventListener('click', function(e) {
                        if (!container.contains(e.target)) {
                            results.style.display = 'none';
                        }
                    });

                    return container;
                }
            });

            function performSearch(query, resultsEl) {
                resultsEl.innerHTML = '<div class="fw4a-search-item fw4a-search-loading"><i class="fa fa-spinner fa-spin"></i> Searching...</div>';
                resultsEl.style.display = 'block';

                var localResults = searchLocalRecords(query);
                var nominatimUrl = 'https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(query) +
                    '&format=json&limit=5&addressdetails=1';

                var nominatimPromise = fetch(nominatimUrl, {
                    headers: { 'Accept': 'application/json' }
                }).then(function(r) { return r.json(); }).catch(function() { return []; });

                nominatimPromise.then(function(places) {
                    renderSearchResults(query, localResults, places || [], resultsEl);
                });
            }

            function searchLocalRecords(query) {
                var q = query.toLowerCase();
                var matches = [];
                for (var i = 0; i < currentMapPoints.length && matches.length < 10; i++) {
                    var p = currentMapPoints[i];
                    if (activeStatusFilter !== 'All' && p.status !== activeStatusFilter) continue;
                    var haystack = [
                        p.locations || '', p.locality || '', p.barangay || '',
                        p.code || '', p.nationwide_id || '', p.strategy || '', p.status || ''
                    ].join(' ').toLowerCase();
                    if (haystack.indexOf(q) !== -1) {
                        matches.push(p);
                    }
                }
                return matches;
            }

            function renderSearchResults(query, localResults, places, resultsEl) {
                var html = '';

                if (localResults.length > 0) {
                    html += '<div class="fw4a-search-section"><span class="fw4a-search-section-label"><i class="fa fa-database"></i> Our Records</span></div>';
                    localResults.forEach(function(p) {
                        var label = escHtml(p.locations || p.locality || 'Unnamed');
                        var sub = escHtml([p.barangay, p.locality].filter(Boolean).join(', '));
                        html += '<div class="fw4a-search-item fw4a-search-record" data-type="record" data-id="' + p.id + '">' +
                            '<span class="fw4a-search-icon"><i class="fa fa-map-marker"></i></span>' +
                            '<span class="fw4a-search-text"><span class="fw4a-search-name">' + label + '</span>' +
                            '<span class="fw4a-search-sub">' + sub + '</span></span></div>';
                    });
                }

                if (places.length > 0) {
                    html += '<div class="fw4a-search-section"><span class="fw4a-search-section-label"><i class="fa fa-globe"></i> Places</span></div>';
                    places.forEach(function(place) {
                        var name = place.display_name || 'Unknown place';
                        if (name.length > 70) name = name.substring(0, 70) + '...';
                        html += '<div class="fw4a-search-item fw4a-search-place" data-type="place" data-lat="' + place.lat + '" data-lng="' + place.lon + '">' +
                            '<span class="fw4a-search-icon"><i class="fa fa-globe"></i></span>' +
                            '<span class="fw4a-search-text"><span class="fw4a-search-name">' + escHtml(name) + '</span></span></div>';
                    });
                }

                if (html === '') {
                    html = '<div class="fw4a-search-item fw4a-search-empty">No results found</div>';
                }

                resultsEl.innerHTML = html;
                resultsEl.style.display = 'block';

                var items = resultsEl.querySelectorAll('.fw4a-search-item[data-type]');
                for (var i = 0; i < items.length; i++) {
                    items[i].addEventListener('click', function() {
                        var type = this.getAttribute('data-type');
                        if (type === 'record') {
                            selectSearchRecord(parseInt(this.getAttribute('data-id')));
                        } else if (type === 'place') {
                            selectSearchPlace(parseFloat(this.getAttribute('data-lat')), parseFloat(this.getAttribute('data-lng')));
                        }
                        resultsEl.style.display = 'none';
                        resultsEl.innerHTML = '';
                        document.getElementById('mapSearchInput').value = '';
                    });
                }
            }

            function selectSearchRecord(id) {
                var point = null;
                for (var i = 0; i < currentMapPoints.length; i++) {
                    if (currentMapPoints[i].id === id) { point = currentMapPoints[i]; break; }
                }
                if (!point) return;

                fw4aMap.flyTo([point.lat, point.lng], 16, { duration: 1.2 });

                if (mapMode === 'heatmap') {
                    fw4aMap.removeLayer(mapHeatLayer);
                    mapMarkersLayer.addTo(fw4aMap);
                    mapMode = 'markers';
                    $('#mapToggleView').html('<i class="fa fa-map-marker"></i> Locators');
                }

                setTimeout(function() {
                    var targetMarker = null;
                    mapMarkersLayer.eachLayer(function(layer) {
                        if (targetMarker) return;
                        var latlng = layer.getLatLng();
                        if (latlng && Math.abs(latlng.lat - point.lat) < 0.000001 && Math.abs(latlng.lng - point.lng) < 0.000001) {
                            targetMarker = layer;
                        }
                    });
                    if (targetMarker) {
                        targetMarker.openPopup();
                        addSearchHighlight(point.lat, point.lng);
                    }
                }, 1300);
            }

            function selectSearchPlace(lat, lng) {
                fw4aMap.flyTo([lat, lng], 15, { duration: 1.2 });
                setTimeout(function() { addSearchHighlight(lat, lng); }, 1300);
            }

            function addSearchHighlight(lat, lng) {
                removeSearchHighlight();
                searchHighlightMarker = L.circleMarker([lat, lng], {
                    radius: 18,
                    fillColor: 'transparent',
                    color: '#e74c3c',
                    weight: 4,
                    opacity: 1,
                    dashArray: '6, 4',
                    className: 'fw4a-search-highlight-ring'
                }).addTo(fw4aMap);

                setTimeout(function() { removeSearchHighlight(); }, 3500);
            }

            function removeSearchHighlight() {
                if (searchHighlightMarker) {
                    fw4aMap.removeLayer(searchHighlightMarker);
                    searchHighlightMarker = null;
                }
            }

            // ========== MAP STATUS FILTER ==========
            var StatusFilterControl = L.Control.extend({
                options: { position: 'topright' },
                onAdd: function(map) {
                    var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control fw4a-filter-control');
                    var wrapper = L.DomUtil.create('div', 'fw4a-filter-wrapper', container);
                    var label = L.DomUtil.create('span', 'fw4a-filter-label', wrapper);
                    label.innerHTML = '<i class="fa fa-filter"></i>';
                    var select = L.DomUtil.create('select', 'fw4a-filter-select', wrapper);
                    select.id = 'mapStatusFilter';
                    L.DomEvent.disableClickPropagation(container);
                    L.DomEvent.disableScrollPropagation(container);

                    select.addEventListener('change', function() {
                        activeStatusFilter = this.value;
                        applyStatusFilter();
                    });

                    container._fw4aSelect = select;
                    return container;
                }
            });

            function buildStatusFilterOptions() {
                var select = document.getElementById('mapStatusFilter');
                if (!select) return;

                var orderedStatuses = [];

                for (var known in statusColors) {
                    orderedStatuses.push(known);
                }

                currentMapPoints.forEach(function(p) {
                    if (p.status && orderedStatuses.indexOf(p.status) === -1) {
                        orderedStatuses.push(p.status);
                    }
                });

                var html = '<option value="All">All Statuses</option>';
                orderedStatuses.forEach(function(s) {
                    html += '<option value="' + escHtml(s) + '">' + escHtml(s) + '</option>';
                });
                select.innerHTML = html;
                select.value = activeStatusFilter;
            }

            function applyStatusFilter() {
                mapMarkersLayer.clearLayers();

                var filteredHeat = [];
                var filteredMarkers = [];
                currentMapPoints.forEach(function(p) {
                    if (activeStatusFilter !== 'All' && p.status !== activeStatusFilter) return;

                    var statusColor = getStatusColor(p.status);
                    var marker = L.circleMarker([p.lat, p.lng], {
                        radius: 7,
                        fillColor: statusColor,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    });
                    marker._fw4aStatus = p.status;
                    marker._fw4aPointId = p.id;

                    var popupContent =
                        '<div style="font-size:13px; line-height:1.6;">' +
                        '<b style="color:darkblue; font-size:14px;">' + escHtml(p.locations || 'N/A') + '</b><br>' +
                        '<b>Locality:</b> ' + escHtml(p.locality) + '<br>' +
                        '<b>Barangay:</b> ' + escHtml(p.barangay) + '<br>' +
                        '<b>District:</b> ' + escHtml(p.district || 'N/A') + '<br>' +
                        '<b>Code:</b> ' + escHtml(p.code || 'N/A') + '<br>' +
                        '<b>Type:</b> ' + escHtml(p.type) + '<br>' +
                        '<b>Strategy:</b> ' + escHtml(p.strategy) + '<br>' +
                        '<b>Status:</b> <span style="color:' + statusColor + '; font-weight:bold;">' + escHtml(p.status) + '</span><br>' +
                        '<b>Coordinates:</b> ' + p.lat.toFixed(6) + ', ' + p.lng.toFixed(6) +
                        (p.remarks ? '<br><b>Remarks:</b> ' + escHtml(p.remarks) : '') +
                        '</div>';

                    marker.bindPopup(popupContent, { maxWidth: 300, className: 'fw4a-popup' });
                    mapMarkersLayer.addLayer(marker);
                    filteredMarkers.push(marker);
                    filteredHeat.push([p.lat, p.lng, 1]);
                });

                mapHeatLayer = L.heatLayer(filteredHeat, {
                    radius: 25, blur: 15, maxZoom: 17,
                    gradient: { 0.2: 'blue', 0.4: 'cyan', 0.6: 'lime', 0.8: 'yellow', 1.0: 'red' }
                });

                fw4aMap.removeLayer(mapMarkersLayer);
                fw4aMap.removeLayer(mapHeatLayer);

                if (mapMode === 'heatmap') {
                    mapHeatLayer.addTo(fw4aMap);
                } else {
                    mapMarkersLayer.addTo(fw4aMap);
                }
            }

            function loadMapData() {
                if (!fw4aMap) return;
                var f = getFilters();
                var params = 'strategy=' + encodeURIComponent(f.strategy) +
                    '&type=' + encodeURIComponent(f.type) +
                    '&locality=' + encodeURIComponent(f.locality) +
                    '&barangay=' + encodeURIComponent(f.barangay);

                $.ajax({
                    url: basePath + 'fw4a_map_data.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        renderMapPoints(res.points || []);
                    },
                    error: function() {
                        console.error('Failed to load map data');
                    }
                });
            }

            function renderMapPoints(points) {
                currentMapPoints = points || [];
                fw4aMap.removeLayer(mapMarkersLayer);
                fw4aMap.removeLayer(mapHeatLayer);
                mapMarkersLayer.clearLayers();

                if (points.length === 0) return;

                var heatData = [];
                var markers = [];

                points.forEach(function(p) {
                    var statusColor = getStatusColor(p.status);
                    var marker = L.circleMarker([p.lat, p.lng], {
                        radius: 7,
                        fillColor: statusColor,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    });
                    marker._fw4aStatus = p.status;
                    marker._fw4aPointId = p.id;

                    var popupContent =
                        '<div style="font-size:13px; line-height:1.6;">' +
                        '<b style="color:darkblue; font-size:14px;">' + escHtml(p.locations || 'N/A') + '</b><br>' +
                        '<b>Locality:</b> ' + escHtml(p.locality) + '<br>' +
                        '<b>Barangay:</b> ' + escHtml(p.barangay) + '<br>' +
                        '<b>District:</b> ' + escHtml(p.district || 'N/A') + '<br>' +
                        '<b>Code:</b> ' + escHtml(p.code || 'N/A') + '<br>' +
                        '<b>Type:</b> ' + escHtml(p.type) + '<br>' +
                        '<b>Strategy:</b> ' + escHtml(p.strategy) + '<br>' +
                        '<b>Status:</b> <span style="color:' + statusColor + '; font-weight:bold;">' + escHtml(p.status) + '</span><br>' +
                        '<b>Coordinates:</b> ' + p.lat.toFixed(6) + ', ' + p.lng.toFixed(6) +
                        (p.remarks ? '<br><b>Remarks:</b> ' + escHtml(p.remarks) : '') +
                        '</div>';

                    marker.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'fw4a-popup'
                    });
                    mapMarkersLayer.addLayer(marker);
                    markers.push(marker);

                    heatData.push([p.lat, p.lng, 1]);
                });

                mapHeatLayer = L.heatLayer(heatData, {
                    radius: 25,
                    blur: 15,
                    maxZoom: 17,
                    gradient: {
                        0.2: 'blue',
                        0.4: 'cyan',
                        0.6: 'lime',
                        0.8: 'yellow',
                        1.0: 'red'
                    }
                });

                if (mapMode === 'heatmap') {
                    mapHeatLayer.addTo(fw4aMap);
                } else {
                    mapMarkersLayer.addTo(fw4aMap);
                }

                if (markers.length > 0) {
                    var group = new L.featureGroup(markers);
                    fw4aMap.fitBounds(group.getBounds().pad(0.1));
                }

                setTimeout(function() {
                    fw4aMap.invalidateSize();
                }, 300);

                buildStatusFilterOptions();
                if (activeStatusFilter !== 'All') {
                    applyStatusFilter();
                }
            }

            $('#mapToggleView').on('click', function() {
                if (mapMode === 'heatmap') {
                    fw4aMap.removeLayer(mapHeatLayer);
                    mapMarkersLayer.addTo(fw4aMap);
                    mapMode = 'markers';
                    $(this).html('<i class="fa fa-map-marker"></i> Locators');
                } else {
                    fw4aMap.removeLayer(mapMarkersLayer);
                    mapHeatLayer.addTo(fw4aMap);
                    mapMode = 'heatmap';
                    $(this).html('<i class="fa fa-fire"></i> Heatmap');
                }
            });

            $('#mapCollapse').on('shown.bs.collapse', function() {
                if (fw4aMap) fw4aMap.invalidateSize();
            });
            $('#mapCollapse').on('hidden.bs.collapse', function() {
                if (fw4aMap) fw4aMap.invalidateSize();
            });

            // ========== INIT ==========
            loadFilters();
            loadData(1);
            initMap();

        })();
    </script>

    <style>
        .info-box-icon {
            background-color: white;
            /* Change the background to white */
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4);
            /* Add an inner shadow */
            border-radius: 5px;
            /* Optional: Adjust the border-radius if needed */
            padding: 10px;
            /* Optional: Add padding to make the icon fit better */
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
            /* Set a fixed height for the charts */
            margin-bottom: 20px;
        }

        .chart-container canvas {
            width: 100% !important;
            /* Ensure canvas takes up full width */
            height: 100% !important;
            /* Ensure canvas takes up full height */
        }

        .panel-body {
            padding: 15px;
            /* Add padding to the panel body */
        }

        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            /* Adjust height as needed */
            width: 80px;
            /* Adjust width as needed */
            font-size: 40px;
            /* Adjust icon size */
        }

        .info-box-number a {
            color: inherit;
            /* Inherit the color from parent element */
            text-decoration: none;
            /* Remove underline from links */
        }

        .info-box-number a:hover {
            text-decoration: underline;
            /* Add underline on hover for better UX */
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
        }

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
            height: 250px;
            /* Fixed height for uniformity */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
            /* Smooth transition for hover effects */
        }

        /* Thumbnail styling (uniform for all file types) */
        .file-thumbnail,
        .file-thumbnail-pdf,
        .file-thumbnail-office {
            width: 150px;
            /* Fixed width */
            height: 150px;
            /* Fixed height */
            object-fit: cover;
            /* Ensures images and other content fill the area */
            margin-bottom: 15px;
            /* Uniform space between thumbnail and filename */
            transition: transform 0.3s ease;
            /* Smooth transition for hover effect */
        }

        /* Hover effect only on the file thumbnail */
        .file-thumbnail:hover,
        .file-thumbnail-pdf:hover,
        .file-thumbnail-office:hover {
            transform: scale(1.05);
            /* Slight zoom on hover for thumbnails */
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
            font-size: 12px;
            /* Smaller font size for filename */
            color: #333;
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            /* Truncate long filenames */
            white-space: nowrap;
            max-width: 100px;
            /* Limit the width for better alignment */
            text-align: center;
            /* Center-align the filename text */
            margin-bottom: 5px;
            /* Consistent space between filename and download icon */
        }

        /* Container for the filename and download button */
        .file-info {
            display: flex;
            align-items: center;
            /* Align text and icon vertically */
            justify-content: center;
            margin-top: 0;
            /* Remove any additional top margin */
        }

        /* Download icon styling */
        .download-btn {
            font-size: 12px;
            /* Smaller size for the download icon */
            color: #007bff;
            text-decoration: none;
            padding: 0 5px;
            vertical-align: middle;
            /* Align it with the text */
        }

        .download-btn i {
            font-size: 14px;
            /* Matching the icon size with filename size */
            vertical-align: middle;
            /* Align it with the text */
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
            align-items: center;
            /* Align items vertically */
            justify-content: space-between;
            /* Space between logo/title and date/time */
        }

        .header-date-time {
            font-size: 16px;
            /* Adjust font size as needed */
            color: #555;
            /* Optional: Change color for better visibility */
            margin-left: auto;
            /* Push the date/time to the right */
        }

        /* Other styles remain unchanged */

        /* ========== TABLE STYLES ========== */
        #table {
            border-collapse: collapse;
            border: 1px solid #ddd;
            width: 100%;
        }

        #table thead tr:first-child th,
        #table thead tr:nth-child(2) th {
            background-color: #3c8dbc;
            color: #ffffff;
            border: 1px solid #32739e;
            text-align: center;
            font-weight: 600;
            padding: 8px;
        }

        #table tbody td {
            border: 1px solid #ddd;
            padding: 8px;
            white-space: nowrap;
        }

        #tableBody tr {
            background-color: #ffffff !important;
        }

        #tableBody tr:hover {
            background-color: #e8f4fd !important;
            cursor: default;
            transition: background-color 0.15s ease;
        }

        /* ========== MAP STYLES ========== */
        #fw4aMap {
            z-index: 1;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px;
            font-size: 13px;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.3);
        }

        .leaflet-popup-content {
            margin: 10px 15px;
            line-height: 1.5;
        }

        .leaflet-popup-content b {
            color: darkblue;
        }

        .fw4a-popup .leaflet-popup-content {
            max-height: 300px;
            overflow-y: auto;
        }

        .map-legend {
            background: white;
            padding: 10px 14px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            font-size: 12px;
            line-height: 1.9;
            font-family: Arial, sans-serif;
        }

        .map-legend b {
            font-size: 13px;
            margin-bottom: 2px;
            display: block;
        }

        .panel-heading:hover {
            background-color: #f5f5f5;
        }

        #mapToggleView {
            margin-right: 10px;
        }

        /* ========== MAP SEARCH STYLES ========== */
        .fw4a-search-control {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            margin-top: 10px !important;
        }

        .fw4a-search-wrapper {
            position: relative;
            width: 320px;
        }

        .fw4a-search-input {
            width: 100%;
            padding: 8px 12px 8px 32px;
            border: 2px solid #fff;
            border-radius: 4px;
            font-size: 13px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            outline: none;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='%23999'%3E%3Cpath d='M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E") 10px center no-repeat;
            transition: box-shadow 0.2s;
        }

        .fw4a-search-input:focus {
            border-color: #3c8dbc;
            box-shadow: 0 2px 8px rgba(60, 141, 188, 0.4);
        }

        .fw4a-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 4px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            max-height: 360px;
            overflow-y: auto;
            z-index: 10000;
            font-size: 13px;
        }

        .fw4a-search-section {
            padding: 6px 12px 2px;
            border-bottom: 1px solid #eee;
        }

        .fw4a-search-section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
        }

        .fw4a-search-item {
            display: flex;
            align-items: flex-start;
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.1s;
            gap: 10px;
        }

        .fw4a-search-item:hover {
            background-color: #f0f7ff;
        }

        .fw4a-search-item.fw4a-search-record .fw4a-search-icon {
            color: #3c8dbc;
        }

        .fw4a-search-item.fw4a-search-place .fw4a-search-icon {
            color: #27ae60;
        }

        .fw4a-search-icon {
            flex-shrink: 0;
            width: 18px;
            text-align: center;
            padding-top: 2px;
            font-size: 14px;
        }

        .fw4a-search-text {
            flex: 1;
            min-width: 0;
        }

        .fw4a-search-name {
            display: block;
            font-size: 13px;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fw4a-search-sub {
            display: block;
            font-size: 11px;
            color: #999;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fw4a-search-empty, .fw4a-search-loading {
            color: #999;
            text-align: center;
            cursor: default;
            padding: 12px !important;
        }

        .fw4a-search-loading:hover {
            background-color: transparent !important;
        }

        @keyframes fw4a-highlight-pulse {
            0%   { stroke-opacity: 1; stroke-width: 4; }
            50%  { stroke-opacity: 0.4; stroke-width: 8; }
            100% { stroke-opacity: 1; stroke-width: 4; }
        }

        .fw4a-search-highlight-ring {
            animation: fw4a-highlight-pulse 1.2s ease-in-out infinite;
        }

        /* ========== MAP STATUS FILTER STYLES ========== */
        .fw4a-filter-control {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            margin-top: 10px !important;
        }

        .fw4a-filter-wrapper {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            padding: 0;
            overflow: hidden;
        }

        .fw4a-filter-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 8px;
            background: #f4f4f4;
            color: #555;
            font-size: 13px;
            border-right: 1px solid #ddd;
        }

        .fw4a-filter-select {
            border: none;
            outline: none;
            padding: 6px 10px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            background: #fff;
            color: #333;
            cursor: pointer;
            min-width: 140px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23555' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            padding-right: 24px;
        }

        .fw4a-filter-select:focus {
            box-shadow: inset 0 0 0 2px #3c8dbc;
        }
    </style>
    </body>

</html>