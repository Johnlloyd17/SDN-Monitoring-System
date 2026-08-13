<?php echo '
<div id="editModal' . $row['id'] . '" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-lg" style="width:750px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Item</h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" name="hidden_id" value="' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '" />

                            <div class="form-group">
                                <label>Project:</label>
                                <input type="text" name="txt_edit_project" class="form-control input-sm" value="' . htmlspecialchars($row['project'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Item No.:</label>
                                <input type="text" name="txt_edit_item" class="form-control input-sm" value="' . htmlspecialchars($row['item'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Classification:</label>
                                <select name="txt_edit_classification" class="form-control input-sm select2" style="width:100%;">
                                    <option value="">-- Select Classification --</option>
                                    ' . $selectedClass . $classOptions . '
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Quantity:</label>
                                <input type="number" name="txt_edit_quantity" class="form-control input-sm" value="' . htmlspecialchars($row['quantity'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Unit:</label>
                                <input type="text" name="txt_edit_unit" class="form-control input-sm" value="' . htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Description/Model:</label>
                                <input type="text" name="txt_edit_description" class="form-control input-sm" value="' . htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Received From:</label>
                                <input type="text" name="txt_edit_received" class="form-control input-sm" value="' . htmlspecialchars($row['received'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Property Number:</label>
                                <input type="text" name="txt_edit_property" class="form-control input-sm" value="' . htmlspecialchars($row['property'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ICS/PAR Number:</label>
                                <input type="text" name="txt_edit_ics" class="form-control input-sm" value="' . htmlspecialchars($row['ics'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Serial Number:</label>
                                <input type="text" name="txt_edit_serial" class="form-control input-sm" value="' . htmlspecialchars($row['serial'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Date Acquired:</label>
                                <input type="date" name="txt_edit_date" class="form-control input-sm" value="' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Accountable Officer:</label>
                                <input type="text" name="txt_edit_officer" class="form-control input-sm" value="' . htmlspecialchars($row['officer'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Unit Cost:</label>
                                <input type="text" name="txt_edit_cost" class="form-control input-sm" value="' . htmlspecialchars($row['cost'], ENT_QUOTES, 'UTF-8') . '" placeholder="e.g. 43,904.00" />
                            </div>
                            <div class="form-group">
                                <label>Estimated Useful Life:</label>
                                <input type="text" name="txt_edit_life" class="form-control input-sm" value="' . htmlspecialchars($row['life'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Received/Transferred:</label>
                                <input type="text" name="txt_edit_transferred" class="form-control input-sm" value="' . htmlspecialchars($row['transferred'], ENT_QUOTES, 'UTF-8') . '" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <input type="text" name="txt_edit_remarks" class="form-control input-sm" value="' . htmlspecialchars($row['remarks'], ENT_QUOTES, 'UTF-8') . '" />
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
