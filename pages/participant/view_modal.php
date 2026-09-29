<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<div id="viewModal" class="modal fade" role="dialog">
    <form method="post" id="viewParticipantForm" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-folder-open"></i> View Files for Activity: <span id="view_item_title"></span></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="hidden_id" id="view_hidden_id">
                    <input type="checkbox" id="cbxMainphoto" /> <label>Select All</label>
                    <div class="row" id="photoGrid">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-6">
                        <input name="photos[]" class="form-control input-sm" type="file" multiple/>
                    </div>
                    <input type="button" class="btn btn-primary btn-sm" name="btn_addimage" value="Add"/>
                    <input type="button" class="btn btn-danger btn-sm" name="btn_remove" value="Remove Selected"/>
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Close"/>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    // Both footer buttons drive the same form, so post the action explicitly and
    // reload just the attachment grid rather than the page.
    (function () {
        function participantPostPhotoAction(action, button, busyLabel) {
            var form = document.getElementById('viewParticipantForm');
            if (!form) return;

            var data = new FormData(form);
            data.append(action, '1');
            var label = button.value;
            button.disabled = true;
            button.value = busyLabel;

            $.ajax({
                url: 'function.php',
                type: 'POST',
                dataType: 'json',
                data: data
            }).done(function (resp) {
                if (typeof showToast === 'function') {
                    showToast((resp && resp.message) || 'Done.', (resp && resp.type) || 'success');
                }
                if (resp && resp.success) {
                    var id = document.getElementById('view_hidden_id').value;
                    if (action === 'btn_addimage') {
                        form.reset();
                        if (typeof loadParticipantPhotos === 'function') loadParticipantPhotos(id);
                    } else {
                        if (typeof loadParticipantPhotos === 'function') loadParticipantPhotos(id);
                    }
                }
            }).fail(function (xhr) {
                var message = 'Network error.';
                try {
                    message = JSON.parse(xhr.responseText).message || message;
                } catch (err) { /* keep default */ }
                if (typeof showToast === 'function') showToast(message, 'error');
            }).always(function () {
                button.disabled = false;
                button.value = label;
            });
        }

        $(function () {
            var addBtn = document.querySelector('#viewParticipantForm [name="btn_addimage"]');
            if (addBtn) {
                addBtn.onclick = function () { participantPostPhotoAction('btn_addimage', addBtn, 'Adding...'); };
            }
            var rmBtn = document.querySelector('#viewParticipantForm [name="btn_remove"]');
            if (rmBtn) {
                rmBtn.onclick = function () {
                    var picked = document.querySelectorAll('.chk_deletephoto:checked');
                    if (picked.length === 0) {
                        if (typeof showToast === 'function') showToast('Please select at least one file to remove.', 'warning');
                        return;
                    }
                    if (!confirm('Remove ' + picked.length + ' selected file(s)? This cannot be undone.')) return;
                    participantPostPhotoAction('btn_remove', rmBtn, 'Removing...');
                };
            }
        });
    })();
</script>
