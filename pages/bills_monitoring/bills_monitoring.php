<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    session_start();
    if (!isset($_SESSION['role'])) {
        header("Location: ../../login.php");
        exit();
    } else {
        ob_start();
        include('../head_css.php');
        $isAdmin = ($_SESSION['role'] === 'Administrator');
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .info-box-icon {
            background-color: white;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            width: 80px;
            font-size: 40px;
        }
        .panel-body {
            padding: 15px;
        }
        .info-box-number a {
            color: inherit;
            text-decoration: none;
        }
        .info-box-number a:hover {
            text-decoration: underline;
        }
        .header-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .header-logo {
            height: 60px;
            width: auto;
            margin-right: 10px;
        }
        .header-info {
            display: flex;
            flex-direction: column;
        }
        h3 {
            margin: 0;
            font-weight: 600;
        }
        .header-address {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        .header-date-time {
            font-size: 16px;
            color: #555;
            margin-left: auto;
        }
        .chart-panel {
            margin-bottom: 0;
        }
        .chart-panel .panel-body {
            padding: 10px 15px 5px;
        }
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
            margin-bottom: 10px;
        }
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .chart-empty-state {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 16px;
            pointer-events: none;
            z-index: 1;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }
        #table {
            border-collapse: collapse;
            border: 1px solid #ddd;
            width: 100%;
        }
        #table thead tr:first-child th {
            background-color: #3c8dbc;
            color: #ffffff;
            border: 1px solid #32739e;
            text-align: center;
            font-weight: 600;
            padding: 8px;
            white-space: nowrap;
        }
        #table tbody td {
            border: 1px solid #ddd;
            padding: 8px;
            white-space: nowrap;
        }
        #tableBody tr {
            background-color: #ffffff !important;
        }
        #tableBody tr:hover {
            background-color: #e8f4fd !important;
            transition: background-color 0.15s ease;
        }
        .full-width-select {
            width: 100%;
        }
        .metric-card a {
            display: block;
            cursor: pointer;
            background: #ffffff;
            border-radius: 3px;
            transition: box-shadow 0.15s ease, transform 0.1s ease;
        }
        .metric-card a:hover,
        .metric-card a:focus {
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transform: translateY(-1px);
            text-decoration: none;
        }
        .metric-card .info-box {
            margin-bottom: 0;
        }
        .metric-card.card-active a {
            box-shadow: 0 0 0 3px #3c8dbc;
        }
        .metric-card .info-box-content .info-box-number {
            font-size: 20px;
            line-height: 1.3;
            white-space: nowrap;
        }
        .info-box-desc {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            line-height: 1.3;
            color: #999;
            font-weight: normal;
        }
        #metricCardsRow {
            display: grid;
            gap: 15px;
            grid-template-columns: repeat(3, 1fr);
        }
        @media (max-width: 1199px) {
            #metricCardsRow {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 767px) {
            #metricCardsRow {
                grid-template-columns: 1fr;
            }
        }
        #tableBody td a {
            color: #3c8dbc;
            text-decoration: none;
        }
        #tableBody td a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="skin-black">
    <?php
    include "../connection.php";
    ?>
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <section class="content-header">
                <div class="header-title">
                    <img src="../dashboard/icons/dict_logo.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3>Bills Monitoring</h3>
                        <p class="header-address">Utility &amp; Office Billing Records</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div>
                </div>
            </section>

            <section class="content">
                <?php
                $billStats = array('paid' => 0, 'unpaid' => 0);
                $billAmounts = array('paid' => 0.0, 'unpaid' => 0.0);
                $billTotal = 0;
                $amountTotal = 0.0;
                $billRes = mysqli_query($con, "SELECT
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS paid,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS unpaid,
                    COUNT(*) AS total,
                    COALESCE(SUM(amount), 0) AS amount_total,
                    COALESCE(SUM(CASE WHEN status = 1 THEN amount END), 0) AS amount_paid,
                    COALESCE(SUM(CASE WHEN status = 0 THEN amount END), 0) AS amount_unpaid
                    FROM bills_monitoring");
                if ($billRes) {
                    $br = mysqli_fetch_assoc($billRes);
                    $billStats['paid'] = (int)$br['paid'];
                    $billStats['unpaid'] = (int)$br['unpaid'];
                    $billTotal = (int)$br['total'];
                    $billAmounts['paid'] = (float)$br['amount_paid'];
                    $billAmounts['unpaid'] = (float)$br['amount_unpaid'];
                    $amountTotal = (float)$br['amount_total'];
                }

                $typeQuery = mysqli_query($con, "SELECT type_of_billing, COUNT(*) AS cnt, COALESCE(SUM(amount), 0) AS amt FROM bills_monitoring GROUP BY type_of_billing ORDER BY amt DESC, cnt DESC");
                $typeLabels = array();
                $typeData = array();
                $typeAmounts = array();
                if ($typeQuery) {
                    while ($trow = mysqli_fetch_assoc($typeQuery)) {
                        $typeLabels[] = $trow['type_of_billing'];
                        $typeData[] = (int)$trow['cnt'];
                        $typeAmounts[] = (float)$trow['amt'];
                    }
                }

                function peso_fmt($v) {
                    return '&#8369; ' . number_format((float)$v, 2);
                }

                $locQuery = mysqli_query($con, "SELECT COALESCE(location_office, '') AS location_office, COUNT(*) AS total FROM bills_monitoring GROUP BY COALESCE(location_office, '') ORDER BY total DESC");
                $locLabels = array();
                $locData = array();
                if ($locQuery) {
                    while ($lrow = mysqli_fetch_assoc($locQuery)) {
                        $locLabels[] = $lrow['location_office'] !== '' ? $lrow['location_office'] : '(No location)';
                        $locData[] = (int)$lrow['total'];
                    }
                }
                ?>

                <!-- Statistics Panel -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-file-invoice-dollar"></i> Bills Overview</div>
                        <div class="panel-body">
                            <div id="metricCardsRow">
                                <div class="metric-card" data-card="total">
                                    <a href="#" class="bill-card bill-card-total" data-card-status="" title="All billing records">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-navy"><i class="fa fa-file-text-o"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Amount</span>
                                                <span class="info-box-number"><?php echo peso_fmt($amountTotal); ?></span>
                                                <span class="info-box-desc"><?php echo number_format($billTotal); ?> bills · All billing records</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="metric-card" data-card="paid">
                                    <a href="#" class="bill-card" data-card-status="1" title="Billing has been paid">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-blue"><i class="fa fa-check-circle-o"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Paid Amount</span>
                                                <span class="info-box-number"><?php echo peso_fmt($billAmounts['paid']); ?></span>
                                                <span class="info-box-desc"><?php echo number_format($billStats['paid']); ?> bills · Paid</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="metric-card" data-card="unpaid">
                                    <a href="#" class="bill-card" data-card-status="0" title="Billing is not yet paid">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Unpaid Amount</span>
                                                <span class="info-box-number"><?php echo peso_fmt($billAmounts['unpaid']); ?></span>
                                                <span class="info-box-desc"><?php echo number_format($billStats['unpaid']); ?> bills · Unpaid</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default chart-panel">
                                <div class="panel-heading"><i class="fa fa-pie-chart"></i> Bills by Status</div>
                                <div class="panel-body">
                                    <div class="chart-container">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="panel panel-default chart-panel">
                                <div class="panel-heading"><i class="fa fa-bar-chart"></i> Amounts by Type of Billing</div>
                                <div class="panel-body">
                                    <div class="chart-container">
                                        <canvas id="typeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table Panel -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-table"></i> Bills Records</div>
                        <div class="panel-body">
                            <!-- Filter bar -->
                            <div class="row" style="margin-bottom:10px;">
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label for="statusSelect">Filter by Status</label>
                                        <select id="statusSelect" name="status" class="form-control full-width-select">
                                            <option value="">All Status</option>
                                            <option value="1">Paid</option>
                                            <option value="0">Unpaid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label for="typeSelect">Filter by Type</label>
                                        <select id="typeSelect" name="type_of_billing" class="form-control full-width-select">
                                            <option value="">All Types</option>
                                            <option value="Water Bill">Water Bill</option>
                                            <option value="Internet Bill">Internet Bill</option>
                                            <option value="Electricity Bill">Electricity Bill</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label for="locationSelect">Filter by Location</label>
                                        <select id="locationSelect" name="location_office" class="form-control full-width-select">
                                            <option value="">All Locations</option>
                                            <option value="SDN Provincial Office">SDN Provincial Office</option>
                                            <option value="SDN Hill Relay Station">SDN Hill Relay Station</option>
                                            <option value="__none__">(No location)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" id="clearCardFilterBtn" class="btn btn-default btn-sm full-width-select" style="display:none;">
                                            <i class="fa fa-times-circle"></i> Clear filter
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label for="dateFrom">Date Received From</label>
                                        <input type="date" id="dateFrom" name="date_from" class="form-control" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-4 col-xs-6">
                                    <div class="form-group">
                                        <label for="dateTo">Date Received To</label>
                                        <input type="date" id="dateTo" name="date_to" class="form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </div>

                            <!-- Toolbar -->
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <?php if ($isAdmin) { ?>
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-plus"></i> Add Bill</button>
                                        <button class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled><i class="fa fa-trash"></i> Delete</button>
                                    <?php } ?>
                                    <label style="margin:0; font-weight:normal;">Show </label>
                                    <select id="perPageSelect" class="form-control input-sm" style="display:inline-block; width:auto;">
                                        <option value="5" selected>5</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="150">150</option>
                                        <option value="200">200</option>
                                    </select>
                                    <label style="margin:0; font-weight:normal;"> entries</label>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <div class="input-group" style="width:300px;">
                                        <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search bills..." />
                                        <span class="input-group-btn">
                                            <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                            <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                        </span>
                                    </div>
                                    <?php if ($isAdmin) { ?>
                                        <button id="importBtn" class="btn btn-success btn-sm"><i class="fa fa-download"></i> Import</button>
                                        <input type="file" id="importFile" style="display:none;" accept=".csv, .xlsx" />
                                        <button id="exportBtn" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> Export</button>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table id="table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <?php if ($isAdmin) { ?>
                                                <th style="width: 20px !important;"><input type="checkbox" id="cbxMain" /></th>
                                            <?php } ?>
                                            <th>No.</th>
                                            <th>Date Received</th>
                                            <th>Type of Billing</th>
                                            <th>Link to File</th>
                                            <th>Amount</th>
                                            <th>Location/Office</th>
                                            <th>Due Date</th>
                                            <th>Disconnection Date</th>
                                            <th>Status</th>
                                            <th>Date Paid</th>
                                            <th>Remarks</th>
                                            <th>Link to OR</th>
                                            <?php if ($isAdmin) { ?>
                                                <th style="width: 80px !important;">Option</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr>
                                            <td colspan="14" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-top:10px;">
                                <div id="paginationInfo" class="text-muted"></div>
                                <ul class="pagination" style="margin:0;" id="pagination"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include "add_modal.php"; ?>
                <?php include "edit_modal.php"; ?>

                <!-- DELETE CONFIRMATION MODAL -->
                <div id="deleteModal" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Delete Confirmation</h4>
                            </div>
                            <div class="modal-body">
                                <p id="deleteConfirmText">Are you sure you want to delete the selected item(s)?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">No</button>
                                <button type="button" class="btn btn-primary btn-sm" id="confirmDeleteBtn">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </aside>
    </div>

    <?php include dirname(__DIR__) . '/scripts.php'; ?>

    <script type="text/javascript">
        (function() {
            var basePath = '../../ajax/';
            var currentPage = 1;
            var perPage = 5;
            var totalPages = 1;
            var searchTimeout = null;
            var isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
            var selectedIds = {};
            var selectAllActive = false;

            function getFilters() {
                return {
                    status: document.getElementById('statusSelect').value,
                    type_of_billing: document.getElementById('typeSelect').value,
                    location_office: document.getElementById('locationSelect').value,
                    date_from: document.getElementById('dateFrom').value,
                    date_to: document.getElementById('dateTo').value,
                    search: document.getElementById('searchInput').value
                };
            }

            function getParams() {
                var f = getFilters();
                return 'search=' + encodeURIComponent(f.search) +
                    '&status=' + encodeURIComponent(f.status) +
                    '&type_of_billing=' + encodeURIComponent(f.type_of_billing) +
                    '&location_office=' + encodeURIComponent(f.location_office) +
                    '&date_from=' + encodeURIComponent(f.date_from) +
                    '&date_to=' + encodeURIComponent(f.date_to);
            }

            function escHtml(str) {
                if (str === null || str === undefined) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(String(str)));
                return div.innerHTML;
            }

            function linkOrDash(url, label) {
                url = (url || '').trim();
                if (url === '') return '';
                var safe = escHtml(url);
                var text = label || safe;
                if (text.length > 40) text = text.substring(0, 40) + '...';
                var href = url;
                if (!/^https?:\/\//i.test(href) && !/^mailto:/i.test(href)) {
                    href = 'https://' + href;
                }
                return '<a href="' + safe + '" target="_blank" rel="noopener" title="' + safe + '">' + escHtml(text) + '</a>';
            }

            function statusBadge(status) {
                var paid = (status === '1' || status === 1 || status === true);
                return paid
                    ? '<span class="label label-success">Paid</span>'
                    : '<span class="label label-warning">Unpaid</span>';
            }

            function loadData(page) {
                currentPage = page || 1;
                var colspan = isAdmin ? 14 : 13;
                var params = 'page=' + currentPage +
                    '&per_page=' + perPage +
                    '&' + getParams();

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.ajax({
                    url: basePath + 'bills_data.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        totalPages = res.total_pages;
                        renderTable(res.data);
                        renderPagination(res.page, res.total_pages, res.total);
                        syncHeaderCheckbox();
                        updateDeleteBtn();
                    },
                    error: function() {
                        tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center text-danger">Failed to load data.</td></tr>';
                    }
                });
            }

            function renderTable(rows) {
                var tbody = document.getElementById('tableBody');
                var colspan = isAdmin ? 14 : 13;
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    var id = parseInt(row.id);
                    var amount = row.amount !== '' ? '&#8369; ' + escHtml(row.amount) : '';
                    html += '<tr>';
                    if (isAdmin) {
                        html += '<td><input type="checkbox" class="chk_delete" data-id="' + id + '"' + (selectedIds[id] ? ' checked' : '') + ' /></td>';
                    }
                    html += '<td>' + row.row_num + '</td>' +
                        '<td>' + escHtml(row.date_received) + '</td>' +
                        '<td>' + escHtml(row.type_of_billing) + '</td>' +
                        '<td>' + linkOrDash(row.link_to_file) + '</td>' +
                        '<td>' + amount + '</td>' +
                        '<td>' + (row.location_office ? escHtml(row.location_office) : '<span class="text-muted">(No location)</span>') + '</td>' +
                        '<td>' + escHtml(row.due_date) + '</td>' +
                        '<td>' + escHtml(row.disconnection_date) + '</td>' +
                        '<td>' + statusBadge(row.status) + '</td>' +
                        '<td>' + escHtml(row.date_paid) + '</td>' +
                        '<td>' + escHtml(row.remarks) + '</td>' +
                        '<td>' + linkOrDash(row.link_to_or) + '</td>';
                    if (isAdmin) {
                        html += '<td class="option-buttons">' +
                            '<div style="display:flex;gap:5px;flex-wrap:wrap;justify-content:center;">' +
                            '<button class="btn btn-primary btn-xs editBtn" data-id="' + id + '" title="Edit"><i class="fa fa-pencil-square-o"></i></button>' +
                            '</div></td>';
                    }
                    html += '</tr>';
                });
                tbody.innerHTML = html;
            }

            function renderPagination(page, total, count) {
                var pag = document.getElementById('pagination');
                var info = document.getElementById('paginationInfo');

                var start = count > 0 ? (page - 1) * perPage + 1 : 0;
                var end = Math.min(page * perPage, count);
                info.textContent = 'Showing ' + start + ' to ' + end + ' of ' + count + ' entries';

                if (total <= 1) {
                    pag.innerHTML = '';
                    return;
                }

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
                if (!isAdmin) return;
                var count = Object.keys(selectedIds).length;
                var btn = document.getElementById('deleteSelectedBtn');
                if (btn) {
                    btn.disabled = count === 0;
                    btn.innerHTML = '<i class="fa fa-trash"></i> Delete' + (count > 0 ? ' (' + count + ')' : '');
                }
            }

            function syncHeaderCheckbox() {
                if (!isAdmin) return;
                var cbx = document.getElementById('cbxMain');
                if (!cbx) return;
                var all = document.querySelectorAll('.chk_delete').length;
                var checked = document.querySelectorAll('.chk_delete:checked').length;
                cbx.checked = selectAllActive || (all > 0 && all === checked);
                cbx.indeterminate = !selectAllActive && checked > 0 && checked < all;
            }

            function clearSelection() {
                selectedIds = {};
                selectAllActive = false;
                var cbx = document.getElementById('cbxMain');
                if (cbx) {
                    cbx.checked = false;
                    cbx.indeterminate = false;
                }
                document.querySelectorAll('.chk_delete').forEach(function(cb) {
                    cb.checked = false;
                });
                updateDeleteBtn();
            }

            function selectAllMatching() {
                var params = 'get_ids=1&search=' + encodeURIComponent(document.getElementById('searchInput').value) +
                    '&status=' + encodeURIComponent(document.getElementById('statusSelect').value) +
                    '&type_of_billing=' + encodeURIComponent(document.getElementById('typeSelect').value) +
                    '&location_office=' + encodeURIComponent(document.getElementById('locationSelect').value) +
                    '&date_from=' + encodeURIComponent(document.getElementById('dateFrom').value) +
                    '&date_to=' + encodeURIComponent(document.getElementById('dateTo').value);
                $.ajax({
                    url: basePath + 'bills_data.php?' + params,
                    dataType: 'json',
                    cache: false,
                    success: function(res) {
                        if (res.ids) {
                            res.ids.forEach(function(id) {
                                selectedIds[id] = true;
                            });
                            selectAllActive = true;
                        }
                        document.querySelectorAll('.chk_delete').forEach(function(c) {
                            c.checked = true;
                        });
                        syncHeaderCheckbox();
                        updateDeleteBtn();
                    }
                });
            }

            // Filter change handlers
            $('#statusSelect, #typeSelect, #locationSelect, #dateFrom, #dateTo').on('change', function() {
                clearSelection();
                loadData(1);
            });

            // ========== METRIC CARDS ==========
            function setCardFilter(status) {
                document.getElementById('statusSelect').value = status;
                syncCardHighlight(status);
                var btn = document.getElementById('clearCardFilterBtn');
                if (btn) btn.style.display = (status === '' ? 'none' : 'inline-block');
                clearSelection();
                loadData(1);
            }

            function syncCardHighlight(status) {
                var hasFilter = (status !== '');
                document.querySelectorAll('.metric-card').forEach(function(card) {
                    var link = card.querySelector('a[data-card-status]');
                    var cardStatus = link ? (link.getAttribute('data-card-status') || '') : '';
                    var active = hasFilter && cardStatus === status;
                    card.classList.toggle('card-active', active);
                });
                var sel = document.getElementById('statusSelect');
                if (sel && sel.value !== status) sel.value = status;
            }

            $('#metricCardsRow').on('click', '.metric-card a', function(e) {
                e.preventDefault();
                var cardStatus = this.getAttribute('data-card-status') || '';
                var current = document.getElementById('statusSelect').value;
                if (cardStatus === '' || cardStatus === current) {
                    setCardFilter('');
                } else {
                    setCardFilter(cardStatus);
                }
            });

            $('#clearCardFilterBtn').on('click', function() {
                setCardFilter('');
            });

            // Per-page selector
            $('#perPageSelect').on('change', function() {
                perPage = parseInt(this.value);
                clearSelection();
                loadData(1);
            });

            // Search
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    clearSelection();
                    loadData(1);
                }, 400);
            });
            $('#searchBtn').on('click', function() {
                clearSelection();
                loadData(1);
            });
            $('#clearSearchBtn').on('click', function() {
                document.getElementById('searchInput').value = '';
                clearSelection();
                loadData(1);
            });

            // Pagination clicks
            $('#pagination').on('click', 'a[data-page]', function(e) {
                e.preventDefault();
                var pg = parseInt($(this).attr('data-page'));
                if (pg >= 1 && pg <= totalPages) loadData(pg);
            });

            // Select all checkbox
            if (isAdmin) {
                $('#cbxMain').on('change', function() {
                    if (this.checked) {
                        selectAllMatching();
                    } else {
                        selectedIds = {};
                        selectAllActive = false;
                        document.querySelectorAll('.chk_delete').forEach(function(cb) {
                            cb.checked = false;
                        });
                        syncHeaderCheckbox();
                        updateDeleteBtn();
                    }
                });
                $(document).on('change', '.chk_delete', function() {
                    var id = parseInt(this.getAttribute('data-id'));
                    if (this.checked) {
                        selectedIds[id] = true;
                    } else {
                        delete selectedIds[id];
                        selectAllActive = false;
                    }
                    var all = document.querySelectorAll('.chk_delete').length;
                    var checked = document.querySelectorAll('.chk_delete:checked').length;
                    document.getElementById('cbxMain').checked = selectAllActive || (all > 0 && all === checked);
                    document.getElementById('cbxMain').indeterminate = !selectAllActive && checked > 0 && checked < all;
                    updateDeleteBtn();
                });
            }

            // ========== ADD ==========
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('addSubmitBtn');
                btn.disabled = true;
                btn.value = 'Adding...';
                var formData = new FormData(this);
                formData.append('action', 'add');
                $.ajax({
                    url: basePath + 'bills_crud.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#addModal').modal('hide');
                            showToast(res.message, 'success');
                            loadData(currentPage);
                            document.getElementById('addForm').reset();
                        } else {
                            $('#addAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                        }
                    },
                    error: function() {
                        $('#addAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                    },
                    complete: function() {
                        btn.disabled = false;
                        btn.value = 'Add Bill';
                    }
                });
            });

            // ========== EDIT ==========
            $(document).on('click', '.editBtn', function() {
                var id = $(this).attr('data-id');
                $('#editAlert').hide();
                $.getJSON(basePath + 'bills_get_item.php?action=item&id=' + id, function(item) {
                    $('#edit_hidden_id').val(item.id);
                    $('#edit_date_received').val(item.date_received);
                    $('#edit_type_of_billing').val(item.type_of_billing || 'Water Bill');
                    $('#edit_link_to_file').val(item.link_to_file);
                    $('#edit_amount').val(item.amount);
                    $('#edit_location_office').val(item.location_office || '');
                    $('#edit_due_date').val(item.due_date || '');
                    $('#edit_disconnection_date').val(item.disconnection_date || '');
                    $('#edit_status').prop('checked', item.status == 1);
                    $('#edit_date_paid').val(item.date_paid || '');
                    $('#edit_remarks').val(item.remarks);
                    $('#edit_link_to_or').val(item.link_to_or);
                    $('#editModal').modal('show');
                }).fail(function() {
                    showToast('Failed to load bill data.', 'danger');
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('editSubmitBtn');
                btn.disabled = true;
                btn.value = 'Saving...';
                $.ajax({
                    url: basePath + 'bills_crud.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=edit',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            $('#editModal').modal('hide');
                            showToast(res.message, 'success');
                            loadData(currentPage);
                        } else {
                            $('#editAlert').html('<div class="alert alert-danger">' + escHtml(res.error) + '</div>').show();
                        }
                    },
                    error: function() {
                        $('#editAlert').html('<div class="alert alert-danger">Network error. Please try again.</div>').show();
                    },
                    complete: function() {
                        btn.disabled = false;
                        btn.value = 'Save';
                    }
                });
            });

            // ========== DELETE ==========
            $('#deleteSelectedBtn').on('click', function() {
                var count = Object.keys(selectedIds).length;
                if (count === 0) {
                    showToast('No items selected.', 'warning');
                    return;
                }
                document.getElementById('deleteConfirmText').textContent =
                    'Are you sure you want to delete ' + count + ' selected item(s)?';
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                var btn = $(this);
                if (btn.hasClass('disabled')) return;
                btn.addClass('disabled').prop('disabled', true).text('Deleting...');

                var ids = Object.keys(selectedIds);
                var fd = new FormData();
                fd.append('action', 'delete');
                ids.forEach(function(id) {
                    fd.append('ids[]', id);
                });

                $.ajax({
                    url: basePath + 'bills_crud.php',
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        $('#deleteModal').modal('hide');
                        btn.removeClass('disabled').prop('disabled', false).text('OK');
                        showToast(res.message, 'success');
                        clearSelection();
                        loadData(currentPage);
                    },
                    error: function(xhr, status, err) {
                        console.error('[Delete Error]', status, err, xhr.responseText);
                        btn.removeClass('disabled').prop('disabled', false).text('OK');
                        showToast('Network error.', 'danger');
                    }
                });
            });

            // ========== IMPORT ==========
            $('#importBtn').on('click', function() {
                document.getElementById('importFile').click();
            });
            $('#importFile').on('change', function() {
                var formData = new FormData();
                formData.append('file', this.files[0]);

                fetch('import.php', {
                    method: 'POST',
                    body: formData
                }).then(function(response) {
                    return response.text().then(function(text) {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Import raw response:', text);
                            throw e;
                        }
                    });
                }).then(function(data) {
                    console.log('IMPORT response:', JSON.parse(JSON.stringify(data)));
                    if (data.success) {
                        var msg = data.inserted + ' row(s) inserted';
                        if (data.skipped > 0) {
                            msg += ', ' + data.skipped + ' skipped';
                            if (data.skipped_details && data.skipped_details.length > 0) {
                                var lines = data.skipped_details.map(function(d) {
                                    return 'Row ' + d.row + ': ' + d.reason;
                                });
                                console.warn('IMPORT: skipped ' + data.skipped + ' row(s):\n' + lines.join('\n'));
                            }
                        }
                        showToast(msg, data.skipped > 0 ? 'warning' : 'success');
                        loadData(currentPage);
                        refreshStats();
                    } else {
                        showToast(data.error || 'Import failed.', 'danger');
                    }
                }).catch(function() { showToast('Import error.', 'danger'); });
            });

            // ========== EXPORT ==========
            $('#exportBtn').on('click', function() {
                var f = getFilters();
                var url = 'export.php?status=' + encodeURIComponent(f.status) +
                    '&type_of_billing=' + encodeURIComponent(f.type_of_billing) +
                    '&location_office=' + encodeURIComponent(f.location_office) +
                    (f.date_from ? '&date_from=' + encodeURIComponent(f.date_from) : '') +
                    (f.date_to ? '&date_to=' + encodeURIComponent(f.date_to) : '');
                window.location.href = url;
            });

            // ========== DATE/TIME ==========
            function updateDateTime() {
                var now = new Date();
                document.getElementById('dateTime').innerText = now.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            }
            setInterval(updateDateTime, 1000);
            updateDateTime();

            // ========== STATS REFRESH (cards + charts) ==========
            function formatPeso(v) {
                v = Number(v) || 0;
                return '\u20B1 ' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function refreshStats() {
                $.ajax({
                    url: basePath + 'bills_data.php?stats=1',
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                        var cardMeta = [
                            { card: '', numSel: '.metric-card[data-card="total"] .info-box-number', descSel: '.metric-card[data-card="total"] .info-box-desc', amtKey: 'amountTotal', cntKey: 'total', label: 'bills \u00B7 All billing records' },
                            { card: 'paid', numSel: '.metric-card[data-card="paid"] .info-box-number', descSel: '.metric-card[data-card="paid"] .info-box-desc', amtKey: 'paid', cntKey: 'paid', label: 'bills \u00B7 Paid' },
                            { card: 'unpaid', numSel: '.metric-card[data-card="unpaid"] .info-box-number', descSel: '.metric-card[data-card="unpaid"] .info-box-desc', amtKey: 'unpaid', cntKey: 'unpaid', label: 'bills \u00B7 Unpaid' }
                        ];
                        cardMeta.forEach(function(c) {
                            var numEl = document.querySelector(c.numSel);
                            var descEl = document.querySelector(c.descSel);
                            if (!numEl && !descEl) return;
                            var amt = c.card === '' ? data.amountTotal : (data.billAmounts || {})[c.amtKey];
                            var cnt = c.card === '' ? data.total : (data.billStats || {})[c.cntKey];
                            if (numEl) numEl.textContent = formatPeso(amt);
                            if (descEl) descEl.textContent = Number(cnt || 0).toLocaleString('en-US') + ' ' + c.label;
                        });
                        if (typeof renderBillsCharts === 'function') {
                            renderBillsCharts(data);
                        }
                    }
                });
            }

            // ========== INIT ==========
            loadData(1);
        })();
    </script>

    <script type="text/javascript">
        var billsStatusChart;
        var billsTypeChart;
        var billsTypeColors = ['#3c8dbc','#27ae60','#e74c3c','#f39c12','#9b59b6','#1abc9c','#e67e22','#34495e'];

        function setChartEmpty(canvasId, isEmpty) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) return;
            var container = canvas.closest('.chart-container');
            if (!container) return;
            canvas.style.display = isEmpty ? 'none' : 'block';
            var overlay = container.querySelector('.chart-empty-state');
            if (isEmpty) {
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'chart-empty-state';
                    overlay.textContent = 'No data yet';
                    container.appendChild(overlay);
                }
                overlay.style.display = 'flex';
            } else if (overlay) {
                overlay.style.display = 'none';
            }
        }

        function renderBillsCharts(data) {
            function fmtPeso(v) {
                return '\u20B1 ' + (Number(v) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            var paidCount = (data.billStats && data.billStats.paid) || 0;
            var unpaidCount = (data.billStats && data.billStats.unpaid) || 0;
            var countTotal = paidCount + unpaidCount;
            var paidAmt = Number((data.billAmounts && data.billAmounts.paid) || 0);
            var unpaidAmt = Number((data.billAmounts && data.billAmounts.unpaid) || 0);
            var amountTotal = paidAmt + unpaidAmt;
            var useAmounts = amountTotal > 0;
            var statusData = useAmounts ? [paidAmt, unpaidAmt] : [paidCount, unpaidCount];
            var countArr = [paidCount, unpaidCount];
            var typeLabelArr = data.typeLabels || [];
            var typeAmtArr = (data.typeAmounts || []).map(function(v) { return Number(v) || 0; });
            var typeCntArr = (data.typeData || []).map(function(v) { return Number(v) || 0; });
            var typeAmtTotal = typeAmtArr.reduce(function(a, b) { return a + b; }, 0);
            var typeCntTotal = typeCntArr.reduce(function(a, b) { return a + b; }, 0);
            var useTypeAmounts = typeAmtTotal > 0;
            var typeBarData = useTypeAmounts ? typeAmtArr : typeCntArr;

            // ===== STATUS DONUT (amount-weighted, falls back to count when total amount is 0) =====
            setChartEmpty('statusChart', countTotal === 0);
            if (countTotal === 0) {
                if (billsStatusChart) { billsStatusChart.destroy(); billsStatusChart = null; }
            } else if (!billsStatusChart) {
                billsStatusChart = new Chart(document.getElementById('statusChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Paid', 'Unpaid'],
                        datasets: [{
                            data: statusData,
                            backgroundColor: ['#3c8dbc', '#f39c12'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 20, font: { size: 13 } } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var idx = context.dataIndex;
                                        var ctotal = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = ctotal > 0 ? ((context.raw / ctotal) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + fmtPeso(context.raw) + ' (' + pct + '%) \u00B7 ' + (countArr[idx] || 0) + ' bills';
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                billsStatusChart.data.datasets[0].data = statusData;
                billsStatusChart.update();
            }

            // ===== TYPE BAR (amounts per type, falls back to counts when all amounts are 0) =====
            setChartEmpty('typeChart', countTotal === 0);
            if (countTotal === 0) {
                if (billsTypeChart) { billsTypeChart.destroy(); billsTypeChart = null; }
            } else if (!billsTypeChart) {
                billsTypeChart = new Chart(document.getElementById('typeChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: typeLabelArr,
                        datasets: [{
                            label: 'Amount (\u20B1)',
                            data: typeBarData,
                            backgroundColor: billsTypeColors.slice(0, typeLabelArr.length),
                            borderWidth: 0,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var idx = context.dataIndex;
                                        return fmtPeso(context.raw) + ' \u00B7 ' + (typeCntArr[idx] || 0) + ' bills';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { callback: function(v) { return fmtPeso(v); } }
                            },
                            y: { grid: { display: false }, ticks: { font: { size: 12 } } }
                        }
                    }
                });
            } else {
                billsTypeChart.data.labels = typeLabelArr;
                billsTypeChart.data.datasets[0].data = typeBarData;
                billsTypeChart.data.datasets[0].backgroundColor = billsTypeColors.slice(0, typeLabelArr.length);
                billsTypeChart.update();
            }
        }

        // Initial render from server-side PHP data
        renderBillsCharts({
            billStats: {
                'paid': <?php echo (int)$billStats['paid']; ?>,
                'unpaid': <?php echo (int)$billStats['unpaid']; ?>
            },
            billAmounts: {
                'paid': <?php echo (float)$billAmounts['paid']; ?>,
                'unpaid': <?php echo (float)$billAmounts['unpaid']; ?>
            },
            typeLabels: <?php echo json_encode($typeLabels); ?>,
            typeData: <?php echo json_encode($typeData); ?>,
            typeAmounts: <?php echo json_encode($typeAmounts); ?>
        });
    </script>
</body>
</html>
