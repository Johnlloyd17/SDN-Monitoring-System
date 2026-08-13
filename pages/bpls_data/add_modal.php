<!-- ========================= MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sm" style="width:500px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Province:</label>
                                <input name="txt_province" class="form-control input-sm" type="text" placeholder="Province" />
                            </div>
                            <div class="form-group">
                                <label>Congressional District:</label>
                                <input name="txt_district" class="form-control input-sm" type="text" placeholder="Congressional District" />
                            </div>
                            <div class="form-group">
                                <label>City/Municipality:</label>
                                <input name="txt_municipality" class="form-control input-sm" type="text" placeholder="City/Municipality" />
                            </div>
                            <div class="form-group">
                                <label>LGU Name:</label>
                                <input name="txt_lgu" class="form-control input-sm" type="text" placeholder="LGU Name" />
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
                                <label>Business Permit (BP) Y/N:</label>
                                <input name="txt_businessyn" class="form-control input-sm" type="text" placeholder="Business Permit Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit (BP) Status:</label>
                                <input name="txt_businessstatus" class="form-control input-sm" type="text" placeholder="Business Permit Status" />
                            </div>
                            <div class="form-group">
                                <label>Integration of Barangay Clearance (BC) Y/N:</label>
                                <input name="txt_barangayyn" class="form-control input-sm" type="text" placeholder="Barangay Clearance Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Integration of Barangay Clearance (BC) Status:</label>
                                <input name="txt_barangaystatus" class="form-control input-sm" type="text" placeholder="Barangay Clearance Status" />
                            </div>
                            <div class="form-group">
                                <label>Building Permit with Certificate of Occupancy (BPCO) Y/N:</label>
                                <input name="txt_buildingyn" class="form-control input-sm" type="text" placeholder="BPCO Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Building Permit with Certificate of Occupancy (BPCO) Status:</label>
                                <input name="txt_buildingstatus" class="form-control input-sm" type="text" placeholder="BPCO Status" />
                            </div>
                            <div class="form-group">
                                <label>Working Permit (WP) Y/N:</label>
                                <input name="txt_workingyn" class="form-control input-sm" type="text" placeholder="Working Permit Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Working Permit (WP) Status:</label>
                                <input name="txt_workingstatus" class="form-control input-sm" type="text" placeholder="Working Permit Status" />
                            </div>
                            <div class="form-group">
                                <label>Integration of BFP's Fire Safety Inspection Fee (FSIC):</label>
                                <input name="txt_bfpyn" class="form-control input-sm" type="text" placeholder="BFP FSIC Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit and Licensing System (BPLS) Y/N:</label>
                                <input name="txt_bplyn" class="form-control input-sm" type="text" placeholder="BPLS Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit and Licensing System (BPLS) Status:</label>
                                <input name="txt_bplstatus" class="form-control input-sm" type="text" placeholder="BPLS Status" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Community Tax Certificate (eCEDULA) Y/N:</label>
                                <input name="txt_ecedulayn" class="form-control input-sm" type="text" placeholder="eCEDULA Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Community Tax Certificate (eCEDULA) Status:</label>
                                <input name="txt_ecedulastatus" class="form-control input-sm" type="text" placeholder="eCEDULA Status" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Local Civil Registry (eLCR) Y/N:</label>
                                <input name="txt_elcryn" class="form-control input-sm" type="text" placeholder="eLCR Y/N" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Local Civil Registry (eLCR) Status:</label>
                                <input name="txt_elcrstatus" class="form-control input-sm" type="text" placeholder="eLCR Status" />
                            </div>
                            <div class="form-group">
                                <label>eNEWS Y/N:</label>
                                <input name="txt_enewsyn" class="form-control input-sm" type="text" placeholder="eNEWS Y/N" />
                            </div>
                            <div class="form-group">
                                <label>eNEWS Status:</label>
                                <input name="txt_enewsstatus" class="form-control input-sm" type="text" placeholder="eNEWS Status" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <input name="txt_remark" class="form-control input-sm" type="text" placeholder="Remarks" />
                            </div>
                          
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary btn-sm" name="btn_add" value="Add Item" />
                </div>
            </div>
        </div>
    </form>
</div>
