<?php
/**
 * Shared list-refresh + delete + edit/view wiring for the six
 * planned_activities_*.php views. Each page sets $plannedView to its key
 * ('cyber', 'elgu', 'fw4a', 'gecs', 'iidb' or 'ilcdb') before including this
 * file. refreshPlannedData() is what the modals call after a successful save so
 * the list updates without a reload.
 */
require_once __DIR__ . '/planned_rows.php';

$__pv = planned_view_config($plannedView);
if ($__pv === null) { $__pv = planned_view_config('cyber'); }
?>
<script type="text/javascript">
    (function () {
        var plannedView = <?php echo json_encode($plannedView); ?>;
        var plannedFacet = <?php echo json_encode($__pv['facet']); ?>;

        /**
         * Re-read the active filter dropdowns so an in-place refresh shows the
         * same rows the filter form would have produced on a full reload.
         */
        function plannedCurrentFilters() {
            var params = { view: plannedView };
            var f = document.getElementById('filterForm');
            if (f) {
                var selects = f.querySelectorAll('select');
                for (var i = 0; i < selects.length; i++) {
                    if (selects[i].name && selects[i].value !== '') {
                        params[selects[i].name] = selects[i].value;
                    }
                }
            }
            return params;
        }

        function rebindPlannedCheckboxes() {
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

        function refreshPlannedData() {
            return $.ajax({
                url: '../../ajax/planned_data.php',
                type: 'GET',
                dataType: 'json',
                data: plannedCurrentFilters()
            }).done(function (resp) {
                if (!resp || !resp.success) return;

                var table = $('#table');
                if ($.fn.DataTable.isDataTable(table)) {
                    table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
                } else {
                    table.find('tbody').html(resp.rows);
                }
                rebindPlannedCheckboxes();
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Could not refresh the list. Please reload the page.', 'error');
                }
            });
        }

        function bindPlannedPhotoCheckboxes() {
            var selectAll = document.getElementById('cbxMainphoto');
            var cbs = document.getElementsByClassName('chk_deletephoto');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.onchange = function () {
                    for (var j = 0; j < cbs.length; j++) cbs[j].checked = selectAll.checked;
                };
            }
        }

        function postPlanned(formData, busyEl, busyText) {
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
            rebindPlannedCheckboxes();

            // The view modal is a real form; stop it from navigating the page.
            var viewForm = document.getElementById('plannedViewForm');
            if (viewForm) {
                viewForm.addEventListener('submit', function (e) { e.preventDefault(); });
            }

            // ---- Filter changes refresh in place (no page submit) ----
            $('#filterForm').on('change', 'select[data-planned-filter]', function () {
                refreshPlannedData();
            });

            // ---- Delete ----------------------------------------------------
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
                    if (!confirm('Delete ' + ids.length + ' selected record(s)? This cannot be undone.')) return;

                    var fd = new FormData();
                    fd.append('btn_delete', '1');
                    fd.append('view', plannedView);
                    for (var k = 0; k < ids.length; k++) fd.append('chk_delete[]', ids[k]);

                    postPlanned(fd, delBtn, 'Deleting...').done(function (resp) {
                        if (typeof showToast === 'function') {
                            showToast((resp && resp.message) || 'Delete finished.', 'success');
                        }
                        if (resp && resp.deleted > 0) {
                            $('#deleteModal').modal('hide');
                            refreshPlannedData();
                        }
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
                    var form = document.getElementById('plannedAddForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_add', '1');
                    fd.append('view', plannedView);

                    postPlanned(fd, addBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            form.reset();
                            $('#addModal').modal('hide');
                            if (typeof showToast === 'function') showToast(resp.message || 'Activity added.', 'success');
                            refreshPlannedData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not save the activity.', 'error');
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
                    var form = document.getElementById('plannedEditForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_save', '1');
                    fd.append('view', plannedView);

                    postPlanned(fd, saveBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            $('#editModal').modal('hide');
                            if (typeof showToast === 'function') showToast(resp.message || 'Activity updated.', 'success');
                            refreshPlannedData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not update the activity.', 'error');
                        }
                    }).fail(function () {
                        if (typeof showToast === 'function') showToast('Network error while saving.', 'error');
                    });
                });
            }

            // ---- Attachments: add / remove -------------------------------
            var addImgBtn = document.getElementById('btn_addimage');
            if (addImgBtn) {
                addImgBtn.type = 'button';
                addImgBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = $('#view_hidden_id').val();
                    var input = document.getElementById('plannedPhotoInput');
                    if (!input || !input.files || input.files.length === 0) {
                        if (typeof showToast === 'function') showToast('Choose at least one file first.', 'warning');
                        return;
                    }
                    var fd = new FormData();
                    fd.append('btn_addimage', '1');
                    fd.append('view', plannedView);
                    fd.append('hidden_id', id);
                    for (var i = 0; i < input.files.length; i++) fd.append('photos[]', input.files[i]);

                    postPlanned(fd, addImgBtn, 'Uploading...').done(function (resp) {
                        input.value = '';
                        if (resp && resp.success) {
                            if (typeof showToast === 'function') showToast(resp.message || 'Upload finished.', 'success');
                            loadPlannedPhotos(id);
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Upload failed.', 'error');
                        }
                    });
                });
            }

            var remImgBtn = document.getElementById('btn_remove_photo');
            if (remImgBtn) {
                remImgBtn.type = 'button';
                remImgBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = $('#view_hidden_id').val();
                    var ids = [];
                    var checked = document.querySelectorAll('.chk_deletephoto:checked');
                    for (var i = 0; i < checked.length; i++) ids.push(checked[i].value);
                    if (ids.length === 0) {
                        if (typeof showToast === 'function') showToast('Please select at least one file to remove.', 'warning');
                        return;
                    }
                    if (!confirm('Remove ' + ids.length + ' selected file(s)? This cannot be undone.')) return;

                    var fd = new FormData();
                    fd.append('btn_remove', '1');
                    fd.append('view', plannedView);
                    for (var k = 0; k < ids.length; k++) fd.append('chk_deletephoto[]', ids[k]);

                    postPlanned(fd, remImgBtn, 'Removing...').done(function (resp) {
                        if (resp && resp.success) {
                            if (typeof showToast === 'function') showToast(resp.message || 'Files removed.', 'success');
                            loadPlannedPhotos(id);
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Remove failed.', 'error');
                        }
                    });
                });
            }
        });

        // ---- Edit ------------------------------------------------------
        $(document).on('click', '.btn-edit-item', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: '../../ajax/planned_get_item.php?action=item&view=' + encodeURIComponent(plannedView) + '&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (data) {
                $('#edit_hidden_id').val(data.id);
                $('#edit_start').val(data.start || '');
                $('#edit_end').val(data.end || '');
                $('#edit_project').val(data.project || '');
                $('#edit_subproject').val(data.subproject || '');
                $('#edit_indicator').val(data.indicator || '');
                $('#edit_activity').val(data.activity || '');
                $('#edit_training').val(data.training || '');
                $('#edit_municipality').val(data.municipality || '');
                $('#edit_barangay').val(data.barangay || '');
                $('#edit_district').val(data.district || '');
                $('#edit_agency').val(data.agency || '');
                $('#edit_mode').val(data.mode || '');
                $('#edit_sector').val(data.sector || '');
                $('#edit_person').val(data.person || '');
                $('#edit_resource').val(data.resource || '');
                $('#edit_participants').val(data.participants || '');
                $('#edit_completers').val(data.completers || '');
                $('#edit_male').val(data.male || '');
                $('#edit_female').val(data.female || '');
                $('#edit_approved').val(data.approved || '');
                $('#edit_mov').val(data.mov || '');
                $('#edit_remarks').val(data.remarks || '');
                $('#edit_type').val(data.type || '');
                $('#editModal').modal('show');
            }).fail(function () {
                if (typeof showToast === 'function') showToast('Could not load that record.', 'error');
            });
        });

        function plannedThumb(filePath, filename) {
            var ext = filename.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) !== -1) {
                return '<img src="' + filePath + '" alt="' + filename + '" class="file-thumbnail"/>';
            }
            if (ext === 'pdf') {
                return '<div class="file-thumbnail-pdf"><embed src="' + filePath + '" type="application/pdf" width="100%" height="100%" /></div>';
            }
            if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].indexOf(ext) !== -1) {
                return '<div class="file-thumbnail-office"><i class="fas fa-file"></i></div>';
            }
            return '<div class="file-thumbnail-office"><i class="fas fa-file"></i></div>';
        }

        /**
         * Load the attachment grid for one record. Split out so the view modal
         * can call it again after adding or removing a file, which otherwise
         * left the grid showing the pre-change list.
         */
        function loadPlannedPhotos(id) {
            $('#photoGrid').html('<div class="col-md-12 text-center"><p>Loading...</p></div>');
            return $.ajax({
                url: '../../ajax/planned_get_item.php?action=photos&view=' + encodeURIComponent(plannedView) + '&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (photos) {
                var html = '';
                if (!photos || photos.length === 0) {
                    html = '<div class="col-md-12 text-center"><p>No files uploaded yet.</p></div>';
                } else {
                    for (var i = 0; i < photos.length; i++) {
                        var p = photos[i];
                        var filePath = 'photo/' + encodeURIComponent(p.filename);
                        // Uploads are prefixed with a millisecond timestamp; strip
                        // only that leading run so digits inside the real name
                        // ("DICT_13-Response") survive.
                        var displayName = p.filename.replace(/^\d+/, '');
                        html += '<div class="col-md-4">' +
                            '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />' +
                            '<div class="file-item">' + plannedThumb(filePath, p.filename) +
                            '<div class="file-info"><span class="filename">' + displayName + '</span>' +
                            '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                            '</div></div></div>';
                    }
                }
                $('#photoGrid').html(html);
                bindPlannedPhotoCheckboxes();
            }).fail(function () {
                $('#photoGrid').html('<div class="col-md-12 text-center"><p>Could not load files.</p></div>');
                if (typeof showToast === 'function') showToast('Failed to load files.', 'error');
            });
        }

        // ---- View / photos --------------------------------------------
        $(document).on('click', '.btn-view-item', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#view_item_title').text($(this).data('name') || '');
            $('#view_hidden_id').val(id);
            $('#viewModal').modal('show');
            loadPlannedPhotos(id);
        });

        // Expose for the modal scripts in this directory.
        window.plannedView = plannedView;
        window.refreshPlannedData = refreshPlannedData;
        window.loadPlannedPhotos = loadPlannedPhotos;
    })();
</script>
