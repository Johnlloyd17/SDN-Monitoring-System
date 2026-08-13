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
    <?php include '../connection.php'; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

       <aside class="right-side">
    <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Procurement</h1>
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
                                <i class="fa fa-bar-chart"></i> Procurement Statistics
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php
                                    $qTotal = mysqli_query($con, "SELECT COUNT(*) AS total FROM procurement_tracking");
                                    $rTotal = mysqli_fetch_assoc($qTotal);

                                    $qTotalAmount = mysqli_query($con, "SELECT SUM(amount) AS total FROM procurement_tracking");
                                    $rTotalAmount = mysqli_fetch_assoc($qTotalAmount);

                                    $qPaid = mysqli_query($con, "SELECT COUNT(*) AS total FROM procurement_tracking WHERE payment_status = 'Paid'");
                                    $rPaid = mysqli_fetch_assoc($qPaid);

                                    $qPending = mysqli_query($con, "SELECT COUNT(*) AS total FROM procurement_tracking WHERE payment_status = 'Pending' OR payment_status = ''");
                                    $rPending = mysqli_fetch_assoc($qPending);
                                    ?>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Records</span>
                                                <span class="info-box-number"><?php echo $rTotal['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-green"><i class="fa fa-php"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Amount</span>
                                                <span class="info-box-number">PHP <?php echo number_format($rTotalAmount['total'] ?? 0, 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-yellow"><i class="fa fa-check-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Paid</span>
                                                <span class="info-box-number"><?php echo $rPaid['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-red"><i class="fa fa-clock-o"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Pending</span>
                                                <span class="info-box-number"><?php echo $rPending['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Statistics Cards -->

                        <div class="panel panel-default">
                            <div class="panel-heading">Procurement Records</div>
                            <div class="panel-body">
                                <!-- AJAX Filters -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="projectFundSourceSelect">Project Fund Source</label>
                                            <select id="projectFundSourceSelect" class="form-control">
                                                <option value="">All Fund Sources</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="paymentStatusSelect">Payment Status</label>
                                            <select id="paymentStatusSelect" class="form-control">
                                                <option value="">All Status</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="yearSelect">Year Forwarded to RO</label>
                                            <select id="yearSelect" class="form-control">
                                                <option value="">All Years</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Toolbar -->
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <?php if ($_SESSION['role'] !== 'staff') { ?>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-plus"></i> Add Record</button>
                                            <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete Selected</button>
                                        <?php } ?>
                                        <label style="margin:0; font-weight:normal;">Show </label>
                                        <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                                            <option value="5" selected>5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label style="margin:0; font-weight:normal;"> entries</label>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <div class="input-group" style="width:300px;">
                                            <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search procurement_tracking..." />
                                            <span class="input-group-btn">
                                                <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                                <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            <div class="box-body table-responsive">
                                <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 20px !important;"><input type="checkbox" id="cbxMain" /></th>
                                            <th>No.</th>
                                            <th>PR No.</th>
                                            <th>Activity ID</th>
                                            <th>Activity Name</th>
                                            <th>Link to File</th>
                                            <th>Project Fund Source</th>
                                            <th>Type of Items</th>
                                            <th>Amount</th>
                                            <th>Supplier</th>
                                            <th>JO/PO</th>
                                            <th>Link to Attachments</th>
                                            <th>Personnel In-charge</th>
                                            <th>Date Forwarded to RO</th>
                                            <th>Transmittal Report</th>
                                            <th>Payment Status</th>
                                            <th>Remarks</th>
                                            <th style="width: 80px !important;">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr><td colspan="18" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>

                                <!-- Bottom bar -->
                                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                                    <div id="paginationInfo" class="text-muted"></div>
                                    <ul class="pagination" style="margin:0;" id="pagination"></ul>
                                </div>
                            </div>
                        </div>

                        <?php include "../edit_notif.php"; ?>
                        <?php include "../added_notif.php"; ?>
                        <?php include "../delete_notif.php"; ?>
                        <?php include "../duplicate_error.php"; ?>

                    </div>
                </section>
            </aside>
        </div>

        <!-- ========================= ADD MODAL ======================= -->
        <div id="addModal" class="modal fade">
            <form id="addForm">
                <div class="modal-dialog modal-lg" style="width:750px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Add Procurement Record</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="addAlert" style="display:none;"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>PR No.:</label><input name="txt_pr_no" class="form-control input-sm" type="text" placeholder="PR No." /></div>
                                    <div class="form-group"><label>Activity ID:</label><input name="txt_activity_id" class="form-control input-sm" type="text" placeholder="Activity ID" /></div>
                                    <div class="form-group"><label>Activity Name:</label><input name="txt_activity_name" class="form-control input-sm" type="text" placeholder="Activity Name" /></div>
                                    <div class="form-group"><label>Link to File:</label><input name="txt_link_to_file" class="form-control input-sm" type="text" placeholder="Link to File" /></div>
                                    <div class="form-group"><label>Project Fund Source:</label><input name="txt_project_fund_source" class="form-control input-sm" type="text" placeholder="Project Fund Source" /></div>
                                    <div class="form-group"><label>Type of Items Procured:</label><input name="txt_type_of_items_procured" class="form-control input-sm" type="text" placeholder="Type of Items Procured" /></div>
                                    <div class="form-group"><label>Amount:</label><input name="txt_amount" class="form-control input-sm" type="text" placeholder="e.g. 150,000.00" /></div>
                                    <div class="form-group"><label>Name of Supplier:</label><input name="txt_name_of_supplier" class="form-control input-sm" type="text" placeholder="Name of Supplier" /></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>JO/PO:</label><input name="txt_jo_po" class="form-control input-sm" type="text" placeholder="JO/PO" /></div>
                                    <div class="form-group"><label>Link to Attachments:</label><input name="txt_link_to_attachments" class="form-control input-sm" type="text" placeholder="Link to Attachments (AD, Canvass, Abstract)" /></div>
                                    <div class="form-group"><label>Personnel In-charge:</label><input name="txt_personnel_in_charge" class="form-control input-sm" type="text" placeholder="Personnel In-charge" /></div>
                                    <div class="form-group"><label>Date Forwarded to RO:</label><input name="txt_date_forwarded_to_ro" class="form-control input-sm" type="date" /></div>
                                    <div class="form-group"><label>Transmittal Report:</label><input name="txt_transmittal_report" class="form-control input-sm" type="text" placeholder="Transmittal Report" /></div>
                                    <div class="form-group">
                                        <label>Payment Status:</label>
                                        <select name="txt_payment_status" class="form-control input-sm">
                                            <option value="">-- Select Status --</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Partial">Partial</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>Remarks:</label><textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks"></textarea></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel" />
                            <input type="submit" class="btn btn-primary btn-sm" value="Add Record" id="addSubmitBtn" />
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================= EDIT MODAL ======================= -->
        <div id="editModal" class="modal fade">
            <form id="editForm">
                <div class="modal-dialog modal-lg" style="width:750px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Edit Procurement Record</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="editAlert" style="display:none;"></div>
                            <input type="hidden" name="hidden_id" id="edit_hidden_id" />
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>PR No.:</label><input type="text" name="txt_edit_pr_no" id="edit_pr_no" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Activity ID:</label><input type="text" name="txt_edit_activity_id" id="edit_activity_id" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Activity Name:</label><input type="text" name="txt_edit_activity_name" id="edit_activity_name" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Link to File:</label><input type="text" name="txt_edit_link_to_file" id="edit_link_to_file" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Project Fund Source:</label><input type="text" name="txt_edit_project_fund_source" id="edit_project_fund_source" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Type of Items Procured:</label><input type="text" name="txt_edit_type_of_items_procured" id="edit_type_of_items_procured" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Amount:</label><input type="text" name="txt_edit_amount" id="edit_amount" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Name of Supplier:</label><input type="text" name="txt_edit_name_of_supplier" id="edit_name_of_supplier" class="form-control input-sm" /></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>JO/PO:</label><input type="text" name="txt_edit_jo_po" id="edit_jo_po" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Link to Attachments:</label><input type="text" name="txt_edit_link_to_attachments" id="edit_link_to_attachments" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Personnel In-charge:</label><input type="text" name="txt_edit_personnel_in_charge" id="edit_personnel_in_charge" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Date Forwarded to RO:</label><input type="date" name="txt_edit_date_forwarded_to_ro" id="edit_date_forwarded_to_ro" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Transmittal Report:</label><input type="text" name="txt_edit_transmittal_report" id="edit_transmittal_report" class="form-control input-sm" /></div>
                                    <div class="form-group">
                                        <label>Payment Status:</label>
                                        <select name="txt_edit_payment_status" id="edit_payment_status" class="form-control input-sm">
                                            <option value="">-- Select Status --</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Partial">Partial</option>
                                            <option value="Cancelled">Cancelled</option>
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

        <!-- ========================= DELETE CONFIRMATION MODAL ======================= -->
        <div id="deleteModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Delete Confirmation</h4>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the selected record(s)?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary btn-sm" id="confirmDeleteBtn">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div id="ajaxToast" class="alert" style="position:fixed; top:1em; right:1em; z-index:9999; display:none; min-width:250px;"></div>

        <?php include '../footer.php'; ?>

        <style>
            .info-box-icon {
                background-color: white;
                box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4);
                border-radius: 5px;
                padding: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 80px;
                width: 80px;
                font-size: 40px;
            }
            table { table-layout: auto; width: 100%; }
            table th { white-space: normal; text-align: center; word-wrap: break-word; overflow-wrap: break-word; max-width: 200px; }
            table td { white-space: nowrap; text-align: center; vertical-align: middle; }
            table th, table td { padding: 8px; border: 1px solid #ddd; }
            .header-date-time { font-size: 16px; color: #555; margin-left: auto; }
        </style>

        <script>
        (function() {
            var basePath = '../../ajax/';
            var currentPage = 1;
            var perPage = 5;
            var totalPages = 1;
            var searchTimeout = null;

            function showToast(msg, type) {
                var toast = document.getElementById('ajaxToast');
                toast.className = 'alert alert-' + type;
                toast.innerHTML = msg;
                toast.style.display = 'block';
                setTimeout(function() { toast.style.display = 'none'; }, 3000);
            }

            function getFilters() {
                return {
                    project_fund_source: document.getElementById('projectFundSourceSelect').value,
                    payment_status: document.getElementById('paymentStatusSelect').value,
                    year: document.getElementById('yearSelect').value,
                    search: document.getElementById('searchInput').value
                };
            }

            function loadFilters() {
                var f = getFilters();
                var params = 'project_fund_source=' + encodeURIComponent(f.project_fund_source) +
                             '&payment_status=' + encodeURIComponent(f.payment_status);
                $.getJSON(basePath + 'procurement_tracking_crud.php?action=filters&' + params, function(data) {
                    var p = document.getElementById('projectFundSourceSelect');
                    var ps = document.getElementById('paymentStatusSelect');
                    var y = document.getElementById('yearSelect');

                    var pv = p.value, psv = ps.value, yv = y.value;

                    p.innerHTML = '<option value="">All Fund Sources</option>';
                    data.project_fund_sources.forEach(function(v) { p.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    p.value = pv;

                    ps.innerHTML = '<option value="">All Status</option>';
                    data.payment_statuses.forEach(function(v) { ps.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    ps.value = psv;

                    y.innerHTML = '<option value="">All Years</option>';
                    data.years.forEach(function(v) { if(v) y.innerHTML += '<option value="' + v + '">' + v + '</option>'; });
                    y.value = yv;
                });
            }

            function loadData(page) {
                currentPage = page || 1;
                var f = getFilters();
                var params = 'page=' + currentPage + '&per_page=' + perPage +
                             '&search=' + encodeURIComponent(f.search) +
                             '&project_fund_source=' + encodeURIComponent(f.project_fund_source) +
                             '&payment_status=' + encodeURIComponent(f.payment_status) +
                             '&year=' + encodeURIComponent(f.year);

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="18" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.getJSON(basePath + 'procurement_tracking_data.php?' + params, function(res) {
                    totalPages = res.total_pages;
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                    updateDeleteBtn();
                }).fail(function() {
                    tbody.innerHTML = '<tr><td colspan="18" class="text-center text-danger">Failed to load data.</td></tr>';
                });
            }

            function renderTable(rows) {
                var tbody = document.getElementById('tableBody');
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="18" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    var id = parseInt(row.id);
                    var statusBadge = '';
                    if (row.payment_status === 'Paid') {
                        statusBadge = '<span class="label label-success">Paid</span>';
                    } else if (row.payment_status === 'Pending') {
                        statusBadge = '<span class="label label-warning">Pending</span>';
                    } else if (row.payment_status === 'Partial') {
                        statusBadge = '<span class="label label-info">Partial</span>';
                    } else if (row.payment_status === 'Cancelled') {
                        statusBadge = '<span class="label label-danger">Cancelled</span>';
                    } else {
                        statusBadge = escHtml(row.payment_status);
                    }

                    var amt = row.amount ? parseFloat(row.amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';

                    html += '<tr>' +
                        '<td><input type="checkbox" class="chk_delete" data-id="' + id + '" /></td>' +
                        '<td>' + row.row_num + '</td>' +
                        '<td>' + escHtml(row.pr_no) + '</td>' +
                        '<td>' + escHtml(row.activity_id) + '</td>' +
                        '<td>' + escHtml(row.activity_name) + '</td>' +
                        '<td>' + (row.link_to_file ? '<a href="' + escHtml(row.link_to_file) + '" target="_blank"><i class="fa fa-link"></i></a>' : '-') + '</td>' +
                        '<td>' + escHtml(row.project_fund_source) + '</td>' +
                        '<td>' + escHtml(row.type_of_items_procured) + '</td>' +
                        '<td>' + amt + '</td>' +
                        '<td>' + escHtml(row.name_of_supplier) + '</td>' +
                        '<td>' + escHtml(row.jo_po) + '</td>' +
                        '<td>' + (row.link_to_attachments ? '<a href="' + escHtml(row.link_to_attachments) + '" target="_blank"><i class="fa fa-paperclip"></i></a>' : '-') + '</td>' +
                        '<td>' + escHtml(row.personnel_in_charge) + '</td>' +
                        '<td>' + escHtml(row.date_forwarded_to_ro) + '</td>' +
                        '<td>' + escHtml(row.transmittal_report) + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td>' + escHtml(row.remarks) + '</td>' +
                        '<td class="option-buttons">' +
                            '<div style="display:flex;gap:5px;flex-wrap:wrap;">' +
                            '<button class="btn btn-primary btn-xs editBtn" data-id="' + id + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                            '<button class="btn btn-danger btn-xs deleteSingleBtn" data-id="' + id + '" title="Delete"><i class="fa fa-trash"></i></button>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';
                });
                tbody.innerHTML = html;
            }

            function renderPagination(page, total, count) {
                var pag = document.getElementById('pagination');
                var info = document.getElementById('paginationInfo');

                var start = count > 0 ? (page - 1) * perPage + 1 : 0;
                var end = Math.min(page * perPage, count);
                info.textContent = 'Showing ' + start + ' to ' + end + ' of ' + count + ' entries';

                if (total <= 1) { pag.innerHTML = ''; return; }

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
                var checked = document.querySelectorAll('.chk_delete:checked').length;
                var btn = document.getElementById('deleteSelectedBtn');
                if (btn) btn.disabled = checked === 0;
            }

            function escHtml(str) {
                if (str === null || str === undefined) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(String(str)));
                return div.innerHTML;
            }

            function getSelectedIds() {
                var ids = [];
                document.querySelectorAll('.chk_delete:checked').forEach(function(cb) {
                    ids.push(cb.getAttribute('data-id'));
                });
                return ids;
            }

            // Filter change handlers
            $('#projectFundSourceSelect, #paymentStatusSelect, #yearSelect').on('change', function() {
                loadData(1);
                loadFilters();
            });

            // Per-page selector
            $('#perPageSelect').on('change', function() {
                perPage = parseInt(this.value);
                loadData(1);
            });

            // Search
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() { loadData(1); }, 400);
            });
            $('#searchBtn').on('click', function() { loadData(1); });
            $('#clearSearchBtn').on('click', function() {
                document.getElementById('searchInput').value = '';
                loadData(1);
            });

            // Pagination clicks
            $('#pagination').on('click', 'a[data-page]', function(e) {
                e.preventDefault();
                var pg = parseInt($(this).attr('data-page'));
                if (pg >= 1 && pg <= totalPages) loadData(pg);
            });

            // Select all checkbox
            $('#cbxMain').on('change', function() {
                var checked = this.checked;
                document.querySelectorAll('.chk_delete').forEach(function(cb) { cb.checked = checked; });
                updateDeleteBtn();
            });
            $(document).on('change', '.chk_delete', function() {
                var all = document.querySelectorAll('.chk_delete').length;
                var checked = document.querySelectorAll('.chk_delete:checked').length;
                document.getElementById('cbxMain').checked = (all > 0 && all === checked);
                updateDeleteBtn();
            });

            // ========== ADD RECORD ==========
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('addSubmitBtn');
                btn.disabled = true;
                btn.value = 'Adding...';

                $.ajax({
                    url: basePath + 'procurement_tracking_crud.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=add',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#addModal').modal('hide');
                            showToast('Record added successfully!', 'success');
                            loadData(currentPage);
                            loadFilters();
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
                        btn.value = 'Add Record';
                    }
                });
            });

            // ========== EDIT RECORD ==========
            $(document).on('click', '.editBtn', function() {
                var id = $(this).attr('data-id');
                $.getJSON(basePath + 'procurement_tracking_crud.php?action=get_item&id=' + id, function(item) {
                    $('#edit_hidden_id').val(item.id);
                    $('#edit_pr_no').val(item.pr_no);
                    $('#edit_activity_id').val(item.activity_id);
                    $('#edit_activity_name').val(item.activity_name);
                    $('#edit_link_to_file').val(item.link_to_file);
                    $('#edit_project_fund_source').val(item.project_fund_source);
                    $('#edit_type_of_items_procured').val(item.type_of_items_procured);
                    $('#edit_amount').val(item.amount);
                    $('#edit_name_of_supplier').val(item.name_of_supplier);
                    $('#edit_jo_po').val(item.jo_po);
                    $('#edit_link_to_attachments').val(item.link_to_attachments);
                    $('#edit_personnel_in_charge').val(item.personnel_in_charge);
                    $('#edit_date_forwarded_to_ro').val(item.date_forwarded_to_ro);
                    $('#edit_transmittal_report').val(item.transmittal_report);
                    $('#edit_payment_status').val(item.payment_status);
                    $('#edit_remarks').val(item.remarks);
                    $('#editModal').modal('show');
                }).fail(function() {
                    showToast('Failed to load record data.', 'danger');
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('editSubmitBtn');
                btn.disabled = true;
                btn.value = 'Saving...';

                $.ajax({
                    url: basePath + 'procurement_tracking_crud.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=edit',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#editModal').modal('hide');
                            showToast('Record updated successfully!', 'success');
                            loadData(currentPage);
                            loadFilters();
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

            // ========== DELETE ==========
            $(document).on('click', '.deleteSingleBtn', function() {
                var id = $(this).attr('data-id');
                document.getElementById('confirmDeleteBtn').setAttribute('data-ids', JSON.stringify([id]));
                $('#deleteModal').modal('show');
            });

            $('#deleteSelectedBtn').on('click', function() {
                var ids = getSelectedIds();
                if (ids.length === 0) return;
                document.getElementById('confirmDeleteBtn').setAttribute('data-ids', JSON.stringify(ids));
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                var ids = JSON.parse(this.getAttribute('data-ids') || '[]');
                $.ajax({
                    url: basePath + 'procurement_tracking_crud.php',
                    type: 'POST',
                    data: { action: 'delete', ids: ids },
                    dataType: 'json',
                    success: function(res) {
                        $('#deleteModal').modal('hide');
                        if (res.success) {
                            showToast(res.message, 'success');
                            loadData(currentPage);
                            loadFilters();
                        } else {
                            showToast('Delete failed.', 'danger');
                        }
                    }
                });
            });

            // ========== INIT ==========
            loadFilters();
            loadData(1);

            // Date time
            function updateDateTime() {
                var now = new Date();
                var options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                var el = document.getElementById('dateTime');
                if (el) el.textContent = now.toLocaleDateString('en-US', options);
            }
            updateDateTime();
            setInterval(updateDateTime, 1000);

        })();
        </script>

<?php
    ob_end_flush();
}
?>
