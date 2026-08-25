<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
    ?>
    <body class="skin-black">
    <!-- header logo: style can be found in header.less -->
    <?php include "../connection.php"; ?>

    <?php
    // ── All data queries (shared by metric cards, charts, and tables) ──
    $projects = ['PICS MUN', 'PICS-PP', 'PICS-SUC', 'RIS-WISPS', 'RIS-PICS MUN', 'CoRe-FW4A _ UNDP-VSAT', 'CoRe-FW4A _ Phase 4'];

    // Table 1: locality × strategy counts
    $strategyData = [];
    $strategyQuery = mysqli_query($con, "SELECT locality, strategy, COUNT(*) AS total FROM tblfwfa GROUP BY locality, strategy");
    while ($row = mysqli_fetch_assoc($strategyQuery)) {
        $strategyData[$row['locality']][$row['strategy']] = $row['total'];
    }

    $column_totals_1 = array_fill_keys($projects, 0);
    foreach ($strategyData as $locality => $strategies) {
        foreach ($projects as $project) {
            $column_totals_1[$project] += isset($strategies[$project]) ? $strategies[$project] : 0;
        }
    }

    // Table 2: type × strategy counts
    $typeData = [];
    $typeQuery = mysqli_query($con, "SELECT type, strategy, COUNT(*) AS total FROM tblfwfa GROUP BY type, strategy");
    while ($row = mysqli_fetch_assoc($typeQuery)) {
        $typeData[$row['type']][$row['strategy']] = $row['total'];
    }

    $column_totals_2 = array_fill_keys($projects, 0);
    foreach ($typeData as $type => $strategies) {
        foreach ($projects as $project) {
            $column_totals_2[$project] += isset($strategies[$project]) ? $strategies[$project] : 0;
        }
    }

    // Grand totals
    $grand_total = array_sum($column_totals_1);
    $grand_total_2 = array_sum($column_totals_2);
    ?>

    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <?php include('../sidebar-left.php'); ?>

     <!-- Right side column. Contains the navbar and content of the page -->
     <aside class="right-side">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="header-title">
                    <img src="icons/fwfa_logo.png" alt="Logo" class="header-logo" />
                    <div class="header-info">
                        <h3 style="color: darkblue; font-weight: bold;">FreeWifi4All</h3>
                        <p class="header-address">Strategy</p>
                    </div>
                    <div class="header-date-time" id="dateTime"></div> <!-- Date and Time Container -->
                </div>
            </section>
            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- STATISTICS — Metric Cards                                         -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">Statistics</div>
        <div class="panel-body">
            <div class="row">
                <?php
                $card_labels = [
                    'PICS MUN'                              => 'PICS MUN',
                    'PICS-PP'                                => 'PICS-PP',
                    'PICS-SUC'                               => 'PICS-SUC',
                    'RIS-WISPS'                              => 'RIS-WISPS',
                    'RIS-PICS MUN'                           => 'RIS-PICS MUN',
                    'CoRe-FW4A _ UNDP-VSAT'                 => 'UNDP-VSAT',
                    'CoRe-FW4A _ Phase 4'                   => 'Phase 4',
                ];
                $card_icons = [
                    'PICS MUN'          => 'fa-building',
                    'PICS-PP'            => 'fa-users',
                    'PICS-SUC'           => 'fa-graduation-cap',
                    'RIS-WISPS'          => 'fa-wifi',
                    'RIS-PICS MUN'       => 'fa-sitemap',
                    'CoRe-FW4A _ UNDP-VSAT' => 'fa-satellite-dish',
                    'CoRe-FW4A _ Phase 4'   => 'fa-signal',
                ];
                $bg_cycle = ['bg-blue', 'bg-red', 'bg-yellow', 'bg-blue', 'bg-red', 'bg-yellow', 'bg-blue'];
                $i = 0;
                foreach ($projects as $project):
                    $label = $card_labels[$project];
                    $icon  = $card_icons[$project];
                    $bg    = $bg_cycle[$i % count($bg_cycle)];
                    $val   = $column_totals_1[$project];
                ?>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon <?php echo $bg; ?>">
                            <i class="fa <?php echo $icon; ?>"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?php echo $label; ?></span>
                            <span class="info-box-number"><?php echo $val; ?></span>
                        </div>
                    </div>
                </div>
                <?php $i++; endforeach; ?>

                <!-- Grand Total card -->
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua">
                            <i class="fa fa-globe"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Grand Total</span>
                            <span class="info-box-number"><?php echo $grand_total; ?></span>
                        </div>
                    </div>
                </div>
            </div><!-- /.row -->
        </div><!-- /.panel-body -->
    </div><!-- /.panel -->
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- CHARTS                                                            -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="row">
        <!-- Donut Chart: Strategy Distribution -->
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">Strategy Distribution</div>
                <div class="panel-body">
                    <div id="chart-donut" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <!-- Bar Chart: By Site Type -->
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">Distribution by Site Type</div>
                <div class="panel-body">
                    <div id="chart-bar" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TABLE 1 — Internet Access Distribution (Municipality/City)        -->
<!-- ════════════════════════════════════════════════════════════════════ -->
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Internet Access Distribution by Municipality/City</div>
        <div class="panel-body">
        <table id="table1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Municipality/City</th>
                <th class="text-center">PICS MUN</th>
                <th class="text-center">PICS-PP</th>
                <th class="text-center">PICS-SUC</th>
                <th class="text-center">RIS-WISPS</th>
                <th class="text-center">RIS-PICS MUN</th>
                <th class="text-center">CoRe-FW4A _ UNDP-VSAT</th>
                <th class="text-center">CoRe-FW4A _ Phase 4</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $municipalitiesQuery = mysqli_query($con, "SELECT locality FROM tbllocality ORDER BY locality");
            while ($row = mysqli_fetch_assoc($municipalitiesQuery)) {
                $locality = $row['locality'];
                $row_total = 0;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($locality) . '</td>';
                foreach ($projects as $project) {
                    $count = isset($strategyData[$locality][$project]) ? $strategyData[$locality][$project] : 0;
                    echo '<td class="text-center">' . $count . '</td>';
                    $row_total += $count;
                }
                echo '<td class="text-center">' . $row_total . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-left">Total</th>
                <?php
                foreach ($projects as $project) {
                    echo '<th class="text-center">' . $column_totals_1[$project] . '</th>';
                }
                ?>
                <th class="text-center"></th>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ════════════════════════════════════════════════════════════════════ -->
<!-- TABLE 2 — Internet Access Distribution (Site Type)                -->
<!-- ════════════════════════════════════════════════════════════════════ -->
<div class="panel-body">
    <table id="table2" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Site Type</th>
                <th class="text-center">PICS MUN</th>
                <th class="text-center">PICS-PP</th>
                <th class="text-center">PICS-SUC</th>
                <th class="text-center">RIS-WISPS</th>
                <th class="text-center">RIS-PICS MUN</th>
                <th class="text-center">CoRe-FW4A _ UNDP-VSAT</th>
                <th class="text-center">CoRe-FW4A _ Phase 4</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $typesQuery = mysqli_query($con, "SELECT type FROM tbltype ORDER BY type");
            while ($row = mysqli_fetch_assoc($typesQuery)) {
                $type = $row['type'];
                $row_total = 0;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($type) . '</td>';
                foreach ($projects as $project) {
                    $count = isset($typeData[$type][$project]) ? $typeData[$type][$project] : 0;
                    echo '<td class="text-center">' . $count . '</td>';
                    $row_total += $count;
                }
                echo '<td class="text-center">' . $row_total . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-left">Total</th>
                <?php
                foreach ($projects as $project) {
                    echo '<th class="text-center">' . $column_totals_2[$project] . '</th>';
                }
                ?>
                <th class="text-center"></th>
            </tr>
        </tfoot>
    </table>
</div>
</div>

<!-- DataTables + Charts Initialization -->
<script>
    $(document).ready(function() {
        $('#table1').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "lengthChange": true,
            "info": true,
            "autoWidth": false
        });

        $('#table2').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "lengthChange": true,
            "info": true,
            "autoWidth": false
        });

        // ── Morris Donut: Strategy Distribution ──
        Morris.Donut({
            element: 'chart-donut',
            data: [
                <?php
                $donut_colors = ['#3c8dbc', '#dd4b39', '#f39c12', '#00a65a', '#605ca8', '#00c0ef', '#d2d6de'];
                foreach ($projects as $idx => $project) {
                    $short = $card_labels[$project];
                    $val   = $column_totals_1[$project];
                    $color = $donut_colors[$idx % count($donut_colors)];
                    echo "{ label: '" . addslashes($short) . "', value: $val }" . ($idx < count($projects) - 1 ? ',' : '');
                }
                ?>
            ],
            colors: ['#3c8dbc', '#dd4b39', '#f39c12', '#00a65a', '#605ca8', '#00c0ef', '#d2d6de'],
            resize: true,
            maintainAspectRatio: false
        });

        // ── Morris Bar: Distribution by Site Type ──
        var barData = [
            <?php
            $typesQuery2 = mysqli_query($con, "SELECT type FROM tbltype ORDER BY type");
            $first = true;
            while ($row = mysqli_fetch_assoc($typesQuery2)) {
                $t = $row['type'];
                $vals = [];
                foreach ($projects as $p) {
                    $vals[] = isset($typeData[$t][$p]) ? $typeData[$t][$p] : 0;
                }
                $sum = array_sum($vals);
                if (!$first) echo ',';
                $first = false;
                echo "{ type: '" . addslashes($t) . "', total: $sum }";
            }
            ?>
        ];

        Morris.Bar({
            element: 'chart-bar',
            data: barData,
            xkey: 'type',
            ykeys: ['total'],
            labels: ['Total Access Points'],
            barColors: ['#3c8dbc'],
            resize: true,
            maintainAspectRatio: false
        });
    });

    function updateDateTime() {
        var now = new Date();
        var options = {
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
    setInterval(updateDateTime, 1000);
    updateDateTime();
</script>

                                    <?php include "../deleteModal.php"; ?>

                                    </form>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->

                            <?php include "../edit_notif.php"; ?>

                            <?php include "../added_notif.php"; ?>

                            <?php include "../delete_notif.php"; ?>

                            <?php include "../duplicate_error.php"; ?>

            <?php include "add_modal.php"; ?>

            <?php include "function.php"; ?>


                    </div>   <!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
        <?php }
        include "../footer.php"; ?>

<style>
        table {
            table-layout: auto;
            width: 100%;
        }
        table th {
            white-space: nowrap;
            text-align: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 200px;
        }
        table td {
            white-space: nowrap;
        }
        table th, table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table th:nth-child(1) { width: 30px; }
        table th:nth-child(2) { width: 50px; }
        .header-title {
            display: flex;
            align-items: center;
        }
        .header-logo {
            height: 55px;
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
        /* Metric card icon overrides — match dashboard.php pattern */
        .info-box-icon {
            background-color: white;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
            border-radius: 5px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            width: 80px;
            font-size: 40px;
        }
    </style>
    </body>
</html>
