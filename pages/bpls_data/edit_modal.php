<div id="editModal" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Item</h4>
                </div>
                <div class="modal-body modal-tabs">
                    <input type="hidden" value="" name="hidden_id" id="edit_hidden_id"/>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#bpls-edit-general" data-toggle="tab"><i class="fa fa-info-circle"></i> General Info</a></li>
                        <li><a href="#bpls-edit-permits" data-toggle="tab"><i class="fa fa-stamp"></i> Permits &amp; Systems</a></li>
                        <li><a href="#bpls-edit-digital" data-toggle="tab"><i class="fa fa-laptop"></i> Digital Services</a></li>
                    </ul>
                    <div style="margin-bottom:12px;font-size:12px;color:#777;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Fields marked with <span class="req-asterisk">*</span> are required.</div>

                    <div class="tab-content">
                        <!-- Tab 1: General Info -->
                        <div class="tab-pane active" id="bpls-edit-general">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Province:</label>
                                        <input name="txt_edit_province" id="edit_province" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Congressional District:</label>
                                        <input name="txt_edit_district" id="edit_district" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>City/Municipality: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_municipality" id="edit_municipality" class="form-control input-sm" type="text" value="" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>LGU Name: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_lgu" id="edit_lgu" class="form-control input-sm" type="text" value="" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Class:</label>
                                        <input name="txt_edit_class" id="edit_class" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>System Provider:</label>
                                        <input name="txt_edit_system" id="edit_system" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Action:</label>
                                        <input name="txt_edit_action" id="edit_action" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Remark:</label>
                                        <textarea name="txt_edit_remark" id="edit_remark" class="form-control input-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Permits & Systems -->
                        <div class="tab-pane" id="bpls-edit-permits">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Business Permit (BP) Y/N: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_businessyn" id="edit_businessyn" class="form-control input-sm" type="text" value="" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Business Permit (BP) Status:</label>
                                        <input name="txt_edit_businessstatus" id="edit_businessstatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay Clearance (BC) Y/N:</label>
                                        <input name="txt_edit_barangayyn" id="edit_barangayyn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay Clearance (BC) Status:</label>
                                        <input name="txt_edit_barangaystatus" id="edit_barangaystatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Building Permit with CO (BPCO) Y/N:</label>
                                        <input name="txt_edit_buildingyn" id="edit_buildingyn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Building Permit with CO (BPCO) Status:</label>
                                        <input name="txt_edit_buildingstatus" id="edit_buildingstatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Working Permit (WP) Y/N:</label>
                                        <input name="txt_edit_workingyn" id="edit_workingyn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>Working Permit (WP) Status:</label>
                                        <input name="txt_edit_workingstatus" id="edit_workingstatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>BFP Fire Safety Inspection Fee (FSIC):</label>
                                        <input name="txt_edit_bfpyn" id="edit_bfpyn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>BPLS Y/N: <span class="req-asterisk">*</span></label>
                                        <input name="txt_edit_bplyn" id="edit_bplyn" class="form-control input-sm" type="text" value="" required />
                                    </div>
                                    <div class="form-group">
                                        <label>BPLS Status:</label>
                                        <input name="txt_edit_bplstatus" id="edit_bplstatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Digital Services -->
                        <div class="tab-pane" id="bpls-edit-digital">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>eCEDULA Y/N:</label>
                                        <input name="txt_edit_ecedulayn" id="edit_ecedulayn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>eCEDULA Status:</label>
                                        <input name="txt_edit_ecedulastatus" id="edit_ecedulastatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>eLCR Y/N:</label>
                                        <input name="txt_edit_elcryn" id="edit_elcryn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>eLCR Status:</label>
                                        <input name="txt_edit_elcrstatus" id="edit_elcrstatus" class="form-control input-sm" type="text" value="" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>eNEWS Y/N:</label>
                                        <input name="txt_edit_enewsyn" id="edit_enewsyn" class="form-control input-sm" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label>eNEWS Status:</label>
                                        <input name="txt_edit_enewsstatus" id="edit_enewsstatus" class="form-control input-sm" type="text" value="" />
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
