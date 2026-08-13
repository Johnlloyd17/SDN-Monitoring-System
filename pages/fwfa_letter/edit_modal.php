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
                                <label>Locality:</label>
                                <input name="txt_edit_locality" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['locality'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Barangay:</label>
                                <input name="txt_edit_barangay" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['barangay'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>District:</label>
                                <input name="txt_edit_district" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['district'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Location Name:</label>
                                <input name="txt_edit_location" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Date Requested:</label>
                                <input name="txt_edit_date" class="form-control input-sm" type="date" value="' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Year:</label>
                                <input name="txt_edit_year" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['year'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Type:</label>
                                <input name="txt_edit_type" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <input name="txt_edit_status" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Accomplished Date:</label>
                                <input name="txt_edit_accomplished" class="form-control input-sm" type="date" value="' . htmlspecialchars($row['accomplished'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_edit_remarks" class="form-control input-sm">' . htmlspecialchars($row['remarks'], ENT_QUOTES, 'UTF-8') . '</textarea>
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