<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<div id="editModal" class="modal fade">
    <form method="post" id="admEditForm">
        <div class="modal-dialog modal-sdm-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Zone Leader</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" value="" name="hidden_id" id="edit_hidden_id"/>
                            <div class="form-group">
                                <label>Zone #:</label>
                                <input name="txt_edit_zone" id="edit_zone" class="form-control input-sm" type="number" value=""/>
                            </div>
                            <div class="form-group">
                                <label>Username:</label>
                                <input name="txt_edit_uname" id="edit_uname" class="form-control input-sm" type="text" value=""/>
                            </div>
                            <div class="form-group">
                                <label>Password:</label>
                                <input name="txt_edit_pass" id="edit_pass" class="form-control input-sm" type="password" value=""/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel"/>
                    <input type="submit" class="btn btn-primary btn-sm" name="btn_save" id="btn_save" value="Save"/>
                </div>
            </div>
        </div>
    </form>
</div>