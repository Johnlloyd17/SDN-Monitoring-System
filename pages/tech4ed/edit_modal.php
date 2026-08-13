<?php echo '<div id="editModal'.$row['id'].'" class="modal fade">
<form method="post">
  <div class="modal-dialog modal-sm" style="width:500px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Edit Participant</h4>
        </div>
        <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <input type="hidden" value="'.$row['id'].'" name="hidden_id" id="hidden_id"/>
                <div class="form-group">
                    <label>Region: </label>
                    <input name="txt_edit_region" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['region'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Province: </label>
                    <input name="txt_edit_province" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['province'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>District: </label>
                    <input name="txt_edit_district" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['district'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Municipality/City: </label>
                    <input name="txt_edit_municipality" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['municipality'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Barangay: </label>
                    <input name="txt_edit_barangay" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['barangay'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Street Address: </label>
                    <input name="txt_edit_street" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['street'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Specific Center Location: </label>
                    <input name="txt_edit_location" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Center Name: </label>
                    <input name="txt_edit_cname" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['cname'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Host: </label>
                    <input name="txt_edit_host" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['host'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Category: </label>
                    <input name="txt_edit_category" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Longitude: </label>
                    <input name="txt_edit_longitude" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['longitude'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Latitude: </label>
                    <input name="txt_edit_latitude" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['latitude'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Center Managers Name: </label>
                    <input name="txt_edit_cmanager" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['cmanager'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Email: </label>
                    <input name="txt_edit_cemail" class="form-control input-sm" type="email" value="'.htmlspecialchars($row['cemail'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Mobile: </label>
                    <input name="txt_edit_cmobile" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['cmobile'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Landline: </label>
                    <input name="txt_edit_clandline" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['clandline'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Gender: </label>
                    <input name="txt_edit_cgender" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['cgender'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Assistant Center Managers Name: </label>
                    <input name="txt_edit_amanager" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['amanager'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Email: </label>
                    <input name="txt_edit_aemail" class="form-control input-sm" type="email" value="'.htmlspecialchars($row['aemail'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Mobile: </label>
                    <input name="txt_edit_amobile" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['amobile'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Landline: </label>
                    <input name="txt_edit_alandline" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['alandline'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Gender: </label>
                    <input name="txt_edit_agender" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['agender'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Date of Launching: </label>
                    <input name="txt_edit_launch" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['launch'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Date of Platform Registration: </label>
                    <input name="txt_edit_registration" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['registration'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Operational Status: </label>
                    <input name="txt_edit_operation" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['operation'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Date Last Visited: </label>
                    <input name="txt_edit_visited" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['visited'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label># of Functional Desktop Units: </label>
                    <input name="txt_edit_desktop" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['desktop'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label># of Functional Laptop Units: </label>
                    <input name="txt_edit_laptop" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['laptop'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label># of Functional Printer Units: </label>
                    <input name="txt_edit_printer" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['printer'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label># of Functional Scanner Units: </label>
                    <input name="txt_edit_scanner" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['scanner'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Status: </label>
                    <input name="txt_edit_status" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Types of Network: </label>
                    <input name="txt_edit_network" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['network'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Internet Connectivity: </label>
                    <input name="txt_edit_connectivity" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['connectivity'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Internet Speed: </label>
                    <input name="txt_edit_speed" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['speed'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>CMT (# of pax) Male: </label>
                    <input name="txt_edit_cmtmale" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['cmtmale'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>CMT (# of pax) Female: </label>
                    <input name="txt_edit_cmtfemale" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['cmtfemale'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Start Date of Training: </label>
                    <input name="txt_edit_straining" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['straining'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>End Date of Training: </label>
                    <input name="txt_edit_etraining" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['etraining'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Date of Signing: </label>
                    <input name="txt_edit_signing" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['signing'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Partner: </label>
                    <input name="txt_edit_partner" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['partner'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Expiration: </label>
                    <input name="txt_edit_expiration" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['expiration'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Type of Donation: </label>
                    <input name="txt_edit_donation" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['donation'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Date of Donation: </label>
                    <input name="txt_edit_datedonation" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['datedonation'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>TCMS: </label>
                    <input name="txt_edit_tcms" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['tcms'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Key: </label>
                    <input name="txt_edit_key_one" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['key_one'], ENT_QUOTES, 'UTF-8').'" />
                </div>
                <div class="form-group">
                    <label>Identifier: </label>
                    <input name="txt_edit_identifier" class="form-control input-sm" type="text" value="'.htmlspecialchars($row['identifier'], ENT_QUOTES, 'UTF-8').'" />
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
