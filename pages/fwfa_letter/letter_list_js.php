<?php
/**
 * List-refresh + CRUD + attachment wiring for pages/fwfa_letter/letters.php.
 * refreshLetterData() is what the modals call after a successful save so the
 * table, the four summary cards and the cascading filters all update in place
 * instead of reloading the page.
 */
require_once __DIR__ . '/letter_rows.php';
?>
<script type="text/javascript">
    (function () {
        var LETTER_DATA = '../../ajax/letter_data.php';
        var LETTER_ITEM = '../../ajax/letter_get_item.php';

        /**
         * Re-read the active filter dropdowns so an in-place refresh shows the
         * same rows the filter form would have produced on a full reload.
         */
        function letterCurrentFilters() {
            var params = {};
            var selects = document.querySelectorAll('#filterForm select');
            for (var i = 0; i < selects.length; i++) {
                if (selects[i].name && selects[i].value !== '') {
                    params[selects[i].name] = selects[i].value;
                }
            }
            return params;
        }

        function rebindLetterCheckboxes() {
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

        function applyLetterResponse(resp) {
            var table = $('#table');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
            } else {
                table.find('tbody').html(resp.rows);
            }

            // The filter form is replaced wholesale because the dropdown option
            // lists cascade; the server re-renders the current selection.
            if (resp.filterForm) {
                $('#filterForm').replaceWith(resp.filterForm);
            }

            if (resp.stats) {
                for (var key in resp.stats) {
                    if (!Object.prototype.hasOwnProperty.call(resp.stats, key)) continue;
                    var el = document.getElementById('stat' + key.charAt(0).toUpperCase() + key.slice(1));
                    if (el) el.textContent = resp.stats[key];
                }
            }

            rebindLetterCheckboxes();
        }

        function refreshLetterData() {
            return $.ajax({
                url: LETTER_DATA,
                type: 'GET',
                dataType: 'json',
                data: letterCurrentFilters()
            }).done(function (resp) {
                if (!resp || !resp.success) return;
                applyLetterResponse(resp);
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Could not refresh the list. Please reload the page.', 'error');
                }
            });
        }

        function bindLetterPhotoCheckboxes() {
            var selectAll = document.getElementById('cbxMainphoto');
            var cbs = document.getElementsByClassName('chk_deletephoto');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.onchange = function () {
                    for (var j = 0; j < cbs.length; j++) cbs[j].checked = selectAll.checked;
                };
            }
        }

        function postLetter(formData, busyEl, busyText) {
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
            rebindLetterCheckboxes();

            // The modals are real forms; stop them navigating the page.
            ['letterAddForm', 'letterEditForm', 'letterViewForm'].forEach(function (id) {
                var f = document.getElementById(id);
                if (f) f.addEventListener('submit', function (e) { e.preventDefault(); });
            });

            // ---- Filter changes refresh in place (no page submit) ----
            // Delegated on document because the filter form is replaced on
            // every refresh, so a handler bound to #filterForm would be lost.
            $(document).on('change', '#filterForm select[data-letter-filter]', function () {
                refreshLetterData();
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

                    postLetter(fd, delBtn, 'Deleting...').done(function (resp) {
                        if (typeof showToast === 'function') {
                            showToast('Deleted ' + ((resp && resp.deleted) || 0) + ' record(s).', 'success');
                        }
                        $('#deleteModal').modal('hide');
                        refreshLetterData();
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
                    var form = document.getElementById('letterAddForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_add', '1');

                    postLetter(fd, addBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            form.reset();
                            $('#addModal').modal('hide');
                            if (typeof showToast === 'function') showToast('Letter added.', 'success');
                            refreshLetterData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not save the letter.', 'error');
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
                    var form = document.getElementById('letterEditForm');
                    if (!form) return;
                    var fd = new FormData(form);
                    fd.append('btn_save', '1');

                    postLetter(fd, saveBtn, 'Saving...').done(function (resp) {
                        if (resp && resp.success) {
                            $('#editModal').modal('hide');
                            if (typeof showToast === 'function') showToast('Letter updated.', 'success');
                            refreshLetterData();
                        } else if (typeof showToast === 'function') {
                            showToast((resp && resp.error) || 'Could not update the letter.', 'error');
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
                    var input = document.getElementById('letterPhotoInput');
                    if (!input || !input.files || input.files.length === 0) {
                        if (typeof showToast === 'function') showToast('Choose at least one file first.', 'warning');
                        return;
                    }
                    var fd = new FormData();
                    fd.append('btn_addimage', '1');
                    fd.append('hidden_id', id);
                    for (var i = 0; i < input.files.length; i++) fd.append('photos[]', input.files[i]);

                    postLetter(fd, addImgBtn, 'Uploading...').done(function (resp) {
                        input.value = '';
                        if (resp && resp.success) {
                            if (typeof showToast === 'function') showToast('Upload finished.', 'success');
                            loadLetterPhotos(id);
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

                    postLetter(fd, remImgBtn, 'Removing...').done(function (resp) {
                        if (resp && resp.success) {
                            if (typeof showToast === 'function') showToast('Files removed.', 'success');
                            loadLetterPhotos(id);
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
                    document.getElementById('importFile').click();
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
                                refreshLetterData();
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
                url: LETTER_ITEM + '?action=item&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (data) {
                $('#edit_hidden_id').val(data.id);
                $('#edit_locality').val(data.locality || '');
                $('#edit_barangay').val(data.barangay || '');
                $('#edit_district').val(data.district || '');
                $('#edit_location').val(data.location || '');
                $('#edit_date').val(data.date || '');
                $('#edit_year').val(data.year || '');
                $('#edit_type').val(data.type || '');
                $('#edit_status').val(data.status || '');
                $('#edit_accomplished').val(data.accomplished || '');
                $('#edit_remarks').val(data.remarks || '');
                $('#editModal').modal('show');
            }).fail(function () {
                if (typeof showToast === 'function') showToast('Could not load that record.', 'error');
            });
        });

        function letterThumb(filePath, filename, ext) {
            if (typeof SDMPreview !== 'undefined' && SDMPreview.previewCard) {
                if (['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xlsx', 'csv'].indexOf(ext) !== -1) {
                    return SDMPreview.previewCard(filePath, filename, ext, 'letters');
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
        function loadLetterPhotos(id) {
            $('#photoGrid').html('<div class="col-md-12 text-center"><p>Loading...</p></div>');
            return $.ajax({
                url: LETTER_ITEM + '?action=photos&id=' + encodeURIComponent(id),
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
                            '<div class="file-item">' + letterThumb(p.filepath, p.filename, p.type) +
                            '<div class="file-info"><span class="filename">' + displayName + '</span>' +
                            '<a href="' + p.filepath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                            '</div></div></div>';
                    }
                }
                $('#photoGrid').html(html);
                bindLetterPhotoCheckboxes();
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
            loadLetterPhotos(id);
        });

        window.refreshLetterData = refreshLetterData;
        window.loadLetterPhotos = loadLetterPhotos;
    })();
</script>
