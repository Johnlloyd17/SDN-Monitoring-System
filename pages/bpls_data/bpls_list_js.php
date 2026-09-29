<?php
/**
 * List-refresh + CRUD + attachment wiring for pages/bpls_data/bpls.php.
 * refreshBplsData() is what the modals call after a successful save so the
 * table and the filter select update in place instead of reloading the page.
 */
require_once __DIR__ . '/bpls_rows.php';
?>
<script type="text/javascript">
    (function () {
        var BPLS_DATA = '../../ajax/bpls_data.php';
        var BPLS_ITEM = '../../ajax/bpls_get_item.php';

        /**
         * Re-read the active filter select so an in-place refresh shows the same
         * rows the filter form would have produced on a full reload.
         */
        function bplsCurrentFilters() {
            var params = {};
            var selects = document.querySelectorAll('#filterForm select');
            for (var i = 0; i < selects.length; i++) {
                if (selects[i].name && selects[i].value !== '') {
                    params[selects[i].name] = selects[i].value;
                }
            }
            return params;
        }

        function rebindBplsCheckboxes() {
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

        function applyBplsResponse(resp) {
            var table = $('#table');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
            } else {
                table.find('tbody').html(resp.rows);
            }

            // The filter form is replaced wholesale because the option list
            // comes from the same request that applied the filter.
            if (resp.filterForm) {
                $('#filterForm').replaceWith(resp.filterForm);
            }

            rebindBplsCheckboxes();
        }

        function refreshBplsData() {
            return $.ajax({
                url: BPLS_DATA,
                type: 'GET',
                dataType: 'json',
                data: bplsCurrentFilters()
            }).done(function (resp) {
                if (!resp || !resp.success) return;
                applyBplsResponse(resp);
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Could not refresh the list. Please reload the page.', 'error');
                }
            });
        }

        function bindBplsPhotoCheckboxes() {
            var selectAll = document.getElementById('cbxMainphoto');
            var cbs = document.getElementsByClassName('chk_deletephoto');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.onchange = function () {
                    for (var j = 0; j < cbs.length; j++) cbs[j].checked = selectAll.checked;
                };
            }
        }

        function postBpls(formData, busyEl, busyText) {
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
            rebindBplsCheckboxes();

            // The modals are real forms; stop them navigating the page.
            ['bplsAddForm', 'bplsEditForm', 'bplsViewForm'].forEach(function (id) {
                var f = document.getElementById(id);
                if (f) f.addEventListener('submit', function (e) { e.preventDefault(); });
            });

            // ---- Filter changes refresh in place (no page submit) ----
            // Delegated on document because the filter form is replaced on
            // every refresh, so a handler bound to #filterForm would be lost.
            $(document).on('change', '#filterForm select[data-bpls-filter]', function () {
                refreshBplsData();
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
                    if (!confirm('Delete ' + ids.length + ' selected record(s)? This and its files cannot be undone.')) return;

                    var fd = new FormData();
                    fd.append('btn_delete', '1');
                    for (var k = 0; k < ids.length; k++) fd.append('chk_delete[]', ids[k]);

                    postBpls(fd, delBtn, 'Deleting...').done(function (resp) {
                        if (typeof showToast === 'function') {
                            showToast('Deleted ' + ((resp && resp.deleted) || 0) + ' record(s).', 'success');
                        }
                        $('#deleteModal').modal('hide');
                        refreshBplsData();
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
                    var form = document.getElementById('bplsAddForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_add', '1');

                    postBpls(fd, addBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            form.reset();
                            $('#addModal').modal('hide');
                            if (typeof showToast === 'function') showToast('Record added.', 'success');
                            refreshBplsData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not save the record.', 'error');
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
                    var form = document.getElementById('bplsEditForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_save', '1');

                    postBpls(fd, saveBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            $('#editModal').modal('hide');
                            if (typeof showToast === 'function') showToast('Record updated.', 'success');
                            refreshBplsData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not update the record.', 'error');
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
                    var input = document.getElementById('bplsPhotoInput');
                    if (!input || !input.files || input.files.length === 0) {
                        if (typeof showToast === 'function') showToast('Choose at least one file first.', 'warning');
                        return;
                    }
                    var fd = new FormData();
                    fd.append('btn_addimage', '1');
                    fd.append('hidden_id', id);
                    for (var i = 0; i < input.files.length; i++) fd.append('photos[]', input.files[i]);

                    postBpls(fd, addImgBtn, 'Uploading...').done(function (resp) {
                        input.value = '';
                        if (resp && resp.success) {
                            if (typeof showToast === 'function') showToast('Upload finished.', 'success');
                            loadBplsPhotos(id);
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
                    fd.append('hidden_id', id);
                    for (var k = 0; k < ids.length; k++) fd.append('chk_deletephoto[]', ids[k]);

                    postBpls(fd, remImgBtn, 'Removing...').done(function (resp) {
                        if (resp && resp.success) {
                            if (typeof showToast === 'function') showToast('Files removed.', 'success');
                            loadBplsPhotos(id);
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Remove failed.', 'error');
                        }
                    });
                });
            }

            // ---- Import: refresh the list instead of reloading ----------
            var importBtn = document.getElementById('importBtn');
            if (importBtn) {
                importBtn.addEventListener('click', function () {
                    var file = document.getElementById('importFile');
                    if (file) file.click();
                });
            }
            var importFile = document.getElementById('importFile');
            if (importFile) {
                importFile.addEventListener('change', function () {
                    if (!this.files || this.files.length === 0) return;
                    var fd = new FormData();
                    fd.append('file', this.files[0]);

                    fetch('import.php', { method: 'POST', body: fd })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data && data.success) {
                                if (typeof showToast === 'function') showToast('Data imported successfully!', 'success');
                                refreshBplsData();
                            } else {
                                if (typeof showToast === 'function') {
                                    showToast((data && data.error) || 'Import failed.', 'error');
                                }
                            }
                        })
                        .catch(function () {
                            if (typeof showToast === 'function') showToast('Import failed. Check console for details.', 'error');
                        })
                        .then(function () { importFile.value = ''; });
                });
            }
        });

        // ---- Edit ------------------------------------------------------
        $(document).on('click', '.btn-edit-item', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: BPLS_ITEM + '?action=item&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (data) {
                $('#edit_hidden_id').val(data.id);
                // The field list comes from the same definition the endpoint
                // reads, so a new column cannot show up in one place only.
                var fields = $('#bplsEditForm').find('input[name^="txt_edit_"], select[name^="txt_edit_"], textarea[name^="txt_edit_"]');
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

        function bplsThumb(filePath, filename, ext) {
            if (typeof SDMPreview !== 'undefined' && SDMPreview.previewCard) {
                if (['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xlsx', 'csv'].indexOf(ext) !== -1) {
                    return SDMPreview.previewCard(filePath, filename, ext, 'bpls');
                }
            }
            if (['docx', 'pptx'].indexOf(ext) !== -1) {
                return '<div class="file-thumbnail-office"><i class="fas fa-file-word"></i></div>';
            }
            return '<div class="file-thumbnail">File type not previewable</div>';
        }

        /**
         * Load the attachment grid for one record. Split out so the view modal
         * can call it again after adding or removing a file, which otherwise
         * left the grid showing the pre-change list.
         */
        function loadBplsPhotos(id) {
            $('#photoGrid').html('<div class="col-md-12 text-center"><p>Loading...</p></div>');
            return $.ajax({
                url: BPLS_ITEM + '?action=photos&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (photos) {
                var html = '';
                if (!photos || photos.length === 0) {
                    html = '<div class="col-md-12 text-center"><p>No files uploaded yet.</p></div>';
                } else {
                    for (var i = 0; i < photos.length; i++) {
                        var p = photos[i];
                        // Uploads are prefixed with a millisecond timestamp; strip
                        // only that leading run so digits inside the real name survive.
                        var displayName = String(p.filename).replace(/^\d+/, '');
                        html += '<div class="col-md-4">' +
                            '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />' +
                            '<div class="file-item">' + bplsThumb(p.filepath, p.filename, p.type) +
                            '<div class="file-info"><span class="filename">' + displayName + '</span>' +
                            '<a href="' + p.filepath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                            '</div></div></div>';
                    }
                }
                $('#photoGrid').html(html);
                bindBplsPhotoCheckboxes();
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
            loadBplsPhotos(id);
        });

        window.refreshBplsData = refreshBplsData;
        window.loadBplsPhotos = loadBplsPhotos;
    })();
</script>
