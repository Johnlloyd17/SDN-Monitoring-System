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
<?php
require_once __DIR__ . '/letter_rows.php';

$letterManage = letter_can_manage();
$letterParams = letter_filter_params();
?>
<?php echo letter_render_stats($con, $letterParams); ?>

<?php echo letter_render_filters($con, $letterParams); ?>

<!-- Toolbar: Show N records per page + Add + Delete (left) | Search + Import + Export (right) -->
<div style="padding:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
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
        <label style="margin:0; font-weight:normal;"> records per page</label>
<?php if ($letterManage) { ?>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
<?php } ?>
    </div>
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <div class="input-group" style="width:300px;">
            <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search letters..." />
            <span class="input-group-btn">
                <button type="button" class="btn btn-default btn-sm" id="searchBtn" title="Search"><i class="fa fa-search"></i></button>
                <button type="button" class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
            </span>
        </div>
<?php if ($letterManage) { ?>
        <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i> Import</button>
        <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
        <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload" aria-hidden="true"></i> Export</button>
<?php } ?>
    </div>
</div>

<div class="box-body table-responsive">
    <table id="table" class="table table-bordered table-striped">
        <thead>
<?php echo letter_render_headers($letterManage); ?>
        </thead>
        <tbody>
<?php echo letter_render_rows(letter_fetch_rows($con, $letterParams), $letterManage); ?>
        </tbody>
    </table>

    <?php include "../deleteModal.php"; ?>
</div><!-- /.box-body -->
</div><!-- /.box -->

<?php include "add_modal.php"; ?>
<?php include "edit_modal.php"; ?>
<?php include "view_modal.php"; ?>
<?php include dirname(__DIR__) . '/sheet_preview_modal.php'; ?>

</div>   <!-- /.row -->
</section><!-- /.content -->
</aside><!-- /.right-side -->
</div><!-- ./wrapper -->
<?php }
include "../footer.php"; ?>
<script src="../../js/sdm-preview.js"></script>
<?php include "letter_list_js.php"; ?>
<script type="text/javascript">
    $(function () {
        if ($.fn.DataTable.isDataTable('#table')) {
            $('#table').DataTable().destroy();
        }
        $('#table').dataTable({
            "aoColumnDefs": [{ "bSortable": false, "aTargets": [0] }],
            "aaSorting": [],
            // "ltip" keeps the length dropdown and the DataTables search box out
            // of the DOM. ../toolbar_js.php draws "Show N records per page" and
            // the search box instead, so both features stay enabled and only
            // their default controls are hidden.
            "dom": "ltip",
            "pageLength": 5
        });
    });
    <?php include dirname(__DIR__) . '/toolbar_js.php'; ?>

    // Export is a download, so it stays a real navigation rather than ajax.
    // The button only exists for managers, so the listener has to be guarded or
    // a viewer's page throws here and never reaches updateDateTime below.
    var exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            var exportUrl = 'export.php?';
            ['locality', 'barangay', 'type', 'year'].forEach(function (key) {
                var el = document.getElementById('letter' + key.charAt(0).toUpperCase() + key.slice(1) + 'Select');
                if (exportUrl.length > 'export.php?'.length) exportUrl += '&';
                exportUrl += key + '=' + encodeURIComponent(el ? el.value : '');
            });
            window.location.href = exportUrl;
        });
    }

    function updateDateTime() {
        var now = new Date();
        var options = {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        };
        document.getElementById('dateTime').innerText = now.toLocaleString('en-US', options);
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

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

    </body>
</html>
