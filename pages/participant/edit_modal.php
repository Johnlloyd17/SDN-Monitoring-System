<div id="editModal" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Participant</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="hidden_id" id="edit_hidden_id"/>
                            <div class="form-group">
                                <label>Start Date:</label>
                                <input name="txt_edit_start" id="edit_start" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>End Date:</label>
                                <input name="txt_edit_end" id="edit_end" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Activity Name:</label>
                                <input name="txt_edit_activity" id="edit_activity" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Indicators:</label>
                                <input name="txt_edit_indicator" id="edit_indicator" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Fullname:</label>
                                <input name="txt_edit_fullname" id="edit_fullname" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Sex:</label>
                                <input name="txt_edit_sex" id="edit_sex" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Contact:</label>
                                <input name="txt_edit_contact" id="edit_contact" class="form-control input-sm" type="text" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address:</label>
                                <input name="txt_edit_email" id="edit_email" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Mode of Implementation:</label>
                                <input name="txt_edit_mode" id="edit_mode" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Agency:</label>
                                <input name="txt_edit_agency" id="edit_agency" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Target Sector:</label>
                                <input name="txt_edit_sector" id="edit_sector" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Project:</label>
                                <input name="txt_edit_project" id="edit_project" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Responsible Person:</label>
                                <input name="txt_edit_person" id="edit_person" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <input name="txt_edit_remarks" id="edit_remarks" class="form-control input-sm" type="text" />
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
</div>
