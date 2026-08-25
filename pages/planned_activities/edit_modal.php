<div id="editModal" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Activity</h4>
                </div>
                <div class="modal-body modal-tabs">
                    <input type="hidden" name="hidden_id" id="edit_hidden_id"/>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#plan-edit-details" data-toggle="tab"><i class="fa fa-info-circle"></i> Activity Details</a></li>
                        <li><a href="#plan-edit-location" data-toggle="tab"><i class="fa fa-map-marker"></i> Location &amp; Agency</a></li>
                        <li><a href="#plan-edit-participants" data-toggle="tab"><i class="fa fa-users"></i> Participants &amp; MOVs</a></li>
                    </ul>
                    <div style="margin-bottom:12px;font-size:12px;color:#777;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Fields marked with <span class="req-asterisk">*</span> are required.</div>

                    <div class="tab-content">
                        <!-- Tab 1: Activity Details -->
                        <div class="tab-pane active" id="plan-edit-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Start Date: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_start" id="edit_start" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>End Date: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_end" id="edit_end" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Bureau: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_project" id="edit_project" class="form-control input-sm" type="text" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Project: </label>
                                        <input name="txt_edit_subproject" id="edit_subproject" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Indicator: </label>
                                        <input name="txt_edit_indicator" id="edit_indicator" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Activity: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_activity" id="edit_activity" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Training: </label>
                                        <input name="txt_edit_training" id="edit_training" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Location & Agency -->
                        <div class="tab-pane" id="plan-edit-location">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipality: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_municipality" id="edit_municipality" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay: </label>
                                        <input name="txt_edit_barangay" id="edit_barangay" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>District: </label>
                                        <input name="txt_edit_district" id="edit_district" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Agency: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_agency" id="edit_agency" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Mode: </label>
                                        <input name="txt_edit_mode" id="edit_mode" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Sector: </label>
                                        <input name="txt_edit_sector" id="edit_sector" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Person: </label>
                                        <input name="txt_edit_person" id="edit_person" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Resource: </label>
                                        <input name="txt_edit_resource" id="edit_resource" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Participants & MOVs -->
                        <div class="tab-pane" id="plan-edit-participants">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Participants: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_participants" id="edit_participants" class="form-control input-sm" type="text" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Completers: </label>
                                        <input name="txt_edit_completers" id="edit_completers" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Male: </label>
                                        <input name="txt_edit_male" id="edit_male" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Female: </label>
                                        <input name="txt_edit_female" id="edit_female" class="form-control input-sm" type="text" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Approved: </label>
                                        <input name="txt_edit_approved" id="edit_approved" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>MOV: </label>
                                        <input name="txt_edit_mov" id="edit_mov" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks: </label>
                                        <input name="txt_edit_remarks" id="edit_remarks" class="form-control input-sm" type="text" />
                                    </div>
                                    <div class="form-group">
                                        <label>Type: </label>
                                        <input name="txt_edit_type" id="edit_type" class="form-control input-sm" type="text" />
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
