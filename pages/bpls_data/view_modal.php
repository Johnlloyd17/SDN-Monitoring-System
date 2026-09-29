<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<div id="viewModal" class="modal fade" role="dialog">
    <form method="post" enctype="multipart/form-data" id="bplsViewForm">
        <div class="modal-dialog modal-sdm-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-folder-open"></i> View Files for Activity: <span id="view_item_title"></span></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="hidden_id" id="view_hidden_id" value="">
                    <input type="checkbox" id="cbxMainphoto" /> <label>Select All</label>
                    <div class="row" id="photoGrid"></div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-6">
                        <input name="photos[]" id="bplsPhotoInput" class="form-control input-sm" type="file" multiple/>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="btn_addimage">Add</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn_remove_photo">Remove Selected</button>
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Close"/>
                </div>
            </div>
        </div>
    </form>
</div>
