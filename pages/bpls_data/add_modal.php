<!-- ========================= MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Item</h4>
                </div>
                <div class="modal-body modal-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#bpls-tab-general" data-toggle="tab"><i class="fa fa-info-circle"></i> General Info</a></li>
                        <li><a href="#bpls-tab-permits" data-toggle="tab"><i class="fa fa-stamp"></i> Permits &amp; Systems</a></li>
                        <li><a href="#bpls-tab-digital" data-toggle="tab"><i class="fa fa-laptop"></i> Digital Services</a></li>
                    </ul>
                    <div style="margin-bottom:12px;font-size:12px;color:#777;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Fields marked with <span class="req-asterisk">*</span> are required.</div>

                    <div class="tab-content">
                        <!-- Tab 1: General Info -->
                        <div class="tab-pane active" id="bpls-tab-general">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Province:</label>
                                        <input name="txt_province" class="form-control input-sm" type="text" placeholder="Province" />
                                    </div>
                                    <div class="form-group">
                                        <label>Congressional District:</label>
                                        <input name="txt_district" class="form-control input-sm" type="text" placeholder="Congressional District" />
                                    </div>
                                    <div class="form-group">
                                        <label>City/Municipality: <span class="req-asterisk">*</span></label>
                                        <input name="txt_municipality" class="form-control input-sm" type="text" placeholder="City/Municipality" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>LGU Name: <span class="req-asterisk">*</span></label>
                                        <input name="txt_lgu" class="form-control input-sm" type="text" placeholder="LGU Name" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Class:</label>
                                        <input name="txt_class" class="form-control input-sm" type="text" placeholder="Class" />
                                    </div>
                                    <div class="form-group">
                                        <label>System Provider:</label>
                                        <input name="txt_system" class="form-control input-sm" type="text" placeholder="System Provider" />
                                    </div>
                                    <div class="form-group">
                                        <label>Action:</label>
                                        <input name="txt_action" class="form-control input-sm" type="text" placeholder="Action" />
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks:</label>
                                        <input name="txt_remark" class="form-control input-sm" type="text" placeholder="Remarks" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Permits & Systems -->
                        <div class="tab-pane" id="bpls-tab-permits">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Business Permit (BP) Y/N: <span class="req-asterisk">*</span></label>
                                        <input name="txt_businessyn" class="form-control input-sm" type="text" placeholder="Y/N" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Business Permit (BP) Status:</label>
                                        <input name="txt_businessstatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay Clearance (BC) Y/N:</label>
                                        <input name="txt_barangayyn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay Clearance (BC) Status:</label>
                                        <input name="txt_barangaystatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                    <div class="form-group">
                                        <label>Building Permit with CO (BPCO) Y/N:</label>
                                        <input name="txt_buildingyn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>Building Permit with CO (BPCO) Status:</label>
                                        <input name="txt_buildingstatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Working Permit (WP) Y/N:</label>
                                        <input name="txt_workingyn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>Working Permit (WP) Status:</label>
                                        <input name="txt_workingstatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                    <div class="form-group">
                                        <label>BFP Fire Safety Inspection Fee (FSIC):</label>
                                        <input name="txt_bfpyn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>BPLS Y/N: <span class="req-asterisk">*</span></label>
                                        <input name="txt_bplyn" class="form-control input-sm" type="text" placeholder="Y/N" required />
                                    </div>
                                    <div class="form-group">
                                        <label>BPLS Status:</label>
                                        <input name="txt_bplstatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Digital Services -->
                        <div class="tab-pane" id="bpls-tab-digital">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>eCEDULA Y/N:</label>
                                        <input name="txt_ecedulayn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>eCEDULA Status:</label>
                                        <input name="txt_ecedulastatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                    <div class="form-group">
                                        <label>eLCR Y/N:</label>
                                        <input name="txt_elcryn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>eLCR Status:</label>
                                        <input name="txt_elcrstatus" class="form-control input-sm" type="text" placeholder="Status" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>eNEWS Y/N:</label>
                                        <input name="txt_enewsyn" class="form-control input-sm" type="text" placeholder="Y/N" />
                                    </div>
                                    <div class="form-group">
                                        <label>eNEWS Status:</label>
                                        <input name="txt_enewsstatus" class="form-control input-sm" type="text" placeholder="Status" />
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
