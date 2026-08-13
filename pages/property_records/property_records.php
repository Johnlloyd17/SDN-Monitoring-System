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
        <h1 style="margin: 0;">Inventory Records</h1>
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
                                <i class="fa fa-bar-chart"></i> Inventory Statistics
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <?php
                                    $qTotal = mysqli_query($con, "SELECT COUNT(*) AS total FROM inventory WHERE project != ''");
                                    $rTotal = mysqli_fetch_assoc($qTotal);

                                    $passSlipExists = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0") ? true : false;
                                    $itemTypeExists = @mysqli_query($con, "SELECT item_type FROM inventory LIMIT 0") ? true : false;

                                    if ($passSlipExists) {
                                        $qBorrowed = mysqli_query($con, "SELECT COUNT(*) AS total FROM pass_slip WHERE status = 'borrowed'");
                                        $rBorrowed = mysqli_fetch_assoc($qBorrowed);
                                        $qOverdue = mysqli_query($con, "SELECT COUNT(*) AS total FROM pass_slip WHERE status = 'borrowed' AND return_date < CURDATE()");
                                        $rOverdue = mysqli_fetch_assoc($qOverdue);
                                    } else {
                                        $rBorrowed = ['total' => 0];
                                        $rOverdue = ['total' => 0];
                                    }

                                    $qValue = mysqli_query($con, "SELECT SUM(CAST(REPLACE(cost, ',', '') AS DECIMAL(15,2)) * quantity) AS total FROM inventory WHERE cost IS NOT NULL AND cost != '' AND project != ''");
                                    $rValue = mysqli_fetch_assoc($qValue);

                                    if ($itemTypeExists) {
                                        $qConsume = mysqli_query($con, "SELECT COUNT(*) AS total FROM inventory WHERE item_type = 'consumable' AND project != ''");
                                        $rConsume = mysqli_fetch_assoc($qConsume);
                                        $qNonConsume = mysqli_query($con, "SELECT COUNT(*) AS total FROM inventory WHERE item_type = 'equipment' AND project != ''");
                                        $rNonConsume = mysqli_fetch_assoc($qNonConsume);
                                    } else {
                                        $rConsume = ['total' => 0];
                                        $rNonConsume = ['total' => 0];
                                    }
                                    ?>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="#" style="text-decoration:none;">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-aqua"><i class="fa fa-boxes"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Items</span>
                                                    <span class="info-box-number"><?php echo $rTotal['total']; ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="#" style="text-decoration:none;">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-green"><i class="fa fa-php"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Asset Value</span>
                                                    <span class="info-box-number">php <?php echo number_format($rValue['total'], 2); ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="../pass_slip/pass_slip.php" style="text-decoration:none;">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-yellow"><i class="fa fa-hand-holding"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Currently Borrowed</span>
                                                    <span class="info-box-number"><?php echo $rBorrowed['total']; ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="../pass_slip/pass_slip.php" style="text-decoration:none;">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Overdue Returns</span>
                                                    <span class="info-box-number"><?php echo $rOverdue['total']; ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="#" style="text-decoration:none;">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-blue"><i class="fa fa-undo"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Non-Consumable</span>
                                                    <span class="info-box-number"><?php echo $rNonConsume['total']; ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <a href="#" style="text-decoration:none;">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-orange"><i class="fa fa-scroll"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Consumable Supplies</span>
                                                    <span class="info-box-number"><?php echo $rConsume['total']; ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Statistics Cards -->

                        <div class="panel panel-default">
                            <div class="panel-heading">Inventory Records</div>
                            <div class="panel-body">
                                <!-- AJAX Filters -->
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="projectSelect">Select project</label>
                                            <select id="projectSelect" class="form-control">
                                                <option value="">All projects</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="icsSelect">Select ICS</label>
                                            <select id="icsSelect" class="form-control">
                                                <option value="">All ICS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="yearSelect">Select Year</label>
                                            <select id="yearSelect" class="form-control">
                                                <option value="">All Years</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="remarksSelect">Select Remarks</label>
                                            <select id="remarksSelect" class="form-control">
                                                <option value="">All Remarks</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Toolbar: Add + Delete + Row Filter (left) | Search + Import + Export (right) -->
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <?php if ($_SESSION['role'] !== 'staff') { ?>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus"></i> Add Record</button>
                                            <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete Selected</button>
                                        <?php } ?>
                                        <label style="margin:0; font-weight:normal;">Show </label>
                                        <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                                            <option value="5" selected>5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
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
                                            <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search inventory..." />
                                            <span class="input-group-btn">
                                                <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                                <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                            </span>
                                        </div>
                                        <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Import</button>
                                        <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
                                        <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> Export</button>
                                    </div>
                                </div>

                            <div class="box-body table-responsive">
                                <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 20px !important;"><input type="checkbox" id="cbxMain" /></th>
                                            <th>No.</th>
                                            <th>Project</th>
                                            <th>Item No.</th>
                                            <th>Classification</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Description/Model</th>
                                            <th>Received From</th>
                                            <th>Property Number</th>
                                            <th>ICS/PAR Number</th>
                                            <th>Serial Number</th>
                                            <th>Date Acquired</th>
                                            <th>Accountable Officer</th>
                                            <th>Unit Cost</th>
                                            <th>Estimated Useful Life</th>
                                            <th>Received/Transferred</th>
                                            <th>Remarks</th>
                                            <th style="width: 80px !important;">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr><td colspan="20" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>

                                <!-- Bottom bar: Info text (left) + Pagination (right) -->
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
            <form id="addForm" enctype="multipart/form-data">
                <div class="modal-dialog modal-lg" style="width:750px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Add Item</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="addAlert" style="display:none;"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>Project:</label><input name="txt_project" class="form-control input-sm" type="text" placeholder="Project" /></div>
                                    <div class="form-group"><label>Item No.:</label><input name="txt_item" class="form-control input-sm" type="text" placeholder="Item No." /></div>
                                    <div class="form-group">
                                        <label>Classification:</label>
                                        <select name="txt_classification" class="form-control input-sm select2" style="width:100%;">
                                            <option value="">-- Select Classification --</option>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>Quantity:</label><input name="txt_quantity" class="form-control input-sm" type="number" placeholder="Quantity" /></div>
                                    <div class="form-group"><label>Unit:</label><input name="txt_unit" class="form-control input-sm" type="text" placeholder="Unit" /></div>
                                    <div class="form-group"><label>Description/Model:</label><input name="txt_description" class="form-control input-sm" type="text" placeholder="Description/Model" /></div>
                                    <div class="form-group"><label>Received From:</label><input name="txt_received" class="form-control input-sm" type="text" placeholder="Received From" /></div>
                                    <div class="form-group"><label>Property Number:</label><input name="txt_property" class="form-control input-sm" type="text" placeholder="Property Number" /></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>ICS/PAR Number:</label><input name="txt_ics" class="form-control input-sm" type="text" placeholder="ICS/PAR Number" /></div>
                                    <div class="form-group"><label>Serial Number:</label><input name="txt_serial" class="form-control input-sm" type="text" placeholder="Serial Number" /></div>
                                    <div class="form-group"><label>Date Acquired:</label><input name="txt_date" class="form-control input-sm" type="date" placeholder="Date Acquired" /></div>
                                    <div class="form-group"><label>Accountable Officer:</label><input name="txt_officer" class="form-control input-sm" type="text" placeholder="Accountable Officer" /></div>
                                    <div class="form-group"><label>Unit Cost:</label><input name="txt_cost" class="form-control input-sm" type="text" placeholder="e.g. 43,904.00" /></div>
                                    <div class="form-group"><label>Estimated Useful Life:</label><input name="txt_life" class="form-control input-sm" type="number" placeholder="Estimated Useful Life" /></div>
                                    <div class="form-group"><label>Received/Transferred:</label><input name="txt_transferred" class="form-control input-sm" type="text" placeholder="Received/Transferred" /></div>
                                    <div class="form-group"><label>Remarks:</label><textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks"></textarea></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel" />
                            <input type="submit" class="btn btn-primary btn-sm" value="Add Item" id="addSubmitBtn" />
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================= EDIT MODAL (Single Dynamic) ======================= -->
        <div id="editModal" class="modal fade">
            <form id="editForm">
                <div class="modal-dialog modal-lg" style="width:750px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">Edit Item</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="editAlert" style="display:none;"></div>
                            <input type="hidden" name="hidden_id" id="edit_hidden_id" />
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>Project:</label><input type="text" name="txt_edit_project" id="edit_project" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Item No.:</label><input type="text" name="txt_edit_item" id="edit_item" class="form-control input-sm" /></div>
                                    <div class="form-group">
                                        <label>Classification:</label>
                                        <select name="txt_edit_classification" id="edit_classification" class="form-control input-sm select2" style="width:100%;">
                                            <option value="">-- Select Classification --</option>
                                        </select>
                                    </div>
                                    <div class="form-group"><label>Quantity:</label><input type="number" name="txt_edit_quantity" id="edit_quantity" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Unit:</label><input type="text" name="txt_edit_unit" id="edit_unit" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Description/Model:</label><input type="text" name="txt_edit_description" id="edit_description" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Received From:</label><input type="text" name="txt_edit_received" id="edit_received" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Property Number:</label><input type="text" name="txt_edit_property" id="edit_property" class="form-control input-sm" /></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>ICS/PAR Number:</label><input type="text" name="txt_edit_ics" id="edit_ics" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Serial Number:</label><input type="text" name="txt_edit_serial" id="edit_serial" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Date Acquired:</label><input type="date" name="txt_edit_date" id="edit_date" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Accountable Officer:</label><input type="text" name="txt_edit_officer" id="edit_officer" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Unit Cost:</label><input type="text" name="txt_edit_cost" id="edit_cost" class="form-control input-sm" placeholder="e.g. 43,904.00" /></div>
                                    <div class="form-group"><label>Estimated Useful Life:</label><input type="text" name="txt_edit_life" id="edit_life" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Received/Transferred:</label><input type="text" name="txt_edit_transferred" id="edit_transferred" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Remarks:</label><input type="text" name="txt_edit_remarks" id="edit_remarks" class="form-control input-sm" /></div>
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

        <!-- ========================= VIEW/PHOTOS MODAL (Single Dynamic) ======================= -->
        <div id="viewModal" class="modal fade" role="dialog">
            <form id="photoForm" enctype="multipart/form-data">
                <div class="modal-dialog modal-lg" style="width:850px !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">View Files for: <span id="viewItemName"></span></h4>
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

        <!-- ========================= PREVIEW FILE MODAL ======================= -->
        <div id="previewFileModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1100px;">
                <div class="modal-content">
                    <div class="modal-header" style="background: #1b3a6b; color: #fff;">
                        <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-file-image-o"></i> <span id="previewFileName">Preview</span></h4>
                    </div>
                    <div class="modal-body" style="padding: 0; height: 700px; background: #e9e9e9; display: flex; align-items: center; justify-content: center;">
                        <div id="previewSpinner" style="text-align:center;">
                            <i class="fa fa-spinner fa-spin" style="font-size:36px; color:#888;"></i>
                            <p style="margin-top:10px; color:#888;">Loading preview...</p>
                        </div>
                        <iframe id="previewPdfFrame" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                        <img id="previewImgTag" src="" style="max-width: 100%; max-height: 100%; display: none;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="printPreviewFile()"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
            </div>
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
                        <p>Are you sure you want to delete the selected item(s)?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary btn-sm" id="confirmDeleteBtn">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pass Slip History Modal -->
        <div class="modal fade" id="passSlipHistoryModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Pass Slip History - <span id="passSlipHistoryItemName"></span></h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Pass Slip No.</th>
                                    <th>Pull-Out Date</th>
                                    <th>Return Date</th>
                                    <th>Requested By</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Print</th>
                                </tr>
                            </thead>
                            <tbody id="passSlipHistoryBody"></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
            .header-title { display: flex; align-items: center; justify-content: space-between; }
            .header-date-time { font-size: 16px; color: #555; margin-left: auto; }
            .file-item {
                position: relative; text-align: center; margin-bottom: 15px;
                border: 1px solid #ddd; border-radius: 8px; padding: 15px;
                background-color: #f9f9f9; width: 100%; height: 250px;
                display: flex; flex-direction: column; justify-content: center; align-items: center;
                transition: all 0.3s ease;
            }
            .file-thumbnail, .file-thumbnail-pdf, .file-thumbnail-office {
                width: 150px; height: 150px; object-fit: cover;
                margin-bottom: 15px; transition: transform 0.3s ease;
            }
            .file-thumbnail:hover, .file-thumbnail-pdf:hover, .file-thumbnail-office:hover { transform: scale(1.05); }
            .file-thumbnail-pdf {
                background-color: #f4f4f4; display: flex; justify-content: center;
                align-items: center; font-size: 40px; color: #444;
            }
            .file-thumbnail-office {
                background-color: #e6e6e6; display: flex; justify-content: center;
                align-items: center; font-size: 40px; color: #444;
            }
            .filename {
                font-size: 12px; color: #333; display: inline-block; overflow: hidden;
                text-overflow: ellipsis; white-space: nowrap; max-width: 100px; text-align: center; margin-bottom: 5px;
            }
            .file-info { display: flex; align-items: center; justify-content: center; margin-top: 0; }
            .download-btn { font-size: 12px; color: #007bff; text-decoration: none; padding: 0 5px; vertical-align: middle; }
            .download-btn i { font-size: 14px; vertical-align: middle; }
            .file-item input[type="checkbox"] { position: absolute; top: 10px; left: 10px; z-index: 10; }
            .loading-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.7); display: flex; align-items: center; justify-content: center; z-index: 10; }
        </style>

        <script>
        (function() {
            var basePath = '../../ajax/';
            var currentPage = 1;
            var perPage = 5;
            var totalPages = 1;
            var searchTimeout = null;
            var classOptionsHtml = '';

            function showToast(msg, type) {
                var toast = document.getElementById('ajaxToast');
                toast.className = 'alert alert-' + type;
                toast.innerHTML = msg;
                toast.style.display = 'block';
                setTimeout(function() { toast.style.display = 'none'; }, 3000);
            }

            function getFilters() {
                return {
                    project: document.getElementById('projectSelect').value,
                    ics: document.getElementById('icsSelect').value,
                    year: document.getElementById('yearSelect').value,
                    remarks: document.getElementById('remarksSelect').value,
                    search: document.getElementById('searchInput').value
                };
            }

            function loadFilters() {
                var f = getFilters();
                var params = 'project=' + encodeURIComponent(f.project) + '&ics=' + encodeURIComponent(f.ics) +
                             '&year=' + encodeURIComponent(f.year) + '&remarks=' + encodeURIComponent(f.remarks);
                $.getJSON(basePath + 'inventory_filters.php?' + params, function(data) {
                    var p = document.getElementById('projectSelect');
                    var i = document.getElementById('icsSelect');
                    var y = document.getElementById('yearSelect');
                    var r = document.getElementById('remarksSelect');

                    var pv = p.value, iv = i.value, yv = y.value, rv = r.value;

                    p.innerHTML = '<option value="">All projects</option>';
                    data.projects.forEach(function(v) { p.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    p.value = pv;

                    i.innerHTML = '<option value="">All ICS</option>';
                    data.ics_list.forEach(function(v) { i.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    i.value = iv;

                    y.innerHTML = '<option value="">All Years</option>';
                    data.years.forEach(function(v) { y.innerHTML += '<option value="' + v + '">' + v + '</option>'; });
                    y.value = yv;

                    r.innerHTML = '<option value="">All Remarks</option>';
                    data.remarks.forEach(function(v) { r.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    r.value = rv;
                });
            }

            function loadClassifications() {
                $.getJSON(basePath + 'inventory_get_item.php?action=classifications', function(data) {
                    classOptionsHtml = '<option value="">-- Select Classification --</option>';
                    var currentCat = '';
                    data.forEach(function(row) {
                        if (row.category !== currentCat) {
                            if (currentCat !== '') classOptionsHtml += '</optgroup>';
                            currentCat = row.category;
                            classOptionsHtml += '<optgroup label="' + escHtml(currentCat) + '">';
                        }
                        classOptionsHtml += '<option value="' + escHtml(row.sub_item) + '">' + escHtml(row.sub_item) + '</option>';
                    });
                    if (currentCat !== '') classOptionsHtml += '</optgroup>';

                    $('select[name="txt_classification"]').html(classOptionsHtml);
                    $('#edit_classification').html(classOptionsHtml);
                });
            }

            function loadData(page) {
                currentPage = page || 1;
                var f = getFilters();
                var params = 'page=' + currentPage + '&per_page=' + perPage +
                             '&search=' + encodeURIComponent(f.search) +
                             '&project=' + encodeURIComponent(f.project) +
                             '&ics=' + encodeURIComponent(f.ics) +
                             '&year=' + encodeURIComponent(f.year) +
                             '&remarks=' + encodeURIComponent(f.remarks);

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="20" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.getJSON(basePath + 'inventory_data.php?' + params, function(res) {
                    totalPages = res.total_pages;
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                    updateDeleteBtn();
                }).fail(function() {
                    tbody.innerHTML = '<tr><td colspan="20" class="text-center text-danger">Failed to load data.</td></tr>';
                });
            }

            function renderTable(rows) {
                var tbody = document.getElementById('tableBody');
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="20" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    var itemType = '';
                    if (row.item_type === 'equipment') {
                        itemType = '<span class="label label-primary"><i class="fa fa-tools"></i> Equipment</span>';
                    } else if (row.item_type === 'consumable') {
                        itemType = '<span class="label label-warning"><i class="fa fa-scroll"></i> Consumable</span>';
                    } else {
                        itemType = '-';
                    }
                    var id = parseInt(row.id);
                    html += '<tr>' +
                        '<td><input type="checkbox" class="chk_delete" data-id="' + id + '" /></td>' +
                        '<td>' + row.row_num + '</td>' +
                        '<td>' + escHtml(row.project) + '</td>' +
                        '<td>' + escHtml(row.item) + '</td>' +
                        '<td>' + escHtml(row.classification) + '</td>' +
                        '<td>' + itemType + '</td>' +
                        '<td>' + escHtml(row.quantity) + '</td>' +
                        '<td>' + escHtml(row.unit) + '</td>' +
                        '<td>' + escHtml(row.description) + '</td>' +
                        '<td>' + escHtml(row.received) + '</td>' +
                        '<td>' + escHtml(row.property) + '</td>' +
                        '<td>' + escHtml(row.ics) + '</td>' +
                        '<td>' + escHtml(row.serial) + '</td>' +
                        '<td>' + escHtml(row.date) + '</td>' +
                        '<td>' + escHtml(row.officer) + '</td>' +
                        '<td>' + escHtml(row.cost) + '</td>' +
                        '<td>' + escHtml(row.life) + '</td>' +
                        '<td>' + escHtml(row.transferred) + '</td>' +
                        '<td>' + escHtml(row.remarks) + '</td>' +
                        '<td class="option-buttons">' +
                            '<div style="display:flex;gap:5px;flex-wrap:wrap;">' +
                            '<button class="btn btn-primary btn-xs editBtn" data-id="' + id + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                            '<button class="btn btn-info btn-xs viewBtn" data-id="' + id + '" data-desc="' + escHtml(row.description) + '" title="Files"><i class="fa fa-eye"></i></button>' +
                            '<button class="btn btn-success btn-xs passSlipBtn" data-id="' + id + '" data-desc="' + escHtml(row.description) + '" title="Pass Slip History"><i class="fa fa-file-text-o"></i></button>' +
                            '<button class="btn btn-default btn-xs printRowBtn" data-id="' + id + '" title="Print"><i class="fa fa-print"></i></button>' +
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
            $('#projectSelect, #icsSelect, #yearSelect, #remarksSelect').on('change', function() {
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

            // ========== ADD ITEM ==========
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('addSubmitBtn');
                btn.disabled = true;
                btn.value = 'Adding...';

                var formData = new FormData(this);
                formData.append('action', 'add');

                $.ajax({
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#addModal').modal('hide');
                            showToast('Item added successfully!', 'success');
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
                        btn.value = 'Add Item';
                    }
                });
            });

            // ========== EDIT ITEM ==========
            $(document).on('click', '.editBtn', function() {
                var id = $(this).attr('data-id');
                $.getJSON(basePath + 'inventory_get_item.php?action=item&id=' + id, function(item) {
                    $('#edit_hidden_id').val(item.id);
                    $('#edit_project').val(item.project);
                    $('#edit_item').val(item.item);
                    $('#edit_classification').val(item.classification).trigger('change');
                    $('#edit_quantity').val(item.quantity);
                    $('#edit_unit').val(item.unit);
                    $('#edit_description').val(item.description);
                    $('#edit_received').val(item.received);
                    $('#edit_property').val(item.property);
                    $('#edit_ics').val(item.ics);
                    $('#edit_serial').val(item.serial);
                    $('#edit_date').val(item.date);
                    $('#edit_officer').val(item.officer);
                    $('#edit_cost').val(item.cost);
                    $('#edit_life').val(item.life);
                    $('#edit_transferred').val(item.transferred);
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
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=edit',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#editModal').modal('hide');
                            showToast('Item updated successfully!', 'success');
                            loadData(currentPage);
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

            // ========== VIEW PHOTOS ==========
            $(document).on('click', '.viewBtn', function() {
                var id = $(this).attr('data-id');
                var desc = $(this).attr('data-desc');
                document.getElementById('viewItemName').textContent = desc;
                document.getElementById('view_hidden_id').value = id;
                loadPhotos(id);
                $('#viewModal').modal('show');
            });

            function loadPhotos(inventoryId) {
                var grid = document.getElementById('photoGrid');
                grid.innerHTML = '<div class="col-md-12 text-center"><i class="fa fa-spinner fa-spin"></i> Loading files...</div>';

                $.getJSON(basePath + 'inventory_get_item.php?action=photos&id=' + inventoryId, function(photos) {
                    if (!photos || photos.length === 0) {
                        grid.innerHTML = '<div class="col-md-12 text-center text-muted">No files found.</div>';
                        return;
                    }
                    var html = '';
                    photos.forEach(function(photo) {
                        var ext = photo.type.toLowerCase();
                        var preview = '';
                        if (['jpg','jpeg','png','gif'].indexOf(ext) >= 0) {
                            preview = '<img src="' + photo.filepath + '" alt="" class="file-thumbnail" style="cursor:pointer;" onclick="previewFile(\'' + photo.filepath + '\', \'' + photo.filename.replace(/'/g, "\\'") + '\', \'' + ext + '\')"/>';
                        } else if (ext === 'pdf') {
                            preview = '<div class="file-thumbnail-pdf" style="cursor:pointer;" onclick="previewFile(\'' + photo.filepath + '\', \'' + photo.filename.replace(/'/g, "\\'") + '\', \'pdf\')"><i class="fa fa-file-pdf-o" style="font-size:50px; color:#e74c3c;"></i></div>';
                        } else if (['docx','xlsx','pptx'].indexOf(ext) >= 0) {
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

            function previewFile(fileUrl, fileName, ext) {
                var pdfFrame = document.getElementById('previewPdfFrame');
                var imgTag = document.getElementById('previewImgTag');
                var spinner = document.getElementById('previewSpinner');
                document.getElementById('previewFileName').textContent = fileName;

                pdfFrame.style.display = 'none';
                pdfFrame.src = '';
                imgTag.style.display = 'none';
                imgTag.src = '';
                spinner.style.display = 'block';
                spinner.innerHTML = '<i class="fa fa-spinner fa-spin" style="font-size:36px; color:#888;"></i><p style="margin-top:10px; color:#888;">Loading preview...</p>';

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

                $('#previewFileModal').modal('show');
            }

            function printPreviewFile() {
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

            $('#previewFileModal').on('hidden.bs.modal', function() {
                document.getElementById('previewPdfFrame').src = '';
                document.getElementById('previewImgTag').src = '';
            });

            $('#viewSelectAll').on('change', function() {
                var c = this.checked;
                document.querySelectorAll('.photoChk').forEach(function(cb) { cb.checked = c; });
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
                    url: basePath + 'inventory_crud.php',
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
                    error: function() { showToast('Network error.', 'danger'); }
                });
            });

            $('#removePhotoBtn').on('click', function() {
                var ids = [];
                document.querySelectorAll('.photoChk:checked').forEach(function(cb) { ids.push(cb.value); });
                if (ids.length === 0) { showToast('No files selected.', 'warning'); return; }
                if (!confirm('Remove selected files?')) return;

                var fd = new FormData();
                fd.append('action', 'remove_photos');
                ids.forEach(function(id) { fd.append('photo_ids[]', id); });

                $.ajax({
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        showToast(res.message, 'success');
                        loadPhotos(document.getElementById('view_hidden_id').value);
                    },
                    error: function() { showToast('Network error.', 'danger'); }
                });
            });

            // ========== DELETE ==========
            $('#deleteSelectedBtn').on('click', function() {
                var ids = getSelectedIds();
                if (ids.length === 0) { showToast('No items selected.', 'warning'); return; }
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                var ids = getSelectedIds();
                var fd = new FormData();
                fd.append('action', 'delete');
                ids.forEach(function(id) { fd.append('ids[]', id); });

                $.ajax({
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        $('#deleteModal').modal('hide');
                        showToast(res.message, 'success');
                        loadData(currentPage);
                        loadFilters();
                    },
                    error: function() { showToast('Network error.', 'danger'); }
                });
            });

            // ========== IMPORT ==========
            $('#importBtn').on('click', function() { document.getElementById('importFile').click(); });
            $('#importFile').on('change', function() {
                var formData = new FormData();
                formData.append('file', this.files[0]);
                fetch('import.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            loadData(1);
                            loadFilters();
                            showToast('Import successful!', 'success');
                        } else {
                            showToast(data.error || 'Import failed.', 'danger');
                        }
                    })
                    .catch(function() { showToast('Import error.', 'danger'); });
            });

            // ========== EXPORT ==========
            $('#exportBtn').on('click', function() {
                var f = getFilters();
                var url = 'export.php?ics=' + encodeURIComponent(f.ics) +
                          '&project=' + encodeURIComponent(f.project) +
                          '&year=' + encodeURIComponent(f.year) +
                          '&remarks=' + encodeURIComponent(f.remarks);
                window.location.href = url;
            });

            // ========== PRINT ROW ==========
            $(document).on('click', '.printRowBtn', function() {
                var id = $(this).attr('data-id');
                window.open('print_record.php?id=' + id, '_blank', 'width=960,height=700');
            });

            // ========== PASS SLIP HISTORY ==========
            $(document).on('click', '.passSlipBtn', function() {
                var id = $(this).attr('data-id');
                var desc = $(this).attr('data-desc');
                document.getElementById('passSlipHistoryItemName').textContent = desc;

                fetch('../pass_slip/function.php?action=history&inventory_id=' + id)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var tbody = document.getElementById('passSlipHistoryBody');
                        tbody.innerHTML = '';
                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No pass slip records found for this item.</td></tr>';
                        } else {
                            data.forEach(function(row) {
                                var statusClass = row.status === 'returned' ? 'label label-success' : (row.status === 'overdue' ? 'label label-danger' : 'label label-warning');
                                tbody.innerHTML += '<tr>' +
                                    '<td>' + row.pass_slip_no + '</td>' +
                                    '<td>' + row.pullout_date + '</td>' +
                                    '<td>' + (row.return_date || '-') + '</td>' +
                                    '<td>' + row.requested_by_out + '</td>' +
                                    '<td>' + row.qty + ' ' + row.unit + '</td>' +
                                    '<td><span class="' + statusClass + '">' + row.status.charAt(0).toUpperCase() + row.status.slice(1) + '</span></td>' +
                                    '<td><a href="print_slip.php?id=' + row.id + '" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-print"></i></a></td>' +
                                    '</tr>';
                            });
                        }
                        $('#passSlipHistoryModal').modal('show');
                    })
                    .catch(function() { showToast('Failed to load pass slip history.', 'danger'); });
            });

            // ========== DATE/TIME ==========
            function updateDateTime() {
                var now = new Date();
                document.getElementById('dateTime').innerText = now.toLocaleString('en-US', {
                    year:'numeric', month:'long', day:'numeric',
                    hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true
                });
            }
            setInterval(updateDateTime, 1000);
            updateDateTime();

            // ========== INIT ==========
            loadClassifications();
            loadFilters();
            loadData(1);

        })();
        </script>

    </body>
</html>
<?php } ?>
