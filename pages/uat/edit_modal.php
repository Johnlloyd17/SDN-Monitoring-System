<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!-- View / Edit UAT Modal (re-used; view = read-only mode) -->
<div class="modal fade" id="editUatModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="editUatForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="editUatModalTitle"><i class="fa fa-map-marker"></i> UAT Detail</h4>
                </div>
                <div class="modal-body">

                    <input type="hidden" name="uat_id" id="editUatId" value="">

                    <div class="modal-section-header"><i class="fa fa-map-marker"></i> Location Info</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Municipality <span class="text-danger">*</span></label>
                                <input type="text" name="municipality" id="editUatMunicipality" class="form-control" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Strategy <span class="text-danger">*</span></label>
                                <input type="text" name="strategy" id="editUatStrategy" class="form-control" required autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transport Location <span class="text-danger">*</span></label>
                                <input type="text" name="transport_location" id="editUatTransportLocation" class="form-control" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Latitude</label>
                                <input type="number" name="latitude" id="editUatLatitude" class="form-control" step="any" min="-90" max="90" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="number" name="longitude" id="editUatLongitude" class="form-control" step="any" min="-180" max="180" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <small class="text-muted" id="editUatCoordsHint">Printed form format: <strong id="editUatCoordsFmt">—</strong></small>

                    <div class="modal-section-header"><i class="fa fa-boxes"></i> Equipment / CPE Details</div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="editUatItemsTable" style="margin-bottom: 5px;">
                            <thead>
                                <tr>
                                    <th style="width: 6%;">Qty</th>
                                    <th style="width: 8%;">Unit</th>
                                    <th style="width: 20%;">Item Name <span class="text-danger">*</span></th>
                                    <th style="width: 24%;">Description / Brand / Model</th>
                                    <th style="width: 28%;">Serial Numbers</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="editUatItemsBody"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="addEditUatRowBtn"><i class="fa fa-plus"></i> Add Row</button>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="editUatCloseBtn">Close</button>
                    <button type="submit" name="edit_uat" class="btn btn-primary" id="editUatSaveBtn"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var uatViewMode = false;

    function coordFmt(lat, lng) {
        var parts = [];
        if (lat !== '' && lat !== null && !isNaN(parseFloat(lat))) {
            var v = parseFloat(lat);
            parts.push(Math.abs(v).toFixed(6) + (v < 0 ? 'S' : 'N'));
        }
        if (lng !== '' && lng !== null && !isNaN(parseFloat(lng))) {
            var w = parseFloat(lng);
            parts.push(Math.abs(w).toFixed(6) + (w < 0 ? 'W' : 'E'));
        }
        return parts.join('  ') || '';
    }

    function updateCoordsHint() {
        var lat = document.getElementById('editUatLatitude').value;
        var lng = document.getElementById('editUatLongitude').value;
        var fmt = coordFmt(lat, lng);
        document.getElementById('editUatCoordsFmt').textContent = fmt ? fmt : '—';
    }

    function setMode(edit) {
        uatViewMode = !edit;
        var disabled = uatViewMode;
        var form = document.getElementById('editUatForm');
        form.querySelectorAll('input').forEach(function(inp) { inp.disabled = disabled; });

        document.getElementById('editUatSaveBtn').style.display = disabled ? 'none' : '';
        document.getElementById('editUatCloseBtn').textContent = disabled ? 'Close' : 'Cancel';
        document.getElementById('addEditUatRowBtn').style.display = disabled ? 'none' : '';
        document.querySelectorAll('#editUatItemsBody .uat-remove-row').forEach(function(b) { b.style.display = disabled ? 'none' : ''; });
    }

    function addEditRow(data) {
        var tbody = document.getElementById('editUatItemsBody');
        var tr = document.createElement('tr');
        tr.className = 'uat-item-row';

        var td = function(html) { var d = document.createElement('td'); d.innerHTML = html; return d; };

        tr.appendChild(td('<input type="number" name="qty[]" class="form-control uat-qty" min="1" value="' + (data.qty || 1) + '">'));
        tr.appendChild(td('<input type="text" name="unit[]" class="form-control" value="' + (data.unit || '') + '">'));
        tr.appendChild(td('<input type="text" name="item_name[]" class="form-control uat-item-name" value="' + (data.item_name || '') + '">'));
        tr.appendChild(td('<input type="text" name="description[]" class="form-control" value="' + (data.description || '') + '">'));
        tr.appendChild(td('<input type="text" name="serial_numbers[]" class="form-control" value="' + (data.serial_numbers || '') + '">'));

        var actionTd = document.createElement('td');
        actionTd.setAttribute('style', 'text-align: center; vertical-align: middle;');
        actionTd.innerHTML = '<button type="button" class="btn btn-danger btn-xs uat-remove-row" title="Remove Row"><i class="fa fa-times"></i></button>';
        tr.appendChild(actionTd);

        tbody.appendChild(tr);
    }

    window.openUatDetail = function(id, mode) {
        if (mode === 'view') {
            document.getElementById('editUatModalTitle').innerHTML = '<i class="fa fa-eye"></i> View UAT Location';
        } else {
            document.getElementById('editUatModalTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit UAT Location';
        }

        $.getJSON('function.php?action=get_uat&id=' + id, function(resp) {
            if (!resp.success) {
                showToast(resp.message || 'Failed to load UAT record.', 'error');
                return;
            }
            var h = resp.uat;

            document.getElementById('editUatId').value = h.id;
            document.getElementById('editUatMunicipality').value = h.municipality;
            document.getElementById('editUatStrategy').value = h.strategy;
            document.getElementById('editUatTransportLocation').value = h.transport_location;
            document.getElementById('editUatLatitude').value = h.latitude;
            document.getElementById('editUatLongitude').value = h.longitude;
            updateCoordsHint();

            var tbody = document.getElementById('editUatItemsBody');
            tbody.innerHTML = '';
            if (resp.items.length === 0) {
                addEditRow({});
            } else {
                resp.items.forEach(function(it) { addEditRow(it); });
            }

            setMode(mode === 'edit');
            $('#editUatModal').modal('show');
        }).fail(function() {
            showToast('Network error loading UAT record.', 'error');
        });
    };

    document.getElementById('addEditUatRowBtn').addEventListener('click', function() {
        addEditRow({});
    });

    document.getElementById('editUatItemsBody').addEventListener('click', function(e) {
        var btn = e.target.closest('.uat-remove-row');
        if (btn && !uatViewMode) {
            var tbody = document.getElementById('editUatItemsBody');
            if (tbody.querySelectorAll('.uat-item-row').length > 1) {
                btn.closest('.uat-item-row').remove();
            } else {
                showToast('At least one equipment row must remain. Clear its fields instead.', 'warning');
            }
        }
    });

    document.getElementById('editUatLatitude').addEventListener('input', updateCoordsHint);
    document.getElementById('editUatLongitude').addEventListener('input', updateCoordsHint);
});
</script>