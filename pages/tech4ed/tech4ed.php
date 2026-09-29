<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!DOCTYPE html>
<html>
<?php

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
    require_once __DIR__ . '/tech4ed_rows.php';
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
                    <img src="icons/tech4Ed_logo.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">ILCDB</h3>
                        <p class="header-address">Tech4ED Centers</p>
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
                                    <div class="panel-heading">Tech4ED Centers Overview</div>
                                    <div class="panel-body">
                                        <form method="post" id="filterForm">
                                        
                                        <div class="row">
    <!-- Total LGUs -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_lgu.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="icons/municipality_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Total LGUs</span>
                <span class="info-box-number" id="totalParticipants">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_lgu FROM tbltech4ed WHERE category = 'LGU'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_lgu'];
                    ?></a>
                </span>
            </div>
        </div>
    </div>
    <!-- Total Schools -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_school.php"><div class="info-box">
                <span class="info-box-icon bg-red">
                    <img src="icons/school_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Schools</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_school FROM tbltech4ed WHERE category = 'School'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_school'];
                    ?></a>
                </span>
            </div>
        </div>
    </div>
    <!-- Total NGAs -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_nga.php"><div class="info-box">
                <span class="info-box-icon bg-yellow">
                    <img src="icons/nga_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Total NGAs</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_nga FROM tbltech4ed WHERE category = 'NGA'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_nga'];;
                    ?></a>
                </span>
            </div>
        </div>
    </div>
    <!-- Total Private -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_private.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="icons/private_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Private</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_private FROM tbltech4ed WHERE category = 'Private'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_private'];
                    ?></a>
                </span>
            </div>
        </div>
    </div>
    <!-- Total RIS -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_ris.php"><div class="info-box">
                <span class="info-box-icon bg-red">
                    <img src="icons/hub_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Total RIS</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_ris FROM tbltech4ed WHERE category = 'RIS'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_ris'];
                    ?></a>
                </span>
            </div>
        </div>
    </div>
    <!-- Total Provincial Center -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_center.php"><div class="info-box">
                <span class="info-box-icon bg-yellow">
                    <img src="icons/center_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Provincial Center</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_provincial FROM tbltech4ed WHERE category = 'DICT Provincial Training Center'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_provincial'];
                    ?></a>
                </span>
            </div>
        </div>
    </div>
    <!-- Operational -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_operational.php"><div class="info-box">
                <span class="info-box-icon bg-blue">
                    <img src="icons/operational_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Operational</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_operational FROM tbltech4ed WHERE operation = 'Operational'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_operational'];
                    ?>
                </span></a>
            </div>
        </div>
    </div>
    <!-- Non-Operational -->
    <div class="col-md-3 col-sm-6 col-xs-12">
    <a href="../tech4ed/tech4ed_nonoperational.php"><div class="info-box">
                <span class="info-box-icon bg-red">
                    <img src="icons/nonoperational_1.png" alt="Total Activities" style="width: 50px; height: 50px;">
                </span>
            <div class="info-box-content">
                <span class="info-box-text">Non-Operational</span>
                <span class="info-box-number">
                    <?php
                    $filterQuery = "SELECT COUNT(*) AS total_nonoperational FROM tbltech4ed WHERE operation = 'Non-Operational'";
                    if (isset($_POST['category']) && $_POST['category'] != '') {
                        $filterQuery .= " AND category = '" . mysqli_real_escape_string($con, $_POST['category']) . "'";
                    }
                    if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                        $filterQuery .= " AND municipality = '" . mysqli_real_escape_string($con, $_POST['municipality']) . "'";
                    }
                    if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                        $filterQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $_POST['barangay']) . "'";
                    }
                    if (isset($_POST['year']) && $_POST['year'] != '') {
                        $filterQuery .= " AND YEAR(launch) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                    }
                    $result = mysqli_query($con, $filterQuery);
                    $data = mysqli_fetch_assoc($result);
                    echo $data['total_nonoperational'];
                    ?>
              </span></a>
                                                    </div>
                                          
                                                </div>
                                                </div>
                                        </div>
                                        <div class="row">
                                        <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="categorySelect">Select Category</label>
        <select id="categorySelect" name="category" class="form-control" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php
            $categoryQuery = "SELECT DISTINCT category FROM tbltech4ed WHERE category != ''";
            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                $categoryQuery .= " AND barangay = '$barangay'";
            }
            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                $categoryQuery .= " AND municipality = '$municipality'";
            }
            if (isset($_POST['year']) && $_POST['year'] != '') {
                $year = mysqli_real_escape_string($con, $_POST['year']);
                $categoryQuery .= " AND YEAR(launch) = '$year'";
            }
            // Add ORDER BY clause to sort categories in ascending order
            $categoryQuery .= " ORDER BY category ASC";
            
            $categorysQuery = mysqli_query($con, $categoryQuery);
            if ($categorysQuery) {
                while ($category = mysqli_fetch_assoc($categorysQuery)) {
                    echo '<option value="' . $category['category'] . '"' . (isset($_POST['category']) && $_POST['category'] == $category['category'] ? ' selected' : '') . '>' . $category['category'] . '</option>';
                }
            } else {
                echo "Error fetching localities: " . mysqli_error($con);
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
            $municipalityQuery = "SELECT DISTINCT municipality FROM tbltech4ed WHERE municipality != ''";
            if (isset($_POST['category']) && $_POST['category'] != '') {
                $category = mysqli_real_escape_string($con, $_POST['category']);
                $municipalityQuery .= " AND category = '$category'";
            }
            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                $municipalityQuery .= " AND barangay = '$barangay'";
            }
            if (isset($_POST['year']) && $_POST['year'] != '') {
                $year = mysqli_real_escape_string($con, $_POST['year']);
                $municipalityQuery .= " AND YEAR(launch) = '$year'";
            }
            // Add ORDER BY clause to sort municipalities in ascending order
            $municipalityQuery .= " ORDER BY municipality ASC";
            
            $municipalitysQuery = mysqli_query($con, $municipalityQuery);
            if ($municipalitysQuery) {
                while ($municipality = mysqli_fetch_assoc($municipalitysQuery)) {
                    echo '<option value="' . $municipality['municipality'] . '"' . (isset($_POST['municipality']) && $_POST['municipality'] == $municipality['municipality'] ? ' selected' : '') . '>' . $municipality['municipality'] . '</option>';
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
            $barangayQuery = "SELECT DISTINCT barangay FROM tbltech4ed WHERE barangay != ''";
            if (isset($_POST['category']) && $_POST['category'] != '') {
                $category = mysqli_real_escape_string($con, $_POST['category']);
                $barangayQuery .= " AND category = '$category'";
            }
            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                $barangayQuery .= " AND municipality = '$municipality'";
            }
            if (isset($_POST['year']) && $_POST['year'] != '') {
                $year = mysqli_real_escape_string($con, $_POST['year']);
                $barangayQuery .= " AND YEAR(launch) = '$year'";
            }
            // Add ORDER BY clause to sort barangays in ascending order
            $barangayQuery .= " ORDER BY barangay ASC";
            
            $barangaysQuery = mysqli_query($con, $barangayQuery);
            if ($barangaysQuery) {
                while ($barangay = mysqli_fetch_assoc($barangaysQuery)) {
                    echo '<option value="' . $barangay['barangay'] . '"' . (isset($_POST['barangay']) && $_POST['barangay'] == $barangay['barangay'] ? ' selected' : '') . '>' . $barangay['barangay'] . '</option>';
                }
            } else {
                echo "Error fetching barangays: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>

<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="yearSelect">Select Year</label>
        <select id="yearSelect" name="year" class="form-control" onchange="this.form.submit()">
            <option value="">All Years</option>
            <?php
            // Query to select distinct years from the launch date
            $yearQuery = "SELECT DISTINCT YEAR(launch) AS year FROM tbltech4ed WHERE launch IS NOT NULL";
            if (isset($_POST['category']) && $_POST['category'] != '') {
                $category = mysqli_real_escape_string($con, $_POST['category']);
                $yearQuery .= " AND category = '$category'";
            }
            if (isset($_POST['municipality']) && $_POST['municipality'] != '') {
                $municipality = mysqli_real_escape_string($con, $_POST['municipality']);
                $yearQuery .= " AND municipality = '$municipality'";
            }
            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                $yearQuery .= " AND barangay = '$barangay'";
            }
            $yearQuery .= " ORDER BY year ASC";
            
            $yearsQuery = mysqli_query($con, $yearQuery);
            if ($yearsQuery) {
                while ($year = mysqli_fetch_assoc($yearsQuery)) {
                    echo '<option value="' . $year['year'] . '"' . (isset($_POST['year']) && $_POST['year'] == $year['year'] ? ' selected' : '') . '>' . $year['year'] . '</option>';
                }
            } else {
                echo "Error fetching years: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>
<div style="padding:10px; display: flex; justify-content: space-between;">
                                            <div>
                                            <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                        
                                                    <?php } elseif ($_SESSION['username'] === 'fwfasdn') { ?>
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
                                                
                                                <?php } elseif ($_SESSION['username'] === 'fwfasdn') { ?>
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
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'fwfasdn') {
                                            ?>
                                                <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>
                                                <th>No.</th>
                                            <?php 
                                            }
                                            ?>
                                                    <th>Region</th>
                                                            <th>Province</th>
                                                            <th>Congressional District</th>
                                                            <th>Municipality/City</th>
                                                            <th>Barangay</th>
                                                            <th>Street Address</th>
                                                            <th>Specific Center Location</th>
                                                            <th>Center Name</th>
                                                            <th>Host</th>
                                                            <th>Category</th>
                                                            <th>Longitude</th>
                                                            <th>Latitude</th>
                                                            <th>Center Manager's Name</th>
                                                            <th>Email</th>
                                                            <th>Mobile</th>
                                                            <th>Landline</th>
                                                            <th>Gender</th>
                                                            <th>Assistant Center Manager's Name</th>
                                                            <th>Email</th>
                                                            <th>Mobile</th>
                                                            <th>Landline</th>
                                                            <th>Gender</th>
                                                            <th>Date of Launching</th>
                                                            <th>Date of Platform Registration</th>
                                                            <th>Operational Status</th>
                                                            <th>Date Last Visited</th>
                                                            <th># of functional desktop units</th>
                                                            <th># of functional laptop units</th>
                                                            <th># of functional printer</th>
                                                            <th># of functional scanner/copier</th>
                                                            <th>Status</th>
                                                            <th>Types of Network</th>
                                                            <th>Internet Connectivity</th>
                                                            <th>Internet Speed</th>
                                                            <th>CMT (# of pax) Male</th>
                                                            <th>CMT (# of pax) Female</th>
                                                            <th>Start Date of Training</th>
                                                            <th>End Date of Training</th>
                                                            <th>Date of Signing</th>
                                                            <th>Partner</th>
                                                            <th>Expiration</th>
                                                            <th>Type of Donation</th>
                                                            <th>Date of Donation</th>
                                                            <th>TCMS</th>
                                                            <th>Key</th>
                                                            <th>Identifier</th>
                                                            <?php 
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'fwfasdn') {
                                            ?>
                                                <th style="width: 40px !important;">Option</th>
                                            <?php 
                                            }
                                            ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        echo tech4ed_render_rows($con, tech4ed_filters($_POST), tech4ed_can_manage());
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
<?php include dirname(__DIR__) . '/sheet_preview_modal.php'; ?>

                    </div>   <!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
        <?php }
        include "../footer.php"; ?>
<script src="../../js/sdm-preview.js"></script>
<script type="text/javascript">

    // Master checkbox for the delete column; re-bindable after an in-place refresh.
    var tech4edSelectAll = document.getElementById("cbxMain");

    function rebindTech4edCheckboxes() {
        tech4edSelectAll = document.getElementById("cbxMain");
        var boxes = document.getElementsByClassName("chk_delete");
        if (tech4edSelectAll) tech4edSelectAll.checked = false;
        if (tech4edSelectAll) {
            tech4edSelectAll.onchange = function () {
                for (var i = 0; i < boxes.length; i++) boxes[i].checked = tech4edSelectAll.checked;
            };
        }
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].onchange = function () {
                if (this.checked === false && tech4edSelectAll) tech4edSelectAll.checked = false;
                var all = document.getElementsByClassName("chk_delete");
                if (tech4edSelectAll && document.querySelectorAll('.chk_delete:checked').length === all.length) {
                    tech4edSelectAll.checked = true;
                }
            };
        }
    }

    function checkMain(el) {
        var boxes = document.getElementsByClassName("chk_delete");
        for (var i = 0; i < boxes.length; i++) boxes[i].checked = el.checked;
    }

    // Re-render the table body in place, preserving the active filters.
    function refreshTech4edData() {
        var params = {};
        var f = document.getElementById('filterForm');
        if (f) {
            ['municipality', 'barangay', 'category', 'year'].forEach(function (n) {
                var el = f.querySelector('[name="' + n + '"]');
                if (el && el.value !== '') params[n] = el.value;
            });
        }

        return $.getJSON('../../ajax/tech4ed_data.php', params, function (resp) {
            if (!resp || !resp.success) return;
            var table = $('#table');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
            } else {
                table.find('tbody').html(resp.rows);
            }
            rebindTech4edCheckboxes();
        }).fail(function () {
            showToast('Could not refresh the list. Please reload the page.', 'error');
        });
    }

    /**
     * Submit a form over AJAX so the page never reloads.
     * opts: { action, modal, multipart, success, reset }
     */
    function bindAjaxForm(formId, opts) {
        opts = opts || {};
        var form = document.getElementById(formId);
        if (!form || form.dataset.ajaxBound === '1') return;
        form.dataset.ajaxBound = '1';

        var alertBox = form.querySelector('.modal-alert-slot');

        function showAlert(msg, type) {
            if (!alertBox) { if (msg) showToast(msg, type); return; }
            if (!msg) { alertBox.innerHTML = ''; return; }
            alertBox.innerHTML = '<div class="alert alert-' + (type === 'error' ? 'danger' : type) +
                '" style="margin:10px 12px 0;"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>';
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            showAlert('');

            var settings = {
                url: 'function.php',
                type: 'POST',
                dataType: 'json'
            };

            if (opts.multipart) {
                // FormData keeps the repeated name="photos[]" inputs intact.
                var fd = new FormData(form);
                if (opts.action) fd.append(opts.action, '1');
                settings.data = fd;
                settings.processData = false;
                settings.contentType = false;
            } else {
                var data = $(form).serialize();
                if (opts.action) data += (data ? '&' : '') + encodeURIComponent(opts.action) + '=1';
                settings.data = data;
            }

            var submitBtn = form.querySelector('[type="submit"]');
            // These modals use <input type="submit">, so toggle value vs innerHTML.
            var isInput = submitBtn && submitBtn.tagName === 'INPUT';
            var label = submitBtn ? (isInput ? submitBtn.value : submitBtn.innerHTML) : null;
            if (submitBtn) {
                submitBtn.disabled = true;
                if (isInput) submitBtn.value = 'Processing...';
                else submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            }

            $.ajax(settings).done(function (resp) {
                if (!resp) { showAlert('Unexpected server response.', 'error'); return; }
                if (!resp.success) { showAlert(resp.message || 'The request failed.', 'error'); return; }

                showToast(resp.message || 'Saved successfully.', 'success');
                if (opts.reset && typeof form.reset === 'function') form.reset();
                if (opts.modal) $(opts.modal).modal('hide');
                if (opts.success) opts.success(resp);
            }).fail(function () {
                showAlert('Network error. Please check your connection and try again.', 'error');
            }).always(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (isInput) submitBtn.value = label;
                    else submitBtn.innerHTML = label;
                }
            });
        });
    }

    // Bulk delete via the shared confirm modal, without reloading.
    $(function () {
        var btn = document.getElementById('btn_delete');
        if (!btn) return;
        btn.type = 'button';
        btn.addEventListener('click', function () {
            var ids = [];
            var checked = document.querySelectorAll('.chk_delete:checked');
            for (var i = 0; i < checked.length; i++) ids.push(checked[i].value);
            if (ids.length === 0) {
                $('#deleteModal').modal('hide');
                showToast('Please select at least one record to delete.', 'warning');
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Deleting...';
            $.ajax({
                url: 'function.php',
                type: 'POST',
                dataType: 'json',
                data: { btn_delete: '1', 'chk_delete[]': ids }
            }).done(function (resp) {
                showToast((resp && resp.message) || 'Delete finished.', (resp && resp.type) || 'success');
                if (resp && resp.deleted > 0) {
                    $('#deleteModal').modal('hide');
                    refreshTech4edData();
                }
            }).fail(function () {
                showToast('Network error while deleting.', 'error');
            }).always(function () {
                btn.disabled = false;
                btn.value = 'Yes';
            });
        });
    });

    $(function() {
        $("#table").dataTable({
           "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0,3 ] } ],"aaSorting": []
        });
        rebindTech4edCheckboxes();
    });

    $(function () {
        bindAjaxForm('addForm', {
            action: 'btn_add',
            modal: '#addModal',
            reset: true,
            success: function () { refreshTech4edData(); }
        });
        bindAjaxForm('editForm', {
            action: 'btn_save',
            modal: '#editModal',
            success: function () { refreshTech4edData(); }
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
                        showToast('Data imported successfully!', 'success');
                        refreshTech4edData();
                    } else {
                        console.error('[Tech4Ed Import Error]', data.error);
                        showToast(data.error || 'Import failed.', 'error');
                    }
                }).catch(function(error) {
                    console.error('[Tech4Ed Import Error]', error);
                    showToast('Import failed. Check console for details.', 'error');
                });
            });

            document.getElementById('exportBtn').addEventListener('click', function() {
    // Get the selected sector value from the filter
    var selectedMunicipality = document.getElementById('projectSelect').value;
    // Redirect to the export.php script with the selected sector as a GET parameter
    window.location.href = 'exporttech4ed.php?municipality=' + encodeURIComponent(selectedMunicipality);
});

// Update statistics when sector is selected
document.getElementById('municipalitySelect').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

$(document).on('click', '.btn-edit-item', function() {
    var id = $(this).data('id');
    $.getJSON('../../ajax/tech4ed_get_item.php?action=item&id=' + id, function(data) {
        $('#edit_hidden_id').val(data.id);
        $('#edit_region').val(data.region);
        $('#edit_province').val(data.province);
        $('#edit_district').val(data.district);
        $('#edit_municipality').val(data.municipality);
        $('#edit_barangay').val(data.barangay);
        $('#edit_street').val(data.street);
        $('#edit_location').val(data.location);
        $('#edit_cname').val(data.cname);
        $('#edit_host').val(data.host);
        $('#edit_category').val(data.category);
        $('#edit_longitude').val(data.longitude);
        $('#edit_latitude').val(data.latitude);
        $('#edit_cmanager').val(data.cmanager);
        $('#edit_cemail').val(data.cemail);
        $('#edit_cmobile').val(data.cmobile);
        $('#edit_clandline').val(data.clandline);
        $('#edit_cgender').val(data.cgender);
        $('#edit_amanager').val(data.amanager);
        $('#edit_aemail').val(data.aemail);
        $('#edit_amobile').val(data.amobile);
        $('#edit_alandline').val(data.alandline);
        $('#edit_agender').val(data.agender);
        $('#edit_launch').val(data.launch);
        $('#edit_registration').val(data.registration);
        $('#edit_operation').val(data.operation);
        $('#edit_visited').val(data.visited);
        $('#edit_desktop').val(data.desktop);
        $('#edit_laptop').val(data.laptop);
        $('#edit_printer').val(data.printer);
        $('#edit_scanner').val(data.scanner);
        $('#edit_status').val(data.status);
        $('#edit_network').val(data.network);
        $('#edit_connectivity').val(data.connectivity);
        $('#edit_speed').val(data.speed);
        $('#edit_cmtmale').val(data.cmtmale);
        $('#edit_cmtfemale').val(data.cmtfemale);
        $('#edit_straining').val(data.straining);
        $('#edit_etraining').val(data.etraining);
        $('#edit_signing').val(data.signing);
        $('#edit_partner').val(data.partner);
        $('#edit_expiration').val(data.expiration);
        $('#edit_donation').val(data.donation);
        $('#edit_datedonation').val(data.datedonation);
        $('#edit_tcms').val(data.tcms);
        $('#edit_key_one').val(data.key_one);
        $('#edit_identifier').val(data.identifier);
        $('#editModal').modal('show');
    }).fail(function(xhr, status, error) {
        console.error('[Tech4Ed Load Item Error]', status, error);
        showToast('Failed to load record data.', 'error');
    });
});

// Load (or reload) the attachment grid for a record.
function loadTech4edPhotos(id, showModal) {
    var $grid = $('#photoGrid').empty();
    return $.getJSON('../../ajax/tech4ed_get_item.php?action=photos&id=' + id, function(photos) {
        if (photos.length === 0) {
            $grid.html('<div class="col-md-12"><p>No files uploaded.</p></div>');
        } else {
            $.each(photos, function(i, p) {
                var filePath = p.filepath;
                var ext = p.type;
                var nameClean = p.filename.replace(/\d+/g, '');
                var thumb = '';
                if (['jpg','jpeg','png','gif','pdf','xlsx','csv'].indexOf(ext) !== -1) {
                    thumb = SDMPreview.previewCard(filePath, p.filename, ext, 'tech4ed');
                } else if (['docx','pptx'].indexOf(ext) !== -1) {
                    thumb = '<div class="file-thumbnail-office"><i class="fas fa-file-word"></i></div>';
                } else {
                    thumb = '<div class="file-thumbnail">File type not previewable</div>';
                }
                $grid.append(
                    '<div class="col-md-4">' +
                        '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />' +
                        '<div class="file-item">' + thumb +
                            '<div class="file-info">' +
                                '<span class="filename">' + nameClean + '</span>' +
                                '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            });
        }
        var master = document.getElementById('cbxMainphoto');
        if (master) {
            master.checked = false;
            master.onchange = function () {
                var boxes = document.getElementsByClassName('chk_deletephoto');
                for (var i = 0; i < boxes.length; i++) boxes[i].checked = master.checked;
            };
        }
        if (showModal) $('#viewModal').modal('show');
    }).fail(function(xhr, status, error) {
        console.error('[Tech4Ed Load Photos Error]', status, error);
        showToast('Failed to load files.', 'error');
    });
}

$(document).on('click', '.btn-view-item', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    $('#view_item_title').text(name);
    $('#view_hidden_id').val(id);
    loadTech4edPhotos(id, true);
});

// Upload / delete attachments without reloading the page.
$(function () {
    var form = document.getElementById('viewFilesForm');
    if (!form) return;
    var alertBox = form.querySelector('.modal-alert-slot');

    function showAlert(msg, type) {
        if (!msg) { alertBox.innerHTML = ''; return; }
        alertBox.innerHTML = '<div class="alert alert-' + (type === 'error' ? 'danger' : type) +
            '" style="margin:10px 12px 0;"><i class="fa fa-exclamation-circle"></i> ' + msg + '</div>';
    }

    function busy(btn, on) {
        if (!btn) return;
        btn.disabled = on;
        if (!btn.dataset.label) btn.dataset.label = btn.value;
        btn.value = on ? 'Working...' : btn.dataset.label;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        showAlert('');

        var id = document.getElementById('view_hidden_id').value;
        var clicked = document.activeElement;
        var action = (clicked && (clicked.name === 'btn_addimage' || clicked.name === 'btn_remove'))
            ? clicked.name
            : 'btn_addimage';

        var settings = { url: 'function.php', type: 'POST', dataType: 'json' };

        if (action === 'btn_addimage') {
            var fd = new FormData(form);
            fd.append('btn_addimage', '1');
            settings.data = fd;
            settings.processData = false;
            settings.contentType = false;
        } else {
            var ids = [];
            var checked = document.querySelectorAll('.chk_deletephoto:checked');
            for (var i = 0; i < checked.length; i++) ids.push(checked[i].value);
            if (ids.length === 0) {
                showAlert('Select at least one file to delete.', 'error');
                return;
            }
            settings.data = { hidden_id: id, btn_remove: '1', 'chk_deletephoto[]': ids };
        }

        busy(clicked, true);
        $.ajax(settings).done(function (resp) {
            if (!resp || !resp.success) {
                showAlert((resp && resp.message) || 'The request failed.', 'error');
                return;
            }
            showToast(resp.message || 'Done.', 'success');
            if (action === 'btn_addimage') {
                document.getElementById('viewFilesForm').reset();
            }
            loadTech4edPhotos(id, false);
        }).fail(function () {
            showAlert('Network error. Please try again.', 'error');
        }).always(function () {
            busy(clicked, false);
        });
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
