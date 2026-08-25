<div id="editModal" class="modal fade">
    <form method="post" id="editForm">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Participant</h4>
                </div>
                <div class="modal-body modal-tabs">
                    <input type="hidden" name="hidden_id" id="edit_hidden_id"/>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#t4e-edit-location" data-toggle="tab"><i class="fa fa-map-marker"></i> Location</a></li>
                        <li><a href="#t4e-edit-center" data-toggle="tab"><i class="fa fa-building"></i> Center Info</a></li>
                        <li><a href="#t4e-edit-manager" data-toggle="tab"><i class="fa fa-user"></i> Manager</a></li>
                        <li><a href="#t4e-edit-assistant" data-toggle="tab"><i class="fa fa-user"></i> Asst. Manager</a></li>
                        <li><a href="#t4e-edit-operations" data-toggle="tab"><i class="fa fa-cogs"></i> Operations</a></li>
                        <li><a href="#t4e-edit-donation" data-toggle="tab"><i class="fa fa-gift"></i> Donation &amp; Access</a></li>
                    </ul>
                    <div style="margin-bottom:12px;font-size:12px;color:#777;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Fields marked with <span class="req-asterisk">*</span> are required.</div>

                    <div class="tab-content">
                        <!-- Tab: Location -->
                        <div class="tab-pane active" id="t4e-edit-location">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Region: </label>
                                        <input name="txt_edit_region" id="edit_region" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Province: </label>
                                        <input name="txt_edit_province" id="edit_province" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>District: </label>
                                        <input name="txt_edit_district" id="edit_district" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipality/City: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_municipality" id="edit_municipality" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay: </label>
                                        <input name="txt_edit_barangay" id="edit_barangay" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Street Address: </label>
                                        <input name="txt_edit_street" id="edit_street" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Center Info -->
                        <div class="tab-pane" id="t4e-edit-center">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Specific Center Location: </label>
                                        <input name="txt_edit_location" id="edit_location" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Center Name: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_cname" id="edit_cname" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Host: </label>
                                        <input name="txt_edit_host" id="edit_host" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Category: </label>
                                        <input name="txt_edit_category" id="edit_category" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Longitude: </label>
                                        <input name="txt_edit_longitude" id="edit_longitude" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Latitude: </label>
                                        <input name="txt_edit_latitude" id="edit_latitude" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Center Manager -->
                        <div class="tab-pane" id="t4e-edit-manager">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Center Managers Name: </label>
                                        <input name="txt_edit_cmanager" id="edit_cmanager" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Email: </label>
                                        <input name="txt_edit_cemail" id="edit_cemail" class="form-control input-sm" type="email" />
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile: </label>
                                        <input name="txt_edit_cmobile" id="edit_cmobile" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Landline: </label>
                                        <input name="txt_edit_clandline" id="edit_clandline" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Gender: </label>
                                        <input name="txt_edit_cgender" id="edit_cgender" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Assistant Manager -->
                        <div class="tab-pane" id="t4e-edit-assistant">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Assistant Center Managers Name: </label>
                                        <input name="txt_edit_amanager" id="edit_amanager" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Email: </label>
                                        <input name="txt_edit_aemail" id="edit_aemail" class="form-control input-sm" type="email" />
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile: </label>
                                        <input name="txt_edit_amobile" id="edit_amobile" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Landline: </label>
                                        <input name="txt_edit_alandline" id="edit_alandline" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Gender: </label>
                                        <input name="txt_edit_agender" id="edit_agender" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Operations -->
                        <div class="tab-pane" id="t4e-edit-operations">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Launching: </label>
                                        <input name="txt_edit_launch" id="edit_launch" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Platform Registration: </label>
                                        <input name="txt_edit_registration" id="edit_registration" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Operational Status: </label>
                                        <input name="txt_edit_operation" id="edit_operation" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Date Last Visited: </label>
                                        <input name="txt_edit_visited" id="edit_visited" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Status: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_status" id="edit_status" class="form-control input-sm" type="text" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label># of Functional Desktop Units: </label>
                                        <input name="txt_edit_desktop" id="edit_desktop" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label># of Functional Laptop Units: </label>
                                        <input name="txt_edit_laptop" id="edit_laptop" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label># of Functional Printer Units: </label>
                                        <input name="txt_edit_printer" id="edit_printer" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label># of Functional Scanner Units: </label>
                                        <input name="txt_edit_scanner" id="edit_scanner" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Types of Network: </label>
                                        <input name="txt_edit_network" id="edit_network" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Internet Connectivity: </label>
                                        <input name="txt_edit_connectivity" id="edit_connectivity" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Internet Speed: </label>
                                        <input name="txt_edit_speed" id="edit_speed" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>CMT (# of pax) Male: </label>
                                        <input name="txt_edit_cmtmale" id="edit_cmtmale" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>CMT (# of pax) Female: </label>
                                        <input name="txt_edit_cmtfemale" id="edit_cmtfemale" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Start Date of Training: </label>
                                        <input name="txt_edit_straining" id="edit_straining" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>End Date of Training: </label>
                                        <input name="txt_edit_etraining" id="edit_etraining" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Signing: </label>
                                        <input name="txt_edit_signing" id="edit_signing" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Partner: </label>
                                        <input name="txt_edit_partner" id="edit_partner" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Donation & Access -->
                        <div class="tab-pane" id="t4e-edit-donation">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Expiration: </label>
                                        <input name="txt_edit_expiration" id="edit_expiration" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Type of Donation: </label>
                                        <input name="txt_edit_donation" id="edit_donation" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Donation: </label>
                                        <input name="txt_edit_datedonation" id="edit_datedonation" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>TCMS: </label>
                                        <input name="txt_edit_tcms" id="edit_tcms" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Key: </label>
                                        <input name="txt_edit_key_one" id="edit_key_one" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Identifier: </label>
                                        <input name="txt_edit_identifier" id="edit_identifier" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel"/>
                    <input type="submit" class="btn btn-primary" name="btn_save" value="Save"/>
                </div>
            </div>
        </div>
    </form>
</div>
