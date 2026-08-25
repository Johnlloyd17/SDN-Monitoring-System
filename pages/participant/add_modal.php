<!-- ========================= MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Participant</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date:</label>
                                <input name="txt_start" class="form-control input-sm" type="date" placeholder="Start Date" />
                            </div>
                            <div class="form-group">
                                <label>End Date:</label>
                                <input name="txt_end" class="form-control input-sm" type="date" placeholder="End Date" />
                            </div>
                            <div class="form-group">
                                <label>Activity Name:</label>
                                <input name="txt_activity" class="form-control input-sm" type="text" placeholder="Activity Name" />
                            </div>
                            <div class="form-group">
                                <label>Indicators:</label>
                                <input name="txt_indicator" class="form-control input-sm" type="text" placeholder="Indicators" />
                            </div>
                            <div class="form-group">
                                <label>Fullname:</label>
                                <input name="txt_fullname" class="form-control input-sm" type="text" placeholder="Fullname" />
                            </div>
                            <div class="form-group">
                                <label>Sex:</label>
                                <input name="txt_sex" class="form-control input-sm" type="text" placeholder="Sex" />
                            </div>
                            <div class="form-group">
                                <label>Contact:</label>
                                <input name="txt_contact" class="form-control input-sm" type="text" placeholder="Contact" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address:</label>
                                <input name="txt_email" class="form-control input-sm" type="email" placeholder="Email Address" />
                            </div>
                            <div class="form-group">
                                <label>Mode of Implementation:</label>
                                <input name="txt_mode" class="form-control input-sm" type="text" placeholder="Mode of Implementation" />
                            </div>
                            <div class="form-group">
                                <label>Agency:</label>
                                <input name="txt_agency" class="form-control input-sm" type="text" placeholder="Agency" />
                            </div>
                            <div class="form-group">
                                <label>Target Sector:</label>
                                <input name="txt_sector" class="form-control input-sm" type="text" placeholder="Target Sector" />
                            </div>
                            <div class="form-group">
                                <label>Project:</label>
                                <input name="txt_project" class="form-control input-sm" type="text" placeholder="Project" />
                            </div>
                            <div class="form-group">
                                <label>Responsible Person:</label>
                                <input name="txt_person" class="form-control input-sm" type="text" placeholder="Responsible Person" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <input name="txt_remarks" class="form-control input-sm" type="text" placeholder="Remarks" />
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
