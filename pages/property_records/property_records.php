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
    <?php include '../connection.php'; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

       <aside class="right-side">
    <section class="content-header">
        <div class="header-title">
            <img src="../../img/property image.png" alt="Logo" class="header-logo" />
            <div class="header-info">
                <h3>Inventory Records</h3>
                <p class="header-address">Equipment &amp; Asset Inventory Records</p>
            </div>
            <div class="header-date-time" id="dateTime"></div>
        </div>
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
                                    $qTotal = mysqli_query($con, "SELECT COUNT(*) AS total FROM inventory");
                                    $rTotal = mysqli_fetch_assoc($qTotal);

                                    $passSlipExists = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0") ? true : false;

                                    if ($passSlipExists) {
                                        $qBorrowed = mysqli_query($con, "SELECT COUNT(*) AS total FROM pass_slip WHERE status = 'borrowed'");
                                        $rBorrowed = mysqli_fetch_assoc($qBorrowed);
                                        $qOverdue = mysqli_query($con, "SELECT COUNT(*) AS total FROM pass_slip WHERE status = 'borrowed' AND return_date < CURDATE()");
                                        $rOverdue = mysqli_fetch_assoc($qOverdue);
                                    } else {
                                        $rBorrowed = ['total' => 0];
                                        $rOverdue = ['total' => 0];
                                    }

                                    $qValue = mysqli_query($con, "SELECT SUM(CAST(REPLACE(cost, ',', '') AS DECIMAL(15,2)) * quantity) AS total FROM inventory WHERE cost IS NOT NULL AND cost != ''");
                                    $rValue = mysqli_fetch_assoc($qValue);
                                    ?>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
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
                                    <div class="col-md-3 col-sm-6 col-xs-12">
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
                                    <div class="col-md-3 col-sm-6 col-xs-12">
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
                                    <div class="col-md-3 col-sm-6 col-xs-12">
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
                                        <label style="margin:0; font-weight:normal;"> records per page</label>
                                          <?php if ($_SESSION['role'] !== 'staff') { ?>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-user-plus"></i> Add Record</button>
                                            <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete Selected</button>
                                        <?php } ?>
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
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Description</th>
                                            <th>Serial Number</th>
                                            <th>Unit Cost</th>
                                            <th>Total Cost</th>
                                            <th>Date Acquired</th>
                                            <th>Received From</th>
                                            <th>Inventory Item no.</th>
                                            <th>Assigned / Deployed</th>
                                            <th>Estimated Useful Life</th>
                                            <th>Remarks</th>
                                            <th style="width: 80px !important;">Option</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr><td colspan="17" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
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
                <div class="modal-dialog modal-sdm-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Property Record</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="addAlert" style="display:none;"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>Project:</label><input name="txt_project" class="form-control input-sm" type="text" placeholder="Project" /></div>
                                    <div class="form-group"><label>Item No.:</label><input name="txt_item" class="form-control input-sm" type="text" placeholder="Item No." /></div>
                                    <div class="form-group"><label>Quantity:</label><input name="txt_quantity" id="addQuantity" class="form-control input-sm" type="number" placeholder="Quantity" /></div>
                                    <div class="form-group"><label>Unit:</label><input name="txt_unit" class="form-control input-sm" type="text" placeholder="Unit" /></div>
                                    <div class="form-group"><label>Description:</label><input name="txt_description" class="form-control input-sm" type="text" placeholder="Description" /></div>
                                    <div class="form-group"><label>Date Acquired:</label><input name="txt_date" class="form-control input-sm" type="date" placeholder="Date Acquired" /></div>
                                    <div class="form-group"><label>Received From:</label><input name="txt_received" class="form-control input-sm" type="text" placeholder="Received From" /></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Serial Number:</label><input name="txt_serial" class="form-control input-sm" type="text" placeholder="Serial Number" /></div>
                                    <div class="form-group"><label>Unit Cost:</label><input name="txt_cost" id="add_cost" class="form-control input-sm" type="text" placeholder="e.g. 43,904.00" /></div>
                                    <div class="form-group"><label>Total Cost:</label><input name="txt_total_cost" id="addTotalCost" class="form-control input-sm" type="text" readonly placeholder="Auto-computed (Qty x Unit Cost)" /></div>
                                    <div class="form-group"><label>Inventory Item no.:</label><input name="txt_inventory_item_no" class="form-control input-sm" type="text" placeholder="Inventory Item no." /></div>
                                    <div class="form-group"><label>Assigned / Deployed:</label><input name="txt_assigned_to" class="form-control input-sm" type="text" placeholder="Who/where the item is currently deployed (blank = unassigned)" /></div>
                                    <div class="form-group"><label>Estimated Useful Life:</label><input name="txt_life" class="form-control input-sm" type="text" placeholder="Estimated Useful Life" /></div>
                                    <div class="form-group"><label>Remarks:</label><textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks"></textarea></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                          
                            <input type="submit" class="btn btn-primary btn-sm" value="Add Item" id="addSubmitBtn" />
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================= EDIT MODAL (Single Dynamic) ======================= -->
        <div id="editModal" class="modal fade">
            <form id="editForm">
                <div class="modal-dialog modal-sdm-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Property Record</h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div id="editAlert" style="display:none;"></div>
                            <input type="hidden" name="hidden_id" id="edit_hidden_id" />
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group"><label>Project:</label><input type="text" name="txt_edit_project" id="edit_project" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Item No.:</label><input type="text" name="txt_edit_item" id="edit_item" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Quantity:</label><input type="number" name="txt_edit_quantity" id="edit_quantity" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Unit:</label><input type="text" name="txt_edit_unit" id="edit_unit" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Description:</label><input type="text" name="txt_edit_description" id="edit_description" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Date Acquired:</label><input type="date" name="txt_edit_date" id="edit_date" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Received From:</label><input type="text" name="txt_edit_received" id="edit_received" class="form-control input-sm" /></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group"><label>Serial Number:</label><input type="text" name="txt_edit_serial" id="edit_serial" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Unit Cost:</label><input type="text" name="txt_edit_cost" id="edit_cost" class="form-control input-sm" placeholder="e.g. 43,904.00" /></div>
                                    <div class="form-group"><label>Total Cost:</label><input type="text" name="txt_edit_total_cost" id="editTotalCost" class="form-control input-sm" readonly placeholder="Auto-computed (Qty x Unit Cost)" /></div>
                                    <div class="form-group"><label>Inventory Item no.:</label><input type="text" name="txt_edit_inventory_item_no" id="edit_inventory_item_no" class="form-control input-sm" /></div>
                                    <div class="form-group"><label>Assigned / Deployed:</label><input type="text" name="txt_edit_assigned_to" id="edit_assigned_to" class="form-control input-sm" placeholder="Who/where the item is currently deployed (blank = unassigned)" /></div>
                                    <div class="form-group"><label>Estimated Useful Life:</label><input type="text" name="txt_edit_life" id="edit_life" class="form-control input-sm" /></div>
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
                <div class="modal-dialog modal-sdm-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"><i class="fa fa-folder-open"></i> View Files for: <span id="viewItemName"></span></h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <input type="hidden" name="hidden_id" id="view_hidden_id" value="" />
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <span><input type="checkbox" id="viewSelectAll" /> <label>Select All</label></span>
                                <button type="button" class="btn btn-danger btn-sm" id="removePhotoBtn"><i class="fa fa-trash"></i> Remove Selected</button>
                            </div>
                            <div class="row" id="photoGrid">
                                <div class="col-md-12 text-center text-muted">No files found.</div>
                            </div>
                            <div id="photoUploadZone" style="margin-top:14px; border-top:1px solid #eee; padding-top:12px;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input name="photos[]" id="photoFileInput" class="form-control input-sm" type="file" multiple />
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-primary btn-sm" id="addPhotoBtn" style="width:100%;"><i class="fa fa-plus"></i> Add</button>
                                    </div>
                                </div>
                                <div id="photoPendingWrap" style="display:none; margin-top:10px;">
                                    <div class="modal-section-header"><i class="fa fa-cloud-upload"></i> Ready to Upload</div>
                                    <div class="row" id="photoPendingGrid"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                          
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================= SHARED FILE PREVIEW MODAL ======================= -->
        <?php include dirname(__DIR__) . '/sheet_preview_modal.php'; ?>

        <!-- ========================= DELETE CONFIRMATION MODAL ======================= -->
        <div id="deleteModal" class="modal fade">
            <div class="modal-dialog modal-sdm-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-trash"></i> Delete Confirmation</h4>
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
            <div class="modal-dialog modal-sdm-lg" role="document">
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

        <!-- ========================= GROUPED ITEMS MODAL ======================= -->
        <div id="groupModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-sdm-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
                            <span><i class="fa fa-th-list"></i> Group &mdash; <span id="groupTitle"></span></span>
                            <button type="button" class="btn btn-xs <?php echo $_SESSION['role'] === 'staff' ? 'btn-default' : 'btn-primary'; ?>" id="groupEditDetailsBtn"><i class="fa <?php echo $_SESSION['role'] === 'staff' ? 'fa-lock' : 'fa-pencil'; ?>"></i> <?php echo $_SESSION['role'] === 'staff' ? 'Group Details' : 'Edit Group Details'; ?></button>
                            <small id="groupCount" class="text-muted"></small>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                        <div id="groupAlert" style="display:none;"></div>

                        <!-- Add unit -->
                        <?php if ($_SESSION['role'] !== 'staff') { ?>
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fa fa-plus"></i> Add Unit &mdash; shared fields are carried over automatically</div>
                            <div class="panel-body">
                                <div id="groupAddSection">
                                    <div class="row">
                                        <div class="col-md-2 form-group"><label>Quantity:</label><input type="number" name="ga_qty" id="ga_qty" class="form-control input-sm" value="1" min="1" /></div>
                                        <div class="col-md-5 form-group"><label>Serial Number:</label><input type="text" name="ga_serial" id="ga_serial" class="form-control input-sm" placeholder="Serial Number" /></div>
                                        <div class="col-md-2 form-group"><label>Date Acquired:</label><input type="date" name="ga_date" id="ga_date" class="form-control input-sm" /></div>
                                        <div class="col-md-3 form-group"><label>Assigned / Deployed:</label><input type="text" name="ga_assigned" id="ga_assigned" class="form-control input-sm" placeholder="Who/where it is deployed (blank = unassigned)" /></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group"><label>Remarks:</label><input type="text" name="ga_remarks" id="ga_remarks" class="form-control input-sm" placeholder="Remarks" /></div>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-success btn-sm" id="groupAddBtn"><i class="fa fa-plus"></i> Add Unit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Group metrics -->
                        <div class="row" style="margin-bottom:8px;">
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-aqua"><i class="fa fa-boxes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Units</span>
                                        <span class="info-box-number" id="gMetricUnits">&ndash;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-green"><i class="fa fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Deployed / Assigned</span>
                                        <span class="info-box-number" id="gMetricDeployed">&ndash;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-yellow"><i class="fa fa-inbox"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Unassigned</span>
                                        <span class="info-box-number" id="gMetricUnassigned">&ndash;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-red"><i class="fa fa-php"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Asset Value</span>
                                        <span class="info-box-number" id="gMetricValue">&ndash;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Existing units -->
                        <div class="panel panel-default">
                            <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                <span><i class="fa fa-list"></i> Existing Units</span>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <?php if ($_SESSION['role'] !== 'staff') { ?>
                                    <button class="btn btn-danger btn-xs" id="groupDeleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete Selected</button>
                                    <?php } ?>
                                    <div class="input-group input-group-sm" style="width:220px;">
                                        <input type="text" id="groupSerialSearch" class="form-control" placeholder="Search serial no..." autocomplete="off" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" id="groupSerialClear" title="Clear"><i class="fa fa-times"></i></button>
                                        </span>
                                    </div>
                                    <select id="groupStatusFilter" class="form-control input-sm" style="width:150px;">
                                        <option value="all">Status: All</option>
                                        <option value="deployed">Deployed / Assigned</option>
                                        <option value="unassigned">Unassigned</option>
                                    </select>
                                </div>
                            </div>
                            <div class="panel-body" style="padding:0;">
                                <div style="max-height:240px; overflow-y:auto;">
                                    <table class="table table-bordered table-striped" style="margin-bottom:0;">
                                        <thead>
                                            <tr>
                                                <?php if ($_SESSION['role'] !== 'staff') { ?>
                                                <th style="width:30px;"><input type="checkbox" id="cbxGroupMain" /></th>
                                                <?php } ?>
                                                <th style="width:40px;">#</th>
                                                <th>Serial Number</th>
                                                <th>Date Acquired</th>
                                                <th>Assigned / Deployed</th>
                                                <th>Remarks</th>
                                                <th style="width:90px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="groupMembersBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================= GROUP DETAILS EDIT MODAL ======================= -->
        <?php
        $sharedFieldDefs = array(
            array('Project', 'gSharedProject'),
            array('Item No.', 'gSharedItem'),
            array('Unit Cost', 'gSharedCost'),
            array('Est. Useful Life', 'gSharedLife'),
            array('Received From', 'gSharedReceived'),
            array('Inventory Item No.', 'gSharedInvNo'),
        );
        ?>
        <div id="groupEditModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-sdm-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="fa <?php echo $_SESSION['role'] === 'staff' ? 'fa-lock' : 'fa-pencil'; ?>"></i> Group Details &mdash; <span id="groupEditTitle"></span></h4>
                    </div>
                    <div class="modal-body">
                        <div id="groupEditAlert" style="display:none;"></div>
                        <div class="row">
                            <?php foreach ($sharedFieldDefs as $sharedFieldDef) { ?>
                            <div class="col-md-4">
                                <?php if ($_SESSION['role'] !== 'staff') { ?>
                                <div class="form-group"><label><?php echo $sharedFieldDef[0]; ?>:</label><input type="text" class="form-control input-sm" id="<?php echo $sharedFieldDef[1]; ?>" placeholder="<?php echo $sharedFieldDef[0]; ?>" /></div>
                                <?php } else { ?>
                                <div class="form-group"><label><?php echo $sharedFieldDef[0]; ?>:</label><div class="form-control-static" id="<?php echo $sharedFieldDef[1]; ?>">&ndash;</div></div>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                   
                        <?php if ($_SESSION['role'] !== 'staff') { ?>
                        <button type="button" class="btn btn-primary btn-sm" id="groupSaveSharedBtn"><i class="fa fa-save"></i> Save Changes</button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div id="ajaxToast" class="alert" style="position:fixed; top:1em; right:1em; z-index:9999; display:none; min-width:250px;"></div>

        <?php include dirname(__DIR__) . '/scripts.php'; ?>
        <script src="../../js/sdm-preview.js"></script>

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
            .header-title { display: flex; align-items: center; justify-content: space-between; width: 100%; }
            .header-logo { height: 60px; width: auto; margin-right: 10px; }
            .header-info { display: flex; flex-direction: column; }
            .header-info h3 { margin: 0; font-weight: 600; }
            .header-address { margin: 0; font-size: 14px; color: #555; }
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
            .cell-group { cursor: pointer; }
            .cell-group:hover { background-color: #dce6f0; }
            tr.group-highlight td { background-color: #ffe082 !important; }
            tr.group-summary-row td { background-color: #f4f8fb; border-top: 2px solid #cfd8e3; }
            tr.group-summary-row td:first-child { background-color: #e8f0f8; }
            tr.group-summary-row .fa-cubes { color: #2c6fad; margin-right: 6px; }
            tr.group-summary-row .text-bold { font-weight: 700; color: #2c6fad; }
            tr.group-summary-row .group-badge { font-weight: 400; cursor: pointer; }
        </style>

        <script>
        (function() {
            var basePath = '../../ajax/';
            var currentPage = 1;
            var perPage = 5;
            var totalPages = 1;
            var searchTimeout = null;
            var USER_ROLE = <?php echo json_encode($_SESSION['role'] ?? ''); ?>;
            var CAN_ADD_UNIT = USER_ROLE !== 'staff';
            var currentGroup = { desc: '', unit: '', archetypeId: 0, count: 0, members: [] };
            var lastRows = [];
            var groupDeleteIds = [];
            var GROUP_COLSPAN = <?php echo $_SESSION['role'] === 'staff' ? 6 : 7; ?>;
            function getFilters() {
                return {
                    project: document.getElementById('projectSelect').value,
                    year: document.getElementById('yearSelect').value,
                    remarks: document.getElementById('remarksSelect').value,
                    search: document.getElementById('searchInput').value
                };
            }

            function loadFilters() {
                var f = getFilters();
                var params = 'project=' + encodeURIComponent(f.project) +
                             '&year=' + encodeURIComponent(f.year) + '&remarks=' + encodeURIComponent(f.remarks);
                $.getJSON(basePath + 'inventory_filters.php?' + params, function(data) {
                    var p = document.getElementById('projectSelect');
                    var y = document.getElementById('yearSelect');
                    var r = document.getElementById('remarksSelect');

                    var pv = p.value, yv = y.value, rv = r.value;

                    p.innerHTML = '<option value="">All projects</option>';
                    data.projects.forEach(function(v) { p.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    p.value = pv;

                    y.innerHTML = '<option value="">All Years</option>';
                    data.years.forEach(function(v) { y.innerHTML += '<option value="' + v + '">' + v + '</option>'; });
                    y.value = yv;

                    r.innerHTML = '<option value="">All Remarks</option>';
                    data.remarks.forEach(function(v) { r.innerHTML += '<option value="' + escHtml(v) + '">' + escHtml(v) + '</option>'; });
                    r.value = rv;
                });
            }

            function loadData(page) {
                currentPage = page || 1;
                var f = getFilters();
                var params = 'page=' + currentPage + '&per_page=' + perPage +
                             '&search=' + encodeURIComponent(f.search) +
                             '&project=' + encodeURIComponent(f.project) +
                             '&year=' + encodeURIComponent(f.year) +
                             '&remarks=' + encodeURIComponent(f.remarks);

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="17" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.getJSON(basePath + 'inventory_data.php?' + params, function(res) {
                    totalPages = res.total_pages;
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                    updateDeleteBtn();
                }).fail(function() {
                    tbody.innerHTML = '<tr><td colspan="17" class="text-center text-danger">Failed to load data.</td></tr>';
                });
            }

            function fmtNum(v) {
                if (v === null || v === undefined || v === '' || isNaN(parseFloat(v))) return '-';
                return parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function renderTable(rows) {
                var tbody = document.getElementById('tableBody');
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="17" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                lastRows = rows;
                rows.forEach(function(row) {
                    var id = parseInt(row.id);
                    var isGroup = parseInt(row.is_group) === 1;
                    var ng = escHtml(row.description);
                    var nu = escHtml(row.unit);
                    var groupClickable = ' class="cell-group" data-id="' + id + '" data-desc="' + ng + '" data-unit="' + nu + '" title="View group"';
                    if (isGroup) {
                        html += '<tr class="group-summary-row">' +
                            '<td></td>' +
                            '<td>' + row.row_num + '</td>' +
                            '<td>' + escHtml(row.project) + '</td>' +
                            '<td>' + escHtml(row.item) + '</td>' +
                            '<td class="text-bold">' + escHtml(row.quantity) + '</td>' +
                            '<td>' + nu + '</td>' +
                            '<td' + groupClickable + '><i class="fa fa-cubes" aria-hidden="true"></i> ' + ng + '</td>' +
                            '<td' + groupClickable + '><span class="label label-info group-badge">' + escHtml(row.group_badge) + '</span></td>' +
                            '<td>' + fmtNum(row.cost) + '</td>' +
                            '<td>' + fmtNum(row.total_cost) + '</td>' +
                            '<td>&ndash;</td>' +
                            '<td>' + escHtml(row.received) + '</td>' +
                            '<td>' + escHtml(row.inventory_item_no) + '</td>' +
                            '<td>&ndash;</td>' +
                            '<td>' + (row.life ? escHtml(row.life) + ' yrs' : '-') + '</td>' +
                            '<td>&ndash;</td>' +
                            '<td class="option-buttons">' +
                                '<div style="display:flex;gap:5px;flex-wrap:wrap;">' +
                                (CAN_ADD_UNIT
                                    ? '<button class="btn btn-primary btn-xs groupEditShortcut" data-id="' + id + '" data-desc="' + ng + '" data-unit="' + nu + '" title="Edit Group Details"><i class="fa fa-pencil-square-o"></i></button>'
                                    : '<button class="btn btn-default btn-xs groupEditShortcut" data-id="' + id + '" data-desc="' + ng + '" data-unit="' + nu + '" title="View Group Details"><i class="fa fa-lock"></i></button>') +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    } else {
                        html += '<tr>' +
                            '<td><input type="checkbox" class="chk_delete" data-id="' + id + '" /></td>' +
                            '<td>' + row.row_num + '</td>' +
                            '<td>' + escHtml(row.project) + '</td>' +
                            '<td>' + escHtml(row.item) + '</td>' +
                            '<td>' + escHtml(row.quantity) + '</td>' +
                            '<td>' + nu + '</td>' +
                            '<td>' + ng + '</td>' +
                            '<td>' + escHtml(row.serial) + '</td>' +
                            '<td>' + fmtNum(row.cost) + '</td>' +
                            '<td>' + fmtNum(row.total_cost) + '</td>' +
                            '<td>' + escHtml(row.date) + '</td>' +
                            '<td>' + escHtml(row.received) + '</td>' +
                            '<td>' + escHtml(row.inventory_item_no) + '</td>' +
                            '<td>' + escHtml(row.assigned_to) + '</td>' +
                            '<td>' + (row.life ? escHtml(row.life) + ' yrs' : '-') + '</td>' +
                            '<td>' + escHtml(row.remarks) + '</td>' +
                            '<td class="option-buttons">' +
                                '<div style="display:flex;gap:5px;flex-wrap:wrap;">' +
                                '<button class="btn btn-primary btn-xs editBtn" data-id="' + id + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                                '<button class="btn btn-info btn-xs viewBtn" data-id="' + id + '" data-desc="' + ng + '" title="Files"><i class="fa fa-eye"></i></button>' +
                                '<button class="btn btn-success btn-xs passSlipBtn" data-id="' + id + '" data-desc="' + ng + '" title="Pass Slip History"><i class="fa fa-file-text-o"></i></button>' +
                                '<button class="btn btn-default btn-xs printRowBtn" data-id="' + id + '" title="Print"><i class="fa fa-print"></i></button>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    }
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
            $('#projectSelect, #yearSelect, #remarksSelect').on('change', function() {
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

            // ========== TOTAL COST AUTO-CALC ==========
            function calcTotalCost(qtySel, costSel, totalSel) {
                var q = parseFloat($(qtySel).val());
                var c = parseFloat(String($(costSel).val() || '').replace(/,/g, ''));
                if (isNaN(q) || isNaN(c)) { $(totalSel).val(''); return; }
                $(totalSel).val(fmtNum(q * c));
            }
            $('#addQuantity, #edit_quantity').on('input', function() {
                calcTotalCost('#addQuantity', '#add_cost', '#addTotalCost');
            });
            $('#add_cost').on('input', function() {
                calcTotalCost('#addQuantity', '#add_cost', '#addTotalCost');
            });
            $('#edit_cost').on('input', function() {
                calcTotalCost('#edit_quantity', '#edit_cost', '#editTotalCost');
            });

            // ========== ADD ITEM ==========
            $('#addForm').on('submit', function(e) {
                e.preventDefault();

                var filled = false;
                $('#addForm input:not([type=file]):not([readonly]):not([type=hidden]), #addForm textarea').each(function() {
                    if (String(this.value).trim() !== '') filled = true;
                });
                if (!filled) {
                    $('#addAlert').html('<div class="alert alert-danger">Please fill in at least one field before saving.</div>').show();
                    return;
                }

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
            var isGroupContext = false;
            $(document).on('click', '.editBtn', function() {
                var id = $(this).attr('data-id');
                isGroupContext = $(this).attr('data-in-group') === '1';
                $.getJSON(basePath + 'inventory_get_item.php?action=item&id=' + id, function(item) {
                    $('#edit_hidden_id').val(item.id);
                    $('#edit_project').val(item.project);
                    $('#edit_item').val(item.item);
                    $('#edit_quantity').val(item.quantity);
                    $('#edit_unit').val(item.unit);
                    $('#edit_description').val(item.description);
                    $('#edit_received').val(item.received);
                    $('#edit_serial').val(item.serial);
                    $('#edit_date').val(item.date);
                    $('#edit_cost').val(item.cost);
                    $('#edit_inventory_item_no').val(item.inventory_item_no);
                    $('#edit_assigned_to').val(item.assigned_to);
                    $('#edit_life').val(item.life);
                    $('#edit_remarks').val(item.remarks);
                    calcTotalCost('#edit_quantity', '#edit_cost', '#editTotalCost');
                    $('#editModal').modal('show');
                }).fail(function() {
                    showToast('Failed to load item data.', 'danger');
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                var filled = false;
                $('#editForm input:not([type=file]):not([readonly]):not([type=hidden]), #editForm textarea').each(function() {
                    if (String(this.value).trim() !== '') filled = true;
                });
                if (!filled) {
                    $('#editAlert').html('<div class="alert alert-danger">Please fill in at least one field before saving.</div>').show();
                    return;
                }

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
                            var groupEdit = isGroupContext;
                            isGroupContext = false;
                            $('#editModal').modal('hide');
                            showToast('Item updated successfully!', 'success');
                            loadData(currentPage);
                            if (groupEdit) refreshCurrentGroup();
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

            $('#editModal').on('hidden.bs.modal', function() {
                isGroupContext = false;
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
                        } else if (['xlsx','csv'].indexOf(ext) >= 0) {
                            preview = SDMPreview.previewCard(photo.filepath, photo.filename, ext, 'property_records');
                        } else if (['docx','pptx'].indexOf(ext) >= 0) {
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
                SDMPreview.open(fileUrl, fileName, ext, 'property_records');
            }

            window.previewFile = previewFile;

            $('#viewSelectAll').on('change', function() {
                var c = this.checked;
                document.querySelectorAll('.photoChk').forEach(function(cb) { cb.checked = c; });
            });

            var pendingPhotos = [];
            function pendingPreviewHtml(file, i) {
                var ext = (file.name.split('.').pop() || '').toLowerCase();
                var preview;
                if (['jpg','jpeg','png','gif','webp','bmp'].indexOf(ext) >= 0) {
                    if (!file._url) file._url = URL.createObjectURL(file);
                    preview = '<img src="' + file._url + '" alt="" class="file-thumbnail" style="object-fit:cover;" />';
                } else if (ext === 'pdf') {
                    preview = '<div class="file-thumbnail-pdf"><i class="fa fa-file-pdf-o" style="font-size:44px; color:#e74c3c;"></i></div>';
                } else if (['doc','docx','xls','xlsx','ppt','pptx'].indexOf(ext) >= 0) {
                    var icon = 'fa-file-word-o';
                    if (['doc','docx'].indexOf(ext) !== -1) icon = 'fa-file-word-o';
                    else if (['xls','xlsx'].indexOf(ext) !== -1) icon = 'fa-file-excel-o';
                    else icon = 'fa-file-powerpoint-o';
                    preview = '<div class="file-thumbnail-office" style="font-size:44px; color:#2c6fad;"><i class="fa ' + icon + '"></i></div>';
                } else {
                    preview = '<div class="file-thumbnail" style="font-size:44px; color:#999;"><i class="fa fa-file-o"></i></div>';
                }
                return '<div class="col-md-4">' +
                    '<div class="file-item">' +
                    '<button type="button" class="btn btn-danger btn-xs pendingRemoveBtn" data-idx="' + i + '" title="Remove from upload" style="position:absolute; top:4px; right:4px; z-index:5;"><i class="fa fa-times"></i></button>' +
                    preview +
                    '<div class="file-info"><span class="filename">' + escHtml(file.name) + '</span></div>' +
                    '</div></div>';
            }
            function renderPending() {
                var grid = document.getElementById('photoPendingGrid');
                var wrap = document.getElementById('photoPendingWrap');
                if (!grid || !wrap) return;
                if (pendingPhotos.length === 0) {
                    wrap.style.display = 'none';
                    grid.innerHTML = '';
                    return;
                }
                var html = '';
                pendingPhotos.forEach(function(f, i) { html += pendingPreviewHtml(f, i); });
                grid.innerHTML = html;
                wrap.style.display = '';
            }
            function clearPending() {
                pendingPhotos.forEach(function(f) { if (f._url) URL.revokeObjectURL(f._url); delete f._url; });
                pendingPhotos = [];
                renderPending();
            }
            $(document).on('click', '.pendingRemoveBtn', function() {
                var i = parseInt($(this).attr('data-idx'));
                if (isNaN(i)) return;
                if (pendingPhotos[i] && pendingPhotos[i]._url) { URL.revokeObjectURL(pendingPhotos[i]._url); }
                pendingPhotos.splice(i, 1);
                renderPending();
            });
            $('#photoFileInput').on('change', function() {
                if (!this.files) return;
                for (var i = 0; i < this.files.length; i++) {
                    var f = this.files[i];
                    var dup = pendingPhotos.some(function(p) { return p.name === f.name && p.size === f.size && p.lastModified === f.lastModified; });
                    if (!dup) pendingPhotos.push(f);
                }
                this.value = '';
                renderPending();
            });
            $('#addPhotoBtn').on('click', function() {
                var inventoryId = document.getElementById('view_hidden_id').value;
                if (pendingPhotos.length === 0) {
                    showToast('Please select files to upload.', 'warning');
                    return;
                }
                var btn = document.getElementById('addPhotoBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
                var fd = new FormData();
                fd.append('action', 'add_photo');
                fd.append('hidden_id', inventoryId);
                pendingPhotos.forEach(function(f) { fd.append('photos[]', f); });
                $.ajax({
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-plus"></i> Add';
                        if (res.success) {
                            showToast(res.message, 'success');
                            clearPending();
                            loadPhotos(inventoryId);
                        } else {
                            showToast(res.error || 'Upload failed.', 'danger');
                        }
                    },
                    error: function() {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-plus"></i> Add';
                        showToast('Network error.', 'danger');
                    }
                });
            });
            $('#viewModal').on('hidden.bs.modal', function() {
                clearPending();
                var fi = document.getElementById('photoFileInput');
                if (fi) fi.value = '';
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
                var groupMode = groupDeleteIds.length > 0;
                var ids;
                var fd = new FormData();
                if (groupMode) {
                    ids = groupDeleteIds;
                    groupDeleteIds = [];
                    fd.append('action', 'group_delete');
                    fd.append('group_description', currentGroup.desc);
                    fd.append('group_unit', currentGroup.unit === null ? '' : currentGroup.unit);
                } else {
                    ids = getSelectedIds();
                    if (ids.length === 0) { $('#deleteModal').modal('hide'); showToast('No items selected.', 'warning'); return; }
                    fd.append('action', 'delete');
                }
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
                        if (!res.success) { showToast(res.error || res.message || 'Delete failed.', 'danger'); return; }
                        showToast(res.message, 'success');
                        if (groupMode) refreshCurrentGroup();
                        loadData(currentPage);
                        loadFilters();
                    },
                    error: function() { showToast('Network error.', 'danger'); }
                });
            });

            // ========== GROUPED ITEMS MODAL ==========
function memberRowHtml(u, index) {
    var chk = CAN_ADD_UNIT ? '<td><input type="checkbox" class="chk_delete_group" data-id="' + u.id + '" /></td>' : '';
    return '<tr class="group-member-row" data-id="' + u.id + '">' +
        chk +
        '<td>' + index + '</td>' +
        '<td>' + escHtml(u.serial) + '</td>' +
        '<td>' + escHtml(u.date) + '</td>' +
        '<td>' + escHtml(u.assigned_to) + '</td>' +
        '<td>' + escHtml(u.remarks) + '</td>' +
        '<td>' +
            '<div style="display:flex;gap:4px;justify-content:center;">' +
            (CAN_ADD_UNIT ?
                '<button class="btn btn-primary btn-xs editBtn" data-id="' + u.id + '" data-in-group="1" title="Edit"><i class="fa fa-pencil-square-o"></i></button>'
                : '') +
            '<button class="btn btn-info btn-xs viewBtn" data-id="' + u.id + '" data-desc="' + escHtml(u.serial) + '" title="View Files"><i class="fa fa-eye"></i></button>' +
            '<button class="btn btn-default btn-xs printRowBtn" data-id="' + u.id + '" title="Print sticker"><i class="fa fa-print"></i></button>' +
            '</div>' +
        '</td>' +
        '</tr>';
}
function renderGroupMembers(highlightId) {
                var tbody = document.getElementById('groupMembersBody');
                var searchBox = document.getElementById('groupSerialSearch');
                var filterText = searchBox ? searchBox.value.trim().toLowerCase() : '';
                var statusEl = document.getElementById('groupStatusFilter');
                var statusFilter = statusEl ? statusEl.value : 'all';
                var cbxAll = document.getElementById('cbxGroupMain');
                if (cbxAll) cbxAll.checked = false;

    var isDeployed = function(u) {
        var a = (u.assigned_to || '').trim().toLowerCase();
        return a !== '' && a !== 'unassigned' && a !== '(unassigned)';
    };
    var all = currentGroup.members || [];
    var visible = all.filter(function(u) {
        if (filterText && (u.serial || '').toLowerCase().indexOf(filterText) === -1) return false;
        if (statusFilter === 'deployed') return isDeployed(u);
        if (statusFilter === 'unassigned') return !isDeployed(u);
        return true;
    });

    var countText = '\u00b7 ' + currentGroup.count + ' units in this group';
    if (visible.length !== currentGroup.count) countText += ' \u2014 ' + visible.length + ' shown';
    document.getElementById('groupCount').textContent = countText;

    if (visible.length === 0) {
        var msg = 'No units match the current filters.';
        if (!filterText && statusFilter === 'all') msg = 'No units in this group yet.';
        else if (filterText && statusFilter === 'all') msg = 'No serial number matches "' + escHtml(searchBox.value.trim()) + '".';
        else if (!filterText) msg = 'No ' + (statusFilter === 'deployed' ? 'deployed' : 'unassigned') + ' units in this group.';
        tbody.innerHTML = '<tr><td colspan="' + GROUP_COLSPAN + '" class="text-center text-muted">' + msg + '</td></tr>';
        updateGroupDeleteBtn();
        return;
    }
    var html = '';
    visible.forEach(function(u, i) {
        html += memberRowHtml(u, i + 1);
    });
    tbody.innerHTML = html;
    if (highlightId) {
        var el = tbody.querySelector('tr[data-id="' + highlightId + '"]');
        if (el) {
            el.classList.add('group-highlight');
            if (typeof el.scrollIntoView === 'function') el.scrollIntoView({ block: 'center' });
        }
    }
updateGroupDeleteBtn();
}

            function updateGroupDeleteBtn() {
                var checked = document.querySelectorAll('.chk_delete_group:checked').length;
                var btn = document.getElementById('groupDeleteSelectedBtn');
                if (btn) btn.disabled = checked === 0;
            }

            $('#cbxGroupMain').on('change', function() {
                var checked = this.checked;
                document.querySelectorAll('.chk_delete_group').forEach(function(cb) { cb.checked = checked; });
                updateGroupDeleteBtn();
            });
            $(document).on('change', '.chk_delete_group', function() {
                var all = document.querySelectorAll('.chk_delete_group').length;
                var checked = document.querySelectorAll('.chk_delete_group:checked').length;
                var cbxMain = document.getElementById('cbxGroupMain');
                if (cbxMain) cbxMain.checked = (all > 0 && all === checked);
                updateGroupDeleteBtn();
            });
            $('#groupDeleteSelectedBtn').on('click', function() {
                groupDeleteIds = [];
                document.querySelectorAll('.chk_delete_group:checked').forEach(function(cb) {
                    groupDeleteIds.push(cb.getAttribute('data-id'));
                });
                if (groupDeleteIds.length === 0) { showToast('No units selected.', 'warning'); return; }
                $('#deleteModal').modal('show');
            });

            function resetGroupAddForm() {
                document.getElementById('ga_serial').value = '';
                document.getElementById('ga_date').value = '';
                document.getElementById('ga_assigned').value = '';
                document.getElementById('ga_remarks').value = '';
                document.getElementById('ga_qty').value = '1';
                document.getElementById('groupAlert').style.display = 'none';
            }

            function fillSharedFields(a) {
                var put = function(id, display, raw) {
                    var el = document.getElementById(id);
                    if (!el) return;
                    if (el.tagName === 'INPUT') el.value = raw === undefined ? display : raw;
                    else el.textContent = display;
                };
                put('gSharedProject', a && a.project ? a.project : '\u2013', a && a.project ? a.project : '');
                put('gSharedItem', a && a.item ? a.item : '\u2013', a && a.item ? a.item : '');
                put('gSharedCost', a && a.cost ? a.cost : '\u2013', a && a.cost ? a.cost : '');
                put('gSharedLife', a && a.life ? a.life + ' yrs' : '\u2013', a && a.life ? a.life : '');
                put('gSharedReceived', a && a.received ? a.received : '\u2013', a && a.received ? a.received : '');
                put('gSharedInvNo', a && a.inventory_item_no ? a.inventory_item_no : '\u2013', a && a.inventory_item_no ? a.inventory_item_no : '');
            }

            function resetGroupMetrics() {
                ['gMetricUnits', 'gMetricDeployed', 'gMetricUnassigned', 'gMetricValue'].forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = '\u2013';
                });
            }

            function updateGroupMetrics() {
                var members = currentGroup.members || [];
                var deployed = 0;
                var unassigned = 0;
                var value = 0;
                members.forEach(function(u) {
                    var a = String(u.assigned_to || '').trim().toLowerCase();
                    if (a !== '' && a !== 'unassigned' && a !== '(unassigned)') deployed++;
                    else unassigned++;
                    var c = parseFloat(String(u.cost || '').replace(/,/g, ''));
                    if (!isNaN(c)) {
                        var q = parseFloat(u.quantity);
                        if (!q && q !== 0) q = 1;
                        value += c * q;
                    }
                });
                var set = function(id, txt) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = txt;
                };
                set('gMetricUnits', members.length);
                set('gMetricDeployed', deployed);
                set('gMetricUnassigned', unassigned);
                set('gMetricValue', 'php ' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }

            function openGroupModal(desc, unit, highlightId) {
                currentGroup.desc = desc;
                currentGroup.unit = unit || '';
                currentGroup.archetypeId = highlightId ? parseInt(highlightId) : 0;
                currentGroup.count = 0;
                currentGroup.members = [];

                document.getElementById('groupTitle').textContent = desc + (unit ? ' \u00b7 ' + unit : '');
                document.getElementById('groupCount').textContent = '';
                document.getElementById('groupMembersBody').innerHTML = '<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';
                var gaSec = document.getElementById('groupAddSection'); if (gaSec) gaSec.style.display = CAN_ADD_UNIT ? '' : 'none';
                resetGroupAddForm(); document.getElementById('groupSerialSearch').value = '';
                var statusEl = document.getElementById('groupStatusFilter'); if (statusEl) statusEl.value = 'all';
                resetGroupMetrics();
 
                $('#groupModal').modal('show');
                $(document).on('keyup', '#groupSerialSearch', function() {
                    renderGroupMembers();
                });
                $(document).on('click', '#groupSerialClear', function() {
                    document.getElementById('groupSerialSearch').value = '';
                    renderGroupMembers();
                });
                $(document).on('change', '#groupStatusFilter', function() {
                    renderGroupMembers();
                });
                var url = basePath + 'inventory_get_item.php?action=group' +
                    '&description=' + encodeURIComponent(desc) +
                    '&unit=' + encodeURIComponent(unit || '') +
                    (highlightId ? '&archetype_id=' + encodeURIComponent(highlightId) : '');

                $.getJSON(url, function(res) {
                    if (currentGroup.desc !== desc) return; // stale
                    currentGroup.count = res.count;
                    currentGroup.members = res.members || [];
                    fillSharedFields(res.archetype);
                    updateGroupMetrics();
                    renderGroupMembers(highlightId);
                }).fail(function() {
                    document.getElementById('groupMembersBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load group.</td></tr>';
                });
            }


function refreshCurrentGroup(highlightId) {
    if (!currentGroup.desc) return;
    var url = basePath + 'inventory_get_item.php?action=group' +
        '&description=' + encodeURIComponent(currentGroup.desc) +
        '&unit=' + encodeURIComponent(currentGroup.unit || '') +
        (currentGroup.archetypeId ? '&archetype_id=' + encodeURIComponent(currentGroup.archetypeId) : '');
    $.getJSON(url, function(res) {
        currentGroup.count = res.count;
        currentGroup.members = res.members || [];
        fillSharedFields(res.archetype);
        updateGroupMetrics();
        renderGroupMembers(highlightId);
    });
}
            $(document).on('click', '.cell-group', function() {
                var desc = $(this).attr('data-desc');
                var unit = $(this).attr('data-unit');
                var id = $(this).attr('data-id');
                openGroupModal(desc, unit, id);
            });

            $(document).on('click', '.groupEditShortcut', function() {
                var desc = $(this).attr('data-desc');
                var unit = $(this).attr('data-unit');
                var id = parseInt($(this).attr('data-id'));
                if (!desc) return;
                var row = null;
                if (typeof lastRows !== 'undefined' && lastRows) {
                    for (var i = 0; i < lastRows.length; i++) {
                        if (lastRows[i].id == id) { row = lastRows[i]; break; }
                    }
                }
                currentGroup = { desc: desc, unit: unit || '', archetypeId: id || 0, count: 0, members: [] };
                fillSharedFields(row);
                var t = document.getElementById('groupEditTitle');
                if (t) t.textContent = desc + (unit ? ' \u00b7 ' + unit : '');
                $('#groupEditModal').modal('show');
            });

       $('#groupModal').on('hidden.bs.modal', function() {
    document.getElementById('groupMembersBody').innerHTML = '';
    document.getElementById('groupSerialSearch').value = '';
    var statusEl = document.getElementById('groupStatusFilter'); if (statusEl) statusEl.value = 'all';
    resetGroupMetrics();
    currentGroup = { desc: '', unit: '', archetypeId: 0, count: 0, members: [] };
});

            $('#groupSaveSharedBtn').on('click', function() {
                if (!CAN_ADD_UNIT) return;
                if (!currentGroup.desc) return;

                var btn = document.getElementById('groupSaveSharedBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

                var fd = new FormData();
                fd.append('action', 'group_update');
                fd.append('group_description', currentGroup.desc);
                fd.append('group_unit', currentGroup.unit);
                fd.append('shared_project', document.getElementById('gSharedProject').value);
                fd.append('shared_item', document.getElementById('gSharedItem').value);
                fd.append('shared_cost', document.getElementById('gSharedCost').value);
                fd.append('shared_life', document.getElementById('gSharedLife').value);
                fd.append('shared_received', document.getElementById('gSharedReceived').value);
                fd.append('shared_inv_no', document.getElementById('gSharedInvNo').value);

                $.ajax({
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
                        if (!res.success) { showToast(res.error || res.message || 'Update failed.', 'danger'); return; }
                        showToast(res.message, 'success');
                        refreshCurrentGroup();
                        loadData(currentPage);
                        loadFilters();
                        $('#groupEditModal').modal('hide');
                    },
                    error: function() {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
                        showToast('Network error.', 'danger');
                    }
                });
            });

            $('#groupEditDetailsBtn').on('click', function() {
                if (!currentGroup.desc) return;
                var t = document.getElementById('groupEditTitle');
                if (t) t.textContent = currentGroup.desc + (currentGroup.unit ? ' \u00b7 ' + currentGroup.unit : '');
                $('#groupEditModal').modal('show');
            });

            $('#groupAddBtn').on('click', function() {
                if (!CAN_ADD_UNIT) return;
                if (!currentGroup.desc) return;

                var btn = document.getElementById('groupAddBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adding...';

                var fd = new FormData();
                fd.append('action', 'add_unit');
                fd.append('group_description', currentGroup.desc);
                fd.append('group_unit', currentGroup.unit);
                fd.append('archetype_id', currentGroup.archetypeId || 0);
                fd.append('txt_quantity', document.getElementById('ga_qty').value);
                fd.append('txt_serial', document.getElementById('ga_serial').value);
                fd.append('txt_date', document.getElementById('ga_date').value);
                fd.append('txt_assigned_to', document.getElementById('ga_assigned').value);
                fd.append('txt_remarks', document.getElementById('ga_remarks').value);

                $.ajax({
                    url: basePath + 'inventory_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success && res.unit) {
                            currentGroup.members.unshift(res.unit);
                            currentGroup.count++;
                            renderGroupMembers();
                            resetGroupAddForm();
                            showToast('Unit added successfully!', 'success');
                            loadData(currentPage);
                            loadFilters();
                        } else {
                            var el = document.getElementById('groupAlert');
                            el.innerHTML = '<div class="alert alert-danger">' + escHtml(res.error) + '</div>';
                            el.style.display = '';
                        }
                    },
                    error: function() {
                        var el = document.getElementById('groupAlert');
                        el.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
                        el.style.display = '';
                    },
                    complete: function() {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-plus"></i> Add Unit';
                    }
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
                var url = 'export.php?project=' + encodeURIComponent(f.project) +
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
            loadFilters();
            loadData(1);

        })();
        </script>

    </body>
</html>
<?php } ?>