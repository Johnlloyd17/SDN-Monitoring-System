<!-- ========================= ADD LETTER MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form id="addForm">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Letter</h4>
                </div>
                <div class="modal-body">
                    <div id="addAlert" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date<span class="req-asterisk">*</span></label>
                                <input name="txt_date" id="add_date" class="form-control input-sm" type="date" required />
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                <select name="txt_type" id="add_type" class="form-control input-sm">
                                    <option value="Incoming">Incoming</option>
                                    <option value="Outgoing">Outgoing</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input name="txt_subject" id="add_subject" class="form-control input-sm" type="text" placeholder="Subject of the letter" />
                            </div>
                            <div class="form-group">
                                <label>FW4A</label>
                                <select name="txt_fw4a" id="add_fw4a" class="form-control input-sm">
                                    <option value="">&mdash; none &mdash;</option>
                                    <option value="Request">Request</option>
                                    <option value="Provision">Provision</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Drive link to File "Incoming"</label>
                                <input name="txt_link_incoming" id="add_link_incoming" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                            <div class="form-group">
                                <label>For Response?</label>
                                <select name="txt_for_response" id="add_for_response" class="form-control input-sm">
                                    <option value="">&mdash; none &mdash;</option>
                                    <option value="Y">Yes</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Responded/Sent</label>
                                <input name="txt_date_responded" id="add_date_responded" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Drive link to File "Outgoing"</label>
                                <input name="txt_link_outgoing" id="add_link_outgoing" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                            <div class="form-group">
                                <label>Responsible Person</label>
                                <input name="txt_responsible_person" id="add_responsible_person" class="form-control input-sm" type="text" placeholder="Name of responsible person" />
                            </div>
                            <div class="form-group">
                                <label>If meeting/events, who attended?</label>
                                <textarea name="txt_who_attended" id="add_who_attended" class="form-control input-sm" rows="2" placeholder="Comma-separated names"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="txt_remarks" id="add_remarks" class="form-control input-sm" rows="2" placeholder="Remarks"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Post Activity Report</label>
                                <input name="txt_post_activity_report" id="add_post_activity_report" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" id="addSubmitBtn" value="Add Letter" />
                </div>
            </div>
        </div>
    </form>
</div>
