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
                    <img src="icons/logo.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>Planned Activities - GECS</h3>
                        <p class="header-address">DICT SDN Office</p>
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
                                Targets and Initiatives
                                    </div>
                                    <div class="panel-body">
    <form method="post" id="filterForm">
        <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="indicatorSelect">Select Indicator</label>
        <select id="indicatorSelect" name="indicator" class="form-control" onchange="this.form.submit()">
            <option value="">All Indicators</option>
            <?php
            $indicatorQuery = "SELECT DISTINCT indicator FROM targets_initiatives WHERE project = 'GECS' and indicator IS NOT NULL AND indicator != ''";
            if (!empty($_POST['year'])) {
                $year = mysqli_real_escape_string($con, $_POST['year']);
                $icsQuery .= " AND YEAR(start) = '$year'";
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
        <label for="yearSelect">Select Year</label>
        <select id="yearSelect" name="year" class="form-control" onchange="this.form.submit()">
            <option value="">All Years</option>
            <?php
            $yearQuery = "SELECT DISTINCT YEAR(start) AS year FROM targets_initiatives WHERE project = 'GECS' and start IS NOT NULL AND start != ''";

            if (!empty($_POST['indicator'])) {
                $indicator = mysqli_real_escape_string($con, $_POST['indicator']);
                $yearQuery .= " AND indicator = '$indicator'";
            }

            $yearResult = mysqli_query($con, $yearQuery);
            if ($yearResult) {
                while ($year = mysqli_fetch_assoc($yearResult)) {
                    echo '<option value="' . htmlspecialchars($year['year']) . '"' .
                         (isset($_POST['year']) && $_POST['year'] == $year['year'] ? ' selected' : '') . '>' .
                         htmlspecialchars($year['year']) . '</option>';
                }
            } else {
                echo "Error fetching years: " . mysqli_error($con);
            }
            ?>
        </select>
    </div>
</div>

    </form>
</div>
</div><div style="padding:10px; display: flex; justify-content: space-between;">
                                            <div>
                                            <?php if ($_SESSION['role'] === 'Administrator') { ?>
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                        
                                                    <?php } elseif ($_SESSION['username'] === 'gecssdn') { ?>
                                                        <!-- Limited access for specific user -->
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Activity</button>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    <?php } ?>
                                            </div>
                                            
                                        </div>
                                
                                <div class="box-body table-responsive">
                                <form method="post">
                                <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <?php 
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'gecssdn') {
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
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'gecssdn') {
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
                                                      $tableQuery = "SELECT * FROM targets_initiatives WHERE project = 'GECS'";
                                                      
                                                      if (isset($_POST['indicator']) && $_POST['indicator'] != '') {
                                                          $tableQuery .= " AND indicator = '" . mysqli_real_escape_string($con, $_POST['indicator']) . "'";
                                                      }
                                                      if (isset($_POST['year']) && $_POST['year'] != '') {
                                                       $tableQuery .= " AND YEAR(start) = '" . mysqli_real_escape_string($con, $_POST['year']) . "'";
                                                   }
                                                      $tableQuery .= " ORDER BY indicator ASC"; // Order by start date
                                                      $result = mysqli_query($con, $tableQuery);
                                   
                                                
                                                  if (!$result) {
                                                      die('Error: ' . mysqli_error($con));
                                                  }
                                                  
                                                  while ($row = mysqli_fetch_assoc($result)) {
                                                    echo '<tr>';
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'gecssdn') {
                                                echo '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="'.$row['id'].'" /></td>';
                                                echo ' <td>' . $counter++ . '</td>'; // Assuming 'id' is the primary key
                                            }
                                            echo '
                                                <td>' . $row['start'] . '</td>
                                                                <td>' . $row['end'] . '</td>
                                                                <td>' . $row['project'] . '</td>
                                                                <td>' . $row['subproject'] . '</td>
                                                                <td>' . $row['activity'] . '</td>
                                                                <td>' . $row['training'] . '</td>
                                                                <td>' . $row['municipality'] . '</td>
                                                                <td>' . $row['barangay'] . '</td>
                                                                <td>' . $row['district'] . '</td>
                                                                <td>' . $row['agency'] . '</td>
                                                                <td>' . $row['subproject'] . '</td>
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
                                            if ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'gecssdn') {
                                                echo '<td>
                                                    <button class="btn btn-primary btn-sm btn-edit-item" data-id="'.$row['id'].'" data-name="'.htmlspecialchars($row['activity'], ENT_QUOTES).'"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button>
                                                    <button class="btn btn-primary btn-sm btn-view-item" data-id="'.$row['id'].'" data-name="'.htmlspecialchars($row['activity'], ENT_QUOTES).'"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
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

            <?php include "edit_modal.php"; ?>
            <?php include "view_modal.php"; ?>
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

        $(document).on('click', '.btn-edit-item', function() {
            var id = $(this).data('id');
            $.get('../../ajax/planned_get_item.php', { action: 'item', id: id }, function(data) {
                $('#edit_hidden_id').val(data.id || '');
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
                $('#edit_type').val(data.type || '');
                $('#editModal').modal('show');
            }, 'json');
        });

        $(document).on('click', '.btn-view-item', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#view_item_title').text(name);
            $('#view_hidden_id').val(id);
            $.get('../../ajax/planned_get_item.php', { action: 'photos', id: id }, function(data) {
                var grid = $('#photoGrid');
                grid.empty();
                if (data.length === 0) {
                    grid.html('<div class="col-md-12 text-center"><p>No files uploaded yet.</p></div>');
                } else {
                    $.each(data, function(i, photo) {
                        var filePath = 'photo/' + photo.filename;
                        var ext = photo.filename.split('.').pop().toLowerCase();
                        var thumb = '';
                        if (['jpg','jpeg','png','gif'].indexOf(ext) !== -1) {
                            thumb = '<img src="' + filePath + '" alt="' + photo.filename + '" class="file-thumbnail"/>';
                        } else if (ext === 'pdf') {
                            thumb = '<div class="file-thumbnail-pdf"><embed src="' + filePath + '" type="application/pdf" width="100%" height="100%" /></div>';
                        } else {
                            thumb = '<div class="file-thumbnail-office"><i class="fas fa-file"></i></div>';
                        }
                        var nameNoNum = photo.filename.replace(/\d+/g, '');
                        grid.append(
                            '<div class="col-md-4">' +
                            '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + photo.id + '" />' +
                            '<div class="file-item">' + thumb +
                            '<div class="file-info"><span class="filename">' + nameNoNum + '</span>' +
                            '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                            '</div></div></div>'
                        );
                    });
                }
                $('#viewModal').modal('show');
            }, 'json');
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