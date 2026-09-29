<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<div id="viewModal" class="modal fade" role="dialog">
    <form method="post" enctype="multipart/form-data" id="viewActivityForm">
        <div class="modal-dialog modal-sdm-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-folder-open"></i> View Files for Activity: <span id="view_activity_title"></span></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="hidden_id" id="view_hidden_id" value="">
                    <input type="checkbox" id="cbxMainphoto" /> <label>Select All</label>
                    <div class="row" id="photoGrid">
                        <p class="text-muted">Loading photos...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-6">
                        <input name="photos[]" class="form-control input-sm" type="file" multiple/>
                    </div>
                    <!-- type=button so the two actions can be handled separately
                         instead of both posting the same form. -->
                    <input type="button" class="btn btn-primary btn-sm" id="btnAddImage" value="Add"/>
                    <input type="button" class="btn btn-danger btn-sm" id="btnRemoveImage" value="Remove Selected"/>
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Close"/>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function () {
        var form = document.getElementById('viewActivityForm');

        function postImageAction(btn, action, busyLabel) {
            var ids = [];
            var checked = document.querySelectorAll('.chk_deletephoto:checked');
            for (var i = 0; i < checked.length; i++) ids.push(checked[i].value);

            var data = new FormData(form);
            data.append(action, '1');
            if (action === 'btn_remove') {
                for (var j = 0; j < ids.length; j++) data.append('chk_deletephoto[]', ids[j]);
            }

            var label = btn.value;
            btn.disabled = true;
            btn.value = busyLabel;

            $.ajax({
                url: 'function.php',
                type: 'POST',
                dataType: 'json',
                data: data
            }).done(function (resp) {
                if (typeof showToast === 'function') {
                    showToast((resp && resp.message) || 'Done.', (resp && resp.success) ? 'success' : 'error');
                }
                if (resp && resp.success) {
                    // Re-open the modal so the photo grid reloads.
                    var id = form.querySelector('#view_hidden_id').value;
                    var title = form.querySelector('#view_activity_title').textContent;
                    $('.btn-view-activity[data-id="' + id + '"]').first().trigger('click');
                    if (title) $('#view_activity_title').text(title);
                }
            }).fail(function (xhr) {
                var message = 'Network error.';
                try {
                    message = JSON.parse(xhr.responseText).message || message;
                } catch (err) { /* keep default */ }
                if (typeof showToast === 'function') showToast(message, 'error');
            }).always(function () {
                btn.disabled = false;
                btn.value = label;
            });
        }

        var addBtn = document.getElementById('btnAddImage');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                postImageAction(addBtn, 'btn_addimage', 'Uploading...');
            });
        }

        var delBtn = document.getElementById('btnRemoveImage');
        if (delBtn) {
            delBtn.addEventListener('click', function () {
                postImageAction(delBtn, 'btn_remove', 'Removing...');
            });
        }
    });
</script>
