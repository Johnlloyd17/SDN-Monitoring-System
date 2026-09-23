<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    
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
    <!-- FullCalendar (activities calendar) -->
    <link href="../../css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css">
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
        .info-box .info-box-text,
        .info-box .info-box-number {
            color: #000000; /* Match black/default text color on all dashboard stat cards */
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
        /* Module status grid tiles */
        .module-tile {
            margin-bottom: 15px;
        }
        .module-tile .box {
            border-top-width: 3px;
            margin-bottom: 0;
        }
        .module-tile .module-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .module-tile .module-status {
            font-size: 13px;
            color: #555;
        }
        .module-tile .module-status strong {
            color: #333;
        }
        .module-tile .module-status text-block {
            display: block;
        }
        .module-emphasis {
            font-weight: 600;
            color: #333;
        }
        .kpi-amount {
            display: block;
            font-size: 18px;
        }
        .panel-default > .panel-heading {
            color: #000000;
        }
        .panel-default > .panel-heading:hover {
            color: #000000;
        }
        /* Interactive stat cards: whole card is a clickable link */
        .stat-card--clickable {
            display: block;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: box-shadow 0.15s ease, transform 0.1s ease;
        }
        .stat-card--clickable:hover,
        .stat-card--clickable:focus {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
            text-decoration: none;
        }
        /* Static/display-only stat cards: explicit default cursor */
        .stat-card--static {
            cursor: default;
        }
        /* Live notifications widget */
        #dashNotifList {
            list-style: none;
            margin: 0;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
            overscroll-behavior: contain;
            -ms-scroll-chaining: none;
        }
        #dashNotifList .notif-item {
            display: flex;
            align-items: center;
            padding: 0 10px;
            border-bottom: 1px solid #f4f4f4;
        }
        #dashNotifList .notif-item > a.notif-title {
            flex: 1;
            padding: 10px 0;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        /* Alert row colors come from the shared .notif-bill/.notif-letter rules
           defined in pages/header.php (see js/notifications.js bellItem()). */
        /* Activities calendar widget */
        #dashCalendar {
            margin: 0 auto;
        }
        /* Reset inherited zoom so FullCalendar measures and renders in the
           same coordinate space; a non-1 zoom on <html> causes progressive
           horizontal drift of event bars (bars further right shift more). */
        #dashCalendar { zoom: 1; }

        /* Give event bars a consistent gap on ALL four sides, uniformly for every
           bar in every row (single or stacked). Boundary (start/end) edges are
           measured off .fc-grid .fc-day-content, so its uniform 4px padding is the
           base inset; .fc-event-hori's uniform 3px margin on every side ensures each
           bar segment (incl. mid-span parts of multi-day events) is visibly pulled
           in from the day-number row, the row divider below, and both left/right cell
           borders. FullCalendar's stacking math uses outerHeight(true), so the same
           margin is absorbed identically whether a week holds one bar or several.
           Net inset per bar: 7px top/right/bottom/left; 6px between stacked bars. */
        #dashCalendar .fc-grid .fc-day-content {
            padding: 4px;
        }
        #dashCalendar .fc-event-hori {
            margin: 3px;
        }
        /* Year jump: embedded inline next to the month title in the calendar header */
        #dashCalendar .fc-header-left {
            white-space: nowrap;
            vertical-align: middle;
        }
        #dashCalendar .fc-header-title {
            display: inline-block;
            vertical-align: middle;
        }
        #dashCalYearWrap {
            display: inline-block;
            vertical-align: middle;
            margin-left: 6px;
            white-space: nowrap;
        }
        #dashCalYearWrap .fa-calendar {
            font-size: 13px;
            color: #777;
            margin-right: 4px;
        }
        #dashCalYearWrap select {
            display: inline-block;
            width: auto;
            min-width: 76px;
            height: 24px;
            padding: 1px 6px;
            margin: 0;
            font-size: 13px;
            line-height: 1.2;
            vertical-align: middle;
        }
        /* Read-only activity detail modal */
        #dashCalEventModal .dash-cal-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #eee;
            padding: 7px 0;
        }
        #dashCalEventModal .dash-cal-label {
            display: table-cell;
            width: 185px;
            padding-right: 12px;
            vertical-align: top;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: #777;
        }
        #dashCalEventModal .dash-cal-value {
            display: table-cell;
            vertical-align: top;
            font-size: 13px;
            color: #333;
            word-break: break-word;
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

            <!-- ============================================================ -->
            <!-- SECTION 1: SYSTEM KPI ROW -->
            <!-- ============================================================ -->
            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        System Overview
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php
                            // ---- Original activity/participant stat cards ----
                            $qActivities = mysqli_query($con, "SELECT COUNT(*) AS total_trainings FROM tblactivity");
                            $rActivities = mysqli_fetch_assoc($qActivities);

                            $qParticipants = mysqli_query($con, "SELECT COUNT(*) AS total_participants FROM tblparticipant");
                            $rParticipants = mysqli_fetch_assoc($qParticipants);

                            $qSectors = mysqli_query($con, "SELECT COUNT(*) AS total_sector FROM (SELECT DISTINCT sector FROM tblactivity WHERE sector IS NOT NULL AND sector != '') AS sectors");
                            $rSectors = mysqli_fetch_assoc($qSectors);

                            $qAgencies = mysqli_query($con, "SELECT COUNT(DISTINCT agency) AS total_agencies FROM tblactivity WHERE agency IS NOT NULL AND agency != ''");
                            $rAgencies = mysqli_fetch_assoc($qAgencies);

                            $qDistrict1 = mysqli_query($con, "SELECT COUNT(*) AS district_1_count FROM tblactivity WHERE district = 'District 1' OR district = 'District 1 (Siargao)'");
                            $rDistrict1 = mysqli_fetch_assoc($qDistrict1);

                            $qDistrict2 = mysqli_query($con, "SELECT COUNT(*) AS district_2_count FROM tblactivity WHERE district = 'District 2' OR district = 'District 2 (Mainland)'");
                            $rDistrict2 = mysqli_fetch_assoc($qDistrict2);

                            $qMunicipalities = mysqli_query($con, "SELECT COUNT(DISTINCT municipality) AS total_municipality FROM tblactivity WHERE municipality IS NOT NULL AND municipality != ''");
                            $rMunicipalities = mysqli_fetch_assoc($qMunicipalities);

                            $qBarangays = mysqli_query($con, "SELECT COUNT(DISTINCT barangay) AS total_barangay FROM tblactivity WHERE barangay IS NOT NULL AND barangay != ''");
                            $rBarangays = mysqli_fetch_assoc($qBarangays);

                            // ---- New KPI cards ----
                            $qUnpaid = mysqli_query($con, "SELECT COALESCE(SUM(amount), 0) AS unpaid_amount FROM bills_monitoring WHERE status = 0");
                            $rUnpaid = mysqli_fetch_assoc($qUnpaid);

                            $qNeedsResponse = mysqli_query($con, "SELECT SUM(CASE WHEN date_responded IS NULL AND (for_response = 'Y' OR for_response IS NULL OR for_response = '') THEN 1 ELSE 0 END) AS needs_response FROM letters_monitoring");
                            $rNeedsResponse = mysqli_fetch_assoc($qNeedsResponse);

                            $qInventoryItems = mysqli_query($con, "SELECT COUNT(*) AS total_items FROM inventory WHERE project != ''");
                            $rInventoryItems = mysqli_fetch_assoc($qInventoryItems);

                            $qProcurementRecords = mysqli_query($con, "SELECT COUNT(*) AS total_records FROM procurement_tracking");
                            $rProcurementRecords = mysqli_fetch_assoc($qProcurementRecords);
                            ?>

                            <!-- Row 1: Original 8 cards -->
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/activities_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <img src="icons/activities.png" alt="Total Activities" style="width: 50px; height: 50px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Activities</span>
                                        <span class="info-box-number">        
                                                <?php
                                                echo $rActivities['total_trainings'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/participants_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <img src="icons/participants.png" alt="Total Participants" style="width: 52px; height: 52px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Participants</span>
                                        <span class="info-box-number">                                      
                                                <?php
                                                echo $rParticipants['total_participants'];
                                                ?>
                                    
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/sectors_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-yellow">
                                        <img src="icons/sectors.png" alt="Total Sectors" style="width: 60px; height: 60px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Sectors</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                echo $rSectors['total_sector'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/agencies_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <img src="icons/agencies.png" alt="Total Agencies" style="width: 50px; height: 50px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Agencies</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                echo $rAgencies['total_agencies'];
                                                ?>
                                         
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/district1_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <img src="icons/district_1.png" alt="District 1" style="width: 60px; height: 60px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">District 1</span>
                                        <span class="info-box-number">
                                                <?php
                                                echo $rDistrict1['district_1_count'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/district2_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-yellow">
                                        <img src="icons/district_2.png" alt="District 2" style="width: 55px; height: 55px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">District 2</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                echo $rDistrict2['district_2_count'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/municipalities_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-blue">
                                        <img src="icons/municipalities.png" alt="Total Municipality" style="width: auto; height: 58px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Municipalities</span>
                                        <span class="info-box-number">
                                         
                                                <?php
                                                echo $rMunicipalities['total_municipality'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                            <a href="../dashboard_data/barangays_data.php" class="stat-card--clickable"><div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <img src="icons/barangays.png" alt="Total Barangays" style="width: 60px; height: 60px;">
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Barangays</span>
                                        <span class="info-box-number">
                                            
                                                <?php
                                                echo $rBarangays['total_barangay'];
                                                ?>
                                            
                                        </span>
                                    </div>
                                </div>
                            </a>
                            </div>
                            <!-- End Row 1 -->

                            <!-- Row 2: New KPI cards -->
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <a href="../bills_monitoring/bills_monitoring.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-red">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Unpaid Bills Amount</span>
                                        <span class="info-box-number" id="statUnpaidAmount">
                                            <?php echo 'PHP ' . number_format($rUnpaid['unpaid_amount'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <a href="../letters_monitoring/letters_monitoring.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow">
                                        <i class="fa fa-envelope-open"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Letters Needing Response</span>
                                        <span class="info-box-number" id="statNeedsResponse">
                                            <?php echo $rNeedsResponse['needs_response']; ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <a href="../property_records/property_records.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua">
                                        <i class="fa fa-boxes"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Inventory Items</span>
                                        <span class="info-box-number">
                                            <?php echo $rInventoryItems['total_items']; ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <a href="../property_items/property.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua">
                                        <i class="fa fa-shopping-cart"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Procurement Records</span>
                                        <span class="info-box-number">
                                            <?php echo $rProcurementRecords['total_records']; ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <!-- End Row 2 -->
                        </div><!-- /.row -->
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->
            </div>

            <!-- ============================================================ -->
            <!-- SECTION 2: FINANCIAL OVERVIEW ROW -->
            <!-- ============================================================ -->
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Financial Overview
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php
                            // Bills Paid
                            $qBillsPaid = mysqli_query($con, "SELECT COALESCE(SUM(amount), 0) AS bills_paid FROM bills_monitoring WHERE status = 1");
                            $rBillsPaid = mysqli_fetch_assoc($qBillsPaid);

                            // Procurement Spend
                            $qProcurementSpend = mysqli_query($con, "SELECT COALESCE(SUM(amount), 0) AS procurement_spend FROM procurement_tracking");
                            $rProcurementSpend = mysqli_fetch_assoc($qProcurementSpend);

                            // Inventory Value (query-time CAST for TEXT cost column)
                            $qInventoryValue = mysqli_query($con, "SELECT COALESCE(SUM(CAST(REPLACE(cost, ',', '') AS DECIMAL(15,2)) * quantity), 0) AS inventory_value FROM inventory WHERE cost IS NOT NULL AND cost != '' AND project != ''");
                            $rInventoryValue = mysqli_fetch_assoc($qInventoryValue);
                            ?>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <a href="../bills_monitoring/bills_monitoring.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green">
                                        <i class="fa fa-credit-card"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Bills Paid</span>
                                        <span class="info-box-number" id="statBillsPaid">
                                            <?php echo 'PHP ' . number_format($rBillsPaid['bills_paid'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <a href="../property_items/property.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green">
                                        <i class="fa fa-file-invoice-dollar"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Procurement Spend</span>
                                        <span class="info-box-number">
                                            <?php echo 'PHP ' . number_format($rProcurementSpend['procurement_spend'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <a href="../property_records/property_records.php" class="stat-card--clickable">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green">
                                        <i class="fa fa-boxes-stacked"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Inventory Value</span>
                                        <span class="info-box-number">
                                            <?php echo 'PHP ' . number_format($rInventoryValue['inventory_value'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                </a>
                            </div>
                        </div><!-- /.row -->
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->
            </div>

            <!-- ============================================================ -->
            <!-- SECTION 3: MODULE STATUS GRID -->
            <!-- ============================================================ -->
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Module Status
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php
                            // Gather per-module counts in a single pass
                            $moduleCounts = [];

                            // Cybersecurity activities
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblactivity WHERE project = 'Cybersecurity'");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['cybersecurity'] = (int)$r['cnt'];

                            // eLGU BPLS - monitored LGUs
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblbpls");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['bpls'] = (int)$r['cnt'];

                            // FreeWifi4All - access points
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblfwfa");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['fw4a'] = (int)$r['cnt'];

                            // GECS activities
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblactivity WHERE project = 'GECS'");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['gecs'] = (int)$r['cnt'];

                            // GovNet - not implemented, no table
                            $moduleCounts['govnet'] = null;

                            // IIDB activities
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblactivity WHERE project = 'IIDB'");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['iidb'] = (int)$r['cnt'];

                            // ILCDB activities
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM tblactivity WHERE project = 'ILCDB'");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['ilcdb'] = (int)$r['cnt'];

                            // Property Management - inventory items
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM inventory WHERE project != ''");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['property'] = (int)$r['cnt'];

                            // Procurement records
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM procurement_tracking");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['procurement'] = (int)$r['cnt'];

                            // Purchase Request - under maintenance
                            $moduleCounts['purchase_request'] = null;

                            // Bills Monitoring
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM bills_monitoring");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['bills'] = (int)$r['cnt'];

                            // Letters Monitoring
                            $q = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM letters_monitoring");
                            $r = mysqli_fetch_assoc($q);
                            $moduleCounts['letters'] = (int)$r['cnt'];
                            ?>

                            <?php
                            function sdn_module_tile($icon, $title, $label, $value, $border, $muted = false) {
                                echo '<div class="col-md-3 col-sm-6 col-xs-12 module-tile stat-card--static">';
                                echo '<div class="box box-default" style="border-top: 3px solid ' . $border . ';">';
                                echo '<div class="box-body">';
                                echo '<div class="module-title">' . $icon . ' ' . htmlspecialchars($title) . '</div>';
                                if ($muted) {
                                    echo '<span class="module-status text-muted"><em>' . htmlspecialchars($label) . '</em></span>';
                                } else {
                                    echo '<span class="module-status">' . htmlspecialchars($label) . ': <strong>' . number_format($value) . '</strong></span>';
                                }
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>

                            <?php
                            // Cybersecurity
                            sdn_module_tile('<i class="fa fa-shield-alt"></i>', 'Cybersecurity',
                                'Activities recorded', $moduleCounts['cybersecurity'],
                                '#3498db');
                            // eLGU BPLS
                            sdn_module_tile('<i class="fa fa-building"></i>', 'eLGU BPLS',
                                'LGUs tracked', $moduleCounts['bpls'],
                                '#27ae60');
                            // FreeWifi4All
                            sdn_module_tile('<i class="fa fa-wifi"></i>', 'FreeWifi4All',
                                'Access points deployed', $moduleCounts['fw4a'],
                                '#8e44ad');
                            // GECS
                            sdn_module_tile('<i class="fa fa-desktop"></i>', 'GECS',
                                'Activities recorded', $moduleCounts['gecs'],
                                '#1abc9c');
                            ?>
                            <!-- Row 2 of tiles -->
                            <?php
                            // GovNet -- empty/placeholder module
                            sdn_module_tile('<i class="fa fa-globe"></i>', 'GovNet',
                                'Not yet implemented', 0,
                                '#d4a017', true);
                            // IIDB
                            sdn_module_tile('<i class="fa fa-database"></i>', 'IIDB',
                                'Activities recorded', $moduleCounts['iidb'],
                                '#e67e22');
                            // ILCDB
                            sdn_module_tile('<i class="fa fa-graduation-cap"></i>', 'ILCDB',
                                'Activities recorded', $moduleCounts['ilcdb'],
                                '#2980b9');
                            // Property Management
                            sdn_module_tile('<i class="fa fa-boxes"></i>', 'Property Management',
                                'Items tracked', $moduleCounts['property'],
                                '#16a085');
                            ?>
                            <!-- Row 3 of tiles -->
                            <?php
                            // Procurement
                            sdn_module_tile('<i class="fa fa-shopping-cart"></i>', 'Procurement',
                                'Records', $moduleCounts['procurement'],
                                '#c0392b');
                            // Purchase Request -- under maintenance
                            sdn_module_tile('<i class="fa fa-wrench"></i>', 'Purchase Request',
                                'Under maintenance', 0,
                                '#d4a017', true);
                            // Bills Monitoring
                            sdn_module_tile('<i class="fa fa-file-invoice"></i>', 'Bills Monitoring',
                                'Bills tracked', $moduleCounts['bills'],
                                '#00c0ef');
                            // Letters Monitoring
                            sdn_module_tile('<i class="fa fa-envelope"></i>', 'Letters Monitoring',
                                'Letters tracked', $moduleCounts['letters'],
                                '#39cccc');
                            ?>
                        </div><!-- /.row -->
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->
            </div>

            <!-- ============================================================ -->
            <!-- SECTION 3B: LIVE NOTIFICATIONS + ACTIVITIES CALENDAR -->
            <!-- ============================================================ -->
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                    <!-- Live Notifications -->
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bell"></i> Live Notifications
                                <span class="label label-warning pull-right" id="dashNotifBadge" style="display:none;">0</span>
                            </div>
                            <div class="panel-body">
                                <ul class="menu" id="dashNotifList"></ul>
                            </div>
                        </div>
                    </div>
                    <!-- Activities Calendar -->
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-calendar"></i> Activities Calendar
                            </div>
                            <div class="panel-body">
                                <?php
                                // Calendar events: one block per recorded activity (all-day range)
                                $calEvents = [];
                                $minYear = null;
                                $maxYear = null;
                                $calQuery = mysqli_query($con, "SELECT id, project, activity, start, end FROM tblactivity WHERE start IS NOT NULL AND start != '' AND start >= '2000-01-01' ORDER BY start ASC");
                                if ($calQuery) {
                                    while ($ev = mysqli_fetch_assoc($calQuery)) {
                                        $startTs = strtotime($ev['start']);
                                        if ($startTs === false) continue;
                                        $endTs = strtotime($ev['end']);
                                        if ($endTs === false || $endTs < $startTs) {
                                            $endTs = $startTs;
                                        }
                                        $evYear = (int)date('Y', $startTs);
                                        if ($minYear === null || $evYear < $minYear) $minYear = $evYear;
                                        if ($maxYear === null || $evYear > $maxYear) $maxYear = $evYear;
                                        $title = trim($ev['activity']);
                                        if ($ev['project'] !== null && $ev['project'] !== '') {
                                            $title = $ev['project'] . ' — ' . $title;
                                        }
                                        $calEvents[] = [
                                            'id'     => (int)$ev['id'],
                                            'title'  => $title,
                                            'start'  => date('Y-m-d', $startTs),
                                            'end'    => date('Y-m-d', $endTs),
                                            'allDay' => true
                                        ];
                                    }
                                }
                                // Year jump: pad the data range by 1 year each side, always include the current year
                                $currentYear = (int)date('Y');
                                if ($minYear === null) {
                                    $minYear = $currentYear - 1;
                                    $maxYear = $currentYear + 1;
                                }
                                $minYear = min($minYear, $currentYear) - 1;
                                $maxYear = max($maxYear, $currentYear) + 1;
                                $yearOptions = range($minYear, $maxYear);
                                ?>
                                <span id="dashCalYearWrap" style="display:none;">
                                    <i class="fa fa-calendar"></i>
                                    <select id="dashCalYear" class="form-control input-sm">
                                        <?php foreach ($yearOptions as $optY) echo '<option value="' . $optY . '">' . $optY . '</option>'; ?>
                                    </select>
                                </span>
                                <div id="dashCalendar"></div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.row -->
            </div>

            <!-- ========================= ACTIVITY DETAIL MODAL (read-only View) ======================= -->
            <div id="dashCalEventModal" class="modal fade">
                <div class="modal-dialog modal-sdm-xl modal-view">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="dashCalEventTitle"><i class="fa fa-eye"></i> Activity Details</h4>
                        </div>
                        <div class="modal-body" id="dashCalEventBody">
                            <div class="text-center" style="padding: 20px;">
                                <i class="fa fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SECTION 4: EXISTING CHARTS -->
            <!-- ============================================================ -->
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

            <!-- ============================================================ -->
            <!-- SECTION 5: MUNICIPALITY/CITY CROSS-TAB TABLE -->
            <!-- ============================================================ -->
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

    <!-- FullCalendar (activities calendar) -->
    <script src="../../js/plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {
            $("#table").dataTable({
                "aoColumnDefs": [{ "bSortable": false, "aTargets": [] }],
                "aaSorting": [],
                "dom": '<"search"f><"top"l>rt<"bottom"ip><"clear">'
            });

            // --- LIVE NOTIFICATIONS WIDGET (piggybacks on the header's 45s poll) ---
            if (window.SDN_NOTIF) {
                window.SDN_NOTIF.onList = function(list) {
                    var n = list ? list.length : 0;
                    $('#dashNotifBadge').text(n).css('display', n > 0 ? '' : 'none');
                    window.SDN_NOTIF.renderList('#dashNotifList', list);
                };
            }

            // --- ACTIVITIES CALENDAR WIDGET ---
            window.SDN_Cal = window.SDN_Cal || {
                base: (window.SDN_NOTIF && window.SDN_NOTIF.base) || '../../'
            };
            window.SDN_Cal.viewEvent = function(eventId) {
                if (!eventId) {
                    return;
                }
                var $title = $('#dashCalEventTitle');
                var $body = $('#dashCalEventBody');
                $title.html('<i class="fa fa-eye"></i> Activity Details');
                $body.html('<div class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
                $('#dashCalEventModal').modal('show');
                var endpoint = window.SDN_Cal.base + 'ajax/activity_get_item.php';
                var esc = function(v) {
                    return $('<div>').text(v).html();
                };
                $.getJSON(endpoint, { action: 'item', id: eventId })
                    .done(function(row) {
                        if (!row || row.error) {
                            var errMsg = (row && row.error) ? String(row.error) : 'Empty response';
                            console.error('[SDN_Cal] Activity Details: no record returned for id=' + eventId + ' (' + endpoint + '): ' + errMsg);
                            $title.html('<i class="fa fa-eye"></i> Activity Details');
                            $body.html('<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i> ' + esc(errMsg) + '</div>');
                            return;
                        }
                        var fmt = function(v) {
                            return (v === null || v === undefined || v === '' ? '—' : v);
                        };
                        var pairs = [
                            ['Date Range', row.start ? (row.start + (row.end && row.end !== row.start ? ' — ' + row.end : '')) : ''],
                            ['Training', row.training],
                            ['Project', row.project],
                            ['Subproject', row.subproject],
                            ['Indicator', row.indicator],
                            ['Municipality', row.municipality],
                            ['Barangay', row.barangay],
                            ['District', row.district],
                            ['Agency', row.agency],
                            ['Mode', row.mode],
                            ['Sector', row.sector],
                            ['Person', row.person],
                            ['Resource Speaker', row.resource],
                            ['Participants', row.participants],
                            ['Completers', row.completers],
                            ['Male', row.male],
                            ['Female', row.female],
                            ['Approved', row.approved],
                            ['MOV', row.mov],
                            ['Remarks', row.remarks]
                        ];
                        var html = '';
                        for (var i = 0; i < pairs.length; i++) {
                            html += '<div class="dash-cal-row">' +
                                '<div class="dash-cal-label">' + esc(pairs[i][0]) + '</div>' +
                                '<div class="dash-cal-value">' + esc(fmt(pairs[i][1])) + '</div>' +
                                '</div>';
                        }
                        $title.html('<i class="fa fa-eye"></i> ' + esc(row.activity || 'Activity Details'));
                        $body.html(html);
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        var status = jqXHR && jqXHR.status;
                        var resp = jqXHR && jqXHR.responseText;
                        if (resp && resp.length > 500) {
                            resp = resp.slice(0, 500) + '...';
                        }
                        console.error('[SDN_Cal] Activity Details fetch failed (id=' + eventId + '). url=' + endpoint + ', HTTP=' + status + ', textStatus=' + textStatus + ', errorThrown=' + (errorThrown || '') + '. responseBody=' + (resp || ''));
                        $title.html('<i class="fa fa-eye"></i> Activity Details');
                        $body.html('<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i> Failed to load activity details. Check the console for details.</div>');
                    });
            };

            if ($.fn.fullCalendar) {
                $('#dashCalendar').fullCalendar({
                    defaultView: 'month',
                    height: 400,
                    events: <?php echo json_encode($calEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
                    eventTextColor: '#fff',
                    header: { left: 'title', center: '', right: 'today,prev,next' },
                    eventClick: function(event) {
                        window.SDN_Cal.viewEvent(event.id);
                    },
                    viewRender: function(view) {
                        if (view && view.start) {
                            var y = view.start.getFullYear();
                            var M = ['January', 'February', 'March', 'April', 'May', 'June',
                                'July', 'August', 'September', 'October', 'November', 'December'][view.start.getMonth()];
                            $('#dashCalYear').val(y);
                            $('#dashCalendar .fc-header-title h2').text(M);
                        }
                    },
                    eventAfterAllRender: function() {
                        var htmlZoom = parseFloat($(document.documentElement).css('zoom')) || 1;
                        var inv = 1 / htmlZoom;

                        // Keep bars snug against their day columns/cells when <html> is
                        // zoomed: FullCalendar lays out in zoomed coordinates, so undo the
                        // zoom on left/width. (top/height are left as-is; a uniform zoom
                        // scales them consistently.)
                        if (htmlZoom !== 1) {
                            $('#dashCalendar .fc-event-hori').each(function() {
                                var $e = $(this);
                                var l = parseFloat($e.css('left'));
                                var w = parseFloat($e.css('width'));
                                if (!isNaN(l)) $e.css('left', (l * inv) + 'px');
                                if (!isNaN(w)) $e.css('width', (w * inv) + 'px');
                            });
                        }

                        // A long wrapped title can make a stacked bar taller than the height
                        // FullCalendar measured when it staggered the row, so the bar below
                        // ends up overlapping its bottom edge. Nudge the lower bar down by
                        // the shortfall (plus the normal 6px gap between stacked bars).
                        var gapPx = 6 * htmlZoom;
                        for (var pass = 0; pass < 3; pass++) {
                            var moved = false;
                            var evts = $('#dashCalendar .fc-event-hori').get();
                            for (var i = 0; i < evts.length; i++) {
                                var ri = evts[i].getBoundingClientRect();
                                var $i = $(evts[i]);
                                for (var j = 0; j < evts.length; j++) {
                                    if (i === j) continue;
                                    var rj = evts[j].getBoundingClientRect();
                                    if (rj.top >= ri.top - 0.01) continue;
                                    if (!(rj.left < ri.right && ri.left < rj.right)) continue;
                                    var minTop = rj.bottom + gapPx;
                                    if (ri.top < minTop - 0.5) {
                                        var shift = (minTop - ri.top) / htmlZoom;
                                        $i.css('top', ((parseFloat($i.css('top')) || 0) + shift) + 'px');
                                        moved = true;
                                        break;
                                    }
                                }
                            }
                            if (!moved) break;
                        }
                    }
                });
                // Embed the year jump inline with the month title in the header row
                $('#dashCalYearWrap').appendTo($('#dashCalendar td.fc-header-left').first()).css('display', '');
            }

            // Year jump dropdown
            $('#dashCalYear').on('change', function() {
                var nextYear = parseInt(String($(this).val()), 10);
                if (isNaN(nextYear) || !$.fn.fullCalendar) {
                    return;
                }
                var curView = $('#dashCalendar').fullCalendar('getView');
                var curMonth = (curView && curView.start) ? curView.start.getMonth() : 0;
                $('#dashCalendar').fullCalendar('gotoDate', nextYear, curMonth);
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