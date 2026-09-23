<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!-- Add UAT Modal -->
<div class="modal fade" id="addUatModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="addUatForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-file-signature"></i> Add UAT Record</h4>
                </div>
                <div class="modal-body">

                    <div class="modal-section-header"><i class="fa fa-map-marker"></i> Location Info</div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Municipality <span class="text-danger">*</span></label>
                                <input type="text" name="municipality" class="form-control" required placeholder="e.g., General Luna" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Strategy <span class="text-danger">*</span></label>
                                <input type="text" name="strategy" class="form-control" required placeholder="e.g., PICS-PP P2" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transport Location <span class="text-danger">*</span></label>
                                <input type="text" name="transport_location" class="form-control" required placeholder="e.g., Consuelo Brgy. Hall" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Latitude</label>
                                <input type="number" name="latitude" class="form-control" step="any" min="-90" max="90" placeholder="e.g., 9.796003" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="number" name="longitude" class="form-control" step="any" min="-180" max="180" placeholder="e.g., 126.108861" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Coordinates match the values shown on the physical UAT form (e.g. 9.796003N / 126.108861E).</small>

                    <div class="modal-section-header"><i class="fa fa-boxes"></i> Equipment / CPE Details</div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="uatItemsTable" style="margin-bottom: 5px;">
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
                            <tbody id="uatItemsBody">
                                <tr class="uat-item-row">
                                    <td><input type="number" name="qty[]" class="form-control uat-qty" min="1" placeholder="Qty"></td>
                                    <td><input type="text" name="unit[]" class="form-control" placeholder="e.g., Pcs"></td>
                                    <td><input type="text" name="item_name[]" class="form-control uat-item-name" placeholder="e.g., Access Point"></td>
                                    <td><input type="text" name="description[]" class="form-control" placeholder="e.g., Ruije/Reyee:EG310GH-P-E"></td>
                                    <td><input type="text" name="serial_numbers[]" class="form-control" placeholder="Serial(s), comma separated"></td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button" class="btn btn-danger btn-xs uat-remove-row" title="Remove Row"><i class="fa fa-times"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="addUatRowBtn"><i class="fa fa-plus"></i> Add Row</button>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_uat" class="btn btn-primary"><i class="fa fa-save"></i> Save UAT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    document.getElementById('addUatRowBtn').addEventListener('click', function() {
        var tbody = document.getElementById('uatItemsBody');
        var firstRow = tbody.querySelector('.uat-item-row');
        var newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
        tbody.appendChild(newRow);
    });

    document.getElementById('uatItemsBody').addEventListener('click', function(e) {
        var btn = e.target.closest('.uat-remove-row');
        if (btn) {
            var tbody = document.getElementById('uatItemsBody');
            if (tbody.querySelectorAll('.uat-item-row').length > 1) {
                btn.closest('.uat-item-row').remove();
            } else {
                showToast('At least one equipment row must remain. Clear its fields instead.', 'warning');
            }
        }
    });
});
</script>