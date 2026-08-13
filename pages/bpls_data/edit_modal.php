<?php echo '
<div id="editModal' . $row['id'] . '" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-sm" style="width:500px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '" name="hidden_id" id="hidden_id"/>
                            
                            <div class="form-group">
                                <label>Province:</label>
                                <input name="txt_edit_province" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['province'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Congressional District:</label>
                                <input name="txt_edit_district" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['district'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>City/Municipality:</label>
                                <input name="txt_edit_municipality" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['municipality'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>LGU Name:</label>
                                <input name="txt_edit_lgu" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['lgu'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Class:</label>
                                <input name="txt_edit_class" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['class'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>System Provider:</label>
                                <input name="txt_edit_system" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['system'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Action:</label>
                                <input name="txt_edit_action" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit (BP) Y/N:</label>
                                <input name="txt_edit_businessyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['businessyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit (BP) Status:</label>
                                <input name="txt_edit_businessstatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['businessstatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Integration of Barangay Clearance (BC) Y/N:</label>
                                <input name="txt_edit_barangayyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['barangayyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Integration of Barangay Clearance (BC) Status:</label>
                                <input name="txt_edit_barangaystatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['barangaystatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Building Permit with Certificate of Occupancy (BPCO) Y/N:</label>
                                <input name="txt_edit_buildingyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['buildingyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Building Permit with Certificate of Occupancy (BPCO) Status:</label>
                                <input name="txt_edit_buildingstatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['buildingstatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Working Permit (WP) Y/N:</label>
                                <input name="txt_edit_workingyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['workingyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Working Permit (WP) Status:</label>
                                <input name="txt_edit_workingstatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['workingstatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Integration of BFPs Fire Safety Inspection Fee (FSIC):</label>
                                <input name="txt_edit_bfpyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['bfpyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit and Licensing System (BPLS) Y/N:</label>
                                <input name="txt_edit_bplyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['bplyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Business Permit and Licensing System (BPLS) Status:</label>
                                <input name="txt_edit_bplstatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['bplstatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Community Tax Certificate (eCEDULA) Y/N:</label>
                                <input name="txt_edit_ecedulayn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['ecedulayn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Community Tax Certificate (eCEDULA) Status:</label>
                                <input name="txt_edit_ecedulastatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['ecedulastatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Local Civil Registry (eLCR) Y/N:</label>
                                <input name="txt_edit_elcryn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['elcryn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Electronic Local Civil Registry (eLCR) Status:</label>
                                <input name="txt_edit_elcrstatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['elcrstatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>eNEWS Y/N:</label>
                                <input name="txt_edit_enewsyn" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['enewsyn'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>eNEWS Status:</label>
                                <input name="txt_edit_enewsstatus" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['enewsstatus'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            
                            <!-- New Remark Field -->
                            <div class="form-group">
                                <label>Remark:</label>
                                <textarea name="txt_edit_remark" class="form-control input-sm">' . htmlspecialchars($row['remark'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel"/>
                    <input type="submit" class="btn btn-primary btn-sm" name="btn_save" value="Save"/>
                </div>
            </div>
        </div>
    </form>
</div>'; ?>
