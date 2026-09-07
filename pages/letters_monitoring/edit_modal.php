<!-- ========================= EDIT LETTER MODAL ======================= -->
<div id="editModal" class="modal fade">
    <form id="editForm">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Letter</h4>
                </div>
                <div class="modal-body">
                    <div id="editAlert" style="display:none;"></div>
                    <input type="hidden" name="hidden_id" id="edit_hidden_id" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date<span class="req-asterisk">*</span></label>
                                <input name="txt_edit_date" id="edit_date" class="form-control input-sm" type="date" required />
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="txt_edit_type" id="edit_type" class="form-control input-sm">
                                    <option value="">-- Select Type --</option>
                                    <option value="Incoming">Incoming</option>
                                    <option value="Outgoing">Outgoing</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input name="txt_edit_subject" id="edit_subject" class="form-control input-sm" type="text" placeholder="Subject of the letter" />
                            </div>
                            <div class="form-group">
                                <label>FW4A</label>
                                <select name="txt_edit_fw4a" id="edit_fw4a" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Request">Request</option>
                                    <option value="Provision">Provision</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Drive link to File "Incoming"</label>
                                <input name="txt_edit_link_incoming" id="edit_link_incoming" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                            <div class="form-group">
                                <label>For Response?</label>
                                <select name="txt_edit_for_response" id="edit_for_response" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Y">Yes</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Responded/Sent</label>
                                <input name="txt_edit_date_responded" id="edit_date_responded" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Drive link to File "Outgoing"</label>
                                <input name="txt_edit_link_outgoing" id="edit_link_outgoing" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                            <div class="form-group">
                                <label>Responsible Person</label>
                                <input name="txt_edit_responsible_person" id="edit_responsible_person" class="form-control input-sm" type="text" placeholder="Name of responsible person" />
                            </div>
                            <div class="form-group">
                                <label>If meeting/events, who attended?</label>
                                <textarea name="txt_edit_who_attended" id="edit_who_attended" class="form-control input-sm" rows="2" placeholder="Comma-separated names"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="txt_edit_remarks" id="edit_remarks" class="form-control input-sm" rows="2" placeholder="Remarks"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Post Activity Report</label>
                                <input name="txt_edit_post_activity_report" id="edit_post_activity_report" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" id="editSubmitBtn" value="Save" />
                </div>
            </div>
        </div>
    </form>
</div>
