<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php

include "../connection.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Pass Slip ID.");
}

$id = mysqli_real_escape_string($con, $_GET['id']);

$psQuery = mysqli_query($con, "SELECT pass_slip_no FROM pass_slip WHERE id = '$id'");
if (!$psQuery || mysqli_num_rows($psQuery) == 0) {
    die("Pass Slip not found.");
}
$psData = mysqli_fetch_assoc($psQuery);
$pass_slip_no = mysqli_real_escape_string($con, $psData['pass_slip_no']);

$query = "SELECT ps.*, i.description AS item_desc, i.serial AS serial_no_inv
          FROM pass_slip ps 
          LEFT JOIN inventory i ON ps.inventory_id = i.id 
          WHERE ps.pass_slip_no = '$pass_slip_no'
          ORDER BY ps.id ASC";
$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Pass Slip not found.");
}

$row = mysqli_fetch_assoc($result);
$allItems = array();
$allItems[] = $row;
while ($nextRow = mysqli_fetch_assoc($result)) {
    $allItems[] = $nextRow;
}

require_once __DIR__ . '/serial_matcher.php';
$uatLocations = load_uat_transport_locations($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pass Slip - <?php echo $row['pass_slip_no']; ?></title>
    <style>
        :root {
            --navy: #1b3a6b;
            --line: #111111;
            --paper: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Georgia", "Times New Roman", serif;
            color: #111;
            background: #e9e9e9;
            padding: 40px 24px;
        }

        .sheet {
            max-width: 900px;
            margin: 0 auto;
            background: var(--paper);
            padding: 48px 56px 60px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-bottom: 8px;
        }

        .logo {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .header-text {
            text-align: left;
        }

        .republic {
            font-size: 15px;
            letter-spacing: 1px;
            color: var(--navy);
            font-weight: 700;
            border-bottom: 2px solid var(--navy);
            padding-bottom: 4px;
            margin-bottom: 4px;
            display: inline-block;
        }

        .dept {
            font-size: 15px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.25;
            text-transform: uppercase;
        }

        .form-title {
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 34px 0;
        }

        .slip-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .slip-info strong {
            font-weight: 700;
        }

        table.items {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.items th,
        table.items td {
            border: 1.5px solid var(--line);
            padding: 6px 8px;
            font-size: 12px;
            vertical-align: top;
            overflow-wrap: normal;
            word-break: normal;
        }

        table.items th {
            text-align: center;
            font-weight: 700;
            background: #fff;
            font-size: 12px;
        }

        table.items td.desc {
            line-height: 1.4;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        table.items td.serial {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .uat-location-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            line-height: 1.4;
            color: #fff;
            background: #28a745;
            border-radius: 3px;
            vertical-align: middle;
        }

        table.items tr.item-stretch {
            height: 100%;
        }

        table.items tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        table.items tbody.merge-group {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        table.items td.center,
        table.items th.center {
            text-align: center;
        }

        .purpose {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 44px;
        }

        .purpose strong {
            font-weight: 700;
        }

        .purpose-text {
            display: inline;
        }

        .sig-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            column-gap: 24px;
            margin-bottom: 46px;
        }

        .sig-block {
            text-align: left;
        }

        .sig-label {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 40px;
        }

        .sig-name {
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .sig-line {
            border-bottom: 1px solid var(--line);
            height: 1px;
            margin-bottom: 6px;
        }

        .sig-caption {
            font-size: 13px;
            color: #333;
        }

        .divider {
            border: none;
            border-top: 1.5px dashed var(--line);
            margin: 30px 0;
        }

        .section-label {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #f0f0f0;
            border: 1px solid #ccc;
        }

        .condition-group {
            margin-bottom: 20px;
        }

        .condition-box {
            display: inline-block;
            padding: 4px 14px;
            border: 1.5px solid var(--line);
            margin: 0 5px;
            font-size: 14px;
        }

        .condition-selected {
            background-color: var(--line);
            color: #fff;
        }

        .remarks-box {
            border: 1px solid var(--line);
            padding: 12px 14px;
            min-height: 40px;
            margin-bottom: 20px;
            font-size: 15px;
            line-height: 1.6;
        }

        .not-returned {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
            font-size: 15px;
        }

        .pulled-dates,
        .returned-dates {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-family: "Segoe Script", "Bradley Hand", cursive;
            font-size: 17px;
            min-height: 40px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            @page {
                margin: 8mm;
            }
            .sheet {
                box-shadow: none;
                padding: 16px 24px;
                max-width: none;
                min-height: calc(100vh - 24mm);
                display: flex;
                flex-direction: column;
            }
            table.items {
                flex: 1;
                font-size: 11px;
            }
            table.items th,
            table.items td {
                padding: 4px 6px;
            }
        }
    </style>
</head>
<body>

<div class="sheet">

    <div class="header">
        <img class="logo" src="../../img/logofinal.png" alt="DICT Logo">
        <div class="header-text">
            <div class="republic">REPUBLIC OF THE PHILIPPINES</div>
            <div class="dept">Department of Information and<br>Communications Technology</div>
        </div>
    </div>

    <div class="form-title">OFFICE EQUIPMENT PASS SLIP</div>

    <div class="slip-info">
        <span>Pass Slip No.: <strong><?php echo htmlspecialchars($row['pass_slip_no']); ?></strong></span>
        <span>Date: <strong><?php echo date('F d, Y', strtotime($row['pullout_date'])); ?></strong></span>
    </div>

    <table class="items">
        <colgroup>
            <col style="width: 6%;">
            <col style="width: 19%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 16%;">
            <col style="width: 14%;">
            <col style="width: 14%;">
            <col style="width: 15%;">
        </colgroup>
        <thead>
            <tr>
                <th class="center">NO.</th>
                <th>ITEM DESCRIPTION</th>
                <th class="center">QTY.</th>
                <th class="center">UNIT</th>
                <th>SERIAL NO.</th>
                <th>PULLED&#8209;OUT DATE</th>
                <th>RETURNED DATE</th>
                <th>REMARKS</th>
            </tr>
        </thead>
        <?php
            $itemNum = 1;
            $totalItems = count($allItems);

            $descriptions = array();
            $units = array();
            $qtys = array();
            $serials = array();
            $pulloutText = array();
            $returnText = array();
            $remarkText = array();

            for ($pi = 0; $pi < $totalItems; $pi++) {
                $pit = $allItems[$pi];
                $descriptions[$pi] = (string)($pit['item_description'] ?? '');
                $units[$pi] = (string)($pit['unit'] ?? '');
                $qtys[$pi] = $pit['qty'];
                $psn = (string)($pit['serial_no'] ?? '');
                $serials[$pi] = $psn;
                $pulloutText[$pi] = date('F d, Y', strtotime($pit['pullout_date']));
                $returnText[$pi] = $pit['return_date'] ? date('F d, Y', strtotime($pit['return_date'])) : '';
                $premark = '';
                if (trim($psn) !== '') {
                    $pk = normalize_serial($psn);
                    if (isset($uatLocations[$pk])) {
                        $premark = $uatLocations[$pk];
                    }
                }
                $remarkText[$pi] = $premark;
            }

            $groupSpan = array();
            for ($gi = 0; $gi < $totalItems; ) {
                $gkey = trim($descriptions[$gi]) . "\0" . trim($units[$gi]);
                $gspan = 1;
                while ($gi + $gspan < $totalItems) {
                    $gnext = trim($descriptions[$gi + $gspan]) . "\0" . trim($units[$gi + $gspan]);
                    if ($gnext !== $gkey) break;
                    $gspan++;
                }
                $groupSpan[$gi] = $gspan;
                $gi += $gspan;
            }

            $descSkip = array();
            $qtySkip = array();
            $unitSkip = array();
            $qtyDisplay = array();
            $pulledSpan = array();
            $pulledSkip = array();
            $returnSpan = array();
            $returnSkip = array();
            $remarkSpan = array();
            $remarkSkip = array();

            $buildRuns = function ($start, $span, $values, &$spanArr, &$skipArr) {
                $ro = 0;
                while ($ro < $span) {
                    $rlen = 1;
                    while ($ro + $rlen < $span && $values[$start + $ro + $rlen] === $values[$start + $ro]) {
                        $rlen++;
                    }
                    if ($rlen > 1) {
                        $spanArr[$start + $ro] = $rlen;
                    }
                    for ($rc = 1; $rc < $rlen; $rc++) {
                        $skipArr[$start + $ro + $rc] = true;
                    }
                    $ro += $rlen;
                }
            };

            foreach ($groupSpan as $gstart => $gspan) {
                for ($go = 1; $go < $gspan; $go++) {
                    $descSkip[$gstart + $go] = true;
                    $qtySkip[$gstart + $go] = true;
                    $unitSkip[$gstart + $go] = true;
                }
                $qtyDisplay[$gstart] = $gspan > 1 ? $gspan : $qtys[$gstart];
                $buildRuns($gstart, $gspan, $pulloutText, $pulledSpan, $pulledSkip);
                $buildRuns($gstart, $gspan, $returnText, $returnSpan, $returnSkip);
                $buildRuns($gstart, $gspan, $remarkText, $remarkSpan, $remarkSkip);
            }

            $itemIndex = 0;
            $openedTbody = false;
            foreach ($allItems as $item):
                $idx = $itemIndex++;
                $isLastItem = ($itemIndex === $totalItems);
                $sn = $serials[$idx];
                $isGroupStart = isset($groupSpan[$idx]);
                $rowspanAttr = ($isGroupStart && $groupSpan[$idx] > 1) ? ' rowspan="' . $groupSpan[$idx] . '"' : '';
                if ($isGroupStart) {
                    if ($openedTbody) {
                        echo '</tbody>';
                    }
                    echo '<tbody class="merge-group">';
                    $openedTbody = true;
                }
            ?>
            <tr class="<?php echo $isLastItem ? 'item-stretch' : ''; ?>">
                <td class="center"><?php echo $itemNum++; ?></td>
                <?php if (empty($descSkip[$idx])): ?>
                <td class="desc"<?php echo $rowspanAttr; ?>><?php echo htmlspecialchars($descriptions[$idx]); ?></td>
                <?php endif; ?>
                <?php if (empty($qtySkip[$idx])): ?>
                <td class="center"<?php echo $rowspanAttr; ?>><?php echo htmlspecialchars((string)$qtyDisplay[$idx]); ?></td>
                <?php endif; ?>
                <?php if (empty($unitSkip[$idx])): ?>
                <td class="center"<?php echo $rowspanAttr; ?>><?php echo htmlspecialchars($units[$idx]); ?></td>
                <?php endif; ?>
                <td class="serial"><?php echo htmlspecialchars($sn); ?></td>
                <?php if (empty($pulledSkip[$idx])): ?>
                <td<?php echo isset($pulledSpan[$idx]) ? ' rowspan="' . $pulledSpan[$idx] . '"' : ''; ?>><?php echo $pulloutText[$idx]; ?></td>
                <?php endif; ?>
                <?php if (empty($returnSkip[$idx])): ?>
                <td<?php echo isset($returnSpan[$idx]) ? ' rowspan="' . $returnSpan[$idx] . '"' : ''; ?>><?php echo $returnText[$idx]; ?></td>
                <?php endif; ?>
                <?php if (empty($remarkSkip[$idx])): ?>
                <td<?php echo isset($remarkSpan[$idx]) ? ' rowspan="' . $remarkSpan[$idx] . '"' : ''; ?>><?php if ($remarkText[$idx] !== '') { echo '<span class="uat-location-badge">' . htmlspecialchars($remarkText[$idx]) . '</span>'; } ?></td>
                <?php endif; ?>
            </tr>
            <?php
                if ($idx + 1 >= $totalItems && $openedTbody) {
                    echo '</tbody>';
                    $openedTbody = false;
                }
            endforeach; ?>
    </table>

    <div class="purpose">
        <strong>PURPOSE:</strong> <span class="purpose-text"><?php echo htmlspecialchars($row['purpose']); ?></span>
    </div>

    <?php if (!empty(trim($row['remarks'] ?? ''))): ?>
    <div class="remarks-box">
        <strong>REMARKS:</strong> <span class="remarks-text"><?php echo htmlspecialchars($row['remarks']); ?></span>
    </div>
    <?php endif; ?>

    <div class="sig-group">
        <div class="sig-block">
            <div class="sig-label">Requested by:</div>
            <div class="sig-name"><?php echo htmlspecialchars($row['requested_by_out']); ?></div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Inspected by:</div>
            <div class="sig-name"><?php echo htmlspecialchars($row['inspected_by_out']); ?></div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Approved by:</div>
            <div class="sig-name"><?php echo htmlspecialchars($row['approved_by_out']); ?></div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
    </div>

    <?php if ($row['status'] === 'returned'): ?>
    <div class="sig-group">
        <div class="sig-block">
            <div class="sig-label">Requested by (Return):</div>
            <div class="sig-name"><?php echo htmlspecialchars($row['requested_by_return']); ?></div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Inspected by (Return):</div>
            <div class="sig-name"><?php echo htmlspecialchars($row['inspected_by_return']); ?></div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Approved by (Return):</div>
            <div class="sig-name"><?php echo htmlspecialchars($row['approved_by_return']); ?></div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
    </div>
    <?php else: ?>
    <div class="sig-group">
        <div class="sig-block">
            <div class="sig-label">Requested by (Return):</div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Inspected by (Return):</div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Approved by (Return):</div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-line"></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
