<!-- ========================= ADD MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form id="addForm" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Access Point</h4>
                </div>
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div id="addAlert" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Item No.:</label>
                                <input name="txt_item_no" class="form-control input-sm" type="number" min="0" placeholder="Item No." />
                            </div>
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
                                <label>Site Locations:</label>
                                <input name="txt_site_locations" id="add_locations" class="form-control input-sm" type="text" placeholder="Site Locations" />
                            </div>
                            <div class="form-group">
                                <label>Transfer/New Locations:</label>
                                <input name="txt_transfer_new_locations" class="form-control input-sm" type="text" placeholder="Transfer/New Locations" />
                            </div>
                            <div class="form-group">
                                <label>Site Code:</label>
                                <input name="txt_site_code" class="form-control input-sm" type="text" placeholder="Site Code" />
                            </div>
                            <div class="form-group">
                                <label>Nationwide ID:</label>
                                <input name="txt_nationwide_id" class="form-control input-sm" type="text" placeholder="Nationwide ID" />
                            </div>
                            <div class="form-group">
                                <label>Site Type:</label>
                                <input name="txt_site_type" class="form-control input-sm" type="text" placeholder="Site Type" />
                            </div>
                            <div class="form-group">
                                <label>Date of Activation:</label>
                                <input name="txt_date_of_activation" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Current Date of Acceptance:</label>
                                <input name="txt_current_date_of_acceptance" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Latitude:</label>
                                <input name="txt_latitude" class="form-control input-sm" type="number" step="0.0000001" min="-90" max="90" placeholder="Latitude" />
                            </div>
                            <div class="form-group">
                                <label>Longitude:</label>
                                <input name="txt_longitude" class="form-control input-sm" type="number" step="0.0000001" min="-180" max="180" placeholder="Longitude" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Procurement Initiative:</label>
                                <select name="txt_procurement_initiative" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Centrally Procured">Centrally Procured</option>
                                    <option value="Regional Procured">Regional Procured</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Installation Type:</label>
                                <select name="txt_installation_type" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Region Initiated">Region Initiated</option>
                                    <option value="Manage Service">Manage Service</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>UAT:</label>
                                <br>
                                <input type="hidden" name="uat" value="0" />
                                <input type="checkbox" name="uat" id="add_uat" value="1" />
                            </div>
                            <div class="form-group">
                                <label>Conforme:</label>
                                <br>
                                <input type="hidden" name="conforme" value="0" />
                                <input type="checkbox" name="conforme" id="add_conforme" value="1" />
                            </div>
                            <div class="form-group">
                                <label>Strategy:</label>
                                <input name="txt_strategy" class="form-control input-sm" type="text" placeholder="Strategy" />
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <select name="txt_status" class="form-control input-sm">
                                    <option value="">-- Select Status --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Link Type:</label>
                                <input name="txt_link_type" class="form-control input-sm" type="text" placeholder="Link Type" />
                            </div>
                            <div class="form-group">
                                <label>Replacement Form File:</label>
                                <input name="txt_replacement_form_file" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>Conforme File:</label>
                                <input name="txt_conforme_file" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>UAT File:</label>
                                <input name="txt_uat_file" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>Additional UAT:</label>
                                <input name="txt_additional_uat" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>Name (Site Coordinators):</label>
                                <input name="txt_site_coordinator_name" class="form-control input-sm" type="text" placeholder="Site Coordinator Name" />
                            </div>
                            <div class="form-group">
                                <label>Contact Details:</label>
                                <input name="txt_contact_details" class="form-control input-sm" type="text" placeholder="Contact Details" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" id="addSubmitBtn" value="Add Item" />
                </div>
            </div>
        </div>
    </form>
</div>