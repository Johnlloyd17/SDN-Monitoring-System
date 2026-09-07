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
        .info-box-desc {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            line-height: 1.3;
            color: #999;
            font-weight: normal;
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
            font-size: 22px;
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
                        <h3>Letters Monitoring</h3>
                        <p class="header-address">Letter and Correspondence Records</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div>
                </div>
            </section>

            <section class="content">
                <?php
                $letterCounts = array(
                    'needs_response' => 0,
                    'responded'      => 0,
                    'resp_yes'       => 0,
                    'resp_no'        => 0,
                    'not_specified'  => 0,
                    'request'        => 0,
                    'provision'      => 0
                );
                $letterTotal = 0;
                $cardRes = mysqli_query($con, "SELECT
                    SUM(CASE WHEN for_response = 'Y' AND date_responded IS NULL THEN 1 ELSE 0 END) AS needs_response,
                    SUM(CASE WHEN date_responded IS NOT NULL THEN 1 ELSE 0 END) AS responded,
                    SUM(CASE WHEN for_response = 'Y' THEN 1 ELSE 0 END) AS resp_yes,
                    SUM(CASE WHEN for_response = 'N' THEN 1 ELSE 0 END) AS resp_no,
                    SUM(CASE WHEN for_response IS NULL OR for_response = '' THEN 1 ELSE 0 END) AS not_specified,
                    SUM(CASE WHEN fw4a = 'Request' THEN 1 ELSE 0 END) AS request,
                    SUM(CASE WHEN fw4a = 'Provision' THEN 1 ELSE 0 END) AS provision,
                    COUNT(*) AS total
                    FROM letters_monitoring");
                if ($cardRes) {
                    $cr = mysqli_fetch_assoc($cardRes);
                    foreach ($letterCounts as $k => $v) {
                        $letterCounts[$k] = (int)$cr[$k];
                    }
                    $letterTotal = (int)$cr['total'];
                }

                $typeQuery = mysqli_query($con, "SELECT type, COUNT(*) AS total FROM letters_monitoring WHERE type IS NOT NULL AND type != '' GROUP BY type ORDER BY total DESC");
                $typeLabels = array();
                $typeData = array();
                if ($typeQuery) {
                    while ($trow = mysqli_fetch_assoc($typeQuery)) {
                        $typeLabels[] = $trow['type'];
                        $typeData[] = (int)$trow['total'];
                    }
                }

                $fw4aQuery = mysqli_query($con, "SELECT fw4a, COUNT(*) AS total FROM letters_monitoring WHERE fw4a IS NOT NULL AND fw4a != '' GROUP BY fw4a ORDER BY total DESC");
                $fw4aLabels = array();
                $fw4aData = array();
                if ($fw4aQuery) {
                    while ($frow = mysqli_fetch_assoc($fw4aQuery)) {
                        $fw4aLabels[] = $frow['fw4a'];
                        $fw4aData[] = (int)$frow['total'];
                    }
                }
                ?>

                <!-- Statistics Panel -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-file-text-o"></i> Letters Overview</div>
                        <div class="panel-body">
                            <div id="metricCardsRow">
                                <div class="metric-card" data-card="total">
                                    <a href="#" class="letter-card letter-card-total" data-card-status="total" title="All letter records">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-navy"><i class="fa fa-file-text-o"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Letters</span>
                                                <span class="info-box-number"><?php echo number_format($letterTotal); ?></span>
                                                <span class="info-box-desc">All letter records</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <?php
                                $letterCardMeta = array(
                                    'needs_response' => array('bg-yellow', 'fa fa-exclamation-circle', 'Needs Response', 'For Response is Yes and no response date yet'),
                                    'responded'      => array('bg-green',  'fa fa-check-circle-o',      'Responded',      'Has a response date recorded'),
                                    'not_specified'  => array('bg-gray',   'fa fa-minus-circle',        'Not Specified',  'For Response not specified'),
                                    'request'        => array('bg-blue',   'fa fa-paper-plane',         'Request',        'FW4A marked as Request'),
                                    'provision'      => array('bg-purple', 'fa fa-server',              'Provision',      'FW4A marked as Provision')
                                );
                                foreach ($letterCardMeta as $ck => $meta) {
                                    echo '<div class="metric-card" data-card="' . $ck . '">';
                                    echo '<a href="#" class="letter-card" data-card-status="' . $ck . '" title="' . htmlspecialchars($meta[3], ENT_QUOTES) . '">';
                                    echo '<div class="info-box">';
                                    echo '<span class="info-box-icon ' . $meta[0] . '"><i class="' . $meta[1] . '"></i></span>';
                                    echo '<div class="info-box-content">';
                                    echo '<span class="info-box-text">' . $meta[2] . '</span>';
                                    echo '<span class="info-box-number">' . number_format($letterCounts[$ck]) . '</span>';
                                    echo '<span class="info-box-desc">' . htmlspecialchars($meta[3]) . '</span>';
                                    echo '</div></div></a></div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="panel panel-default chart-panel">
                                <div class="panel-heading"><i class="fa fa-pie-chart"></i> Letters by Type</div>
                                <div class="panel-body">
                                    <div class="chart-container">
                                        <canvas id="typeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="panel panel-default chart-panel">
                                <div class="panel-heading"><i class="fa fa-pie-chart"></i> Response Status</div>
                                <div class="panel-body">
                                    <div class="chart-container">
                                        <canvas id="responseChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="panel panel-default chart-panel">
                                <div class="panel-heading"><i class="fa fa-bar-chart"></i> FW4A Distribution</div>
                                <div class="panel-body">
                                    <div class="chart-container">
                                        <canvas id="fw4aChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table Panel -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><i class="fa fa-table"></i> Letters Records</div>
                        <div class="panel-body">
                            <!-- Filter bar -->
                            <div class="row" style="margin-bottom:10px;">
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="typeSelect">Filter by Type</label>
                                        <select id="typeSelect" name="type" class="form-control full-width-select">
                                            <option value="">All Types</option>
                                            <option value="Incoming">Incoming</option>
                                            <option value="Outgoing">Outgoing</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="forResponseSelect">For Response?</label>
                                        <select id="forResponseSelect" name="for_response" class="form-control full-width-select">
                                            <option value="">All</option>
                                            <option value="Y">Yes</option>
                                            <option value="N">No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="dateFrom">Date From</label>
                                        <input type="date" id="dateFrom" name="date_from" class="form-control" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <label for="dateTo">Date To</label>
                                        <input type="date" id="dateTo" name="date_to" class="form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </div>

                            <!-- Toolbar -->
                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <?php if ($isAdmin) { ?>
                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal"><i class="fa fa-plus"></i> Add Letter</button>
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
                                        <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search letters..." />
                                        <span class="input-group-btn">
                                            <button class="btn btn-default btn-sm" id="searchBtn"><i class="fa fa-search"></i></button>
                                            <button class="btn btn-default btn-sm" id="clearSearchBtn" title="Clear search"><i class="fa fa-times"></i></button>
                                        </span>
                                    </div>
                                    <button id="clearCardFilterBtn" class="btn btn-default btn-sm" style="display:none;">
                                        <i class="fa fa-times-circle"></i> Clear filter
                                    </button>
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
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Subject</th>
                                            <th>FW4A</th>
                                            <th>Link Incoming</th>
                                            <th>For Response?</th>
                                            <th>Date Responded</th>
                                            <th>Link Outgoing</th>
                                            <th>Responsible Person</th>
                                            <th>Who Attended</th>
                                            <th>Remarks</th>
                                            <th>Post Activity Report</th>
                                            <?php if ($isAdmin) { ?>
                                                <th style="width: 80px !important;">Option</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <tr>
                                            <td colspan="16" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td>
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
            var activeCard = '';

            function getParams() {
                return 'search=' + encodeURIComponent(document.getElementById('searchInput').value) +
                    '&type=' + encodeURIComponent(document.getElementById('typeSelect').value) +
                    '&for_response=' + encodeURIComponent(document.getElementById('forResponseSelect').value) +
                    '&date_from=' + encodeURIComponent(document.getElementById('dateFrom').value) +
                    '&date_to=' + encodeURIComponent(document.getElementById('dateTo').value) +
                    '&card=' + encodeURIComponent(activeCard);
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

            function loadData(page) {
                currentPage = page || 1;
                var colspan = isAdmin ? 16 : 15;
                var params = 'page=' + currentPage +
                    '&per_page=' + perPage +
                    '&' + getParams();

                var tbody = document.getElementById('tableBody');
                tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>';

                $.ajax({
                    url: basePath + 'letters_data.php?' + params,
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
                var colspan = isAdmin ? 16 : 15;
                if (!rows || rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-center">No records found.</td></tr>';
                    return;
                }
                var html = '';
                rows.forEach(function(row) {
                    var id = parseInt(row.id);
                    html += '<tr>';
                    if (isAdmin) {
                        html += '<td><input type="checkbox" class="chk_delete" data-id="' + id + '"' + (selectedIds[id] ? ' checked' : '') + ' /></td>';
                    }
                    html += '<td>' + row.row_num + '</td>' +
                        '<td>' + escHtml(row.date) + '</td>' +
                        '<td>' + escHtml(row.type) + '</td>' +
                        '<td>' + escHtml(row.subject) + '</td>' +
                        '<td>' + escHtml(row.fw4a) + '</td>' +
                        '<td>' + linkOrDash(row.link_incoming) + '</td>' +
                        '<td>' + (row.for_response === 'Y' ? '<span class="label label-warning">Yes</span>' : (row.for_response === 'N' ? '<span class="label label-default">No</span>' : '<span class="text-muted">&mdash;</span>')) + '</td>' +
                        '<td>' + escHtml(row.date_responded) + '</td>' +
                        '<td>' + linkOrDash(row.link_outgoing) + '</td>' +
                        '<td>' + escHtml(row.responsible_person) + '</td>' +
                        '<td>' + escHtml(row.who_attended) + '</td>' +
                        '<td>' + escHtml(row.remarks) + '</td>' +
                        '<td>' + linkOrDash(row.post_activity_report) + '</td>';
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
                var params = 'get_ids=1&' + getParams();
                $.ajax({
                    url: basePath + 'letters_data.php?' + params,
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
            $('#typeSelect, #forResponseSelect, #dateFrom, #dateTo').on('change', function() {
                clearSelection();
                loadData(1);
            });

            // ========== METRIC CARDS ==========
            function setCardFilter(card) {
                activeCard = card;
                syncCardHighlight(card);
                var btn = document.getElementById('clearCardFilterBtn');
                if (btn) btn.style.display = (card === '' ? 'none' : 'inline-block');
                clearSelection();
                loadData(1);
            }

            function syncCardHighlight(card) {
                var hasFilter = (card !== '');
                document.querySelectorAll('.metric-card').forEach(function(el) {
                    var cardKey = el.getAttribute('data-card') || 'total';
                    var active = hasFilter && (cardKey === card);
                    el.classList.toggle('card-active', active);
                });
            }

            $('#metricCardsRow').on('click', '.metric-card a', function(e) {
                e.preventDefault();
                var card = this.getAttribute('data-card-status') || '';
                if (card === '' || card === activeCard) {
                    setCardFilter('');
                } else {
                    setCardFilter(card);
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

            // Select all
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
                    url: basePath + 'letters_crud.php',
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
                        btn.value = 'Add Letter';
                    }
                });
            });

            // ========== EDIT ==========
            $(document).on('click', '.editBtn', function() {
                var id = $(this).attr('data-id');
                $('#editAlert').hide();
                $.getJSON(basePath + 'letters_get_item.php?action=item&id=' + id, function(item) {
                    $('#edit_hidden_id').val(item.id);
                    $('#edit_date').val(item.date);
                    $('#edit_type').val(item.type || 'Incoming');
                    $('#edit_subject').val(item.subject);
                    $('#edit_fw4a').val(item.fw4a || '');
                    $('#edit_link_incoming').val(item.link_incoming);
                    $('#edit_for_response').val(item.for_response || '');
                    $('#edit_date_responded').val(item.date_responded || '');
                    $('#edit_link_outgoing').val(item.link_outgoing);
                    $('#edit_responsible_person').val(item.responsible_person);
                    $('#edit_who_attended').val(item.who_attended);
                    $('#edit_remarks').val(item.remarks);
                    $('#edit_post_activity_report').val(item.post_activity_report);
                    $('#editModal').modal('show');
                }).fail(function() {
                    showToast('Failed to load letter data.', 'danger');
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('editSubmitBtn');
                btn.disabled = true;
                btn.value = 'Saving...';
                $.ajax({
                    url: basePath + 'letters_crud.php',
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
                    url: basePath + 'letters_crud.php',
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
                var f = {
                    type: document.getElementById('typeSelect').value,
                    for_response: document.getElementById('forResponseSelect').value,
                    date_from: document.getElementById('dateFrom').value,
                    date_to: document.getElementById('dateTo').value
                };
                var url = 'export.php?type=' + encodeURIComponent(f.type) +
                    '&for_response=' + encodeURIComponent(f.for_response) +
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
            function refreshStats() {
                $.ajax({
                    url: basePath + 'letters_data.php?stats=1',
                    dataType: 'json',
                    cache: false,
                    success: function(data) {
                        var cardMeta = [
                            { key: '', selector: '.metric-card[data-card="total"] .info-box-number', keyRef: 'total' },
                            { key: 'needs_response', selector: '.metric-card[data-card="needs_response"] .info-box-number', keyRef: null },
                            { key: 'responded', selector: '.metric-card[data-card="responded"] .info-box-number', keyRef: null },
                            { key: 'not_specified', selector: '.metric-card[data-card="not_specified"] .info-box-number', keyRef: null },
                            { key: 'request', selector: '.metric-card[data-card="request"] .info-box-number', keyRef: null },
                            { key: 'provision', selector: '.metric-card[data-card="provision"] .info-box-number', keyRef: null }
                        ];
                        cardMeta.forEach(function(c) {
                            var el = document.querySelector(c.selector);
                            if (!el) return;
                            var val = 0;
                            if (c.key === '') val = data.total;
                            else val = data.counts[c.key] || 0;
                            el.textContent = Number(val).toLocaleString('en-US');
                        });
                        if (typeof renderLettersCharts === 'function') {
                            renderLettersCharts(data);
                        }
                    }
                });
            }

            // ========== INIT ==========
            loadData(1);
        })();
    </script>

    <script type="text/javascript">
        var lettersTypeChart;
        var lettersResponseChart;
        var lettersFw4aChart;

        function renderLettersCharts(data) {
            // Letters by Type - Doughnut Chart
            if (lettersTypeChart) {
                lettersTypeChart.data.labels = data.typeLabels;
                lettersTypeChart.data.datasets[0].data = data.typeData;
                lettersTypeChart.update();
            } else {
                lettersTypeChart = new Chart(document.getElementById('typeChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.typeLabels,
                        datasets: [{
                            data: data.typeData,
                            backgroundColor: ['#3c8dbc', '#e67e22'],
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
                                        var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.raw + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Response Status - Doughnut Chart (Yes / No / Not Specified)
            var respData = [data.counts['resp_yes'], data.counts['resp_no'], data.counts['not_specified']];
            if (lettersResponseChart) {
                lettersResponseChart.data.labels = ['Yes', 'No', 'Not Specified'];
                lettersResponseChart.data.datasets[0].data = respData;
                lettersResponseChart.update();
            } else {
                lettersResponseChart = new Chart(document.getElementById('responseChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Yes', 'No', 'Not Specified'],
                        datasets: [{
                            data: respData,
                            backgroundColor: ['#00a65a', '#dd4b39', '#9e9e9e'],
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
                                        var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.raw + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // FW4A Distribution - Bar Chart
            if (lettersFw4aChart) {
                lettersFw4aChart.data.labels = data.fw4aLabels;
                lettersFw4aChart.data.datasets[0].data = data.fw4aData;
                lettersFw4aChart.update();
            } else {
                lettersFw4aChart = new Chart(document.getElementById('fw4aChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.fw4aLabels,
                        datasets: [{
                            label: 'Letters',
                            data: data.fw4aData,
                            backgroundColor: ['#3498db', '#27ae60', '#e74c3c', '#f39c12'],
                            borderWidth: 0,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) { return context.raw + ' letters'; }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 12 } } },
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
                        }
                    }
                });
            }
        }

        // Initial render from server-side PHP data
        renderLettersCharts({
            typeLabels: <?php echo json_encode($typeLabels); ?>,
            typeData: <?php echo json_encode($typeData); ?>,
            counts: {
                'needs_response': <?php echo (int)$letterCounts['needs_response']; ?>,
                'responded': <?php echo (int)$letterCounts['responded']; ?>,
                'resp_yes': <?php echo (int)$letterCounts['resp_yes']; ?>,
                'resp_no': <?php echo (int)$letterCounts['resp_no']; ?>,
                'not_specified': <?php echo (int)$letterCounts['not_specified']; ?>,
                'request': <?php echo (int)$letterCounts['request']; ?>,
                'provision': <?php echo (int)$letterCounts['provision']; ?>
            },
            fw4aLabels: <?php echo json_encode($fw4aLabels); ?>,
            fw4aData: <?php echo json_encode($fw4aData); ?>
        });
    </script>
</body>
</html>
