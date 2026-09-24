<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!DOCTYPE html>
<html>
<?php

if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
?>
<body class="skin-black">
    <?php include '../connection.php'; ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0;">Employee Records</h1>
                <div class="header-date-time" id="dateTime"></div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>

                                <div class="panel panel-default">
                                    <div class="panel-heading"><i class="fa fa-bar-chart"></i> Employee Directory</div>
                                    <div class="panel-body">
                                        <!-- Toolbar: Add + Delete (left) | Search (right) -->
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                                <label style="margin:0; font-weight:normal;">Show </label>
                                                <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                                                    <option value="5" selected>5</option>
                                                    <option value="10">10</option>
                                                    <option value="20">20</option>
                                                    <option value="40">40</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                    <option value="200">200</option>
                                                </select>
                                                <label style="margin:0; font-weight:normal;"> records per page</label>
                                                <?php if ($_SESSION['role'] !== 'staff') { ?>
                                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addEmployeeModal"><i class="fa fa-user-plus"></i> Add Record</button>
                                                    <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete Selected</button>
                                                <?php } ?>
                                            </div>
                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                                <div class="input-group" style="width:300px;">
                                                    <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search by name..." />
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                                        <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="box-body table-responsive">
                                            <table id="employeeTable" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 20px !important;"><input type="checkbox" id="cbxMain" /></th>
                                                        <th style="width: 50px !important;">No.</th>
                                                        <th>Full Name</th>
                                                        <th style="width: 120px !important;">Option</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="employeeBody">
                                                    <tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Bottom bar: Info text (left) + Pagination (right) -->
                                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                                            <div id="paginationInfo" class="text-muted"></div>
                                            <ul class="pagination" style="margin:0;" id="pagination"></ul>
                                        </div>

                                        <p class="text-muted" style="margin-top:10px; font-size:12px;">
                                            <i class="fa fa-info-circle"></i> Employees created here appear in the Office Equipment Pass Slip autocomplete for Requested By / Inspected By / Approved By (Pull-Out and Return). Selecting a match links the slip to this record.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <!-- ========================= ADD EMPLOYEE MODAL ======================= -->
    <div id="addEmployeeModal" class="modal fade">
        <form id="addEmployeeForm">
            <div class="modal-dialog modal-sdm-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Employee</h4>
                    </div>
                    <div class="modal-body">
                        <div id="addAlert" style="display:none;"></div>
                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input name="full_name" id="addFullName" class="form-control" type="text" placeholder="Full name of the DICT employee/worker" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="addSubmitBtn">Add Employee</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================= EDIT EMPLOYEE MODAL ======================= -->
    <div id="editEmployeeModal" class="modal fade">
        <form id="editEmployeeForm">
            <div class="modal-dialog modal-sdm-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Employee</h4>
                    </div>
                    <div class="modal-body">
                        <div id="editAlert" style="display:none;"></div>
                        <input type="hidden" name="id" id="edit_hidden_id" />
                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input name="full_name" id="editFullName" class="form-control" type="text" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="editSubmitBtn">Save</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ========================= DELETE CONFIRMATION MODAL ======================= -->
    <div id="deleteModal" class="modal fade">
        <div class="modal-dialog modal-sdm-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-trash"></i> Delete Confirmation</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the selected employee(s)? Pass slips already linked to them will keep the typed name (the link is cleared, the slips are never deleted).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirmDeleteBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <?php include dirname(__DIR__) . '/scripts.php'; ?>

    <script>
    (function() {
        var basePath = '../../ajax/';
        var currentPage = 1;
        var perPage = 5;
        var totalPages = 1;
        var searchTimeout = null;

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        function loadData(page) {
            currentPage = page || 1;
            var search = document.getElementById('searchInput').value;
            var params = 'page=' + currentPage + '&per_page=' + perPage +
                         '&search=' + encodeURIComponent(search);

            var tbody = document.getElementById('employeeBody');
            tbody.innerHTML = '<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

            $.getJSON(basePath + 'employee_data.php?' + params, function(res) {
                totalPages = res.total_pages;
                renderTable(res.data);
                renderPagination(res.page, res.total_pages, res.total);
                updateDeleteBtn();
            }).fail(function() {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load data.</td></tr>';
            });
        }

        function renderTable(rows) {
            var tbody = document.getElementById('employeeBody');
            if (!rows || rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">No records found.</td></tr>';
                return;
            }
            var html = '';
            rows.forEach(function(row) {
                var id = parseInt(row.id);
                html += '<tr>' +
                    '<td><input type="checkbox" class="chk_delete" data-id="' + id + '" /></td>' +
                    '<td>' + row.row_num + '</td>' +
                    '<td>' + escHtml(row.full_name) + '</td>' +
                    '<td class="option-buttons">' +
                        '<div style="display:flex;gap:5px;flex-wrap:wrap;justify-content:center;">' +
                        '<button class="btn btn-primary btn-xs editBtn" data-id="' + id + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                        '<button class="btn btn-danger btn-xs deleteBtn" data-id="' + id + '" title="Delete"><i class="fa fa-trash"></i></button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
        }

        function renderPagination(page, total, count) {
            var pag = document.getElementById('pagination');
            var info = document.getElementById('paginationInfo');

            var start = count > 0 ? (page - 1) * perPage + 1 : 0;
            var end = Math.min(page * perPage, count);
            info.textContent = 'Showing ' + start + ' to ' + end + ' of ' + count + ' entries';

            if (total <= 1) { pag.innerHTML = ''; return; }

            var html = '';
            html += '<li' + (page <= 1 ? ' class="disabled"' : '') + '><a href="#" data-page="' + (page - 1) + '">&laquo;</a></li>';

            var startPage = Math.max(1, page - 2);
            var endPage = Math.min(total, page + 2);

            if (startPage > 1) {
                html += '<li><a href="#" data-page="1">1</a></li>';
                if (startPage > 2) html += '<li class="disabled"><a>&hellip;</a></li>';
            }
            for (var i = startPage; i <= endPage; i++) {
                html += '<li' + (i === page ? ' class="active"' : '') + '><a href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            if (endPage < total) {
                if (endPage < total - 1) html += '<li class="disabled"><a>&hellip;</a></li>';
                html += '<li><a href="#" data-page="' + total + '">' + total + '</a></li>';
            }

            html += '<li' + (page >= total ? ' class="disabled"' : '') + '><a href="#" data-page="' + (page + 1) + '">&raquo;</a></li>';
            pag.innerHTML = html;
        }

        function updateDeleteBtn() {
            var checked = document.querySelectorAll('.chk_delete:checked').length;
            var btn = document.getElementById('deleteSelectedBtn');
            if (btn) btn.disabled = checked === 0;
        }

        function getSelectedIds() {
            var ids = [];
            document.querySelectorAll('.chk_delete:checked').forEach(function(cb) {
                ids.push(cb.getAttribute('data-id'));
            });
            return ids;
        }

        // Per-page selector
        $('#perPageSelect').on('change', function() {
            perPage = parseInt(this.value);
            loadData(1);
        });

        // Search
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() { loadData(1); }, 400);
        });
        $('#searchBtn').on('click', function() { loadData(1); });
        $('#clearSearchBtn').on('click', function() {
            document.getElementById('searchInput').value = '';
            loadData(1);
        });

        // Pagination clicks
        $('#pagination').on('click', 'a[data-page]', function(e) {
            e.preventDefault();
            var pg = parseInt($(this).attr('data-page'));
            if (pg >= 1 && pg <= totalPages) loadData(pg);
        });

        // Select all
        $('#cbxMain').on('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.chk_delete').forEach(function(cb) { cb.checked = checked; });
            updateDeleteBtn();
        });
        $(document).on('change', '.chk_delete', function() {
            var all = document.querySelectorAll('.chk_delete').length;
            var checked = document.querySelectorAll('.chk_delete:checked').length;
            document.getElementById('cbxMain').checked = (all > 0 && all === checked);
            updateDeleteBtn();
        });

        // ========== ADD ==========
        $('#addEmployeeForm').on('submit', function(e) {
            e.preventDefault();
            if (String(document.getElementById('addFullName').value).trim() === '') {
                $('#addAlert').html('<div class="alert alert-danger">Full Name is required.</div>').show();
                return;
            }
            var btn = document.getElementById('addSubmitBtn');
            btn.disabled = true;

            $.ajax({
                url: basePath + 'employee_crud.php',
                type: 'POST',
                data: $(this).serialize() + '&action=add',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#addEmployeeModal').modal('hide');
                        showToast('Employee added successfully!', 'success');
                        loadData(currentPage);
                        document.getElementById('addEmployeeForm').reset();
                    } else {
                        $('#addAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                    }
                },
                error: function() {
                    $('#addAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                },
                complete: function() { btn.disabled = false; }
            });
        });

        // ========== EDIT ==========
        $(document).on('click', '.editBtn', function() {
            var id = $(this).attr('data-id');
            $.getJSON(basePath + 'employee_get_item.php?action=item&id=' + id, function(emp) {
                $('#edit_hidden_id').val(emp.id);
                $('#editFullName').val(emp.full_name);
                $('#editAlert').hide();
                $('#editEmployeeModal').modal('show');
            }).fail(function() {
                showToast('Failed to load employee data.', 'danger');
            });
        });

        $('#editEmployeeForm').on('submit', function(e) {
            e.preventDefault();
            if (String(document.getElementById('editFullName').value).trim() === '') {
                $('#editAlert').html('<div class="alert alert-danger">Full Name is required.</div>').show();
                return;
            }
            var btn = document.getElementById('editSubmitBtn');
            btn.disabled = true;

            $.ajax({
                url: basePath + 'employee_crud.php',
                type: 'POST',
                data: $(this).serialize() + '&action=edit',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#editEmployeeModal').modal('hide');
                        showToast('Employee updated successfully!', 'success');
                        loadData(currentPage);
                    } else {
                        $('#editAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                    }
                },
                error: function() {
                    $('#editAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                },
                complete: function() { btn.disabled = false; }
            });
        });

        // ========== DELETE ==========
        var pendingDeleteIds = [];

        function showDeleteConfirm(ids) {
            pendingDeleteIds = ids;
            $('#deleteModal').modal('show');
        }

        $(document).on('click', '.deleteBtn', function() {
            showDeleteConfirm([$(this).attr('data-id')]);
        });

        $('#deleteSelectedBtn').on('click', function() {
            var ids = getSelectedIds();
            if (ids.length === 0) return;
            showDeleteConfirm(ids);
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (pendingDeleteIds.length === 0) return;
            $.post(basePath + 'employee_crud.php', {
                action: 'delete',
                ids: pendingDeleteIds
            }, function(res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    $('#deleteModal').modal('hide');
                    loadData(currentPage);
                } else {
                    showToast(res.message || 'Failed to delete.', 'error');
                }
            }, 'json').fail(function() {
                showToast('Network error. Please try again.', 'error');
            });
        });

        loadData(1);
    })();
    </script>
</body>
</html>
<?php } ?>