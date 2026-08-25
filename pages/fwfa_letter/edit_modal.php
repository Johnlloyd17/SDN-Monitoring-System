<div id="editModal" class="modal fade">
    <form method="post" id="editForm">
        <div class="modal-dialog modal-sdm-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit FWFA Letter</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="hidden_id" id="edit_hidden_id"/>
                            <div class="form-group">
                                <label>Locality:</label>
                                <input name="txt_edit_locality" id="edit_locality" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Barangay:</label>
                                <input name="txt_edit_barangay" id="edit_barangay" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>District:</label>
                                <input name="txt_edit_district" id="edit_district" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Location Name:</label>
                                <input name="txt_edit_location" id="edit_location" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Date Requested:</label>
                                <input name="txt_edit_date" id="edit_date" class="form-control input-sm" type="date" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Year:</label>
                                <input name="txt_edit_year" id="edit_year" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Type:</label>
                                <input name="txt_edit_type" id="edit_type" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <input name="txt_edit_status" id="edit_status" class="form-control input-sm" type="text" />
                            </div>
                            <div class="form-group">
                                <label>Accomplished Date:</label>
                                <input name="txt_edit_accomplished" id="edit_accomplished" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_edit_remarks" id="edit_remarks" class="form-control input-sm"></textarea>
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
