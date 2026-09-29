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