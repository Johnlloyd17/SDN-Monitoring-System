<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
    exit;
}

include('../connection.php');

// Handle Add Sub-Item
if (isset($_POST['btn_add_subitem'])) {
    $category = mysqli_real_escape_string($con, trim($_POST['txt_category']));
    $sub_item = mysqli_real_escape_string($con, trim($_POST['txt_sub_item']));
    if (!empty($category) && !empty($sub_item)) {
        $check = mysqli_query($con, "SELECT id FROM classifications WHERE category = '$category' AND sub_item = '$sub_item'");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['duplicate'] = 1;
        } else {
            mysqli_query($con, "INSERT INTO classifications (category, sub_item, status) VALUES ('$category', '$sub_item', 'active')");
            $_SESSION['added'] = 1;
        }
    }
    header("Location: classification_list.php");
    exit;
}

// Handle Edit Sub-Item
if (isset($_POST['btn_edit_subitem'])) {
    $id = intval($_POST['hidden_id']);
    $category = mysqli_real_escape_string($con, trim($_POST['txt_edit_category']));
    $sub_item = mysqli_real_escape_string($con, trim($_POST['txt_edit_sub_item']));
    $status = mysqli_real_escape_string($con, $_POST['txt_edit_status']);
    if (!empty($category) && !empty($sub_item)) {
        mysqli_query($con, "UPDATE classifications SET category = '$category', sub_item = '$sub_item', status = '$status' WHERE id = $id");
        $_SESSION['edited'] = 1;
    }
    header("Location: classification_list.php");
    exit;
}

// Handle Delete
if (isset($_POST['btn_delete_subitem'])) {
    if (isset($_POST['chk_delete'])) {
        foreach ($_POST['chk_delete'] as $value) {
            $value = intval($value);
            mysqli_query($con, "DELETE FROM classifications WHERE id = $value");
        }
        $_SESSION['delete'] = 1;
    }
    header("Location: classification_list.php");
    exit;
}

// Get unique categories for dropdowns
$categoriesResult = mysqli_query($con, "SELECT DISTINCT category FROM classifications ORDER BY category ASC");
$categories = [];
while ($cRow = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $cRow['category'];
}

// Metric card queries
$qTotal = mysqli_query($con, "SELECT COUNT(*) AS total FROM classifications");
$rTotal = mysqli_fetch_assoc($qTotal);

$qCategories = mysqli_query($con, "SELECT COUNT(DISTINCT category) AS total FROM classifications");
$rCategories = mysqli_fetch_assoc($qCategories);

$qActive = mysqli_query($con, "SELECT COUNT(*) AS total FROM classifications WHERE status = 'active'");
$rActive = mysqli_fetch_assoc($qActive);

$qInactive = mysqli_query($con, "SELECT COUNT(*) AS total FROM classifications WHERE status = 'inactive'");
$rInactive = mysqli_fetch_assoc($qInactive);
?>
<!DOCTYPE html>
<html>
<?php include('../head_css.php'); ?>
<body class="skin-black">
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0;">Classification Management</h1>
                <div class="header-date-time" id="dateTime"></div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12"><br>

                        <!-- Statistics Cards -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart"></i> Classification Statistics
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-aqua"><i class="fa fa-list"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Classifications</span>
                                                <span class="info-box-number"><?php echo $rTotal['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-blue"><i class="fa fa-layer-group"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Categories</span>
                                                <span class="info-box-number"><?php echo $rCategories['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Active Items</span>
                                                <span class="info-box-number"><?php echo $rActive['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Inactive Items</span>
                                                <span class="info-box-number"><?php echo $rInactive['total']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Statistics Cards -->

                        <div class="panel panel-default">
                            <div class="panel-heading">ICT Equipment Classifications (DICT Annex A.1.1)</div>
                            <div class="panel-body">

                                <!-- Filters -->
                                <div class="row">
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="filterCategory">Filter by Category</label>
                                            <select id="filterCategory" class="form-control">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="form-group">
                                            <label for="filterStatus">Filter by Status</label>
                                            <select id="filterStatus" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Toolbar: Add + Delete + Show entries (left) | Search (right) -->
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <?php if ($_SESSION['role'] !== 'staff') { ?>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSubItemModal">
                                                <i class="fa fa-plus"></i> Add Sub-Item
                                            </button>
                                            <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" data-toggle="modal" data-target="#deleteSubItemModal">
                                                <i class="fa fa-trash"></i> Delete Selected
                                            </button>
                                        <?php } ?>
                                        <label style="margin:0; font-weight:normal;">Show </label>
                                        <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                                            <option value="5" selected>5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="40">40</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="150">150</option>
                                            <option value="200">200</option>
                                        </select>
                                        <label style="margin:0; font-weight:normal;"> entries</label>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <div class="input-group" style="width:300px;">
                                            <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search classifications..." />
                                            <span class="input-group-btn">
                                                <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                                <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <form method="post">
                                    <div class="box-body table-responsive">
                                        <table id="classTable" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 30px;"><input type="checkbox" id="cbxMain" onchange="checkMain(this)"/></th>
                                                    <th style="width: 40px;">No.</th>
                                                    <th>Category</th>
                                                    <th>Sub-Item</th>
                                                    <th>Status</th>
                                                    <th style="width: 80px;">Option</th>
                                                </tr>
                                            </thead>
                                            <tbody id="classTableBody">
                                            <?php
                                            $counter = 1;
                                            $result = mysqli_query($con, "SELECT * FROM classifications ORDER BY category ASC, sub_item ASC");
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $statusLabel = $row['status'] == 'active'
                                                    ? '<span class="label label-success"><i class="fa fa-check"></i> Active</span>'
                                                    : '<span class="label label-default"><i class="fa fa-times"></i> Inactive</span>';

                                                // Build category options for edit modal
                                                $catOpts = '';
                                                foreach ($categories as $cat) {
                                                    $sel = ($cat == $row['category']) ? ' selected' : '';
                                                    $catOpts .= '<option value="' . htmlspecialchars($cat) . '"' . $sel . '>' . htmlspecialchars($cat) . '</option>';
                                                }

                                                echo '
                                                <tr data-category="' . htmlspecialchars($row['category']) . '" data-status="' . htmlspecialchars($row['status']) . '">
                                                    <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $row['id'] . '" /></td>
                                                    <td class="row-num">' . $counter++ . '</td>
                                                    <td class="col-category"><span class="label label-info">' . htmlspecialchars($row['category']) . '</span></td>
                                                    <td class="col-subitem">' . htmlspecialchars($row['sub_item']) . '</td>
                                                    <td>' . $statusLabel . '</td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#editSubItemModal' . $row['id'] . '" title="Edit">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                        </button>
                                                    </td>
                                                </tr>';

                                                // Edit Modal for each row
                                                echo '
                                                <div id="editSubItemModal' . $row['id'] . '" class="modal fade">
                                                    <form method="post">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                    <h4 class="modal-title">Edit Classification</h4>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="hidden_id" value="' . $row['id'] . '" />
                                                                    <div class="form-group">
                                                                        <label>Category:</label>
                                                                        <select name="txt_edit_category" class="form-control" required>
                                                                            <option value="">-- Select Category --</option>
                                                                            ' . $catOpts . '
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Sub-Item:</label>
                                                                        <input type="text" name="txt_edit_sub_item" class="form-control" value="' . htmlspecialchars($row['sub_item']) . '" required />
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label>Status:</label>
                                                                        <select name="txt_edit_status" class="form-control">
                                                                            <option value="active"' . ($row['status'] == 'active' ? ' selected' : '') . '>Active</option>
                                                                            <option value="inactive"' . ($row['status'] == 'inactive' ? ' selected' : '') . '>Inactive</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="btn_edit_subitem" class="btn btn-primary btn-sm">Save Changes</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>';
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Bottom bar: Info text (left) + Pagination (right) -->
                                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                                        <div id="paginationInfo" class="text-muted"></div>
                                        <ul class="pagination" style="margin:0;" id="pagination"></ul>
                                    </div>

                                    <!-- Delete Confirmation Modal -->
                                    <div id="deleteSubItemModal" class="modal fade">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title"><i class="fa fa-trash"></i> Confirm Deletion</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the selected item(s)?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="btn_delete_subitem" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Sub-Item Modal -->
                <div id="addSubItemModal" class="modal fade">
                    <form method="post">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title"><i class="fa fa-plus"></i> Add Classification Sub-Item</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Category: <span style="color:red;">*</span></label>
                                        <select name="txt_category" class="form-control" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Sub-Item: <span style="color:red;">*</span></label>
                                        <input type="text" name="txt_sub_item" class="form-control" placeholder="e.g. Desktop PCs" required />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                                    <button type="submit" name="btn_add_subitem" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </section>
        </aside>
    </div>

    <?php include '../added_notif.php'; ?>
    <?php include '../edit_notif.php'; ?>
    <?php include '../delete_notif.php'; ?>
    <?php include '../duplicate_error.php'; ?>
    <?php include '../footer.php'; ?>

    <style>
        .info-box-icon {
            background-color: white;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4);
            border-radius: 5px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            width: 80px;
            font-size: 40px;
        }
        table { table-layout: auto; width: 100%; }
        table th { white-space: normal; text-align: center; word-wrap: break-word; overflow-wrap: break-word; }
        table td { white-space: nowrap; text-align: center; vertical-align: middle; }
        table th, table td { padding: 8px; border: 1px solid #ddd; }
        .header-date-time { font-size: 16px; color: #555; margin-left: auto; }
    </style>

    <script>
    (function() {
        var allRows = [];
        var filteredRows = [];
        var currentPage = 1;
        var perPage = 5;

        function init() {
            // Collect all data rows from the table
            var tbody = document.getElementById('classTableBody');
            var trs = tbody.querySelectorAll('tr[data-category]');
            for (var i = 0; i < trs.length; i++) {
                allRows.push(trs[i]);
            }
            filteredRows = allRows.slice();
            renderPage();
        }

        function getFilters() {
            return {
                category: document.getElementById('filterCategory').value.toLowerCase(),
                status: document.getElementById('filterStatus').value.toLowerCase(),
                search: document.getElementById('searchInput').value.toLowerCase()
            };
        }

        function applyFilters() {
            var f = getFilters();
            filteredRows = [];
            for (var i = 0; i < allRows.length; i++) {
                var row = allRows[i];
                var cat = (row.getAttribute('data-category') || '').toLowerCase();
                var st = (row.getAttribute('data-status') || '').toLowerCase();
                var subitem = (row.querySelector('.col-subitem') ? row.querySelector('.col-subitem').textContent : '').toLowerCase();
                var catText = (row.querySelector('.col-category') ? row.querySelector('.col-category').textContent : '').toLowerCase();

                if (f.category && cat !== f.category) continue;
                if (f.status && st !== f.status) continue;
                if (f.search && catText.indexOf(f.search) === -1 && subitem.indexOf(f.search) === -1) continue;

                filteredRows.push(row);
            }
            currentPage = 1;
            renderPage();
        }

        function renderPage() {
            var tbody = document.getElementById('classTableBody');
            // Remove all rows from DOM
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            var totalPages = Math.max(1, Math.ceil(filteredRows.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            var start = (currentPage - 1) * perPage;
            var end = Math.min(start + perPage, filteredRows.length);

            if (filteredRows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No records found.</td></tr>';
            } else {
                for (var i = start; i < end; i++) {
                    // Update row number
                    var numCell = filteredRows[i].querySelector('.row-num');
                    if (numCell) numCell.textContent = i + 1;
                    tbody.appendChild(filteredRows[i]);
                }
            }

            renderPagination(currentPage, totalPages, filteredRows.length);
        }

        function renderPagination(page, total, count) {
            var pag = document.getElementById('pagination');
            var info = document.getElementById('paginationInfo');

            var startNum = count > 0 ? (page - 1) * perPage + 1 : 0;
            var endNum = Math.min(page * perPage, count);
            info.textContent = 'Showing ' + startNum + ' to ' + endNum + ' of ' + count + ' entries';

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

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            init();

            document.getElementById('filterCategory').addEventListener('change', applyFilters);
            document.getElementById('filterStatus').addEventListener('change', applyFilters);

            var searchTimeout = null;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 300);
            });
            document.getElementById('searchBtn').addEventListener('click', function(e) {
                e.preventDefault();
                applyFilters();
            });
            document.getElementById('clearSearchBtn').addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('searchInput').value = '';
                applyFilters();
            });

            document.getElementById('perPageSelect').addEventListener('change', function() {
                perPage = parseInt(this.value);
                currentPage = 1;
                renderPage();
            });

            document.getElementById('pagination').addEventListener('click', function(e) {
                e.preventDefault();
                var target = e.target.closest('a[data-page]');
                if (!target) return;
                var li = target.closest('li');
                if (li && li.classList.contains('disabled')) return;
                var p = parseInt(target.getAttribute('data-page'));
                var totalPages = Math.max(1, Math.ceil(filteredRows.length / perPage));
                if (p >= 1 && p <= totalPages) {
                    currentPage = p;
                    renderPage();
                }
            });
        });
    })();

    function checkMain(x) {
        var checked = $(x).prop('checked');
        $('#classTableBody tr:visible').each(function() {
            $(this).find('.chk_delete').each(function() {
                this.checked = checked;
            });
        });
    }
    </script>
</body>
</html>
