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
                <h1 style="margin: 0;">Classifications</h1>
                <div class="header-date-time" id="dateTime"></div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>

                                <div class="panel panel-default">
                                    <div class="panel-heading">Classification List</div>
                                    <div class="panel-body">
                                        <!-- Toolbar: Add (left) -->
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                                <?php if ($_SESSION['role'] !== 'staff') { ?>
                                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addClassModal"><i class="fa fa-plus"></i> Add Classification</button>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="box-body table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">No.</th>
                                                        <th>Classification</th>
                                                        <th>Description</th>
                                                        <th style="width: 100px;">Status</th>
                                                        <th style="width: 100px;">Option</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="classTableBody">
                                                    <tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <!-- ========================= ADD CLASSIFICATION MODAL ======================= -->
    <div id="addClassModal" class="modal fade">
        <div class="modal-dialog modal-sdm-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Classification</h4>
                </div>
                <div class="modal-body">
                    <div id="addClassAlert" style="display:none;"></div>
                    <div class="form-group">
                        <label>Classification Name:</label>
                        <input type="text" id="add_class_name" class="form-control input-sm" placeholder="e.g. Hardware" />
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea id="add_class_description" class="form-control input-sm" rows="3" placeholder="Explain what belongs in this category"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <select id="add_class_status" class="form-control input-sm">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel" />
                    <input type="button" class="btn btn-primary btn-sm" value="Add Classification" id="addClassSubmitBtn" />
                </div>
            </div>
        </div>
    </div>

    <!-- ========================= EDIT CLASSIFICATION MODAL ======================= -->
    <div id="editClassModal" class="modal fade">
        <div class="modal-dialog modal-sdm-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil-square-o"></i> Edit Classification</h4>
                </div>
                <div class="modal-body">
                    <div id="editClassAlert" style="display:none;"></div>
                    <input type="hidden" id="edit_class_id" />
                    <div class="form-group">
                        <label>Classification Name:</label>
                        <input type="text" id="edit_class_name" class="form-control input-sm" />
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea id="edit_class_description" class="form-control input-sm" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <select id="edit_class_status" class="form-control input-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" id="editClassSubmitBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================= DELETE CONFIRMATION MODAL ======================= -->
    <div id="deleteClassModal" class="modal fade">
        <div class="modal-dialog modal-sdm-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-trash"></i> Delete Confirmation</h4>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete classification "<span id="deleteClassName"></span>"?</p>
                    <p class="text-warning"><i class="fa fa-exclamation-triangle"></i> Classifications are used to organize inventory records. Deleting this classification cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary btn-sm" id="confirmDeleteClassBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="ajaxToast" class="alert" style="position:fixed; top:1em; right:1em; z-index:9999; display:none; min-width:250px;"></div>

    <?php include dirname(__DIR__) . '/scripts.php'; ?>

    <style>
        table { table-layout: auto; width: 100%; }
        table th { white-space: normal; text-align: center; word-wrap: break-word; overflow-wrap: break-word; }
        table td { text-align: center; vertical-align: middle; }
        table th, table td { padding: 8px; border: 1px solid #ddd; }
        .header-date-time { font-size: 16px; color: #555; margin-left: auto; }
        .option-buttons { white-space: nowrap; }
    </style>

    <script>
    (function() {
        var basePath = '../../ajax/';

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(str)));
            return div.innerHTML;
        }

        function loadClassifications() {
            var tbody = document.getElementById('classTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

            $.getJSON(basePath + 'classifications_crud.php?action=list', function(res) {
                if (!res.success) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load classifications.</td></tr>'; return; }
                var rows = res.data || [];
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No classifications found.</td></tr>';
                    return;
                }
                var canEdit = document.body.getAttribute('data-can-manage') === '1';
                var html = '';
                rows.forEach(function(row, idx) {
                    var statusBadge = row.status === 'active'
                        ? '<span class="label label-success">Active</span>'
                        : '<span class="label label-default">Inactive</span>';

                    var options = '-';
                    if (canEdit) {
                        options = '<div style="display:flex;gap:5px;justify-content:center;">' +
                            '<button class="btn btn-primary btn-xs editClassBtn" data-id="' + row.id + '" data-name="' + escHtml(row.category) + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                            '<button class="btn btn-danger btn-xs deleteClassBtn" data-id="' + row.id + '" data-name="' + escHtml(row.category) + '" title="Delete"><i class="fa fa-trash"></i></button>' +
                            '</div>';
                    }

                    html += '<tr>' +
                        '<td>' + (idx + 1) + '</td>' +
                        '<td>' + escHtml(row.category) + '</td>' +
                        '<td style="white-space: normal; text-align: left;">' + escHtml(row.description) + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td class="option-buttons">' + options + '</td>' +
                        '</tr>';
                });
                tbody.innerHTML = html;
            }).fail(function() {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load classifications.</td></tr>';
            });
        }

        // ========== ADD ==========
        $('#addClassSubmitBtn').on('click', function() {
            var btn = this;
            btn.disabled = true;
            var payload = {
                action: 'add',
                name: $('#add_class_name').val(),
                description: $('#add_class_description').val(),
                status: $('#add_class_status').val()
            };
            $.ajax({
                url: basePath + 'classifications_crud.php',
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#addClassModal').modal('hide');
                        $('#addClassAlert').hide().html('');
                        showToast(res.message, 'success');
                        loadClassifications();
                        $('#add_class_name').val('');
                        $('#add_class_description').val('');
                        $('#add_class_status').val('active');
                    } else {
                        $('#addClassAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                    }
                },
                error: function() {
                    $('#addClassAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                },
                complete: function() { btn.disabled = false; }
            });
        });

        // ========== EDIT ==========
        $(document).on('click', '.editClassBtn', function() {
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var $row = $(this);
            $.getJSON(basePath + 'classifications_crud.php?action=list', function(res) {
                if (!res.success) { showToast('Failed to load classification.', 'danger'); return; }
                var match = null;
                (res.data || []).forEach(function(r) { if (String(r.id) === String(id)) match = r; });
                if (!match) { showToast('Classification not found.', 'danger'); return; }
                $('#edit_class_id').val(match.id);
                $('#edit_class_name').val(match.category);
                $('#edit_class_description').val(match.description || '');
                $('#edit_class_status').val(match.status === 'inactive' ? 'inactive' : 'active');
                $('#editClassAlert').hide().html('');
                $('#editClassModal').modal('show');
            }).fail(function() { showToast('Failed to load classification.', 'danger'); });
        });

        $('#editClassSubmitBtn').on('click', function() {
            var btn = this;
            btn.disabled = true;
            var payload = {
                action: 'edit',
                id: $('#edit_class_id').val(),
                name: $('#edit_class_name').val(),
                description: $('#edit_class_description').val(),
                status: $('#edit_class_status').val()
            };
            $.ajax({
                url: basePath + 'classifications_crud.php',
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#editClassModal').modal('hide');
                        showToast(res.message, 'success');
                        loadClassifications();
                    } else {
                        $('#editClassAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                    }
                },
                error: function() {
                    $('#editClassAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                },
                complete: function() { btn.disabled = false; }
            });
        });

        // ========== DELETE ==========
        $(document).on('click', '.deleteClassBtn', function() {
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            $('#deleteClassName').text(name);
            $('#deleteClassModal').data('id', id);
            $('#deleteClassModal').modal('show');
        });

        $('#confirmDeleteClassBtn').on('click', function() {
            var id = $('#deleteClassModal').data('id');
            var btn = this;
            btn.disabled = true;
            $.ajax({
                url: basePath + 'classifications_crud.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(res) {
                    $('#deleteClassModal').modal('hide');
                    if (res.success) {
                        showToast(res.message, 'success');
                    } else {
                        showToast(res.error || 'Delete failed.', 'danger');
                    }
                    loadClassifications();
                },
                error: function() { showToast('Network error.', 'danger'); },
                complete: function() { btn.disabled = false; }
            });
        });

        // ========== DATE/TIME ==========
        function updateDateTime() {
            var now = new Date();
            document.getElementById('dateTime').innerText = now.toLocaleString('en-US', {
                year:'numeric', month:'long', day:'numeric',
                hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true
            });
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // ========== INIT ==========
        document.body.setAttribute('data-can-manage', '<?php echo ($_SESSION['role'] !== "staff") ? "1" : "0"; ?>');
        loadClassifications();
    })();
    </script>

    </body>
</html>
<?php } ?>