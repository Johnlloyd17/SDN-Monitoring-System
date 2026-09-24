<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<style>
    .inv-search-results .list-group-item {
        border-left: none;
        border-right: none;
        border-radius: 0;
        cursor: pointer;
    }
    .inv-search-results .list-group-item:first-child { border-top: none; }
    .inv-search-results .list-group-item:last-child { border-bottom: none; }
    .inv-search-results .list-group-item:hover:not(.disabled-item) { background: #f5f5f5; }
    .inv-search-results .disabled-item {
        cursor: not-allowed;
        opacity: 0.65;
        background: #fafafa;
    }
    .inv-search-results .inv-res-name {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .emp-autocomplete-results .emp-name-item {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .emp-autocomplete-results .emp-name-item.active {
        background: #eef4fb;
    }
    .items-table-scroll {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    .items-table-scroll .table { margin-bottom: 0; }
    .items-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f4f4f4;
    }
</style>
<!-- Add Pass Slip Modal -->
<div class="modal fade" id="addPassSlipModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="addPassSlipForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Generate Office Equipment Pass Slip</h4>
                </div>
                <div class="modal-body">
                    <!-- Pass Slip Number -->
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
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="deployed" selected>Deployed</option>
                                    <option value="borrowed">Borrowed</option>
                                </select>
                                <small class="text-muted">Deployed = installed/issued (non-returnable); Borrowed = out on loan with a return date</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-boxes"></i> Item Details</h5>

                    <!-- Inventory Search (read-only lookup, fills active row) -->
                    <div id="invSearchWrap" class="inv-search-wrap" style="position: relative; margin-bottom: 10px; max-width: 480px;">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" id="invSearchInput" class="form-control inv-search-input" placeholder="Search inventory by description or serial no. (min 2 chars)...">
                        </div>
                        <div id="invSearchResults" class="inv-search-results" style="position: absolute; z-index: 1050; top: 100%; left: 0; right: 0; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.2); display: none; max-height: 260px; overflow-y: auto;"></div>
                    </div>

                    <!-- Dynamic Items Table -->
                    <div class="table-responsive items-table-scroll">
                        <table class="table table-bordered" id="itemsTable" style="margin-bottom: 5px;">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Description <span class="text-danger">*</span></th>
                                    <th style="width: 14%;">Serial No.</th>
                                    <th style="width: 8%;">Qty</th>
                                    <th style="width: 8%;">Unit</th>
                                    <th style="width: 16%;">Pull-Out Date <span class="text-danger">*</span></th>
                                    <th style="width: 16%;">Returned Date</th>
                                    <th style="width: 5%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <input type="hidden" name="id[]" value="">
                                        <input type="hidden" name="inventory_id[]" value="">
                                        <input type="text" name="description[]" class="form-control" required placeholder="Item description">
                                    </td>
                                    <td><input type="text" name="serial_no[]" class="form-control" placeholder="S/N"></td>
<td><input type="number" name="qty[]" class="form-control" min="0" placeholder="Qty"></td>
                                    <td><input type="text" name="unit[]" class="form-control" placeholder="Unit"></td>
                                    <td><input type="date" name="pullout_date[]" class="form-control" required value="<?php echo date('Y-m-d'); ?>"></td>
                                    <td><input type="date" name="return_date[]" class="form-control" placeholder="mm/dd/yyyy"></td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button" class="btn btn-danger btn-xs remove-row" title="Remove Item"><i class="fa fa-times"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="items-toolbar" style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                        <button type="button" class="btn btn-success btn-sm" id="addRowBtn"><i class="fa fa-plus"></i> Add Item</button>
                        <span class="text-muted" id="addItemCount" style="font-size: 12px;">1 Item</span>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="2" required placeholder="Purpose of borrowing..."></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5><i class="fa fa-signature"></i> Pull-Out Authorization</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Requested By <span class="text-danger">*</span></label>
                                <input type="text" name="requested_by_out" id="createRequestedBy" class="form-control" required placeholder="Type to search employee or type manually" autocomplete="off">
                                <input type="hidden" name="requested_by_out_emp_id" id="createRequestedByEmpId" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Inspected By <span class="text-danger">*</span></label>
                                <input type="text" name="inspected_by_out" id="createInspectedBy" class="form-control" required placeholder="Type to search employee or type manually" autocomplete="off">
                                <input type="hidden" name="inspected_by_out_emp_id" id="createInspectedByEmpId" value="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Approved By <span class="text-danger">*</span></label>
                                <input type="text" name="approved_by_out" id="createApprovedBy" class="form-control" required placeholder="Type to search employee or type manually" autocomplete="off">
                                <input type="hidden" name="approved_by_out_emp_id" id="createApprovedByEmpId" value="">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Additional remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_pass_slip" class="btn btn-primary"><i class="fa fa-save"></i> Generate Pass Slip</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var passSlipTodayStr = '<?php echo date("Y-m-d"); ?>';

window.itemRowHtml = function() {
    return ''
        + '<td><input type="hidden" name="id[]" value=""><input type="hidden" name="inventory_id[]" value=""><input type="text" name="description[]" class="form-control" required placeholder="Item description"></td>'
        + '<td><input type="text" name="serial_no[]" class="form-control" placeholder="S/N"></td>'
        + '<td><input type="number" name="qty[]" class="form-control" min="0" placeholder="Qty"></td>'
        + '<td><input type="text" name="unit[]" class="form-control" placeholder="Unit"></td>'
        + '<td><input type="date" name="pullout_date[]" class="form-control" required value="' + passSlipTodayStr + '"></td>'
        + '<td><input type="date" name="return_date[]" class="form-control" placeholder="mm/dd/yyyy"></td>'
        + '<td style="text-align: center; vertical-align: middle;">'
        + '  <button type="button" class="btn btn-danger btn-xs remove-row" title="Remove Item"><i class="fa fa-times"></i></button>'
        + '</td>';
};

window.initItemEditor = function(cfg) {
    var invSearchInput = document.getElementById(cfg.searchInputId);
    var invSearchResults = document.getElementById(cfg.searchResultsId);
    var itemsBody = document.getElementById(cfg.tbodyId);
    var addRowBtn = document.getElementById(cfg.addBtnId);
    if (!itemsBody || !invSearchInput || !invSearchResults) return null;

    var activeRow = null;
    var searchTimer = null;

    var countLabel = cfg.countLabelId ? document.getElementById(cfg.countLabelId) : null;

    function pluralize(n) {
        return n + (n === 1 ? ' Item' : ' Items');
    }

    function updateCount() {
        if (!countLabel) return;
        var n = itemsBody.querySelectorAll('.item-row').length;
        countLabel.textContent = pluralize(n);
    }

    updateCount();

    function currentRow() {
        if (activeRow && itemsBody.contains(activeRow)) return activeRow;
        var rows = itemsBody.querySelectorAll('.item-row');
        return rows.length ? rows[rows.length - 1] : null;
    }

    itemsBody.addEventListener('focusin', function(e) {
        var tr = e.target.closest('.item-row');
        if (tr) activeRow = tr;
    });

    function hideResults() {
        invSearchResults.style.display = 'none';
        invSearchResults.innerHTML = '';
    }

    function makeLabel(cls, text) {
        var s = document.createElement('span');
        s.className = 'label ' + cls;
        s.style.marginLeft = '5px';
        s.textContent = text;
        return s;
    }

    function buildResultRow(item) {
        var d = document.createElement('div');
        d.className = 'list-group-item inv-res-item';
        var qty = parseInt(item.quantity, 10);
        var noStock = isNaN(qty) || qty <= 0;
        var unavailable = !!(item.on_loan || item.deployed || item.on_deployed_slip || noStock);
        if (unavailable) d.classList.add('disabled-item');

        var name = document.createElement('div');
        name.className = 'inv-res-name';
        name.textContent = item.name || '';
        name.title = item.name || '';

        var meta = document.createElement('div');
        meta.appendChild(document.createTextNode('S/N: ' + (item.serial || '-') + ' | Unit: ' + (item.unit || '-') + ' | Available: ' + (isNaN(qty) ? '0' : qty)));
        if (item.on_loan) meta.appendChild(makeLabel('label-danger', 'On loan'));
        if (item.deployed) meta.appendChild(makeLabel('label-warning', 'Deployed'));
        if (item.on_deployed_slip) meta.appendChild(makeLabel('label-warning', 'On deployed slip'));
        if (noStock) meta.appendChild(makeLabel('label-danger', 'No stock'));

        d.appendChild(name);
        d.appendChild(meta);
        return d;
    }

    function fillRow(row, item) {
        if (!row) return;
        var invHidden = row.querySelector('input[name="inventory_id[]"]');
        var desc = row.querySelector('input[name="description[]"]');
        var serial = row.querySelector('input[name="serial_no[]"]');
        var unit = row.querySelector('input[name="unit[]"]');
        if (invHidden) invHidden.value = item.id || '';
        if (desc) desc.value = item.name || '';
        if (serial) serial.value = item.serial || '';
        if (unit) unit.value = item.unit || '';
        hideResults();
        invSearchInput.value = '';
        if (serial) serial.focus();
    }

    function buildNoMatchRow() {
        var empty = document.createElement('div');
        empty.className = 'list-group-item disabled-item text-center text-muted';
        empty.textContent = 'No matching inventory record - you can still type the item manually.';
        return empty;
    }

    function runInvSearch(q) {
        $.getJSON('function.php?action=inventory_search&q=' + encodeURIComponent(q), function(data) {
            invSearchResults.innerHTML = '';
            if (!data || !data.length) {
                invSearchResults.appendChild(buildNoMatchRow());
                invSearchResults.style.display = 'block';
                return;
            }
            data.forEach(function(item) {
                var qty = parseInt(item.quantity, 10);
                var noStock = isNaN(qty) || qty <= 0;
                var d = buildResultRow(item);
                if (!item.on_loan && !item.deployed && !item.on_deployed_slip && !noStock) {
                    d.addEventListener('click', function(e) {
                        e.preventDefault();
                        fillRow(currentRow(), item);
                    });
                } else if (item.on_loan) {
                    d.title = 'This item is currently on loan to another pass slip.';
                } else if (item.deployed) {
                    d.title = 'This item is deployed (assigned to someone).';
                } else if (item.on_deployed_slip) {
                    d.title = 'This item is already on a deployed pass slip.';
                } else {
                    d.title = 'This item has no available stock (quantity is 0).';
                }
                invSearchResults.appendChild(d);
            });
            invSearchResults.style.display = 'block';
        }).fail(function() { hideResults(); });
    }

    invSearchInput.addEventListener('input', function() {
        var q = invSearchInput.value.trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { hideResults(); return; }
        searchTimer = setTimeout(function() { runInvSearch(q); }, 250);
    });

    invSearchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') e.preventDefault();
        if (e.key === 'Escape') hideResults();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') hideResults();
    });

    document.addEventListener('click', function(e) {
        var wrap = document.getElementById(cfg.wrapId);
        if (wrap && !wrap.contains(e.target)) hideResults();
    });

    addRowBtn.addEventListener('click', function() {
        var newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = window.itemRowHtml();
        itemsBody.appendChild(newRow);
        activeRow = newRow;
    });

    itemsBody.addEventListener('click', function(e) {
        var btn = e.target.closest('.remove-row');
        if (btn) {
            if (itemsBody.querySelectorAll('.item-row').length > 1) {
                btn.closest('.item-row').remove();
            } else {
                showToast('At least one item is required.', 'warning');
            }
        }
    });

    new MutationObserver(updateCount).observe(itemsBody, { childList: true });

    return {
        setActiveRow: function(row) {
            if (row && itemsBody.contains(row)) activeRow = row;
        }
    };
};

window.initEmployeeNameAutocomplete = function(inputId, hiddenId) {
    var input = document.getElementById(inputId);
    if (!input || !document.getElementById(hiddenId)) return null;
    var hiddenEl = document.getElementById(hiddenId);
    var parent = input.closest('.form-group') || input.parentNode;
    if (!parent) return null;

    parent.style.position = 'relative';

    var results = document.createElement('div');
    results.className = 'inv-search-results emp-autocomplete-results';
    results.style.cssText = 'position:absolute; z-index:1060; top:100%; left:0; right:0; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.2); display:none; max-height:220px; overflow-y:auto;';
    parent.appendChild(results);

    var timer = null;
    var items = [];
    var activeIndex = -1;

    function hide() {
        results.style.display = 'none';
        results.innerHTML = '';
        items = [];
        activeIndex = -1;
    }

    function applyHighlight() {
        var nodes = results.querySelectorAll('.emp-name-item');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].classList.toggle('active', i === activeIndex);
        }
        if (activeIndex >= 0 && nodes[activeIndex]) {
            nodes[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    function selectItem(item) {
        if (!item) return;
        input.value = item.name;
        hiddenEl.value = item.id;
        hide();
        input.focus();
    }

    input.addEventListener('input', function() {
        hiddenEl.value = '';
        var q = input.value.trim();
        clearTimeout(timer);
        if (q.length < 2) { hide(); return; }
        timer = setTimeout(function() {
            $.getJSON('function.php?action=employee_search&q=' + encodeURIComponent(q), function(data) {
                results.innerHTML = '';
                items = data || [];
                activeIndex = -1;
                if (!items.length) {
                    var e = document.createElement('div');
                    e.className = 'list-group-item disabled-item text-center text-muted';
                    e.textContent = 'No matching employee - you can still type the name manually.';
                    results.appendChild(e);
                    results.style.display = 'block';
                    return;
                }
                items.forEach(function(item, i) {
                    var d = document.createElement('div');
                    d.className = 'list-group-item emp-name-item';
                    d.textContent = item.name;
                    d.title = item.name;
                    d.addEventListener('click', function(ev) {
                        ev.preventDefault();
                        selectItem(item);
                    });
                    results.appendChild(d);
                });
                results.style.display = 'block';
            }).fail(function() { hide(); });
        }, 250);
    });

    input.addEventListener('keydown', function(e) {
        if (results.style.display === 'none' || !items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            applyHighlight();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            applyHighlight();
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && items[activeIndex]) {
                e.preventDefault();
                selectItem(items[activeIndex]);
            }
        } else if (e.key === 'Escape') {
            hide();
        }
    });

    document.addEventListener('click', function(e) {
        if (!parent.contains(e.target)) hide();
    });

    return {
        hide: hide,
        getSelectedId: function() { return hiddenEl.value; }
    };
};

document.addEventListener('DOMContentLoaded', function() {
    window.createItemEditor = initItemEditor({
        searchInputId: 'invSearchInput',
        searchResultsId: 'invSearchResults',
        tbodyId: 'itemsBody',
        addBtnId: 'addRowBtn',
        wrapId: 'invSearchWrap',
        countLabelId: 'addItemCount'
    });
    window.editItemEditor = initItemEditor({
        searchInputId: 'editInvSearchInput',
        searchResultsId: 'editInvSearchResults',
        tbodyId: 'editItemsBody',
        addBtnId: 'editAddRowBtn',
        wrapId: 'editInvSearchWrap',
        countLabelId: 'editItemCount'
    });

    initEmployeeNameAutocomplete('createRequestedBy', 'createRequestedByEmpId');
    initEmployeeNameAutocomplete('createInspectedBy', 'createInspectedByEmpId');
    initEmployeeNameAutocomplete('createApprovedBy', 'createApprovedByEmpId');

    initEmployeeNameAutocomplete('editRequestedBy', 'editRequestedByEmpId');
    initEmployeeNameAutocomplete('editInspectedBy', 'editInspectedByEmpId');
    initEmployeeNameAutocomplete('editApprovedBy', 'editApprovedByEmpId');

    initEmployeeNameAutocomplete('returnRequestedBy', 'returnRequestedByEmpId');
    initEmployeeNameAutocomplete('returnInspectedBy', 'returnInspectedByEmpId');
    initEmployeeNameAutocomplete('returnApprovedBy', 'returnApprovedByEmpId');

    function guardSubmit(formId, btnName) {
        var form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', function(e) {
            var btn = form.querySelector('button[name="' + btnName + '"]');
            if (!btn) return;
            if (form.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = btnName;
            hidden.value = btn.value || btn.textContent.trim();
            form.appendChild(hidden);
            form.dataset.submitting = '1';
            if (!btn.dataset.originalLabel) {
                btn.dataset.originalLabel = btn.innerHTML;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        });
    }
    guardSubmit('addPassSlipForm', 'create_pass_slip');
    guardSubmit('editPassSlipForm', 'edit_pass_slip');

    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            document.querySelectorAll('form[data-submitting="1"]').forEach(function(f) {
                f.removeAttribute('data-submitting');
                var b = f.querySelector('button[type="submit"]');
                if (b) {
                    b.disabled = false;
                    if (b.dataset.originalLabel) {
                        b.innerHTML = b.dataset.originalLabel;
                    }
                }
            });
        }
    });
});
</script>
