<!-- ========================= ADD BILL MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form id="addForm">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Bill</h4>
                </div>
                <div class="modal-body">
                    <div id="addAlert" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Received<span class="req-asterisk">*</span></label>
                                <input name="txt_date_received" id="add_date_received" class="form-control input-sm" type="date" required />
                            </div>
                            <div class="form-group">
                                <label>Type of Billing<span class="req-asterisk">*</span></label>
                                <select name="txt_type_of_billing" id="add_type_of_billing" class="form-control input-sm" required>
                                    <option value="Water Bill">Water Bill</option>
                                    <option value="Internet Bill">Internet Bill</option>
                                    <option value="Electricity Bill">Electricity Bill</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Link to File (BILLS)</label>
                                <input name="txt_link_to_file" id="add_link_to_file" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                            <div class="form-group">
                                <label>Amount</label>
                                <input name="txt_amount" id="add_amount" class="form-control input-sm" type="number" step="0.01" min="0" placeholder="0.00" />
                            </div>
                            <div class="form-group">
                                <label>Location/Office</label>
                                <select name="txt_location_office" id="add_location_office" class="form-control input-sm">
                                    <option value="">(No location)</option>
                                    <option value="SDN Provincial Office" selected>SDN Provincial Office</option>
                                    <option value="SDN Hill Relay Station">SDN Hill Relay Station</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Due Date</label>
                                <input name="txt_due_date" id="add_due_date" class="form-control input-sm" type="date" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Disconnection Date</label>
                                <input name="txt_disconnection_date" id="add_disconnection_date" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Paid?</label>
                                <div style="padding-top:5px;">
                                    <input type="hidden" name="txt_status" value="0" />
                                    <label style="font-weight:normal; cursor:pointer;">
                                        <input type="checkbox" name="txt_status" value="1" id="add_status" style="margin-right:5px;" /> Mark as Paid
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Date Paid</label>
                                <input name="txt_date_paid" id="add_date_paid" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="txt_remarks" id="add_remarks" class="form-control input-sm" rows="3" placeholder="Remarks"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Link to OR</label>
                                <input name="txt_link_to_or" id="add_link_to_or" class="form-control input-sm" type="text" placeholder="Paste Google Drive / external URL" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" id="addSubmitBtn" value="Add Bill" />
                </div>
            </div>
        </div>
    </form>
</div>
