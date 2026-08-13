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
                   <input type="hidden" value="'.$row['id'].'" name="hidden_id" id="hidden_id"/>
                <div class="form-group">
                    <label>Start Date: </label>
                    <input name="txt_edit_start" class="form-control input-sm" type="text" value="'.$row['start'].'" />
                </div>
                <div class="form-group">
                    <label>End Date: </label>
                    <input name="txt_edit_end" class="form-control input-sm" type="text" value="'.$row['end'].'" />
                </div>
                <div class="form-group">
                    <label>Activity Name: </label>
                    <input name="txt_edit_activity" class="form-control input-sm" type="text" value="'.$row['activity'].'" />
                </div>
                <div class="form-group">
                    <label>Fullname: </label>
                    <input name="txt_edit_fullname" class="form-control input-sm" type="text" value="'.$row['fullname'].'" />
                </div>
                <div class="form-group">
                    <label>Sex: </label>
                    <input name="txt_edit_sex" class="form-control input-sm" type="text" value="'.$row['sex'].'" />
                </div>
                <div class="form-group">
                    <label>Contact: </label>
                    <input name="txt_edit_contact" class="form-control input-sm" type="text" value="'.$row['contact'].'" />
                </div>
                <div class="form-group">
                    <label>Email Address: </label>
                    <input name="txt_edit_email" class="form-control input-sm" type="email" value="'.$row['email'].'" />
                </div>
                <div class="form-group">
                    <label>Mode of Implementation: </label>
                    <input name="txt_edit_mode" class="form-control input-sm" type="text" value="'.$row['mode'].'" />
                </div>
                <div class="form-group">
                    <label>Agency: </label>
                    <input name="txt_edit_agency" class="form-control input-sm" type="text" value="'.$row['agency'].'" />
                </div>
                <div class="form-group">
                    <label>Target Sector: </label>
                    <input name="txt_edit_sector" class="form-control input-sm" type="text" value="'.$row['sector'].'" />
                </div>
                <div class="form-group">
                    <label>Project: </label>
                    <input name="txt_edit_project" class="form-control input-sm" type="text" value="'.$row['project'].'" />
                </div>
                <div class="form-group">
                    <label>Responsible Person: </label>
                    <input name="txt_edit_person" class="form-control input-sm" type="text" value="'.$row['person'].'" />
                </div>
                <div class="form-group">
                    <label>Remarks: </label>
                    <input name="txt_edit_remarks" class="form-control input-sm" type="text" value="'.$row['remarks'].'" />
                </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm" name="btn_save">Save</button>
        </div>
    </div>
  </div>
</form>
</div>'; ?>
