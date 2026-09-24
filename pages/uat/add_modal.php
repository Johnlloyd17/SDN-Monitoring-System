<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<style>
    .uat-inv-search-results .list-group-item {
        border-left: none;
        border-right: none;
        border-radius: 0;
        cursor: pointer;
    }
    .uat-inv-search-results .list-group-item:first-child { border-top: none; }
    .uat-inv-search-results .list-group-item:last-child { border-bottom: none; }
    .uat-inv-search-results .list-group-item:hover { background: #f5f5f5; }
    .uat-inv-search-results .uat-res-name {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .uat-inv-search-wrap { position: relative; margin-bottom: 10px; max-width: 480px; z-index: 1050; }
    .uat-inv-search-results { position: absolute; top: 100%; left: 0; right: 0; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.2); display: none; max-height: 260px; overflow-y: auto; }
</style>
<!-- Add UAT Modal -->
<div class="modal fade" id="addUatModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sdm-xl" role="document">
        <div class="modal-content">
            <form method="POST" action="function.php" id="addUatForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-file-signature"></i> Create UAT Record</h4>
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

                    <!-- Equipment search (read-only inventory lookup, fills active row) -->
                    <div id="uatInvSearchWrap" class="uat-inv-search-wrap">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" id="uatInvSearchInput" class="form-control uat-inv-search-input" placeholder="Search inventory by description or serial no. (min 2 chars)...">
                        </div>
                        <div id="uatInvSearchResults" class="uat-inv-search-results list-group"></div>
                    </div>

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
                                    <td><input type="hidden" name="inventory_id[]" value=""><input type="number" name="qty[]" class="form-control uat-qty" min="1" placeholder="Qty"></td>
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
                    <div class="items-toolbar" style="display: flex; align-items: center; justify-content: space-between;">
                        <button type="button" class="btn btn-success btn-sm" id="addUatRowBtn"><i class="fa fa-plus"></i> Add Row</button>
                        <span class="text-muted" id="uatItemCount" style="font-size: 12px;">1 Item</span>
                    </div>

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
window.initUatItemEditor = function(cfg) {
    var searchInput = document.getElementById(cfg.searchInputId);
    var searchResults = document.getElementById(cfg.searchResultsId);
    var tbody = document.getElementById(cfg.tbodyId);
    var addBtn = document.getElementById(cfg.addBtnId);
    if (!tbody || !searchInput || !searchResults) return null;

    var activeRow = null;
    var searchTimer = null;
    var countLabel = cfg.countLabelId ? document.getElementById(cfg.countLabelId) : null;

    function pluralize(n) { return n + (n === 1 ? ' Item' : ' Items'); }
    function updateCount() { if (countLabel) countLabel.textContent = pluralize(tbody.querySelectorAll('.uat-item-row').length); }
    updateCount();

    function currentRow() {
        if (activeRow && tbody.contains(activeRow)) return activeRow;
        var rows = tbody.querySelectorAll('.uat-item-row');
        return rows.length ? rows[rows.length - 1] : null;
    }

    tbody.addEventListener('focusin', function(e) {
        var tr = e.target.closest('.uat-item-row');
        if (tr) activeRow = tr;
    });

    function hideResults() { searchResults.style.display = 'none'; searchResults.innerHTML = ''; }

    function makeBadge(cls, text) {
        var s = document.createElement('span');
        s.className = 'label ' + cls;
        s.style.marginLeft = '5px';
        s.textContent = text;
        return s;
    }

    function buildResultRow(item) {
        var d = document.createElement('div');
        d.className = 'list-group-item';

        var name = document.createElement('div');
        name.className = 'uat-res-name';
        name.textContent = item.item || '';
        name.title = name.textContent;

        var meta = document.createElement('div');
        meta.appendChild(document.createTextNode('S/N: ' + (item.serial || '-') + ' | Unit: ' + (item.unit || '-') + ' | Qty: ' + (item.quantity || '-')));
        meta.appendChild(makeBadge('label-success', 'Inventory'));

        d.appendChild(name);
        d.appendChild(meta);
        return d;
    }

    function fillRow(row, item) {
        if (!row) return;
        var setVal = function(name, value) {
            var inp = row.querySelector('input[name="' + name + '"]');
            if (inp) inp.value = value;
        };
        setVal('inventory_id[]', item.id || '');
        setVal('qty[]', item.quantity || '1');
        setVal('unit[]', item.unit || '');
        setVal('item_name[]', item.item || '');
        setVal('description[]', item.description || '');
        setVal('serial_numbers[]', item.serial || '');
        hideResults();
        searchInput.value = '';
        var ser = row.querySelector('input[name="serial_numbers[]"]');
        if (ser) ser.focus();
    }

    function buildNoMatchRow() {
        var empty = document.createElement('div');
        empty.className = 'list-group-item text-center text-muted';
        empty.textContent = 'No matching inventory item - you can still type the item manually.';
        return empty;
    }

    function runSearch(q) {
        $.getJSON('function.php?action=inventory_item_search&q=' + encodeURIComponent(q), function(data) {
            searchResults.innerHTML = '';
            if (!data || !data.length) {
                searchResults.appendChild(buildNoMatchRow());
                searchResults.style.display = 'block';
                return;
            }
            data.forEach(function(item) {
                var d = buildResultRow(item);
                d.addEventListener('click', function(e) {
                    e.preventDefault();
                    fillRow(currentRow(), item);
                });
                searchResults.appendChild(d);
            });
            searchResults.style.display = 'block';
        }).fail(function() { hideResults(); });
    }

    searchInput.addEventListener('input', function() {
        var q = searchInput.value.trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { hideResults(); return; }
        searchTimer = setTimeout(function() { runSearch(q); }, 250);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') e.preventDefault();
        if (e.key === 'Escape') hideResults();
    });

    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') hideResults(); });
    document.addEventListener('click', function(e) {
        var wrap = document.getElementById(cfg.wrapId);
        if (wrap && !wrap.contains(e.target)) hideResults();
    });

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            var firstRow = tbody.querySelector('.uat-item-row');
            var newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
            tbody.appendChild(newRow);
            activeRow = newRow;
        });
    }

    tbody.addEventListener('click', function(e) {
        var btn = e.target.closest('.uat-remove-row');
        if (btn) {
            if (tbody.querySelectorAll('.uat-item-row').length > 1) {
                btn.closest('.uat-item-row').remove();
            } else {
                showToast('At least one equipment row must remain. Clear its fields instead.', 'warning');
            }
        }
    });

    new MutationObserver(updateCount).observe(tbody, { childList: true });

    return {
        setActiveRow: function(row) { if (row && tbody.contains(row)) activeRow = row; }
    };
};

document.addEventListener('DOMContentLoaded', function() {
    window.uatAddEditor = initUatItemEditor({
        searchInputId: 'uatInvSearchInput',
        searchResultsId: 'uatInvSearchResults',
        tbodyId: 'uatItemsBody',
        addBtnId: 'addUatRowBtn',
        wrapId: 'uatInvSearchWrap',
        countLabelId: 'uatItemCount'
    });
});
</script>