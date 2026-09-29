<?php
/**
 * Shared list-refresh + delete + edit/view wiring for the two participant
 * views. Each page sets $participantView to its key ('cyber' or 'ilcdb') before
 * including this file; refreshParticipantData() is what the modals call after a
 * successful save so the list updates without a reload.
 */
require_once __DIR__ . '/participant_rows.php';

$__pv = participant_view_config($participantView);
?>
<script type="text/javascript">
    (function () {
        var participantView = <?php echo json_encode($participantView); ?>;
        var participantExportUrl = <?php echo json_encode($__pv['export']); ?>;
        // column => query-string name expected by the exporter for this view
        var participantExportParams = <?php echo json_encode($__pv['exportParams']); ?>;

        /**
         * Re-read the active filter dropdowns so an in-place refresh shows the
         * same rows the filter form would have produced on a full reload.
         */
        function participantCurrentFilters() {
            var params = { view: participantView };
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

        function rebindParticipantCheckboxes() {
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

        function refreshParticipantData() {
            return $.getJSON('../../ajax/participant_data.php', participantCurrentFilters(), function (resp) {
                if (!resp || !resp.success) return;

                var table = $('#table');
                if ($.fn.DataTable.isDataTable(table)) {
                    table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
                } else {
                    table.find('tbody').html(resp.rows);
                }

                var cards = document.getElementById('participantCards');
                if (cards && resp.cards) cards.innerHTML = resp.cards;

                rebindParticipantCheckboxes();
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Could not refresh the list. Please reload the page.', 'error');
                }
            });
        }

        function bindParticipantPhotoCheckboxes() {
            var selectAll = document.getElementById('cbxMainphoto');
            var cbs = document.getElementsByClassName('chk_deletephoto');
            if (selectAll) {
                selectAll.checked = false;
                selectAll.onchange = function () {
                    for (var j = 0; j < cbs.length; j++) cbs[j].checked = selectAll.checked;
                };
            }
        }

        // ---- Delete ----------------------------------------------------
        $(function () {
            rebindParticipantCheckboxes();

            var delBtn = document.getElementById('btn_delete');
            if (delBtn) {
                // The confirm modal ships a real submit input; take it out of the
                // surrounding form so "Yes" no longer posts the whole page.
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

                    var label = delBtn.value;
                    delBtn.disabled = true;
                    delBtn.value = 'Deleting...';

                    $.ajax({
                        url: 'function.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { btn_delete: '1', 'chk_delete[]': ids }
                    }).done(function (resp) {
                        if (typeof showToast === 'function') {
                            showToast((resp && resp.message) || 'Delete finished.', (resp && resp.type) || 'success');
                        }
                        if (resp && resp.deleted > 0) {
                            $('#deleteModal').modal('hide');
                            refreshParticipantData();
                        }
                    }).fail(function () {
                        if (typeof showToast === 'function') {
                            showToast('Network error while deleting.', 'error');
                        }
                    }).always(function () {
                        delBtn.disabled = false;
                        delBtn.value = label;
                    });
                });
            }

            // ---- Filter changes refresh in place (no page submit) ----
            $('#filterForm').on('change', '.filterSelect', function () {
                refreshParticipantData();
            });

            // ---- Export: hand off to the exporter, a real navigation ----
            var exportBtn = document.getElementById('exportBtn');
            if (exportBtn) {
                exportBtn.onclick = function () {
                    var current = participantCurrentFilters();
                    var query = [];
                    for (var col in participantExportParams) {
                        if (current[col]) {
                            query.push(encodeURIComponent(participantExportParams[col]) + '=' + encodeURIComponent(current[col]));
                        }
                    }
                    window.location.href = participantExportUrl + (query.length ? '?' + query.join('&') : '');
                };
            }

            // ---- Import: refresh the list instead of reloading ----
            var importBtn = document.getElementById('importBtn');
            var importFile = document.getElementById('importFile');
            if (importBtn && importFile) {
                importBtn.onclick = function () { importFile.click(); };
                importFile.onchange = function () {
                    if (!this.files || !this.files[0]) return;
                    var formData = new FormData();
                    formData.append('file', this.files[0]);
                    formData.append('view', participantView);

                    importBtn.disabled = true;
                    fetch('import.php', { method: 'POST', body: formData })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.success) {
                                if (typeof showToast === 'function') showToast('Data imported successfully!', 'success');
                                refreshParticipantData();
                            } else {
                                if (typeof showToast === 'function') showToast(data.error || 'Import failed.', 'error');
                            }
                        })
                        .catch(function (error) {
                            if (typeof showToast === 'function') showToast('Import failed. Check console for details.', 'error');
                        })
                        .then(function () {
                            importBtn.disabled = false;
                            importFile.value = '';
                        });
                };
            }
        });

        // ---- Edit ------------------------------------------------------
        $(document).on('click', '.btn-edit-item', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: '../../ajax/participant_get_item.php?action=item&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (data) {
                $('#edit_hidden_id').val(data.id);
                $('#edit_start').val(data.start || '');
                $('#edit_end').val(data.end || '');
                $('#edit_activity').val(data.activity || '');
                $('#edit_indicator').val(data.indicator || '');
                $('#edit_fullname').val(data.fullname || '');
                $('#edit_sex').val(data.sex || '');
                $('#edit_contact').val(data.contact || '');
                $('#edit_email').val(data.email || '');
                $('#edit_mode').val(data.mode || '');
                $('#edit_agency').val(data.agency || '');
                $('#edit_sector').val(data.sector || '');
                $('#edit_project').val(data.project || '');
                $('#edit_person').val(data.person || '');
                $('#edit_remarks').val(data.remarks || '');
                $('#editModal').modal('show');
            }).fail(function () {
                if (typeof showToast === 'function') showToast('Could not load that record.', 'error');
            });
        });

        /**
         * Load the attachment grid for one record. Split out so the view modal
         * can call it again after adding or removing a file, which otherwise
         * left the grid showing the pre-change list.
         */
        function loadParticipantPhotos(id) {
            $('#photoGrid').html('<div class="col-md-12 text-center"><p>Loading...</p></div>');

            return $.ajax({
                url: '../../ajax/participant_get_item.php?action=photos&id=' + encodeURIComponent(id),
                method: 'GET',
                dataType: 'json'
            }).done(function (photos) {
                var html = '';
                if (!photos || photos.length === 0) {
                    html = '<div class="col-md-12 text-center"><p>No photos found.</p></div>';
                } else {
                    for (var i = 0; i < photos.length; i++) {
                        var p = photos[i];
                        var filePath = 'photo/' + encodeURIComponent(p.filename);
                        var ext = p.filename.split('.').pop().toLowerCase();
                        var thumb;
                        if (['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xlsx', 'csv'].indexOf(ext) !== -1) {
                            thumb = SDMPreview.previewCard(filePath, p.filename, ext, 'participant');
                        } else if (['docx', 'pptx'].indexOf(ext) !== -1) {
                            thumb = '<div class="file-thumbnail-office"><i class="fas fa-file-word"></i></div>';
                        } else {
                            thumb = '<div class="file-thumbnail">File type not previewable</div>';
                        }
                        var nameWithoutNums = p.filename.replace(/\d+/g, '');
                        html += '<div class="col-md-4">' +
                            '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />' +
                            '<div class="file-item">' + thumb +
                            '<div class="file-info"><span class="filename">' + nameWithoutNums + '</span>' +
                            '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a>' +
                            '</div></div></div>';
                    }
                }
                $('#photoGrid').html(html);
                bindParticipantPhotoCheckboxes();
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
            loadParticipantPhotos(id);
        });

        // Expose for the modal scripts in this directory.
        window.participantView = participantView;
        window.refreshParticipantData = refreshParticipantData;
        window.loadParticipantPhotos = loadParticipantPhotos;
    })();
</script>
