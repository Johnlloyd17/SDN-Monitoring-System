<!DOCTYPE html>
<html>

<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
    exit();
} else {
    ob_start();
    include('../head_css.php');
}
?>
<body class="skin-black">
    <!-- header logo: style can be found in header.less -->
    <?php 
    include "../connection.php";
    include('../header.php');
    ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <?php include('../sidebar-left.php'); ?>

        <!-- Right side column. Contains the navbar and content of the page -->
       <!-- Right side column. Contains the navbar and content of the page -->
<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Activity Logs</h1>
        <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
    </section>
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        Statistics
                                    </div>
                                   
                  
                        <div class="box-body table-responsive">
                            <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                                <div class="input-group" style="width:280px;">
                                    <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search user or action..." />
                                    <span class="input-group-btn">
                                        <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                        <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                    </span>
                                </div>
                                <label style="margin:0; font-weight:normal;">Show </label>
                                <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label style="margin:0; font-weight:normal;"> entries</label>
                            </div>
                            <table id="table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>
                                </tbody>
                            </table>
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-top:10px;">
                                <div id="paginationInfo" class="text-muted"></div>
                                <ul class="pagination" style="margin:0;" id="pagination"></ul>
                            </div>
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div><!-- /.row -->
            </section><!-- /.content -->
        </aside><!-- /.right-side -->
    </div><!-- ./wrapper -->
    <!-- jQuery 2.0.2 -->
    <?php include dirname(__DIR__) . '/scripts.php'; ?>
    <script type="text/javascript">
        $(function () {
            var basePath = '../../ajax/';
            var currentPage = 1;
            var perPage = 10;
            var totalPages = 1;
            var searchTimeout = null;

            function loadData(page) {
                currentPage = page || 1;
                var search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
                var params = 'page=' + currentPage + '&per_page=' + perPage +
                             '&search=' + encodeURIComponent(search);

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.getJSON(basePath + 'logs_data.php?' + params, function(res) {
                    totalPages = res.total_pages;
                    renderTable(res.data);
                    renderPagination(res.page, res.total_pages, res.total);
                }).fail(function() {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load data.</td></tr>';
                });
            }

            function renderTable(rows) {
                var tbody = document.getElementById('tableBody');
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    html += '<tr>' +
                        '<td>' + row.row_num + '</td>' +
                        '<td>' + escHtml(row.user) + '</td>' +
                        '<td>' + escHtml(row.logdate) + '</td>' +
                        '<td>' + escHtml(row.action) + '</td>' +
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

            function escHtml(str) {
                if (str === null || str === undefined) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(String(str)));
                return div.innerHTML;
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

            // ========== INIT ==========
            loadData(1);
        });

    // Function to update the date and time
 function updateDateTime() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: true 
            };
            document.getElementById('dateTime').innerText = now.toLocaleString('en-US', options);
        }

        // Update the date and time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call to display immediately
    </script>
    <style>
     .header-date-time {
        font-size: 16px; /* Adjust font size as needed */
        color: #555; /* Optional: Change color for better visibility */
        margin-left: 20px; /* Optional: Add some space between title and date/time */
    }
</style>
</body>
</html>
