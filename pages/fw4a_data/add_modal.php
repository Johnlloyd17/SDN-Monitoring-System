<!-- ========================= ADD MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form id="addForm" enctype="multipart/form-data">
        <div class="modal-dialog modal-lg" style="width:750px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Access Point</h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div id="addAlert" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Locality:</label>
                                <input name="txt_locality" id="add_locality" class="form-control input-sm" type="text" placeholder="Locality" />
                            </div>
                            <div class="form-group">
                                <label>Barangay:</label>
                                <input name="txt_barangay" id="add_barangay" class="form-control input-sm" type="text" placeholder="Barangay" />
                            </div>
                            <div class="form-group">
                                <label>District:</label>
                                <input name="txt_district" class="form-control input-sm" type="text" placeholder="District" />
                            </div>
                            <div class="form-group">
                                <label>Transport Location:</label>
                                <input name="txt_transport_location" class="form-control input-sm" type="text" placeholder="Transport Location" />
                            </div>
                            <div class="form-group">
                                <label>Transport Type:</label>
                                <input name="txt_transport_type" class="form-control input-sm" type="text" placeholder="Transport Type" />
                            </div>
                            <div class="form-group">
                                <label>Locations:</label>
                                <input name="txt_locations" id="add_locations" class="form-control input-sm" type="text" placeholder="Locations" />
                            </div>
                            <div class="form-group">
                                <label>Site Type:</label>
                                <input name="txt_type" class="form-control input-sm" type="text" placeholder="Site Type" />
                            </div>
                            <div class="form-group">
                                <label>Site Code:</label>
                                <input name="txt_code" class="form-control input-sm" type="text" placeholder="Site Code" />
                            </div>
                            <div class="form-group">
                                <label>Nationwide ID:</label>
                                <input name="txt_nationwide_id" class="form-control input-sm" type="text" placeholder="Nationwide ID" />
                            </div>
                            <div class="form-group">
                                <label>Date of Activation:</label>
                                <input name="txt_date_of_activation" class="form-control input-sm" type="date" placeholder="Date of Activation" />
                            </div>
                            <div class="form-group">
                                <label>Current Date of Acceptance:</label>
                                <input name="txt_current_date_of_acceptance" class="form-control input-sm" type="date" placeholder="Current Date of Acceptance" />
                            </div>
                            <div class="form-group">
                                <label>Latitude:</label>
                                <input name="txt_latitude" class="form-control input-sm" type="text" placeholder="Latitude" />
                            </div>
                            <div class="form-group">
                                <label>Longitude:</label>
                                <input name="txt_longitude" class="form-control input-sm" type="text" placeholder="Longitude" />
                            </div>
                            <div class="form-group">
                                <label>Strategy:</label>
                                <input name="txt_strategy" class="form-control input-sm" type="text" placeholder="Strategy" />
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <input name="txt_status" class="form-control input-sm" type="text" placeholder="Status" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary btn-sm" id="addSubmitBtn" value="Add Item" />
                </div>
            </div>
        </div>
    </form>
</div>
