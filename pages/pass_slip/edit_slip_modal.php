<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!-- Edit Pass Slip Modal (full form, mirrors Create) -->
<div class="modal fade" id="editPassSlipModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="editPassSlipForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil"></i> Edit Pass Slip - <span id="editSlipNo"></span></h4>
                </div>
                <div class="modal-body">
                    <!-- Pass Slip Number (read-only) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pass Slip No. <span class="text-danger">*</span></label>
                                <input type="text" name="pass_slip_no" id="editPassSlipNo" class="form-control" readonly
                                    placeholder="Pass Slip No.">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-boxes"></i> Item Details</h5>

                    <!-- Inventory Search (read-only lookup, fills active row) -->
                    <div id="editInvSearchWrap" class="inv-search-wrap" style="position: relative; margin-bottom: 10px; max-width: 480px;">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" id="editInvSearchInput" class="form-control inv-search-input" placeholder="Search inventory by description or serial no. (min 2 chars)...">
                        </div>
                        <div id="editInvSearchResults" class="inv-search-results" style="position: absolute; z-index: 1050; top: 100%; left: 0; right: 0; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.2); display: none; max-height: 260px; overflow-y: auto;"></div>
                    </div>

                    <!-- Dynamic Items Table -->
                    <div class="table-responsive items-table-scroll">
                        <table class="table table-bordered" id="editItemsTable" style="margin-bottom: 5px;">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Description <span class="text-danger">*</span></th>
                                    <th style="width: 14%;">Serial No.</th>
                                    <th style="width: 8%;">Qty</th>
                                    <th style="width: 8%;">Unit</th>
                                    <th style="width: 16%;">Pull-Out Date <span class="text-danger">*</span></th>
                                    <th style="width: 16%;">Returned Date</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="editItemsBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="items-toolbar" style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                        <button type="button" class="btn btn-success btn-sm" id="editAddRowBtn"><i class="fa fa-plus"></i> Add Item</button>
                        <span class="text-muted" id="editItemCount" style="font-size: 12px;">0 Items</span>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" id="editPurpose" class="form-control" rows="2" required placeholder="Purpose of borrowing..."></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-signature"></i> Pull-Out Authorization</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Requested By <span class="text-danger">*</span></label>
                                <input type="text" name="requested_by_out" id="editRequestedBy" class="form-control" required placeholder="Name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inspected By <span class="text-danger">*</span></label>
                                <input type="text" name="inspected_by_out" id="editInspectedBy" class="form-control" required placeholder="Name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Approved By <span class="text-danger">*</span></label>
                                <input type="text" name="approved_by_out" id="editApprovedBy" class="form-control" required placeholder="Name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" id="editRemarks" class="form-control" rows="2" placeholder="Additional remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_pass_slip" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>