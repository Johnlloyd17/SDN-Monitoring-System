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
                                <label>Locality:</label>
                                <input name="txt_locality" class="form-control input-sm" type="text" placeholder="Locality" />
                            </div>
                            <div class="form-group">
                                <label>Barangay:</label>
                                <input name="txt_barangay" class="form-control input-sm" type="text" placeholder="Barangay" />
                            </div>
                            <div class="form-group">
                                <label>Location Name:</label>
                                <input name="txt_location" class="form-control input-sm" type="text" placeholder="Location Name" />
                            </div>
                            <div class="form-group">
                                <label>Date Requested:</label>
                                <input name="txt_date" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Year:</label>
                                <input name="txt_year" class="form-control input-sm" type="text" placeholder="Year" />
                            </div>
                            <div class="form-group">
                                <label>Type:</label>
                                <input name="txt_type" class="form-control input-sm" type="text" placeholder="Type" />
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <input name="txt_status" class="form-control input-sm" type="text" placeholder="Status" />
                            </div>
                            <div class="form-group">
                                <label>Accomplished Date:</label>
                                <input name="txt_accomplished" class="form-control input-sm" type="date" />
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