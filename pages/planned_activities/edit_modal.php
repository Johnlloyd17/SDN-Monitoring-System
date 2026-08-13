<?php echo '
<div id="editModal' . $row['id'] . '" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-sm" style="width:500px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Activity</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '" name="hidden_id" id="hidden_id"/>
                            
                            <div class="form-group">
                                <label>Start Date: </label>
                                <input name="txt_edit_start" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['start'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>End Date: </label>
                                <input name="txt_edit_end" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['end'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Bureau: </label>
                                <input name="txt_edit_project" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['project'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Project: </label>
                                <input name="txt_edit_subproject" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['subproject'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                             <div class="form-group">
                                <label>Indicator: </label>
                                <input name="txt_edit_indicator" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['indicator'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Activity: </label>
                                <input name="txt_edit_activity" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['activity'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Training: </label>
                                <input name="txt_edit_training" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['training'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Municipality: </label>
                                <input name="txt_edit_municipality" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['municipality'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Barangay: </label>
                                <input name="txt_edit_barangay" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['barangay'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>District: </label>
                                <input name="txt_edit_district" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['district'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Agency: </label>
                                <input name="txt_edit_agency" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['agency'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Mode: </label>
                                <input name="txt_edit_mode" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['mode'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Sector: </label>
                                <input name="txt_edit_sector" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['sector'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Person: </label>
                                <input name="txt_edit_person" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['person'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Resource: </label>
                                <input name="txt_edit_resource" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['resource'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Participants: </label>
                                <input name="txt_edit_participants" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['participants'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Completers: </label>
                                <input name="txt_edit_completers" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['completers'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Male: </label>
                                <input name="txt_edit_male" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['male'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Female: </label>
                                <input name="txt_edit_female" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['female'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Approved: </label>
                                <input name="txt_edit_approved" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['approved'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>MOV: </label>
                                <input name="txt_edit_mov" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['mov'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Remarks: </label>
                                <input name="txt_edit_remarks" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['remarks'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Type: </label>
                                <input name="txt_edit_type" class="form-control input-sm" type="text" value="' . htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') . '" />
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