<!-- Add Pass Slip Modal -->
<div class="modal fade" id="addPassSlipModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="addPassSlipForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Create Office Equipment Pass Slip</h4>
                </div>
                <div class="modal-body">
                    <!-- Pass Slip Number & Date -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pass Slip No. <span class="text-danger">*</span></label>
                                <input type="text" name="pass_slip_no" class="form-control"
                                    placeholder="e.g., PS-2026-0001" id="passSlipNo">
                                <small class="text-muted">Auto-generated if left empty</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pull-Out Date <span class="text-danger">*</span></label>
                                <input type="date" name="pullout_date" class="form-control" required
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-boxes"></i> Item Details</h5>

                    <!-- Dynamic Items Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable" style="margin-bottom: 5px;">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Select Item <span class="text-danger">*</span></th>
                                    <th style="width: 20%;">Description</th>
                                    <th style="width: 10%;">Qty <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Unit</th>
                                    <th style="width: 10%;">Available</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <select name="inventory_id[]" class="form-control inventory-select" required>
                                            <option value="">-- Select Item --</option>
                                            <?php
                                            include "../connection.php";
                                            $itemQuery = "SELECT id, description, classification, quantity, unit, item_type FROM inventory WHERE item_type = 'equipment' AND project != '' AND quantity > 0 ORDER BY description ASC";
                                            $itemResult = mysqli_query($con, $itemQuery);
                                            $inventoryItems = array();
                                            if ($itemResult) {
                                                while ($item = mysqli_fetch_assoc($itemResult)) {
                                                    $inventoryItems[] = $item;
                                                    echo '<option value="' . $item['id'] . '" 
                                                        data-desc="' . htmlspecialchars($item['description']) . '"
                                                        data-class="' . htmlspecialchars($item['classification']) . '"
                                                        data-qty="' . $item['quantity'] . '"
                                                        data-unit="' . $item['unit'] . '">' 
                                                        . htmlspecialchars($item['description']) . ' (' . $item['classification'] . ') - Qty: ' . $item['quantity'] . ' ' . $item['unit'] 
                                                        . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="item_description[]" class="form-control" readonly placeholder="Auto-filled"></td>
                                    <td><input type="number" name="qty[]" class="form-control" required min="1" placeholder="Qty"></td>
                                    <td><input type="text" name="unit[]" class="form-control" readonly placeholder="Auto-filled"></td>
                                    <td><input type="text" class="form-control" readonly placeholder="Qty"></td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button" class="btn btn-danger btn-xs remove-row" title="Remove Item"><i class="fa fa-times"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="addRowBtn"><i class="fa fa-plus"></i> Add Item</button>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="2" required placeholder="Purpose of borrowing..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Condition on Pull-Out</label>
                                <select name="condition_out" class="form-control">
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-signature"></i> Pull-Out Authorization</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Requested By <span class="text-danger">*</span></label>
                                <input type="text" name="requested_by_out" class="form-control" required placeholder="Name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inspected By <span class="text-danger">*</span></label>
                                <input type="text" name="inspected_by_out" class="form-control" required placeholder="Name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Approved By <span class="text-danger">*</span></label>
                                <input type="text" name="approved_by_out" class="form-control" required placeholder="Name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remarks</label>
                                <input type="text" name="remarks" class="form-control" placeholder="Additional remarks...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_pass_slip" class="btn btn-primary"><i class="fa fa-save"></i> Create Pass Slip</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inventory items data for dynamic row creation
    var inventoryData = <?php echo json_encode($inventoryItems ?? []); ?>;

    // Auto-fill fields when inventory is selected
    function bindInventorySelect(selectEl) {
        selectEl.addEventListener('change', function() {
            var row = this.closest('.item-row');
            var descInput = row.querySelector('input[name="item_description[]"]');
            var unitInput = row.querySelector('input[name="unit[]"]');
            var qtyInput = row.querySelector('input[name="qty[]"]');
            var availInput = row.querySelectorAll('input')[3];

            if (this.value) {
                var selected = this.options[this.selectedIndex];
                descInput.value = selected.getAttribute('data-desc') || '';
                unitInput.value = selected.getAttribute('data-unit') || '';
                qtyInput.value = 1;
                qtyInput.max = selected.getAttribute('data-qty');
                availInput.value = selected.getAttribute('data-qty') || '';
            } else {
                descInput.value = '';
                unitInput.value = '';
                qtyInput.value = '';
                availInput.value = '';
            }
        });
    }

    // Bind existing select
    var firstSelect = document.querySelector('.inventory-select');
    if (firstSelect) bindInventorySelect(firstSelect);

    // Add new item row
    document.getElementById('addRowBtn').addEventListener('click', function() {
        var tbody = document.getElementById('itemsBody');
        var newRow = document.createElement('tr');
        newRow.className = 'item-row';

        var optionsHtml = '<option value="">-- Select Item --</option>';
        inventoryData.forEach(function(item) {
            optionsHtml += '<option value="' + item.id + '" '
                + 'data-desc="' + (item.description || '').replace(/"/g, '&quot;') + '" '
                + 'data-class="' + (item.classification || '').replace(/"/g, '&quot;') + '" '
                + 'data-qty="' + item.quantity + '" '
                + 'data-unit="' + (item.unit || '').replace(/"/g, '&quot;') + '">'
                + (item.description || '') + ' (' + (item.classification || '') + ') - Qty: ' + item.quantity + ' ' + (item.unit || '')
                + '</option>';
        });

        newRow.innerHTML = ''
            + '<td>'
            + '  <select name="inventory_id[]" class="form-control inventory-select" required>'
            + '    ' + optionsHtml
            + '  </select>'
            + '</td>'
            + '<td><input type="text" name="item_description[]" class="form-control" readonly placeholder="Auto-filled"></td>'
            + '<td><input type="number" name="qty[]" class="form-control" required min="1" placeholder="Qty"></td>'
            + '<td><input type="text" name="unit[]" class="form-control" readonly placeholder="Auto-filled"></td>'
            + '<td><input type="text" class="form-control" readonly placeholder="Qty"></td>'
            + '<td style="text-align: center; vertical-align: middle;">'
            + '  <button type="button" class="btn btn-danger btn-xs remove-row" title="Remove Item"><i class="fa fa-times"></i></button>'
            + '</td>';

        tbody.appendChild(newRow);

        // Bind the new select
        var newSelect = newRow.querySelector('.inventory-select');
        bindInventorySelect(newSelect);
    });

    // Remove item row (event delegation)
    document.getElementById('itemsBody').addEventListener('click', function(e) {
        var btn = e.target.closest('.remove-row');
        if (btn) {
            var tbody = document.getElementById('itemsBody');
            if (tbody.querySelectorAll('.item-row').length > 1) {
                btn.closest('.item-row').remove();
            } else {
                alert('At least one item is required.');
            }
        }
    });
});
</script>
