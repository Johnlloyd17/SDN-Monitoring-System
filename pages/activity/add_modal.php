<!-- ========================= MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Activity</h4>
                </div>
                <div class="modal-body modal-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#act-tab-details" data-toggle="tab"><i class="fa fa-info-circle"></i> Activity Details</a></li>
                        <li><a href="#act-tab-location" data-toggle="tab"><i class="fa fa-map-marker"></i> Location &amp; Agency</a></li>
                        <li><a href="#act-tab-participants" data-toggle="tab"><i class="fa fa-users"></i> Participants &amp; MOVs</a></li>
                    </ul>
                    <div style="margin-bottom:12px;font-size:12px;color:#777;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Fields marked with <span class="req-asterisk">*</span> are required.</div>

                    <div class="tab-content">
                        <!-- Tab 1: Activity Details -->
                        <div class="tab-pane active" id="act-tab-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Start Date: <span class="req-asterisk">*</span></label>
                                        <input name="txt_start" class="form-control input-sm" type="date" placeholder="Start Date" required />
                                    </div>
                                    <div class="form-group">
                                        <label>End Date: <span class="req-asterisk">*</span></label>
                                        <input name="txt_end" class="form-control input-sm" type="date" placeholder="End Date" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Bureau: <span class="req-asterisk">*</span></label>
                                        <input name="txt_project" class="form-control input-sm" type="text" placeholder="Bureau" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Project:</label>
                                        <input name="txt_subproject" class="form-control input-sm" type="text" placeholder="Project" />
                                    </div>
                                    <div class="form-group">
                                        <label>Indicator:</label>
                                        <input name="txt_indicator" class="form-control input-sm" type="text" placeholder="Indicator" />
                                    </div>
                                    <div class="form-group">
                                        <label>Activity Name: <span class="req-asterisk">*</span></label>
                                        <input name="txt_activity" class="form-control input-sm" type="text" placeholder="Activity Name" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Training Venue:</label>
                                        <input name="txt_training" class="form-control input-sm" type="text" placeholder="Training Venue" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Location & Agency -->
                        <div class="tab-pane" id="act-tab-location">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipality/City: <span class="req-asterisk">*</span></label>
                                        <input name="txt_municipality" class="form-control input-sm" type="text" placeholder="Municipality/City" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay:</label>
                                        <input name="txt_barangay" class="form-control input-sm" type="text" placeholder="Barangay" />
                                    </div>
                                    <div class="form-group">
                                        <label>District:</label>
                                        <input name="txt_district" class="form-control input-sm" type="text" placeholder="District" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Requesting Agency: <span class="req-asterisk">*</span></label>
                                        <input name="txt_agency" class="form-control input-sm" type="text" placeholder="Requesting Agency" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Mode of Implementation:</label>
                                        <input name="txt_mode" class="form-control input-sm" type="text" placeholder="Mode of Implementation" />
                                    </div>
                                    <div class="form-group">
                                        <label>Target Sector:</label>
                                        <input name="txt_sector" class="form-control input-sm" type="text" placeholder="Target Sector" />
                                    </div>
                                    <div class="form-group">
                                        <label>Responsible Person:</label>
                                        <input name="txt_person" class="form-control input-sm" type="text" placeholder="Responsible Person" />
                                    </div>
                                    <div class="form-group">
                                        <label>Name of Resource Person:</label>
                                        <input name="txt_resource" class="form-control input-sm" type="text" placeholder="Name of Resource Person" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Participants & MOVs -->
                        <div class="tab-pane" id="act-tab-participants">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. of Participants: <span class="req-asterisk">*</span></label>
                                        <input name="txt_participants" class="form-control input-sm" type="number" placeholder="No. of Participants" required />
                                    </div>
                                    <div class="form-group">
                                        <label>No. of Completers:</label>
                                        <input name="txt_completers" class="form-control input-sm" type="number" placeholder="No. of Completers" />
                                    </div>
                                    <div class="form-group">
                                        <label>Male Participants:</label>
                                        <input name="txt_male" class="form-control input-sm" type="number" placeholder="Male" />
                                    </div>
                                    <div class="form-group">
                                        <label>Female Participants:</label>
                                        <input name="txt_female" class="form-control input-sm" type="number" placeholder="Female" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Approved Activity Design:</label>
                                        <input name="txt_approved" class="form-control input-sm" type="text" placeholder="Approved Activity Design" />
                                    </div>
                                    <div class="form-group">
                                        <label>Link to MOVs:</label>
                                        <input name="txt_mov" class="form-control input-sm" type="url" placeholder="Link to MOVs" />
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks:</label>
                                        <textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" name="btn_add" value="Add Item" />
                </div>
            </div>
        </div>
    </form>
</div>
