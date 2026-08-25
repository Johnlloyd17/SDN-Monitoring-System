<!-- ========================= ADD MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Participant</h4>
                </div>
                <div class="modal-body modal-tabs">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#t4e-tab-location" data-toggle="tab"><i class="fa fa-map-marker"></i> Location</a></li>
                        <li><a href="#t4e-tab-center" data-toggle="tab"><i class="fa fa-building"></i> Center Info</a></li>
                        <li><a href="#t4e-tab-manager" data-toggle="tab"><i class="fa fa-user"></i> Manager</a></li>
                        <li><a href="#t4e-tab-assistant" data-toggle="tab"><i class="fa fa-user"></i> Asst. Manager</a></li>
                        <li><a href="#t4e-tab-operations" data-toggle="tab"><i class="fa fa-cogs"></i> Operations</a></li>
                        <li><a href="#t4e-tab-donation" data-toggle="tab"><i class="fa fa-gift"></i> Donation &amp; Access</a></li>
                    </ul>
                    <div style="margin-bottom:12px;font-size:12px;color:#777;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Fields marked with <span class="req-asterisk">*</span> are required.</div>

                    <div class="tab-content">
                        <!-- Tab: Location -->
                        <div class="tab-pane active" id="t4e-tab-location">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Region:</label>
                                        <input name="txt_region" class="form-control input-sm" type="text" placeholder="Region" />
                                    </div>
                                    <div class="form-group">
                                        <label>Province:</label>
                                        <input name="txt_province" class="form-control input-sm" type="text" placeholder="Province" />
                                    </div>
                                    <div class="form-group">
                                        <label>District:</label>
                                        <input name="txt_district" class="form-control input-sm" type="text" placeholder="District" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Municipality/City: <span class="req-asterisk">*</span></label>
                                        <input name="txt_municipality" class="form-control input-sm" type="text" placeholder="Municipality/City" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Barangay:</label>
                                        <input name="txt_barangay" class="form-control input-sm" type="text" placeholder="Barangay" />
                                    </div>
                                    <div class="form-group">
                                        <label>Street Address:</label>
                                        <input name="txt_street" class="form-control input-sm" type="text" placeholder="Street Address" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Center Info -->
                        <div class="tab-pane" id="t4e-tab-center">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Specific Center Location:</label>
                                        <input name="txt_location" class="form-control input-sm" type="text" placeholder="Specific Center Location" />
                                    </div>
                                    <div class="form-group">
                                        <label>Center Name: <span class="req-asterisk">*</span></label>
                                        <input name="txt_cname" class="form-control input-sm" type="text" placeholder="Center Name" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Host:</label>
                                        <input name="txt_host" class="form-control input-sm" type="text" placeholder="Host" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Category:</label>
                                        <input name="txt_category" class="form-control input-sm" type="text" placeholder="Category" />
                                    </div>
                                    <div class="form-group">
                                        <label>Longitude:</label>
                                        <input name="txt_longitude" class="form-control input-sm" type="text" placeholder="Longitude" />
                                    </div>
                                    <div class="form-group">
                                        <label>Latitude:</label>
                                        <input name="txt_latitude" class="form-control input-sm" type="text" placeholder="Latitude" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Center Manager -->
                        <div class="tab-pane" id="t4e-tab-manager">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Center Manager's Name:</label>
                                        <input name="txt_cmanager" class="form-control input-sm" type="text" placeholder="Center Manager's Name" />
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input name="txt_cemail" class="form-control input-sm" type="email" placeholder="CM Email" />
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile:</label>
                                        <input name="txt_cmobile" class="form-control input-sm" type="text" placeholder="CM Mobile" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Landline:</label>
                                        <input name="txt_clandline" class="form-control input-sm" type="text" placeholder="CM Landline" />
                                    </div>
                                    <div class="form-group">
                                        <label>Gender:</label>
                                        <input name="txt_cgender" class="form-control input-sm" type="text" placeholder="CM Gender" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Assistant Manager -->
                        <div class="tab-pane" id="t4e-tab-assistant">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Assistant Center Manager's Name:</label>
                                        <input name="txt_amanager" class="form-control input-sm" type="text" placeholder="Assistant Center Manager's Name" />
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input name="txt_aemail" class="form-control input-sm" type="email" placeholder="AM Email" />
                                    </div>
                                    <div class="form-group">
                                        <label>Mobile:</label>
                                        <input name="txt_amobile" class="form-control input-sm" type="text" placeholder="AM Mobile" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Landline:</label>
                                        <input name="txt_alandline" class="form-control input-sm" type="text" placeholder="AM Landline" />
                                    </div>
                                    <div class="form-group">
                                        <label>Gender:</label>
                                        <input name="txt_agender" class="form-control input-sm" type="text" placeholder="AM Gender" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Operations -->
                        <div class="tab-pane" id="t4e-tab-operations">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Launching:</label>
                                        <input name="txt_launch" class="form-control input-sm" type="date" />
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Platform Registration:</label>
                                        <input name="txt_registration" class="form-control input-sm" type="date" />
                                    </div>
                                    <div class="form-group">
                                        <label>Operational Status:</label>
                                        <input name="txt_operation" class="form-control input-sm" type="text" placeholder="Operational Status" />
                                    </div>
                                    <div class="form-group">
                                        <label>Date Last Visited:</label>
                                        <input name="txt_visited" class="form-control input-sm" type="date" />
                                    </div>
                                    <div class="form-group">
                                        <label>Status: <span class="req-asterisk">*</span></label>
                                        <input name="txt_status" class="form-control input-sm" type="text" placeholder="Status" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label># of Functional Desktop Units:</label>
                                        <input name="txt_desktop" class="form-control input-sm" type="number" placeholder="#" />
                                    </div>
                                    <div class="form-group">
                                        <label># of Functional Laptop Units:</label>
                                        <input name="txt_laptop" class="form-control input-sm" type="number" placeholder="#" />
                                    </div>
                                    <div class="form-group">
                                        <label># of Functional Printer Units:</label>
                                        <input name="txt_printer" class="form-control input-sm" type="number" placeholder="#" />
                                    </div>
                                    <div class="form-group">
                                        <label># of Functional Scanner:</label>
                                        <input name="txt_scanner" class="form-control input-sm" type="number" placeholder="#" />
                                    </div>
                                    <div class="form-group">
                                        <label>Types of Network:</label>
                                        <input name="txt_network" class="form-control input-sm" type="text" placeholder="Types of Network" />
                                    </div>
                                    <div class="form-group">
                                        <label>Internet Connectivity:</label>
                                        <input name="txt_connectivity" class="form-control input-sm" type="text" placeholder="Internet Connectivity" />
                                    </div>
                                    <div class="form-group">
                                        <label>Internet Speed:</label>
                                        <input name="txt_speed" class="form-control input-sm" type="text" placeholder="Internet Speed" />
                                    </div>
                                    <div class="form-group">
                                        <label>CMT (# of pax) Male:</label>
                                        <input name="txt_cmtmale" class="form-control input-sm" type="number" placeholder="#" />
                                    </div>
                                    <div class="form-group">
                                        <label>CMT (# of pax) Female:</label>
                                        <input name="txt_cmtfemale" class="form-control input-sm" type="number" placeholder="#" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Start Date of Training:</label>
                                        <input name="txt_straining" class="form-control input-sm" type="date" />
                                    </div>
                                    <div class="form-group">
                                        <label>End Date of Training:</label>
                                        <input name="txt_etraining" class="form-control input-sm" type="date" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Signing:</label>
                                        <input name="txt_signing" class="form-control input-sm" type="date" />
                                    </div>
                                    <div class="form-group">
                                        <label>Partner:</label>
                                        <input name="txt_partner" class="form-control input-sm" type="text" placeholder="Partner" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Donation & Access -->
                        <div class="tab-pane" id="t4e-tab-donation">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Expiration:</label>
                                        <input name="txt_expiration" class="form-control input-sm" type="date" />
                                    </div>
                                    <div class="form-group">
                                        <label>Type of Donation:</label>
                                        <input name="txt_donation" class="form-control input-sm" type="text" placeholder="Type of Donation" />
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Donation:</label>
                                        <input name="txt_datedonation" class="form-control input-sm" type="date" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>TCMS:</label>
                                        <input name="txt_tcms" class="form-control input-sm" type="text" placeholder="TCMS" />
                                    </div>
                                    <div class="form-group">
                                        <label>Key:</label>
                                        <input name="txt_key_one" class="form-control input-sm" type="text" placeholder="Key" />
                                    </div>
                                    <div class="form-group">
                                        <label>Identifier:</label>
                                        <input name="txt_identifier" class="form-control input-sm" type="text" placeholder="Identifier" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary" name="btn_add" value="Add Participant" />
                </div>
            </div>
        </div>
    </form>
</div>
