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
        <!-- Right side column. Contains the navbar and content of the page -->
        <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/fwfa_logo.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                        <p class="header-address">Letter Requests</p>
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
                                Letter Requests Status Report
                                    </div>
                                    <div class="panel-body">
    <form method="post" id="filterForm">
        
    <div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <a href="../fwfa_letter/fw4a_localities.php"><div class="info-box">
        <span class="info-box-icon bg-blue">
            <img src="icons/municipality_1.png" alt="Total Localities" style="width: 50px; height: 50px;">
        </span>
        <div class="info-box-content">
            <span class="info-box-text">Total Localities</span>
            <span class="info-box-number">
                <?php
                $totalLocalitiesQuery = "SELECT COUNT(DISTINCT locality) AS total_localities FROM locationrequests WHERE locality != ''";
                $conditions = [];

                if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                    $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                    $conditions[] = "barangay = '$barangay'";
                }
                if (isset($_POST['type']) && $_POST['type'] != '') {
                    $type = mysqli_real_escape_string($con, $_POST['type']);
                    $conditions[] = "type = '$type'";
                }
                if (isset($_POST['year']) && $_POST['year'] != '') {
                    $year = mysqli_real_escape_string($con, $_POST['year']);
                    $conditions[] = "year = '$year'";
                }

                if (count($conditions) > 0) {
                    $totalLocalitiesQuery .= " AND " . implode(" AND ", $conditions);
                }

                $totalLocalitiesResult = mysqli_query($con, $totalLocalitiesQuery);
                if ($totalLocalitiesResult) {
                    echo mysqli_fetch_assoc($totalLocalitiesResult)['total_localities'];
                } else {
                    echo "Error fetching total localities: " . mysqli_error($con);
                }
                ?></a>
            </span>
        </div>
    </div>
</div>

<div class="col-md-3 col-sm-6 col-xs-12">
<a href="../fwfa_letter/fw4a_barangays.php"><div class="info-box">
        <span class="info-box-icon bg-red">
            <img src="icons/barangays_1.png" alt="Total Barangays" style="width: 60px; height: 60px;">
        </span>
        <div class="info-box-content">
            <span class="info-box-text">Total Barangays</span>
            <span class="info-box-number">
                <?php
                $totalBarangaysQuery = "SELECT COUNT(DISTINCT barangay) AS total_barangays FROM locationrequests WHERE barangay != ''";
                $conditions = [];

                if (isset($_POST['locality']) && $_POST['locality'] != '') {
                    $locality = mysqli_real_escape_string($con, $_POST['locality']);
                    $conditions[] = "locality = '$locality'";
                }
                if (isset($_POST['type']) && $_POST['type'] != '') {
                    $type = mysqli_real_escape_string($con, $_POST['type']);
                    $conditions[] = "type = '$type'";
                }
                if (isset($_POST['year']) && $_POST['year'] != '') {
                    $year = mysqli_real_escape_string($con, $_POST['year']);
                    $conditions[] = "year = '$year'";
                }

                if (count($conditions) > 0) {
                    $totalBarangaysQuery .= " AND " . implode(" AND ", $conditions);
                }

                $totalBarangaysResult = mysqli_query($con, $totalBarangaysQuery);
                if ($totalBarangaysResult) {
                    echo mysqli_fetch_assoc($totalBarangaysResult)['total_barangays'];
                } else {
                    echo "Error fetching total barangays: " . mysqli_error($con);
                }
                ?></a>
            </span>
        </div>
    </div>
</div>

                                            <div class="col-md-3 col-sm-6 col-xs-12">
                                            <a href="../fwfa_letter/fw4a_requests.php"><div class="info-box">
                                                    <span class="info-box-icon bg-yellow">
                                                        <img src="icons/requests_1.png" alt="Total Requests" style="width: 48px; height: 48px;">
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Requests</span>
                                                        <a class="info-box-number">
                                                            <?php
                                                            $totalRequestsQuery = "SELECT COUNT(*) AS total_requests FROM locationrequests WHERE type = 'Request'";
                                                            if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                                                $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                                                $totalRequestsQuery .= " AND locality = '$locality'";
                                                            }
                                                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                                                $totalRequestsQuery .= " AND barangay = '$barangay'";
                                                            }
                                                            if (isset($_POST['year']) && $_POST['year'] != '') {
                                                                $year = mysqli_real_escape_string($con, $_POST['year']);
                                                                $totalRequestsQuery .= " AND year = '$year'";
                                                            }
                                                            $totalRequestsResult = mysqli_query($con, $totalRequestsQuery);
                                                            if ($totalRequestsResult) {
                                                                echo mysqli_fetch_assoc($totalRequestsResult)['total_requests'];
                                                            } else {
                                                                echo "Error fetching total requests: " . mysqli_error($con);
                                                            }
                                                            ?></a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 col-sm-6 col-xs-12">
                                            <a href="../fwfa_letter/fw4a_provision.php"><div class="info-box">
                                                    <span class="info-box-icon bg-blue">
                                                        <img src="icons/provision_1.png" alt="Total Provisions" style="width: 45px; height: 45px;">
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Provisions</span>
                                                        <span class="info-box-number">
                                                            <?php
                                                            $totalProvisionsQuery = "SELECT COUNT(*) AS total_provisions FROM locationrequests WHERE type = 'Provision'";
                                                            if (isset($_POST['locality ']) && $_POST['locality'] != '') {
                                                                $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                                                $totalProvisionsQuery .= " AND locality = '$locality'";
                                                            }
                                                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                                                $totalProvisionsQuery .= " AND barangay = '$barangay'";
                                                            }
                                                            if (isset($_POST['year']) && $_POST['year'] != '') {
                                                                $year = mysqli_real_escape_string($con, $_POST['year']);
                                                                $totalProvisionsQuery .= " AND year = '$year'";
                                                            }
                                                            $totalProvisionsResult = mysqli_query($con, $totalProvisionsQuery);
                                                            if ($totalProvisionsResult) {
                                                                echo mysqli_fetch_assoc($totalProvisionsResult)['total_provisions'];
                                                            } else {
                                                                echo "Error fetching total provisions: " . mysqli_error($con);
                                                            }
                                                            ?></a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                                <div class="col-md-3 col-sm-6 col-xs-12">
                                                    <div class="form-group">
                                                        <label for="localitySelect">Select Locality</label>
                                                        <select id="localitySelect" name="locality" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Localities</option>
                                                            <?php
                                                            $localityQuery = "SELECT DISTINCT locality FROM locationrequests WHERE locality != ''";
                                                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                                                $localityQuery .= " AND barangay = '$barangay'";
                                                            }
                                                            if (isset($_POST['type']) && $_POST['type'] != '') {
                                                                $type = mysqli_real_escape_string($con, $_POST['type']);
                                                                $localityQuery .= " AND type = '$type'";
                                                            }
                                                            if (isset($_POST['year']) && $_POST['year'] != '') {
                                                                $year = mysqli_real_escape_string($con, $_POST['year']);
                                                                $localityQuery .= " AND year = '$year'";
                                                            }
                                                            $localitysQuery = mysqli_query($con, $localityQuery);
                                                            if ($localitysQuery) {
                                                                while ($locality = mysqli_fetch_assoc($localitysQuery)) {
                                                                    echo '<option value="' . $locality['locality'] . '"' . (isset($_POST['locality']) && $_POST['locality'] == $locality['locality'] ? ' selected' : '') . '>' . $locality['locality'] . '</option>';
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
                                                        <label for="barangaySelect">Select Barangay</label>
                                                        <select id="barangaySelect" name="barangay" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Barangays</option>
                                                            <?php
                                                            $barangayQuery = "SELECT DISTINCT barangay FROM locationrequests WHERE barangay != ''";
                                                            if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                                                $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                                                $barangayQuery .= " AND locality = '$locality'";
                                                            }
                                                            if (isset($_POST['type']) && $_POST['type'] != '') {
                                                                $type = mysqli_real_escape_string($con, $_POST['type']);
                                                                $barangayQuery .= " AND type = '$type'";
                                                            }
                                                            if (isset($_POST['year']) && $_POST['year'] != '') {
                                                                $year = mysqli_real_escape_string($con, $_POST['year']);
                                                                $barangayQuery .= " AND year = '$year'";
                                                            }
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
                                                        <label for="typeSelect">Select Type</label>
                                                        <select id="typeSelect" name="type" class="form-control" onchange="this.form.submit()">
                                                            <option value="">All Types</option>
                                                            <?php
                                                            $typeQuery = "SELECT DISTINCT type FROM locationrequests WHERE type != ''";
                                                            if (isset($_POST['locality']) && $_POST['locality'] != '') {
                                                                $locality = mysqli_real_escape_string($con, $_POST['locality']);
                                                                $typeQuery .= " AND locality = '$locality'";
                                                            }
                                                            if (isset($_POST['barangay']) && $_POST['barangay'] != '') {
                                                                $barangay = mysqli_real_escape_string($con, $_POST['barangay']);
                                                                $typeQuery .= " AND barangay = '$barangay'";
                                                            }
                                                            if (isset($_POST['year']) && $_POST['year'] != '') {
                                                                $year = mysqli_real_escape_string($con, $_POST['year']);
                                                                $typeQuery .= " AND year = '$year'";
                                                            }
                                                            $typesQuery = mysqli_query($con, $typeQuery);
                                                            if ($typesQuery) {
                                                                while ($type = mysqli_fetch_assoc($typesQuery)) {
                                                                    echo '<option value="' . $type['type'] . '"' . (isset($_POST['type']) && $_POST['type'] == $type['type'] ? ' selected' : '') . '>' . $type['type'] . '</option>';
                                                                }
                                                            } else {
                                                                echo "Error fetching types: " . mysqli_error($con);
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
                                                            $yearQuery = "SELECT DISTINCT year FROM locationrequests WHERE year != ''";
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
        </div>
    </form>
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
                                                        <th>Locality</th>
                                                        <th>Barangay</th>
                                                        <th>District</th>
                                                        <th>Location Name</th>
                                                        <th>Date Requested</th>
                                                        <th>Year</th>
                                                        <th>Type</th>
                                                        <th>Status</th>
                                                        <th>Accomplished Date</th>
                                                        <th>Remarks</th>
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
                                                    $counter = 1;  // Initialize counter
                                                    $localityFilter = isset($_POST['locality']) ? $_POST['locality'] : '';
                                                    $barangayFilter = isset($_POST['barangay']) ? $_POST['barangay'] : '';
                                                    $typeFilter = isset($_POST['type']) ? $_POST['type'] : '';
                                                    $yearFilter = isset($_POST['year']) ? $_POST['year'] : '';
                                                    
                                                    // Dynamic query based on selected filters
                                                    $tableQuery = "SELECT * FROM locationrequests WHERE locality != ''";
                                                    
                                                    if ($localityFilter) {
                                                        $tableQuery .= " AND locality = '" . mysqli_real_escape_string($con, $localityFilter) . "'";
                                                    }
                                                    
                                                    if ($barangayFilter) {
                                                        $tableQuery .= " AND barangay = '" . mysqli_real_escape_string($con, $barangayFilter) . "'";
                                                    }
                                                    if ($typeFilter) {
                                                        $tableQuery .= " AND type = '" . mysqli_real_escape_string($con, $typeFilter) . "'";
                                                    }
                                                    if ($yearFilter) {
                                                        $tableQuery .= " AND year = '" . mysqli_real_escape_string($con, $yearFilter) . "'";
                                                    }
                                                    
                                                    $tableQuery .= " ORDER BY locality ASC";
                                                    $result = mysqli_query($con, $tableQuery);
                                                    
                                                    if (!$result) {
                                                        die('Error: ' . mysqli_error($con));
                                                    }
                                                    
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo '<tr>';
                                                if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'fwfasdn') {
                                                    echo '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="'.$row['id'].'" /></td>';
                                                    echo ' <td>' . $counter++ . '</td>'; // Assuming 'id' is the primary key
                                                }
                                                echo '
                                                                <td>' . $row['locality'] . '</td>
                                                                <td>' . $row['barangay'] . '</td>
                                                                <td>' . $row['district'] . '</td>
                                                                <td>' . $row['location'] . '</td>
                                                                <td>' . $row['date'] . '</td>
                                                                <td>' . $row['year'] . '</td>
                                                                <td>' . $row['type'] . '</td>
                                                                <td>' . $row['status'] . '</td>
                                                                <td>' . $row['accomplished'] . '</td>
                                                                <td>' . $row['remarks'] . '</td> ';
  if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'fwfasdn') {
                                                echo '<td>
                                                    <button class="btn btn-primary btn-sm btn-edit-item" data-id="'.$row['id'].'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button>
                                                    <button class="btn btn-primary btn-sm btn-view-item" data-id="'.$row['id'].'" data-name="'.htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8').'"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
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

$(document).on('click', '.btn-edit-item', function() {
    var id = $(this).data('id');
    $.getJSON('ajax/letter_get_item.php?action=item&id=' + id, function(data) {
        $('#edit_hidden_id').val(data.id);
        $('#edit_locality').val(data.locality);
        $('#edit_barangay').val(data.barangay);
        $('#edit_district').val(data.district);
        $('#edit_location').val(data.location);
        $('#edit_date').val(data.date);
        $('#edit_year').val(data.year);
        $('#edit_type').val(data.type);
        $('#edit_status').val(data.status);
        $('#edit_accomplished').val(data.accomplished);
        $('#edit_remarks').val(data.remarks);
        $('#editModal').modal('show');
    });
});

$(document).on('click', '.btn-view-item', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    $('#view_item_title').text(name);
    $('#view_hidden_id').val(id);
    var $grid = $('#photoGrid').empty();
    $.getJSON('ajax/letter_get_item.php?action=photos&id=' + id, function(photos) {
        if (photos.length === 0) {
            $grid.html('<div class="col-md-12"><p>No files uploaded.</p></div>');
        } else {
            $.each(photos, function(i, p) {
                var filePath = p.filepath;
                var ext = p.type;
                var nameClean = p.filename.replace(/\d+/g, '');
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
        $('#viewModal').modal('show');
    });
});

var select_all = document.getElementById("cbxMainphoto");
if (select_all) {
    select_all.addEventListener("change", function(e){
        var checkboxes = document.getElementsByClassName("chk_deletephoto");
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = select_all.checked;
        }
    });
}

                    $(function () {
                        $("#table").DataTable({
                            "aoColumnDefs": [{ "bSortable": false, "aTargets": [0] }], // Update index if needed
                            "aaSorting": [],
                            "pageLength": 10, // Set default rows per page
                            "lengthMenu": [10, 25, 50, 100] // Options for rows per page
                        });
                        $(".select2").select2();
                    });
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

            document.getElementById('exportBtn').addEventListener('click', function() {
    // Get the selected filter values
    var selectedLocality = document.getElementById('localitySelect').value;
    var selectedBarangay = document.getElementById('barangaySelect').value;
    var selectedType = document.getElementById('typeSelect').value; // Ensure you have a typeSelect dropdown
    var selectedYear = document.getElementById('yearSelect').value; // Ensure you have a yearSelect dropdown

    // Build the URL with all selected filters as GET parameters
    var exportUrl = 'export.php?' +
        'locality=' + encodeURIComponent(selectedLocality) +
        '&barangay=' + encodeURIComponent(selectedBarangay) +
        '&type=' + encodeURIComponent(selectedType) +
        '&year=' + encodeURIComponent(selectedYear);

    // Redirect to the export.php script with the filters
    window.location.href = exportUrl;
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