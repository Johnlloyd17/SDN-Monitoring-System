<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!-- ========================= ADD MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form id="addForm" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Access Point</h4>
                </div>
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div id="addAlert" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Item No.:</label>
                                <input name="txt_item_no" class="form-control input-sm" type="number" min="0" placeholder="Item No." />
                            </div>
                            <div class="form-group">
                                <label>Locality:</label>
                                <input name="txt_locality" id="add_locality" class="form-control input-sm" type="text" placeholder="Locality" />
                            </div>
                            <div class="form-group">
                                <label>Barangay:</label>
                                <input name="txt_barangay" id="add_barangay" class="form-control input-sm" type="text" placeholder="Barangay" />
                            </div>
                            <div class="form-group">
                                <label>District:</label>
                                <input name="txt_district" class="form-control input-sm" type="text" placeholder="District" />
                            </div>
                            <div class="form-group">
                                <label>Transport Location:</label>
                                <input name="txt_transport_location" class="form-control input-sm" type="text" placeholder="Transport Location" />
                            </div>
                            <div class="form-group">
                                <label>Transport Type:</label>
                                <input name="txt_transport_type" class="form-control input-sm" type="text" placeholder="Transport Type" />
                            </div>
                            <div class="form-group">
                                <label>Connection Type:</label>
                                <select name="txt_connection_type" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Satellite">Satellite</option>
                                    <option value="Fiber">Fiber</option>
                                    <option value="SIM">SIM</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Site Locations:</label>
                                <input name="txt_site_locations" id="add_locations" class="form-control input-sm" type="text" placeholder="Site Locations" />
                            </div>
                            <div class="form-group">
                                <label>Transfer/New Locations:</label>
                                <input name="txt_transfer_new_locations" class="form-control input-sm" type="text" placeholder="Transfer/New Locations" />
                            </div>
                            <div class="form-group">
                                <label>Site Code:</label>
                                <input name="txt_site_code" class="form-control input-sm" type="text" placeholder="Site Code" />
                            </div>
                            <div class="form-group">
                                <label>Nationwide ID:</label>
                                <input name="txt_nationwide_id" class="form-control input-sm" type="text" placeholder="Nationwide ID" />
                            </div>
                            <div class="form-group">
                                <label>Site Type:</label>
                                <input name="txt_site_type" class="form-control input-sm" type="text" placeholder="Site Type" />
                            </div>
                            <div class="form-group">
                                <label>Date of Activation:</label>
                                <input name="txt_date_of_activation" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Current Date of Acceptance:</label>
                                <input name="txt_current_date_of_acceptance" class="form-control input-sm" type="date" />
                            </div>
                            <div class="form-group">
                                <label>Latitude:</label>
                                <input name="txt_latitude" class="form-control input-sm" type="number" step="0.0000001" min="-90" max="90" placeholder="Latitude" />
                            </div>
                            <div class="form-group">
                                <label>Longitude:</label>
                                <input name="txt_longitude" class="form-control input-sm" type="number" step="0.0000001" min="-180" max="180" placeholder="Longitude" />
                            </div>
                            <div class="form-group">
                                <label>Down Time:</label>
                                <input name="txt_down_time" class="form-control input-sm" type="datetime-local" />
                            </div>
                            <div class="form-group">
                                <label>Connectivity Status:</label>
                                <input name="txt_connectivity_status" class="form-control input-sm" type="text" placeholder="Connectivity Status" />
                            </div>
                            <div class="form-group">
                                <label>Area Classification:</label>
                                <select name="txt_area_classification" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="GIDA">GIDA</option>
                                    <option value="NON-GIDA">NON-GIDA</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>With DICT Free WiFi:</label>
                                <select name="txt_with_dict_free_wifi" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Last Monitored:</label>
                                <input name="txt_last_monitored_at" class="form-control input-sm" type="datetime-local" />
                            </div>
                            <div class="form-group">
                                <label>Monitoring Notes:</label>
                                <textarea name="txt_monitoring_notes" class="form-control input-sm" placeholder="Monitoring Notes" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Procurement Initiative:</label>
                                <select name="txt_procurement_initiative" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Centrally Procured">Centrally Procured</option>
                                    <option value="Regional Procured">Regional Procured</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Installation Type:</label>
                                <select name="txt_installation_type" class="form-control input-sm">
                                    <option value="">-- Select --</option>
                                    <option value="Region Initiated">Region Initiated</option>
                                    <option value="Manage Service">Manage Service</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>UAT:</label>
                                <br>
                                <input type="hidden" name="uat" value="0" />
                                <input type="checkbox" name="uat" id="add_uat" value="1" />
                            </div>
                            <div class="form-group">
                                <label>Conforme:</label>
                                <br>
                                <input type="hidden" name="conforme" value="0" />
                                <input type="checkbox" name="conforme" id="add_conforme" value="1" />
                            </div>
                            <div class="form-group">
                                <label>Strategy:</label>
                                <input name="txt_strategy" class="form-control input-sm" type="text" placeholder="Strategy" />
                            </div>
                            <div class="form-group">
                                <label>Status:</label>
                                <select name="txt_status" class="form-control input-sm">
                                    <option value="">-- Select Status --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <!-- Daily Status columns (7 dates x AM/PM) -->
                            <div class="form-group">
                                <label>2026-10-06 AM:</label>
                                <select name="txt_status_2026_10_06_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-06 PM:</label>
                                <select name="txt_status_2026_10_06_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-07 AM:</label>
                                <select name="txt_status_2026_10_07_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-07 PM:</label>
                                <select name="txt_status_2026_10_07_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-08 AM:</label>
                                <select name="txt_status_2026_10_08_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-08 PM:</label>
                                <select name="txt_status_2026_10_08_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-12 AM:</label>
                                <select name="txt_status_2026_10_12_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-12 PM:</label>
                                <select name="txt_status_2026_10_12_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-13 AM:</label>
                                <select name="txt_status_2026_10_13_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-13 PM:</label>
                                <select name="txt_status_2026_10_13_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-14 AM:</label>
                                <select name="txt_status_2026_10_14_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-14 PM:</label>
                                <select name="txt_status_2026_10_14_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-15 AM:</label>
                                <select name="txt_status_2026_10_15_am" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>2026-10-15 PM:</label>
                                <select name="txt_status_2026_10_15_pm" class="form-control input-sm">
                                    <option value="">--</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Assist">Assist</option>
                                    <option value="Terminated">Terminated</option>
                                    <option value="Deactivated">Deactivated</option>
                                    <option value="Ongoing Acceptance">Ongoing Acceptance</option>
                                    <option value="For Installation">For Installation</option>
                                    <option value="For Transfer">For Transfer</option>
                                    <option value="Down">Down</option>
                                    <option value="No Signal">No Signal</option>
                                    <option value="With SMS Only">With SMS Only</option>
                                    <option value="With Data">With Data</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Link Type:</label>
                                <input name="txt_link_type" class="form-control input-sm" type="text" placeholder="Link Type" />
                            </div>
                            <div class="form-group">
                                <label>Replacement Form File:</label>
                                <input name="txt_replacement_form_file" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>Conforme File:</label>
                                <input name="txt_conforme_file" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>UAT File:</label>
                                <input name="txt_uat_file" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>Additional UAT:</label>
                                <input name="txt_additional_uat" class="form-control input-sm" type="url" placeholder="URL / Google Drive link" />
                            </div>
                            <div class="form-group">
                                <label>Name (Site Coordinators):</label>
                                <input name="txt_site_coordinator_name" class="form-control input-sm" type="text" placeholder="Site Coordinator Name" />
                            </div>
                            <div class="form-group">
                                <label>Contact Details:</label>
                                <input name="txt_contact_details" class="form-control input-sm" type="text" placeholder="Contact Details" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks" rows="3"></textarea>
                            </div>
                            <input type="hidden" name="txt_source_sheet" value="Manual Entry" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" id="addSubmitBtn" value="Add Item" />
                </div>
            </div>
        </div>
    </form>
</div>