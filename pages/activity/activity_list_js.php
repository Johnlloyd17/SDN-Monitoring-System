<?php
/**
 * Shared list-refresh + delete + edit/view wiring for the six activity
 * sector pages (cyber / elgu / fwfa / gecs / iidb / ilcdb).
 *
 * Include this after scripts.php and after the DataTable init. Set
 * $activityView to the sector key so the refresh keeps the page's bureau.
 */
if (!isset($activityView)) {
    $activityView = 'cyber';
}

// A page opts into filtering in place by setting $activityFilterAjax = true
// before this include. The other activity pages are left as they are, with
// onchange="this.form.submit()".
$activityFilterAjax = isset($activityFilterAjax) && $activityFilterAjax;
?>
<script type="text/javascript">
    var activitySectorView = <?php echo json_encode($activityView); ?>;
    var activityFilterAjax = <?php echo $activityFilterAjax ? 'true' : 'false'; ?>;

    /**
     * Re-read the active filter dropdowns so an in-place refresh shows the
     * same rows the filter form would have produced on a full reload.
     */
    function activityCurrentFilters() {
        var params = { view: activitySectorView };
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

    function rebindActivityCheckboxes() {
        // The header checkbox carries class="cbxMain" and no id, so it must be
        // found by class. Its inline onchange="checkMain(this)" is left alone:
        // checkMain is the proven master->rows handler.
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

    function refreshActivityData() {
        return $.getJSON('../../ajax/activity_data.php', activityCurrentFilters(), function (resp) {
            if (!resp || !resp.success) return;

            var table = $('#table');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
            } else {
                table.find('tbody').html(resp.rows);
            }

            var cards = document.getElementById('activityCards');
            if (cards && resp.cards) cards.innerHTML = resp.cards;

            // Rebuild the dropdowns so they keep narrowing each other the way a
            // full page reload does, then wire the new selects up again.
            var cells = document.querySelectorAll('.activity-filter-cell');
            if (cells.length > 0 && resp.filters) {
                for (var i = 0; i < cells.length && i < resp.filters.length; i++) {
                    cells[i].innerHTML = resp.filters[i];
                }
                bindActivityFilterForm();
            }

            rebindActivityCheckboxes();
        }).fail(function () {
            if (typeof showToast === 'function') {
                showToast('Could not refresh the list. Please reload the page.', 'error');
            }
        });
    }

    /**
     * On the pages that opted in, drop the inline onchange="this.form.submit()"
     * from each filter dropdown and refresh the rows in place instead. The
     * dropdowns and the rows are both filtered by the same values, so what you
     * see is what the reload used to show.
     */
    function bindActivityFilterForm() {
        if (!activityFilterAjax) return;
        var f = document.getElementById('filterForm');
        if (!f) return;
        var selects = f.querySelectorAll('select');
        for (var j = 0; j < selects.length; j++) {
            selects[j].removeAttribute('onchange');
            selects[j].addEventListener('change', function () {
                refreshActivityData();
            });
        }
    }

    function bindPhotoCheckboxes() {
        var selectAll = document.getElementById('cbxMainphoto');
        var cbs = document.getElementsByClassName('chk_deletephoto');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.onchange = function () {
                for (var j = 0; j < cbs.length; j++) cbs[j].checked = selectAll.checked;
            };
        }
    }

    $(function () {
        rebindActivityCheckboxes();
        bindActivityFilterForm();

        // ---- Delete ----------------------------------------------------
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
                        refreshActivityData();
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

        // ---- Edit ------------------------------------------------------
        $(document).on('click', '.btn-edit-activity', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            $.getJSON('../../ajax/activity_get_item.php?action=item&id=' + encodeURIComponent(id), function (data) {
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
                $('#editModal').modal('show');
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Could not load that record.', 'error');
                }
            });
        });

        // ---- View / photos ---------------------------------------------
        $(document).on('click', '.btn-view-activity', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var activityName = $(this).data('activity');
            $('#view_hidden_id').val(id);
            $('#view_activity_title').text(activityName);
            $('#photoGrid').html('<p class="text-muted">Loading photos...</p>');
            $('#viewModal').modal('show');

            $.getJSON('../../ajax/activity_get_item.php?action=photos&id=' + encodeURIComponent(id), function (photos) {
                if (!photos || photos.length === 0) {
                    $('#photoGrid').html('<p class="text-muted">No photos found.</p>');
                    bindPhotoCheckboxes();
                    return;
                }
                var html = '';
                for (var i = 0; i < photos.length; i++) {
                    var p = photos[i];
                    var filePath = 'photo/' + p.filename;
                    var ext = String(p.filename).split('.').pop().toLowerCase();
                    html += '<div class="col-md-4">';
                    html += '<input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="' + p.id + '" />';
                    html += '<div class="file-item">';
                    if (['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) !== -1) {
                        html += '<img src="' + filePath + '" alt="' + $('<div>').text(p.filename).html() + '" class="file-thumbnail"/>';
                    } else if (ext === 'pdf') {
                        html += '<div class="file-thumbnail-pdf"><embed src="' + filePath + '" type="application/pdf" width="100%" height="100%" /></div>';
                    } else {
                        html += '<div class="file-thumbnail">File type not previewable</div>';
                    }
                    html += '<div class="file-info"><span class="filename">' + String(p.filename).replace(/\d+/g, '') + '</span>';
                    html += '<a href="' + filePath + '" download class="download-btn"><i class="fas fa-download"></i></a></div></div></div>';
                }
                $('#photoGrid').html(html);
                bindPhotoCheckboxes();
            }).fail(function () {
                $('#photoGrid').html('<p class="text-muted">Could not load photos.</p>');
            });
        });
    });
</script>
