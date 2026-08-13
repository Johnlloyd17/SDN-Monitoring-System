<!-- ========================= MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sm" style="width:500px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Activity</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label>Start Date:</label>
                                <input name="txt_start" class="form-control input-sm" type="date" placeholder="Start Date"  required />
                            </div>
                            <div class="form-group">
                                <label>End Date:</label>
                                <input name="txt_end" class="form-control input-sm" type="date" placeholder="End Date" required />
                            </div>
                            <div class="form-group">
                                <label>Bureau:</label>
                                <input name="txt_project" class="form-control input-sm" type="text" placeholder="Bureau" required />
                            </div>
                            <div class="form-group">
                                <label>Project:</label>
                                <input name="txt_subproject" class="form-control input-sm" type="text" placeholder="Project"  />
                            </div>
                            <div class="form-group">
                                <label>Indicator:</label>
                                <input name="txt_indicator" class="form-control input-sm" type="text" placeholder="Indicator"  />
                            </div>
                            <div class="form-group">
                                <label>Activity Name:</label>
                                <input name="txt_activity" class="form-control input-sm" type="text" placeholder="Activity Name"  />
                            </div>
                            <div class="form-group">
                                <label>Training Venue:</label>
                                <input name="txt_training" class="form-control input-sm" type="text" placeholder="Training Venue"  />
                            </div>
                            <div class="form-group">
                                <label>Municipality/City:</label>
                                <input name="txt_municipality" class="form-control input-sm" type="text" placeholder="Municipality/City"  />
                            </div>
                            <div class="form-group">
                                <label>Barangay:</label>
                                <input name="txt_barangay" class="form-control input-sm" type="text" placeholder="Barangay"  />
                            </div>
                            <div class="form-group">
                                <label>District:</label>
                                <input name="txt_district" class="form-control input-sm" type="text" placeholder="District"  />
                            </div>
                            <div class="form-group">
                                <label>Requesting Agency:</label>
                                <input name="txt_agency" class="form-control input-sm" type="text" placeholder="Requesting Agency"  />
                            </div>
                            <div class="form-group">
                                <label>Mode of Implementation:</label>
                                <input name="txt_mode" class="form-control input-sm" type="text" placeholder="Mode of Implementation"  />
                            </div>
                            <div class="form-group">
                                <label>Target Sector:</label>
                                <input name="txt_sector" class="form-control input-sm" type="text" placeholder="Target Sector"  />
                            </div>
                            <div class="form-group">
                                <label>Responsible Person:</label>
                                <input name="txt_person" class="form-control input-sm" type="text" placeholder="Responsible Person"  />
                            </div>
                            <div class="form-group">
                                <label>Name of Resource Person:</label>
                                <input name="txt_resource" class="form-control input-sm" type="text" placeholder="Name of Resource Person"  />
                            </div>
                            <div class="form-group">
                                <label>No. of Participants:</label>
                                <input name="txt_participants" class="form-control input-sm" type="number" placeholder="No. of Participants"  />
                            </div>
                            <div class="form-group">
                                <label>No. of Completers:</label>
                                <input name="completers" class="form-control input-sm" type="number" placeholder="No. of Completers"  />
                            </div>
                            <div class="form-group">
                                <label>Male Participants:</label>
                                <input name="txt_male" class="form-control input-sm" type="number" placeholder="Male"  />
                            </div>
                            <div class="form-group">
                                <label>Female Participants:</label>
                                <input name="txt_female" class="form-control input-sm" type="number" placeholder="Female"  />
                            </div>
                            <div class="form-group">
                                <label>Approved Activity Design:</label>
                                <input name="txt_approved" class="form-control input-sm" type="text" placeholder="Approved Activity Design"  />
                            </div>
                            <div class="form-group">
                                <label>Link to MOVs:</label>
                                <input name="txt_mov" class="form-control input-sm" type="url" placeholder="Link to MOVs" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks"></textarea>
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
