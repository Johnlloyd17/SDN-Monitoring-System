<!-- Add Inventory Custodian Slip Modal -->
<div class="modal fade" id="addIcsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="addIcsForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-clipboard"></i> Create Inventory Custodian Slip</h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ICS No. <span class="text-danger">*</span></label>
                                <input type="text" name="ics_no" class="form-control" id="icsNo"
                                    placeholder="e.g., 13202409-099-1">
                                <small class="text-muted">Auto-generated if left empty (e.g., 13+YYYY+MM-seq-1)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Issued <span class="text-danger">*</span></label>
                                <input type="date" name="date_issued" class="form-control" required
                                    value="<?php echo date('Y-m-d'); ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-boxes"></i> Item Details</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="icsItemsTable" style="margin-bottom: 5px;">
                            <thead>
                                <tr>
                                    <th style="width: 6%;">Qty <span class="text-danger">*</span></th>
                                    <th style="width: 9%;">Unit</th>
                                    <th style="width: 24%;">Description <span class="text-danger">*</span></th>
                                    <th style="width: 12%;">Unit Cost <span class="text-danger">*</span></th>
                                    <th style="width: 11%;">Date Acquired</th>
                                    <th style="width: 15%;">Inventory Item No.</th>
                                    <th style="width: 11%;">Est. Useful Life</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="icsItemsBody">
                                <tr class="ics-item-row">
                                    <td><input type="number" name="qty[]" class="form-control ics-qty" required min="1" placeholder="Qty"></td>
                                    <td><input type="text" name="unit[]" class="form-control" placeholder="UNIT"></td>
                                    <td><textarea name="description[]" class="form-control ics-desc" rows="2" required placeholder="e.g., PRINTER&#10;HP LaserJet&#10;Serial: XXX"></textarea></td>
                                    <td><input type="number" name="unit_cost[]" class="form-control ics-cost" required min="0" step="0.01" placeholder="0.00"></td>
                                    <td><input type="date" name="date_acquired[]" class="form-control" autocomplete="off"></td>
                                    <td><input type="text" name="inventory_item_no[]" class="form-control ics-inv-no" placeholder="Auto = ICS No."></td>
                                    <td><input type="text" name="estimated_useful_life[]" class="form-control" placeholder="e.g., 5 years"></td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button" class="btn btn-danger btn-xs ics-remove-row" title="Remove Item"><i class="fa fa-times"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="addIcsRowBtn"><i class="fa fa-plus"></i> Add Item</button>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12 text-right">
                            <div class="form-group" style="font-size: 16px;">
                                <strong>TOTAL: &#8369; <span id="icsTotalDisplay">0.00</span></strong>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-signature"></i> Signees</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Received By (Name)</label>
                                <input type="text" name="received_by" class="form-control" placeholder="Full Name">
                            </div>
                            <div class="form-group">
                                <label>Received By (Position/Designation)</label>
                                <input type="text" name="received_by_position" class="form-control" placeholder="e.g., Regional Director, Caraga Region">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Received From (Name)</label>
                                <input type="text" name="received_from" class="form-control" placeholder="Full Name">
                            </div>
                            <div class="form-group">
                                <label>Received From (Position/Designation)</label>
                                <input type="text" name="received_from_position" class="form-control" placeholder="e.g., Technical Operations Division Chief">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Received</label>
                                <input type="date" name="date_received" class="form-control" autocomplete="off">
                                <small class="text-muted">Optional - leave blank for physical signing after printing</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_ics" class="btn btn-primary"><i class="fa fa-save"></i> Save ICS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    function calculateIcsTotal() {
        var rows = document.querySelectorAll('#icsItemsBody .ics-item-row');
        var total = 0;
        for (var i = 0; i < rows.length; i++) {
            var qty = parseFloat(rows[i].querySelector('.ics-qty').value) || 0;
            var cost = parseFloat(rows[i].querySelector('.ics-cost').value) || 0;
            total += qty * cost;
        }
        document.getElementById('icsTotalDisplay').textContent = total.toFixed(2);
    }

    function syncInvNos() {
        var icsNoValue = document.getElementById('icsNo').value.trim();
        if (!icsNoValue) return;
        var rows = document.querySelectorAll('#icsItemsBody .ics-item-row');
        for (var i = 0; i < rows.length; i++) {
            var input = rows[i].querySelector('.ics-inv-no');
            if (!input.value.trim()) {
                input.value = icsNoValue;
            }
        }
    }

    document.getElementById('icsNo').addEventListener('change', function() {
        syncInvNos();
    });
    document.getElementById('icsNo').addEventListener('input', function() {
        syncInvNos();
    });

    document.getElementById('addIcsRowBtn').addEventListener('click', function() {
        var tbody = document.getElementById('icsItemsBody');
        var firstRow = tbody.querySelector('.ics-item-row');
        var newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
        newRow.querySelectorAll('textarea').forEach(function(ta) { ta.value = ''; });
        tbody.appendChild(newRow);
        syncInvNos();
    });

    document.getElementById('icsItemsBody').addEventListener('click', function(e) {
        var btn = e.target.closest('.ics-remove-row');
        if (btn) {
            var tbody = document.getElementById('icsItemsBody');
            if (tbody.querySelectorAll('.ics-item-row').length > 1) {
                btn.closest('.ics-item-row').remove();
                calculateIcsTotal();
            } else {
                showToast('At least one item is required.', 'warning');
            }
        }
    });

    document.getElementById('icsItemsBody').addEventListener('input', function(e) {
        if (e.target.classList.contains('ics-qty') || e.target.classList.contains('ics-cost')) {
            calculateIcsTotal();
        }
    });

    calculateIcsTotal();
});
</script>