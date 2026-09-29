<?php
/**
 * Shared list-refresh + delete wiring for the per-sector Tech4Ed pages
 * (center / lgu / nga / private / ris / school / operational / nonoperational).
 *
 * Include this after scripts.php. Set $tech4edView to the sector key so the
 * refresh keeps the page's fixed base condition.
 */
if (!isset($tech4edView)) {
    $tech4edView = 'all';
}
?>
<script type="text/javascript">
    var tech4edSectorView = <?php echo json_encode($tech4edView); ?>;

    function rebindTech4edCheckboxes() {
        var master = document.getElementById('cbxMain');
        var boxes = document.getElementsByClassName('chk_delete');
        if (master) master.checked = false;
        if (master) {
            master.onchange = function () {
                for (var i = 0; i < boxes.length; i++) boxes[i].checked = master.checked;
            };
        }
        for (var i = 0; i < boxes.length; i++) {
            boxes[i].onchange = function () {
                var all = document.getElementsByClassName('chk_delete');
                if (this.checked === false && master) master.checked = false;
                if (master && document.querySelectorAll('.chk_delete:checked').length === all.length) {
                    master.checked = true;
                }
            };
        }
    }

    function checkMain(el) {
        var boxes = document.getElementsByClassName('chk_delete');
        for (var i = 0; i < boxes.length; i++) boxes[i].checked = el.checked;
    }

    function refreshTech4edData() {
        var params = { view: tech4edSectorView };
        var f = document.getElementById('filterForm');
        if (f) {
            ['municipality', 'barangay', 'category', 'year'].forEach(function (n) {
                var el = f.querySelector('[name="' + n + '"]');
                if (el && el.value !== '') params[n] = el.value;
            });
        }

        return $.getJSON('../../ajax/tech4ed_data.php', params, function (resp) {
            if (!resp || !resp.success) return;
            var table = $('#table');
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().clear().rows.add($.parseHTML(resp.rows, table[0], false)).draw();
            } else {
                table.find('tbody').html(resp.rows);
            }
            rebindTech4edCheckboxes();
        }).fail(function () {
            if (typeof showToast === 'function') {
                showToast('Could not refresh the list. Please reload the page.', 'error');
            }
        });
    }

    $(function () {
        rebindTech4edCheckboxes();

        var btn = document.getElementById('btn_delete');
        if (!btn) return;
        btn.type = 'button';
        btn.addEventListener('click', function () {
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

            var label = btn.value;
            btn.disabled = true;
            btn.value = 'Deleting...';

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
                    refreshTech4edData();
                }
            }).fail(function () {
                if (typeof showToast === 'function') {
                    showToast('Network error while deleting.', 'error');
                }
            }).always(function () {
                btn.disabled = false;
                btn.value = label;
            });
        });
    });
</script>
