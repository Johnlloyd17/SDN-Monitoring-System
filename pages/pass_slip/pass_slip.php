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
    <?php include "../connection.php"; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

       <aside class="right-side">
    <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Office Equipment Pass Slip</h1>
        <div class="header-date-time" id="dateTime"></div>
    </section>
    <section class="content">
        <div class="row">
            <div class="box">
                <div class="box-header">
                    <div class="col-md-12 col-sm-12 col-xs-12"><br>

                        <!-- Statistics Cards -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart"></i> Pass Slip Statistics
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php
                                    $tableCheck = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0");
                                    if ($tableCheck) {
                                        $qTotal = mysqli_query($con, "SELECT COUNT(DISTINCT pass_slip_no) AS total FROM pass_slip");
                                        $rTotal = mysqli_fetch_assoc($qTotal);

                                        $qBorrowed = mysqli_query($con, "SELECT COUNT(DISTINCT pass_slip_no) AS total FROM pass_slip WHERE status = 'borrowed'");
                                        $rBorrowed = mysqli_fetch_assoc($qBorrowed);

                                        $qReturned = mysqli_query($con, "SELECT COUNT(DISTINCT pass_slip_no) AS total FROM pass_slip WHERE status = 'returned'");
                                        $rReturned = mysqli_fetch_assoc($qReturned);

                                        $qOverdue = mysqli_query($con, "SELECT COUNT(DISTINCT pass_slip_no) AS total FROM pass_slip WHERE status = 'borrowed' AND return_date < CURDATE()");
                                        $rOverdue = mysqli_fetch_assoc($qOverdue);
                                    } else {
                                        $rTotal = ['total' => 0];
                                        $rBorrowed = ['total' => 0];
                                        $rReturned = ['total' => 0];
                                        $rOverdue = ['total' => 0];
                                    }
                                    ?>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-aqua">
                                                <i class="fa fa-file-text-o"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Pass Slips</span>
                                                <span class="info-box-number"><?php echo $rTotal['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-yellow">
                                                <i class="fa fa-hand-holding"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Currently Borrowed</span>
                                                <span class="info-box-number"><?php echo $rBorrowed['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-green">
                                                <i class="fa fa-check-circle"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Returned</span>
                                                <span class="info-box-number"><?php echo $rReturned['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-red">
                                                <i class="fa fa-exclamation-triangle"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Overdue</span>
                                                <span class="info-box-number"><?php echo $rOverdue['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Statistics Cards -->

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Office Equipment Pass Slip Records
                            </div>
                            <div class="panel-body">
    <form method="post" id="filterForm">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="statusSelect">Filter by Status</label>
                    <select id="statusSelect" name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="borrowed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                        <option value="returned" <?php echo (isset($_POST['status']) && $_POST['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                        <option value="overdue" <?php echo (isset($_POST['status']) && $_POST['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="dateFrom">Date From</label>
                    <input type="date" id="dateFrom" name="date_from" class="form-control" onchange="this.form.submit()"
                        autocomplete="off"
                        value="<?php echo isset($_POST['date_from']) ? $_POST['date_from'] : ''; ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="dateTo">Date To</label>
                    <input type="date" id="dateTo" name="date_to" class="form-control" onchange="this.form.submit()"
                        autocomplete="off"
                        value="<?php echo isset($_POST['date_to']) ? $_POST['date_to'] : ''; ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="form-group">
                    <label for="searchBorrower">Search Borrower</label>
                    <input type="text" id="searchBorrower" name="search_borrower" class="form-control" placeholder="Search by name..."
                        autocomplete="off"
                        value="<?php echo isset($_POST['search_borrower']) ? $_POST['search_borrower'] : ''; ?>">
                </div>
            </div>
        </div>
    </form>
        <div style="padding:10px; display: flex; justify-content: space-between;">
            <div>
                <?php if ($_SESSION['role'] !== 'staff') { ?>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPassSlipModal"><i class="fa fa-plus"></i> Create Pass Slip</button>
                <?php } ?>
            </div>
            <div>
                <?php if ($_SESSION['role'] !== 'staff') { ?>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSelected()"><i class="fa fa-trash"></i> Delete Selected</button>
                <?php } ?>
            </div>
        </div>

        <div class="box-body table-responsive">
            <form method="post">
            <table id="passSlipTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" id="cbxMain" class="cbxMain" onchange="checkMain(this)"/></th>
                        <th style="width: 20px !important;">No.</th>
                        <th>Pass Slip No.</th>
                        <th>Items</th>
                        <th>#</th>
                        <th>Pull-Out Date</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        <th style="width: 180px !important;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $counter = 1;
                    $tableCheck2 = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0");
                    if ($tableCheck2) {
                    $tableQuery = "SELECT 
                        ps.pass_slip_no,
                        MIN(ps.id) AS first_id,
                        MIN(ps.pullout_date) AS pullout_date,
                        MIN(ps.requested_by_out) AS requested_by_out,
                        MIN(ps.inspected_by_out) AS inspected_by_out,
                        MIN(ps.approved_by_out) AS approved_by_out,
                        MIN(ps.status) AS status,
                        MIN(ps.purpose) AS purpose,
                        MIN(ps.condition_out) AS condition_out,
                        MIN(ps.return_date) AS return_date,
                        MIN(ps.requested_by_return) AS requested_by_return,
                        MIN(ps.inspected_by_return) AS inspected_by_return,
                        MIN(ps.approved_by_return) AS approved_by_return,
                        MIN(ps.condition_return) AS condition_return,
                        MIN(ps.remarks) AS remarks,
                        COUNT(*) AS item_count,
                        GROUP_CONCAT(ps.item_description SEPARATOR ', ') AS items_summary
                    FROM pass_slip ps WHERE 1=1";

                    if (isset($_POST['status']) && $_POST['status'] != '') {
                        $status = mysqli_real_escape_string($con, $_POST['status']);
                        $tableQuery .= " AND ps.status = '$status'";
                    }
                    if (isset($_POST['date_from']) && $_POST['date_from'] != '') {
                        $dateFrom = mysqli_real_escape_string($con, $_POST['date_from']);
                        $tableQuery .= " AND ps.pullout_date >= '$dateFrom'";
                    }
                    if (isset($_POST['date_to']) && $_POST['date_to'] != '') {
                        $dateTo = mysqli_real_escape_string($con, $_POST['date_to']);
                        $tableQuery .= " AND ps.pullout_date <= '$dateTo'";
                    }
                    if (isset($_POST['search_borrower']) && $_POST['search_borrower'] != '') {
                        $search = mysqli_real_escape_string($con, $_POST['search_borrower']);
                        $tableQuery .= " AND (ps.requested_by_out LIKE '%$search%' OR ps.approved_by_out LIKE '%$search%' OR ps.pass_slip_no LIKE '%$search%')";
                    }

                    $tableQuery .= " GROUP BY ps.pass_slip_no ORDER BY MIN(ps.pullout_date) DESC";
                    $result = mysqli_query($con, $tableQuery);

                    if (!$result) {
                        die('Error: ' . mysqli_error($con));
                    }

                    while ($row = mysqli_fetch_assoc($result)) {
                        $statusClass = '';
                        $statusLabel = '';
                        switch($row['status']) {
                            case 'borrowed':
                                $statusClass = 'label label-warning';
                                $statusLabel = 'Borrowed';
                                break;
                            case 'returned':
                                $statusClass = 'label label-success';
                                $statusLabel = 'Returned';
                                break;
                            case 'overdue':
                                $statusClass = 'label label-danger';
                                $statusLabel = 'Overdue';
                                break;
                        }

                        $slipNo = htmlspecialchars($row['pass_slip_no']);
                        $itemsSummary = htmlspecialchars($row['items_summary']);
                        $itemCount = $row['item_count'];

                        echo '
                        <tr>
                            <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $slipNo . '" /></td>
                            <td>' . $counter++ . '</td>
                            <td><strong>' . $slipNo . '</strong></td>
                            <td title="' . $itemsSummary . '">' . (strlen($itemsSummary) > 50 ? substr($itemsSummary, 0, 50) . '...' : $itemsSummary) . '</td>
                            <td>' . $itemCount . '</td>
                            <td>' . $row['pullout_date'] . '</td>
                            <td>' . htmlspecialchars($row['requested_by_out']) . '</td>
                            <td><span class="' . $statusClass . '">' . $statusLabel . '</span></td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">';

                        echo '<button type="button" class="btn btn-info btn-xs" onclick="viewPassSlip(\'' . $slipNo . '\')" title="View Details"><i class="fa fa-eye"></i></button>';

                        if ($row['status'] == 'borrowed' && $_SESSION['role'] !== 'staff') {
                                echo '<button type="button" class="btn btn-success btn-xs" onclick="openReturnModal(\'' . $slipNo . '\')" title="Process Return"><i class="fa fa-undo"></i></button>';
                        }
                        echo '
                                    <button type="button" class="btn btn-default btn-xs" onclick="openPrintSlip(\'' . $slipNo . '\')" title="Print Pass Slip"><i class="fa fa-print"></i></button>
                                    <button type="button" class="btn btn-warning btn-xs" onclick="openUploadModal(\'' . $slipNo . '\')" title="Upload Scanned Copy"><i class="fa fa-paperclip"></i></button>
                                </div>
                            </td>
                        </tr>';
                    }
                    } else {
                        echo '<tr><td colspan="9" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> Pass Slip table not found. Please run the database migration first.</td></tr>';
                    }
                ?>
                </tbody>
            </table>

            <?php include "../deleteModal.php"; ?>
            </form>
        </div>
    </div>
</div>

<?php include "../edit_notif.php"; ?>
<?php include "../added_notif.php"; ?>
<?php include "../delete_notif.php"; ?>

<?php include "add_modal.php"; ?>
<?php include "function.php"; ?>

<!-- View Pass Slip Detail Modal -->
<div class="modal fade" id="viewPassSlipModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #001f3f 0%, #1b3a6b 100%); color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Pass Slip Preview</h4>
            </div>
            <div class="modal-body" style="padding: 0; height: 700px; background: #e9e9e9;">
                <iframe id="viewSlipFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Return Pass Slip Modal -->
<div class="modal fade" id="returnPassSlipModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-undo"></i> Process Return - <span id="returnSlipNo"></span></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pass_slip_no" id="returnSlipNoInput">

                    <div class="alert alert-info">
                        <strong>Borrower:</strong> <span id="returnBorrower"></span> |
                        <strong>Pull-Out Date:</strong> <span id="returnPulloutDate"></span> |
                        <strong>Total Items:</strong> <span id="returnItemCount"></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Item Description</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                </tr>
                            </thead>
                            <tbody id="returnItemsBody"></tbody>
                        </table>
                    </div>

                    <hr>
                    <h5><i class="fa fa-signature"></i> Return Authorization</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" class="form-control" required autocomplete="off" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Condition on Return <span class="text-danger">*</span></label>
                                <select name="condition_return" class="form-control" required>
                                    <option value="Good - No Damage">Good - No Damage</option>
                                    <option value="Fair - Minor Wear">Fair - Minor Wear</option>
                                    <option value="Poor - Needs Repair">Poor - Needs Repair</option>
                                    <option value="Damaged">Damaged</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Requested By (Return) <span class="text-danger">*</span></label>
                                <input type="text" name="requested_by_return" id="returnRequestedBy" class="form-control" required placeholder="Auto-filled from pull-out" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inspected By (Return) <span class="text-danger">*</span></label>
                                <input type="text" name="inspected_by_return" id="returnInspectedBy" class="form-control" required placeholder="Choose or type name" list="inspectorList" autocomplete="off">
                                <datalist id="inspectorList"></datalist>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Approved By (Return) <span class="text-danger">*</span></label>
                                <input type="text" name="approved_by_return" id="returnApprovedBy" class="form-control" required readonly style="background-color: #f0f0f0;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks_return" class="form-control" rows="2" placeholder="Condition notes, damages, etc..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="process_return" class="btn btn-success"><i class="fa fa-check"></i> Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Print Pass Slip Modal -->
<div class="modal fade" id="printPassSlipModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #001f3f 0%, #1b3a6b 100%); color: #fff; border-radius: 0;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-print"></i> Pass Slip Preview</h4>
            </div>
            <div class="modal-body" style="padding: 0; height: 700px; background: #e9e9e9;">
                <iframe id="printSlipFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printSlipFrame()"><i class="fa fa-print"></i> Print Pass Slip</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Attachment Modal -->
<div class="modal fade" id="uploadAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-md" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #e67e22; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-paperclip"></i> Upload Scanned Copy - <span id="uploadSlipNo"></span></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="uploadSlipNoInput">

                <form id="uploadAttachmentForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_attachment">
                    <input type="hidden" name="pass_slip_no" id="uploadFormSlipNo">
                    <div class="form-group">
                        <label>Select File(s) <span class="text-danger">*</span></label>
                        <input type="file" name="attachments[]" id="attachmentFiles" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Accepted: PDF, JPG, PNG (Max 10MB each)</small>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm" id="uploadBtn"><i class="fa fa-cloud-upload"></i> Upload</button>
                </form>

                <hr>
                <h5><i class="fa fa-list"></i> Attached Files</h5>
                <div id="attachmentToolbar" style="margin-bottom:8px; align-items:center; gap:10px;">
                    <input type="checkbox" id="cbxAllAttachments" onchange="toggleAllAttachments(this)"> <label for="cbxAllAttachments" style="margin:0; font-weight:normal; cursor:pointer;">Select All</label>
                    <button type="button" class="btn btn-danger btn-xs" id="btnDeleteSelected" onclick="deleteSelectedAttachments()" disabled><i class="fa fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)</button>
                </div>
                <div id="attachmentList" style="max-height: 250px; overflow-y: auto;">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Attachment Modal -->
<div class="modal fade" id="previewAttachmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #001f3f 0%, #1b3a6b 100%); color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-image-o"></i> <span id="previewFileName">Preview</span></h4>
            </div>
            <div class="modal-body" id="previewModalBody" style="padding: 0; height: 700px; background: #e9e9e9; display: flex; align-items: center; justify-content: center;">
                <div id="previewSpinner" style="text-align:center;">
                    <i class="fa fa-spinner fa-spin" style="font-size:36px; color:#888;"></i>
                    <p style="margin-top:10px; color:#888;">Loading preview...</p>
                </div>
                <iframe id="previewPdfFrame" src="" style="width: 100%; height: 100%; border: none; display: none;" onload="document.getElementById('previewSpinner').style.display='none';"></iframe>
                <img id="previewImgTag" src="" style="max-width: 100%; max-height: 100%; display: none;" onload="document.getElementById('previewSpinner').style.display='none';">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="printPreview()"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
</div>

                    </div>
                </section>
            </aside>
        </div>
    <?php }
    include "../footer.php"; ?>

<script type="text/javascript">
    var select_all = document.getElementById("cbxMain");
    var checkboxes = document.getElementsByClassName("chk_delete");

    select_all.addEventListener("change", function(e){
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = select_all.checked;
        }
    });

    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].addEventListener('change', function(e){
            if(this.checked == false){
                select_all.checked = false;
            }
            if(document.querySelectorAll('.chk_delete:checked').length == checkboxes.length){
                select_all.checked = true;
            }
        });
    }

    // Store all pass slip data for JS access
    var passSlipData = {};

    <?php
    if ($tableCheck2 && $result) {
        mysqli_data_seek($result, 0);
        while ($r = mysqli_fetch_assoc($result)) {
            $key = addslashes($r['pass_slip_no']);
            echo "passSlipData['{$key}'] = " . json_encode($r) . ";\n";
        }
    }
    ?>

    function viewPassSlip(slipNo) {
        var d = passSlipData[slipNo];
        if (!d) return;
        var frame = document.getElementById('viewSlipFrame');
        frame.src = 'print_slip.php?id=' + d.first_id;
        $('#viewPassSlipModal').modal('show');
    }

    function openReturnModal(slipNo) {
        var d = passSlipData[slipNo];
        if (!d) return;

        document.getElementById('returnSlipNo').textContent = d.pass_slip_no;
        document.getElementById('returnSlipNoInput').value = d.pass_slip_no;
        document.getElementById('returnBorrower').textContent = d.requested_by_out;
        document.getElementById('returnPulloutDate').textContent = d.pullout_date;
        document.getElementById('returnItemCount').textContent = d.item_count;

        document.getElementById('returnRequestedBy').value = d.requested_by_out || '';
        document.getElementById('returnInspectedBy').value = d.inspected_by_out || '';
        document.getElementById('returnApprovedBy').value = d.approved_by_out || '';

        $.getJSON('function.php?action=item_details&pass_slip_no=' + encodeURIComponent(slipNo), function(data) {
            var tbody = document.getElementById('returnItemsBody');
            tbody.innerHTML = '';
            if (data && data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    tbody.innerHTML += '<tr><td style="text-align:center;">' + (i + 1) + '</td><td>' + data[i].item_description + '</td><td>' + data[i].qty + '</td><td>' + data[i].unit + '</td></tr>';
                }
            }
        });

        $.getJSON('function.php?action=inspector_list', function(list) {
            var datalist = document.getElementById('inspectorList');
            datalist.innerHTML = '';
            if (list && list.length > 0) {
                for (var j = 0; j < list.length; j++) {
                    var opt = document.createElement('option');
                    opt.value = list[j];
                    datalist.appendChild(opt);
                }
            }
        });

        $('#returnPassSlipModal').modal('show');
    }

    function confirmDeleteSlip(slipNo) {
        if (confirm('Are you sure you want to delete Pass Slip ' + slipNo + '? This will remove all items in this slip.')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_pass_slip_no';
            input.value = slipNo;
            form.appendChild(input);

            var inputBtn = document.createElement('input');
            inputBtn.type = 'hidden';
            inputBtn.name = 'btn_delete';
            inputBtn.value = '1';
            form.appendChild(inputBtn);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteSelected() {
        var checked = document.querySelectorAll('.chk_delete:checked');
        if (checked.length === 0) {
            alert('Please select at least one Pass Slip to delete.');
            return;
        }
        $('#deleteModal').modal('show');
    }

    function openPrintSlip(slipNo) {
        var frame = document.getElementById('printSlipFrame');
        // Find first ID for this slip_no to pass to print page
        var d = passSlipData[slipNo];
        if (!d) return;
        frame.src = 'print_slip.php?id=' + d.first_id;
        $('#printPassSlipModal').modal('show');
    }

    function printSlipFrame() {
        var frame = document.getElementById('printSlipFrame');
        if (frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        }
    }

    function openUploadModal(slipNo) {
        document.getElementById('uploadSlipNo').textContent = slipNo;
        document.getElementById('uploadSlipNoInput').value = slipNo;
        document.getElementById('uploadFormSlipNo').value = slipNo;
        document.getElementById('attachmentFiles').value = '';
        loadAttachmentList(slipNo);
        $('#uploadAttachmentModal').modal('show');
    }

    function loadAttachmentList(slipNo) {
        var container = document.getElementById('attachmentList');
        var toolbar = document.getElementById('attachmentToolbar');
        container.innerHTML = '<p class="text-muted">Loading...</p>';
        toolbar.style.display = 'none';

        $.getJSON('function.php?action=attachment_list&pass_slip_no=' + encodeURIComponent(slipNo), function(data) {
            container.innerHTML = '';
            if (!data || data.length === 0) {
                toolbar.style.display = 'none';
                container.innerHTML = '<p class="text-muted">No attachments uploaded yet.</p>';
                return;
            }

            toolbar.style.display = 'flex';
            document.getElementById('cbxAllAttachments').checked = false;
            document.getElementById('btnDeleteSelected').disabled = true;
            document.getElementById('selectedCount').textContent = '0';

            var html = '<div class="attachment-list">';
            for (var i = 0; i < data.length; i++) {
                var f = data[i];
                var fileUrl = '../../uploads/pass_slip/' + encodeURIComponent(f.filename);
                var ext = f.filename.split('.').pop().toLowerCase();
                var icon = 'fa-file';
                var iconColor = '#999';
                if (ext === 'pdf') { icon = 'fa-file-pdf-o'; iconColor = '#e74c3c'; }
                else if (['jpg','jpeg','png'].indexOf(ext) !== -1) { icon = 'fa-file-image-o'; iconColor = '#3498db'; }

                html += '<div class="attachment-card" onclick="toggleAttachCheck(this, event)">';
                html += '<input type="checkbox" class="chk_attachment" value="' + f.id + '" onchange="updateDeleteBtn(); this.closest(\'.attachment-card\').classList.toggle(\'selected\', this.checked)">';
                html += '<div class="attachment-card-icon"><i class="fa ' + icon + '" style="font-size:28px; color:' + iconColor + ';"></i></div>';
                html += '<div class="attachment-card-body">';
                html += '<div class="attachment-card-filename">' + f.filename + '</div>';
                html += '<div class="attachment-card-meta">' + (f.uploaded_by || '-') + ' &middot; ' + f.uploaded_at + '</div>';
                html += '</div>';
                html += '<button type="button" class="btn btn-info btn-xs" onclick="event.stopPropagation(); previewAttachment(\'' + fileUrl + '\', \'' + f.filename.replace(/'/g, "\\'") + '\')" title="View File"><i class="fa fa-eye"></i></button>';
                html += '</div>';
            }
            html += '</div>';
            container.innerHTML = html;
        });
    }

    function toggleAttachCheck(card, e) {
        if (e.target.tagName === 'INPUT') return;
        var cbx = card.querySelector('.chk_attachment');
        if (cbx) {
            cbx.checked = !cbx.checked;
            card.classList.toggle('selected', cbx.checked);
            updateDeleteBtn();
        }
    }

    function toggleAllAttachments(master) {
        var boxes = document.querySelectorAll('.chk_attachment');
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].checked = master.checked;
            boxes[i].closest('.attachment-card').classList.toggle('selected', master.checked);
        }
        updateDeleteBtn();
    }

    function updateDeleteBtn() {
        var checked = document.querySelectorAll('.chk_attachment:checked');
        var btn = document.getElementById('btnDeleteSelected');
        var countSpan = document.getElementById('selectedCount');
        btn.disabled = checked.length === 0;
        countSpan.textContent = checked.length;
    }

    function deleteSelectedAttachments() {
        var checked = document.querySelectorAll('.chk_attachment:checked');
        if (checked.length === 0) return;
        if (!confirm('Are you sure you want to delete ' + checked.length + ' selected attachment(s)?')) return;

        var ids = [];
        for (var i = 0; i < checked.length; i++) {
            ids.push(checked[i].value);
        }

        var slipNo = document.getElementById('uploadFormSlipNo').value;

        $.post('function.php', { action: 'delete_attachments', ids: ids }, function(resp) {
            if (resp.success) {
                loadAttachmentList(slipNo);
            } else {
                alert(resp.message || 'Failed to delete attachments.');
            }
        }, 'json');
    }

    function previewAttachment(fileUrl, fileName) {
        var ext = fileName.split('.').pop().toLowerCase();
        var pdfFrame = document.getElementById('previewPdfFrame');
        var imgTag = document.getElementById('previewImgTag');
        var spinner = document.getElementById('previewSpinner');
        document.getElementById('previewFileName').textContent = fileName;

        pdfFrame.style.display = 'none';
        pdfFrame.src = '';
        imgTag.style.display = 'none';
        imgTag.src = '';
        spinner.style.display = 'block';

        if (ext === 'pdf') {
            pdfFrame.onload = function() {
                spinner.style.display = 'none';
                pdfFrame.style.display = 'block';
                pdfFrame.onload = null;
            };
            pdfFrame.src = fileUrl;
        } else {
            var preload = new Image();
            preload.onload = function() {
                imgTag.src = fileUrl;
                spinner.style.display = 'none';
                imgTag.style.display = 'block';
            };
            preload.onerror = function() {
                spinner.innerHTML = '<i class="fa fa-exclamation-triangle" style="font-size:36px; color:#e74c3c;"></i><p style="margin-top:10px; color:#e74c3c;">Failed to load file.</p>';
            };
            preload.src = fileUrl;
        }

        $('#previewAttachmentModal').modal('show');
    }

    function printPreview() {
        var pdfFrame = document.getElementById('previewPdfFrame');
        var imgTag = document.getElementById('previewImgTag');

        if (pdfFrame.style.display !== 'none' && pdfFrame.contentWindow) {
            pdfFrame.contentWindow.focus();
            pdfFrame.contentWindow.print();
        } else if (imgTag.style.display !== 'none' && imgTag.src) {
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print</title><style>body{margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;} img{max-width:100%;max-height:100vh;}</style></head><body>');
            printWindow.document.write('<img src="' + imgTag.src + '" onload="window.print();window.close();">');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
    }

    $(document).ready(function() {
        $('#uploadAttachmentForm').on('submit', function(e) {
            e.preventDefault();
            var files = document.getElementById('attachmentFiles').files;
            if (files.length === 0) {
                alert('Please select at least one file to upload.');
                return;
            }

            var formData = new FormData(this);
            var slipNo = document.getElementById('uploadFormSlipNo').value;

            $('#uploadBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

            $.ajax({
                url: 'function.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(resp) {
                    $('#uploadBtn').prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Upload');
                    console.log('Upload Response:', resp);
                    if (resp.success) {
                        alert(resp.uploaded + ' file(s) uploaded successfully!');
                        document.getElementById('attachmentFiles').value = '';
                        loadAttachmentList(slipNo);
                    } else {
                        var msg = resp.errors && resp.errors.length > 0 ? resp.errors.join('\n') : (resp.message || 'Upload failed.');
                        console.error('Upload Errors:', resp.errors || resp.message);
                        alert(msg);
                    }
                },
                error: function(xhr, status, error) {
                    $('#uploadBtn').prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Upload');
                    console.error('Upload Error:', status, error);
                    if (xhr.responseText) {
                        console.error('Server Response:', xhr.responseText.substring(0, 1000));
                    }
                    alert('An error occurred during upload. Check the browser console for details.');
                }
        });

        $('#previewAttachmentModal').on('hidden.bs.modal', function() {
            document.getElementById('previewPdfFrame').src = '';
            document.getElementById('previewImgTag').src = '';
            document.getElementById('previewSpinner').style.display = 'block';
        });
    });
    });

    $(function() {
        $("#passSlipTable").dataTable({
            "aoColumnDefs": [{ "bSortable": false, "aTargets": [0, 8] }],
            "aaSorting": []
        });
    });

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
    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

<style>
    .info-box-icon {
        background-color: white;
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4);
        border-radius: 5px;
        padding: 10px;
    }
    .info-box-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 80px;
        width: 80px;
        font-size: 40px;
    }
    table {
        table-layout: auto;
        width: 100%;
    }
    table th {
        white-space: nowrap;
        text-align: center;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 200px;
    }
    table td {
        white-space: nowrap;
        text-align: center;
    }
    table th, table td {
        padding: 8px;
        border: 1px solid #ddd;
    }
    .header-date-time {
        font-size: 16px;
        color: #555;
        margin-left: auto;
    }
    .attachment-list {
        max-height: 250px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .attachment-card {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fafafa;
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .attachment-card:hover {
        border-color: #e67e22;
        background: #fff8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .attachment-card.selected {
        border-color: #e67e22;
        background: #fff3e6;
    }
    .attachment-card .chk_attachment {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    .attachment-card-icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f0f0;
        border-radius: 6px;
    }
    .attachment-card-body {
        flex: 1;
        min-width: 0;
    }
    .attachment-card-filename {
        font-weight: 600;
        font-size: 12px;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .attachment-card-meta {
        font-size: 11px;
        color: #888;
        margin-top: 2px;
    }
</style>
</style>

</body>
</html>
