<?php echo '
<div id="editModal' . $row['id'] . '" class="modal fade">
    <form method="post">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Access Point</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" value="' . htmlspecialchars($row["id"], ENT_QUOTES, "UTF-8") . '" name="hidden_id" id="hidden_id"/>
                            
                            <div class="form-group">
                                <label>Locality: </label>
                                <input name="txt_edit_locality" id="edit_locality_' . $row["id"] . '" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["locality"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Barangay: </label>
                                <input name="txt_edit_barangay" id="edit_barangay_' . $row["id"] . '" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["barangay"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>District: </label>
                                <input name="txt_edit_district" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["district"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Transport Location: </label>
                                <input name="txt_edit_transport_location" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["transport_location"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Transport Type: </label>
                                <input name="txt_edit_transport_type" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["transport_type"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Locations: </label>
                                <input name="txt_edit_locations" id="edit_locations_' . $row["id"] . '" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["locations"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Site Type: </label>
                                <input name="txt_edit_type" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["type"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Site Code: </label>
                                <input name="txt_edit_code" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["code"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Nationwide ID: </label>
                                <input name="txt_edit_nationwide_id" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["nationwide_id"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Date of Activation: </label>
                                <input name="txt_edit_date_of_activation" class="form-control input-sm" type="date" value="' . htmlspecialchars($row["date_of_activation"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Current Date of Acceptance: </label>
                                <input name="txt_edit_current_date_of_acceptance" class="form-control input-sm" type="date" value="' . htmlspecialchars($row["current_date_of_acceptance"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Latitude: </label>
                                <input name="txt_edit_latitude" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["latitude"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Longitude: </label>
                                <input name="txt_edit_longitude" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["longitude"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Strategy: </label>
                                <input name="txt_edit_strategy" class="form-control input-sm" type="text" value="' . htmlspecialchars($row["strategy"], ENT_QUOTES, "UTF-8") . '" />
                            </div>
                            <div class="form-group">
                                <label>Status: </label>
                                <select name="txt_edit_status" class="form-control input-sm">
                                    <option value="">-- Select Status --</option>' .
                                    '<option value="Active"' . ($row["status"] === "Active" ? " selected" : "") . '>Active</option>' .
                                    '<option value="Inactive"' . ($row["status"] === "Inactive" ? " selected" : "") . '>Inactive</option>' .
                                    '<option value="Ongoing"' . ($row["status"] === "Ongoing" ? " selected" : "") . '>Ongoing</option>' .
                                    '<option value="Terminated"' . ($row["status"] === "Terminated" ? " selected" : "") . '>Terminated</option>' .
                                    '<option value="Deactivated"' . ($row["status"] === "Deactivated" ? " selected" : "") . '>Deactivated</option>' .
                                    '<option value="Ongoing Acceptance"' . ($row["status"] === "Ongoing Acceptance" ? " selected" : "") . '>Ongoing Acceptance</option>' .
                                    '<option value="For Installation"' . ($row["status"] === "For Installation" ? " selected" : "") . '>For Installation</option>' .
                                '<option value="Other"' . ($row["status"] !== "Active" && $row["status"] !== "Inactive" && $row["status"] !== "Ongoing" && $row["status"] !== "Terminated" && $row["status"] !== "Deactivated" && $row["status"] !== "Ongoing Acceptance" && $row["status"] !== "For Installation" ? " selected" : "") . '>Other</option>' .
                                '</select>
                            </div>
                            <div class="form-group">
                                <label>Remarks: </label>
                                <textarea name="txt_edit_remarks" class="form-control input-sm">' . htmlspecialchars($row["remarks"], ENT_QUOTES, "UTF-8") . '</textarea>
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
</div>
'; ?>
