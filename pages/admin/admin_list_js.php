<?php
/**
 * List-refresh + CRUD wiring for pages/admin/admin.php.
 * refreshAdmData() is what the modals call after a successful save so the
 * table updates in place instead of reloading the page.
 */
require_once __DIR__ . '/admin_rows.php';
?>
<script type="text/javascript">
    (function () {
        var ADM_DATA = '../../ajax/admin_data.php';
        var ADM_ITEM = '../../ajax/admin_get_item.php';

        function rebindAdmCheckboxes() {
            // The header checkbox carries class="cbxMain" and no id, so it must
            // be found by class. Its inline onchange="checkMain(this)" is left
            // alone: checkMain is the proven master->rows handler.
            var master = document.querySelector('.cbxMain');
            var boxes = document.getElementsByClassName('chk_delete');
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].checked = false;
                boxes[i].onchange = function () {
                    var all = document.getElementsByClassName('chk_delete');
                    if (master) {
                        master.checked = (all.length > 0 && document.querySelectorAll('.chk_delete:checked').length === all.length);
                    }
                };
            }
            if (master) master.checked = false;
        }

        function applyAdmResponse(resp) {
            var table = $('#table');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
            } else {
                table.find('tbody').html(resp.rows);
            }
            rebindAdmCheckboxes();
        }

        function refreshAdmData() {
            return $.ajax({
                url: ADM_DATA,
                type: 'GET',
                dataType: 'json'
            }).done(function (resp) {
                if (!resp || !resp.success) return;
                applyAdmResponse(resp);
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Could not refresh the list. Please reload the page.', 'error');
                }
            });
        }

        function postAdm(formData, busyEl, busyText) {
            var label = busyEl ? busyEl.value : null;
            if (busyEl) { busyEl.disabled = true; busyEl.value = busyText; }
            return $.ajax({
                url: 'function.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).always(function () {
                if (busyEl) { busyEl.disabled = false; busyEl.value = label; }
            });
        }

        $(function () {
            rebindAdmCheckboxes();

            // The modals are real forms; stop them navigating the page.
            ['admAddForm', 'admEditForm'].forEach(function (id) {
                var f = document.getElementById(id);
                if (f) f.addEventListener('submit', function (e) { e.preventDefault(); });
            });

            // ---- Delete ----------------------------------------------------
            // The button only exists for non-staff, so a staff session never
            // reaches this handler.
            var delBtn = document.getElementById('btn_delete');
            if (delBtn) {
                delBtn.type = 'button';
                delBtn.addEventListener('click', function () {
                    var ids = [];
                    var checked = document.querySelectorAll('.chk_delete:checked');
                    for (var i = 0; i < checked.length; i++) ids.push(checked[i].value);
                    if (ids.length === 0) {
                        $('#deleteModal').modal('hide');
                        if (typeof showToast === 'function') {
                            showToast('Please select at least one record to delete.', 'warning');
                        }
                        return;
                    }
                    if (!confirm('Delete ' + ids.length + ' selected zone leader(s)? This cannot be undone.')) return;

                    var fd = new FormData();
                    fd.append('btn_delete', '1');
                    for (var k = 0; k < ids.length; k++) fd.append('chk_delete[]', ids[k]);

                    postAdm(fd, delBtn, 'Deleting...').done(function (resp) {
                        if (typeof showToast === 'function') {
                            showToast('Deleted ' + ((resp && resp.deleted) || 0) + ' zone leader(s).', 'success');
                        }
                        $('#deleteModal').modal('hide');
                        refreshAdmData();
                    }).fail(function () {
                        if (typeof showToast === 'function') showToast('Network error while deleting.', 'error');
                    });
                });
            }

            // ---- Add -------------------------------------------------------
            var addBtn = document.getElementById('btn_add');
            if (addBtn) {
                addBtn.type = 'button';
                addBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var form = document.getElementById('admAddForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_add', '1');

                    postAdm(fd, addBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            form.reset();
                            $('#addZoneModal').modal('hide');
                            if (typeof showToast === 'function') showToast('Zone leader added.', 'success');
                            refreshAdmData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not save the zone leader.', 'error');
                        }
                    }).fail(function () {
                        if (typeof showToast === 'function') showToast('Network error while saving.', 'error');
                    });
                });
            }

            // ---- Save (edit modal) ----------------------------------------
            var saveBtn = document.getElementById('btn_save');
            if (saveBtn) {
                saveBtn.type = 'button';
                saveBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var form = document.getElementById('admEditForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_save', '1');

                    postAdm(fd, saveBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            $('#editModal').modal('hide');
                            if (typeof showToast === 'function') showToast('Zone leader updated.', 'success');
                            refreshAdmData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not update the zone leader.', 'error');
                        }
                    }).fail(function () {
                        if (typeof showToast === 'function') showToast('Network error while saving.', 'error');
                    });
                });
            }
        });

        // ---- Edit ----------------------------------------------------------
        $(document).on('click', '.btn-edit-item', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: ADM_ITEM + '?action=item&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (data) {
                $('#edit_hidden_id').val(data.id);
                // The field names come from the same read the endpoint does, so
                // a new column cannot show up in one place only.
                var fields = $('#admEditForm').find('input[name^="txt_edit_"]');
                for (var i = 0; i < fields.length; i++) {
                    var el = fields[i];
                    var key = el.name.replace(/^txt_edit_/, '');
                    el.value = (data[key] === undefined || data[key] === null) ? '' : data[key];
                }
                $('#editModal').modal('show');
            }).fail(function () {
                if (typeof showToast === 'function') showToast('Could not load that record.', 'error');
            });
        });

        window.refreshAdmData = refreshAdmData;
    })();
</script>