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
        <?php include "../connection.php"; ?>
        <?php include('../header.php'); ?>

        <?php
        $isAdminUat = !isset($_SESSION['staff']);

        function uatCoordFmt($lat, $lng) {
            $parts = array();
            if ($lat !== null && $lat !== '' && !is_nan((float)$lat)) {
                $v = (float)$lat;
                $parts[] = number_format(abs($v), 6, '.', '') . ($v < 0 ? 'S' : 'N');
            }
            if ($lng !== null && $lng !== '' && !is_nan((float)$lng)) {
                $w = (float)$lng;
                $parts[] = number_format(abs($w), 6, '.', '') . ($w < 0 ? 'W' : 'E');
            }
            return $parts ? implode('  ', $parts) : '';
        }
        ?>

        <div class="wrapper row-offcanvas row-offcanvas-left">
            <?php include('../sidebar-left.php'); ?>

            <aside class="right-side">
                <section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h1 style="margin: 0;">UAT Management</h1>
                    <div class="header-date-time" id="dateTime"></div>
                </section>
                <section class="content">
                    <div class="row">
                        <div class="box">
                            <div class="box-header">
                                <div class="col-md-12 col-sm-12 col-xs-12"><br>

                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a href="#tab-uat" data-toggle="tab"><i class="fa fa-file-signature"></i> UAT Records</a></li>
                                        </ul>
                                        <div class="tab-content">

                                            <!-- ===== TAB: UAT RECORDS ===== -->
                                            <div class="tab-pane active" id="tab-uat">

                                                <!-- Statistics Cards -->
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <i class="fa fa-bar-chart"></i> UAT Statistics
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <?php
                                                            $uatTableCheck = @mysqli_query($con, "SELECT 1 FROM uat LIMIT 0");
                                                            if ($uatTableCheck) {
                                                                $rUatTotal = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM uat"));
                                                                $rUatItems = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM uat_items"));
                                                                $rUatLocations = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT uat_id) AS total FROM uat_items"));
                                                            } else {
                                                                $rUatTotal = ['total' => 0];
                                                                $rUatItems = ['total' => 0];
                                                                $rUatLocations = ['total' => 0];
                                                            }
                                                            ?>
                                                            <div class="col-md-4 col-sm-6 col-xs-12">
                                                                <div class="info-box">
                                                                    <span class="info-box-icon bg-aqua"><i class="fa fa-map-marker"></i></span>
                                                                    <div class="info-box-content">
                                                                        <span class="info-box-text">Total UAT Locations</span>
                                                                        <span class="info-box-number"><?php echo $rUatTotal['total']; ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-6 col-xs-12">
                                                                <div class="info-box">
                                                                    <span class="info-box-icon bg-green"><i class="fa fa-boxes"></i></span>
                                                                    <div class="info-box-content">
                                                                        <span class="info-box-text">Total Equipment Items</span>
                                                                        <span class="info-box-number"><?php echo $rUatItems['total']; ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-sm-6 col-xs-12">
                                                                <div class="info-box">
                                                                    <span class="info-box-icon bg-yellow"><i class="fa fa-wifi"></i></span>
                                                                    <div class="info-box-content">
                                                                        <span class="info-box-text">Locations With Equipment</span>
                                                                        <span class="info-box-number"><?php echo $rUatLocations['total']; ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Statistics Cards -->

                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        User Acceptance Form (UAT) Records
                                                    </div>
                                                    <div class="panel-body">
                                                        <form method="post" id="uatFilterForm">
                                                            <div class="row">
                                                                <div class="col-md-4 col-sm-6 col-xs-12">
                                                                    <div class="form-group">
                                                                        <label for="uatSearch">Search Location</label>
                                                                        <input type="text" id="uatSearch" name="uat_search" class="form-control"
                                                                            placeholder="Search Municipality, Strategy, or Transport Location..."
                                                                            autocomplete="off"
                                                                            value="<?php echo isset($_POST['uat_search']) ? htmlspecialchars($_POST['uat_search']) : ''; ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2 col-sm-6 col-xs-12" style="padding-top: 25px;">
                                                                    <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-search"></i> Search</button>
                                                                    <button type="button" class="btn btn-default btn-sm" onclick="resetUatSearch()"><i class="fa fa-refresh"></i> Reset</button>
                                                                </div>
                                                            </div>
                                                        </form>

                                                        <div style="padding:10px; display: flex; justify-content: flex-start;">
                                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
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
                                                                <label style="margin:0; font-weight:normal;"> records per page</label>
                                                                <?php if ($isAdminUat) { ?>
                                                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#addUatModal" title="Create New UAT Record"><i class="fa fa-file-signature"></i> Create UAT</button>
                                                                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelectedUat" onclick="deleteSelectedUat()" disabled><i class="fa fa-trash"></i> Delete Selected (<span id="uatSelectedCount">0</span>)</button>
                                                                <?php } ?>
                                                            </div>
                                                        </div>

                                                        <div class="box-body table-responsive">
                                                            <table id="uatTable" class="table table-bordered table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 20px !important;"><input type="checkbox" id="cbxMainUat" class="cbxMainUat" onchange="checkMainUat(this)" title="Select All UAT Records" /></th>
                                                                        <th style="width: 20px !important;">No.</th>
                                                                        <th>Municipality</th>
                                                                        <th>Strategy</th>
                                                                        <th>Transport Location</th>
                                                                        <th>Coordinates</th>
                                                                        <th>Equipment Items</th>
                                                                        <th style="width: 120px !important;">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $uatCounter = 1;
                                                                    if ($uatTableCheck) {
                                                                        $uatQuery = "SELECT u.*, COUNT(it.id) AS item_count
                                                                                     FROM uat u
                                                                                     LEFT JOIN uat_items it ON it.uat_id = u.id
                                                                                     WHERE 1=1";

                                                                        if (isset($_POST['uat_search']) && trim($_POST['uat_search']) != '') {
                                                                            $uatSearch = mysqli_real_escape_string($con, trim($_POST['uat_search']));
                                                                            $uatQuery .= " AND (u.municipality LIKE '%$uatSearch%'
                                                                                      OR u.strategy LIKE '%$uatSearch%'
                                                                                      OR u.transport_location LIKE '%$uatSearch%')";
                                                                        }

                                                                        $uatQuery .= " GROUP BY u.id ORDER BY u.created_at DESC, u.id DESC";
                                                                        $uatResult = mysqli_query($con, $uatQuery);
                                                                        if ($uatResult) {
                                                                            while ($row = mysqli_fetch_assoc($uatResult)) {
                                                                                $uatId = intval($row['id']);
                                                                                $municipality = htmlspecialchars($row['municipality']);
                                                                                $strategy = htmlspecialchars($row['strategy']);
                                                                                $transportLocation = htmlspecialchars($row['transport_location']);
                                                                                $coords = uatCoordFmt($row['latitude'], $row['longitude']);
                                                                                $coordDisplay = $coords !== '' ? htmlspecialchars($coords) : '<span class="text-muted">—</span>';
                                                                    echo '
                                                                    <tr>
                                                                            <td><input type="checkbox" class="chk_delete_uat" name="uat_chk_delete[]" value="' . $uatId . '" onchange="updateUatDeleteBtn()" /></td>
                                                                            <td>' . $uatCounter++ . '</td>
                                                                            <td>' . $municipality . '</td>
                                                                            <td>' . $strategy . '</td>
                                                                            <td><a href="javascript:void(0);" class="uat-location-link" onclick="openUatDetail(' . $uatId . ', \'' . ($isAdminUat ? 'edit' : 'view') . '\')" title="Open equipment list">' . $transportLocation . '</a></td>
                                                                            <td>' . $coordDisplay . '</td>
                                                                            <td>' . intval($row['item_count']) . '</td>
                                                                            <td>
                                                                                <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                                                                                    <button type="button" class="btn btn-default btn-xs" onclick="openUatDetail(' . $uatId . ', \'view\')" title="View"><i class="fa fa-eye"></i></button>
                                                                                    ' . ($isAdminUat ? '<button type="button" class="btn btn-warning btn-xs" onclick="openUatDetail(' . $uatId . ', \'edit\')" title="Edit"><i class="fa fa-pencil"></i></button>' : '') . '
                                                                                    <button type="button" class="btn btn-info btn-xs" onclick="openPrintUat(' . $uatId . ')" title="Print"><i class="fa fa-print"></i></button>
                                                                                </div>
                                                                            </td>
                                                                        </tr>';
                                                                            }
                                                                        }
                                                                    } else {
                                                                        echo '<tr><td colspan="8" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> UAT table not found. Please run the database migration first.</td></tr>';
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <!-- ===== END TAB ===== -->
                                        </div>
                                    </div>

                                    <?php include "../edit_notif.php"; ?>

                                    <?php include "add_modal.php"; ?>
                                    <?php include "edit_modal.php"; ?>
                                    <?php include "function.php"; ?>

                                    <?php if (isset($_SESSION['new_uat'])) {
                                        unset($_SESSION['new_uat']);
                                    ?>
                                        <script type="text/javascript">
                                            $(document).ready(function() {
                                                showToast('UAT record created successfully.', 'success');
                                            });
                                        </script>
                                    <?php } ?>

                                    <!-- Print UAT Modal -->
                                    <div class="modal fade" id="printUatModal" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-sdm-xxl" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header" style="background: linear-gradient(135deg, #001f3f 0%, #1b3a6b 100%); color: #fff; border-radius: 0;">
                                                    <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                                                    <h4 class="modal-title"><i class="fa fa-print"></i> User Acceptance Form Preview</h4>
                                                </div>
                                                <div class="modal-body" style="padding: 0; height: 700px; background: #e9e9e9;">
                                                    <iframe id="printUatFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" onclick="printUatFrame()"><i class="fa fa-print"></i> Print UAT</button>
                                                </div>
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

        <?php include dirname(__DIR__) . '/scripts.php'; ?>

        <script>
            function showUatTable(limit) {
                var rows = document.querySelectorAll('#uatTable tbody tr');
                var lb = document.getElementById('perPageSelect');
                var pageLimit = parseInt(lb.value) || 10;
                for (var i = 0; i < rows.length; i++) {
                    rows[i].style.display = (i < pageLimit) ? '' : 'none';
                }
            }

            document.getElementById('perPageSelect').addEventListener('change', function() { showUatTable(); });
            showUatTable();

            function checkMainUat(mainChk) {
                var boxes = document.querySelectorAll('#uatTable .chk_delete_uat');
                for (var i = 0; i < boxes.length; i++) {
                    boxes[i].checked = mainChk.checked;
                }
                updateUatDeleteBtn();
            }

            function updateUatDeleteBtn() {
                var checked = document.querySelectorAll('#uatTable .chk_delete_uat:checked');
                var btn = document.getElementById('btnDeleteSelectedUat');
                if (btn) btn.disabled = checked.length === 0;
                var countSpan = document.getElementById('uatSelectedCount');
                if (countSpan) countSpan.textContent = checked.length;
            }

            function deleteSelectedUat() {
                var checked = document.querySelectorAll('#uatTable .chk_delete_uat:checked');
                if (checked.length === 0) {
                    showToast('Please select at least one UAT record to delete.', 'warning');
                    return;
                }

                if (!confirm('Are you sure you want to delete ' + checked.length + ' selected UAT record(s)? All linked equipment rows will also be removed.')) return;

                var ids = [];
                for (var i = 0; i < checked.length; i++) {
                    ids.push(checked[i].value);
                }

                $.post('function.php', {
                    action: 'delete_uat',
                    ids: ids
                }, function(resp) {
                    if (resp.success) {
                        showToast(resp.deleted + ' UAT record(s) deleted successfully.', 'success');
                        setTimeout(function() { location.reload(); }, 600);
                    } else {
                        showToast(resp.message || 'Failed to delete UAT records.', 'error');
                    }
                }, 'json');
            }

            function openPrintUat(uatId) {
                var frame = document.getElementById('printUatFrame');
                frame.src = 'print_uat.php?id=' + uatId;
                $('#printUatModal').modal('show');
            }

            function printUatFrame() {
                var frame = document.getElementById('printUatFrame');
                if (frame.contentWindow) {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                }
            }

            function resetUatSearch() {
                document.getElementById('uatSearch').value = '';
                document.getElementById('uatFilterForm').submit();
            }

            var updateDateTime = function() {
                var now = new Date();
                var options = {
                    year: 'numeric', month: 'long', day: 'numeric',
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                    hour12: true
                };
                document.getElementById('dateTime').innerText = now.toLocaleString('en-US', options);
            };
            setInterval(updateDateTime, 1000);
            updateDateTime();
        </script>

    </body>

</html>
<?php } ?>