<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php

include "../connection.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid ICS ID.");
}

$id = mysqli_real_escape_string($con, $_GET['id']);

$headerQuery = mysqli_query($con, "SELECT * FROM ics WHERE id = '$id'");
if (!$headerQuery || mysqli_num_rows($headerQuery) == 0) {
    die("Inventory Custodian Slip not found.");
}
$header = mysqli_fetch_assoc($headerQuery);

$itemQuery = mysqli_query($con, "SELECT * FROM ics_items WHERE ics_id = '$id' ORDER BY id ASC");
if (!$itemQuery || mysqli_num_rows($itemQuery) == 0) {
    die("Inventory Custodian Slip has no items.");
}

$allItems = array();
while ($row = mysqli_fetch_assoc($itemQuery)) {
    $allItems[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Custodian Slip - <?php echo htmlspecialchars($header['ics_no']); ?></title>
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
            max-width: 1250px;
            margin: 0 auto;
            background: var(--paper);
            padding: 48px 48px 60px;
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
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 34px 0 8px;
        }

        .form-subtitle {
            text-align: center;
            font-size: 15px;
            color: #555;
            margin-bottom: 30px;
        }

        .slip-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 15px;
        }

        .slip-info strong {
            font-weight: 700;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 34px;
        }

        table.items th,
        table.items td {
            border: 1.5px solid var(--line);
            padding: 10px 12px;
            font-size: 14px;
            vertical-align: top;
        }

        table.items th {
            text-align: center;
            font-weight: 700;
            background: #fff;
            font-size: 13px;
        }

        table.items td.desc {
            line-height: 1.9;
            white-space: pre-line;
        }

        table.items td.center,
        table.items th.center {
            text-align: center;
        }

        table.items td.right,
        table.items th.right {
            text-align: right;
        }

        table.items tr.total-row td {
            font-weight: 700;
        }

        table.items tr.item-stretch {
            height: 100%;
        }

        table.items tr.sig-footer-row td {
            font-weight: 400;
            border: none;
            padding: 12px 0 0;
        }

        table.items tr.sig-footer-row td:first-child {
            border-left: 1.5px solid var(--line);
        }

        table.items tr.sig-footer-row td:last-child {
            border-right: 1.5px solid var(--line);
        }

        table.items tr.sig-footer-row:last-of-type td {
            border-bottom: 1.5px solid var(--line);
        }

        .sig-cell {
            text-align: left;
            vertical-align: top;
        }

        .sig-label {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 22px;
        }

        .sig-name {
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .sig-position {
            font-size: 14px;
            color: #333;
            margin-bottom: 2px;
        }

        .sig-line {
            border-bottom: 1px solid var(--line);
            height: 1px;
            width: 70%;
            margin-bottom: 6px;
        }

        .sig-caption {
            font-size: 12px;
            color: #333;
        }

        .sig-date {
            display: inline;
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            @page {
                margin: 12mm;
            }
            .sheet {
                box-shadow: none;
                padding: 20px 40px;
                max-width: none;
                min-height: calc(100vh - 24mm);
                display: flex;
                flex-direction: column;
            }
            table.items {
                flex: 1;
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

    <div class="form-title">INVENTORY CUSTODIAN SLIP</div>
    <div class="form-subtitle">This is to certify that the below items have been received and are subject to the custody and accountability of the designated custodian.</div>

    <div class="slip-info">
        <span>ICS No.: <strong><?php echo htmlspecialchars($header['ics_no']); ?></strong></span>
        <span>Date Issued: <strong><?php echo date('F d, Y', strtotime($header['date_issued'])); ?></strong></span>
    </div>

    <table class="items">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 6%;">
            <col style="width: 8%;">
            <col style="width: 24%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 11%;">
            <col style="width: 12%;">
            <col style="width: 11%;">
        </colgroup>
        <thead>
            <tr>
                <th class="center">NO.</th>
                <th class="center">QTY.</th>
                <th class="center">UNIT</th>
                <th>DESCRIPTION</th>
                <th class="right">UNIT COST</th>
                <th class="right">TOTAL COST</th>
                <th>DATE&nbsp;ACQUIRED</th>
                <th>ITEM&nbsp;NO.</th>
                <th>EST.&nbsp;USEFUL&nbsp;LIFE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $itemNum = 1;
            $grandTotal = 0.00;
            $totalItems = count($allItems);
            $itemIndex = 0;
            foreach ($allItems as $item):
                $lineTotal = floatval($item['qty']) * floatval($item['unit_cost']);
                $grandTotal += $lineTotal;
                $isLastItem = (++$itemIndex === $totalItems);
            ?>
            <tr class="<?php echo $isLastItem ? 'item-stretch' : ''; ?>">
                <td class="center"><?php echo $itemNum++; ?></td>
                <td class="center"><?php echo htmlspecialchars($item['qty']); ?></td>
                <td class="center"><?php echo htmlspecialchars($item['unit']); ?></td>
                <td class="desc"><?php echo htmlspecialchars($item['description']); ?></td>
                <td class="right"><?php echo number_format(floatval($item['unit_cost']), 2); ?></td>
                <td class="right"><?php echo number_format($lineTotal, 2); ?></td>
                <td class="center"><?php echo $item['date_acquired'] ? date('m/d/Y', strtotime($item['date_acquired'])) : ''; ?></td>
                <td><?php echo htmlspecialchars($item['inventory_item_no']); ?></td>
                <td><?php echo htmlspecialchars($item['estimated_useful_life']); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="5" class="right">TOTAL</td>
                <td class="right" colspan="4" style="text-align: left;">&#8369; <?php echo number_format($grandTotal, 2); ?></td>
              
            </tr>
            <tr class="sig-footer-row">
                <td colspan="5" class="sig-cell" style="padding: 20px">
                    <div class="sig-label">Received By:</div>
                    <div class="sig-name"><?php echo htmlspecialchars($header['received_by'] ?: '____________________'); ?></div>
                    <div class="sig-position"><?php echo htmlspecialchars($header['received_by_position'] ?? ''); ?></div>
                    <div class="sig-line"></div>
                    <div class="sig-caption">Signature Over Printed Name</div>
                </td>
                <td colspan="4" class="sig-cell" style="padding: 20px">
                    <div class="sig-label">Received From:</div>
                    <div class="sig-name"><?php echo htmlspecialchars($header['received_from'] ?: '____________________'); ?></div>
                    <div class="sig-position"><?php echo htmlspecialchars($header['received_from_position'] ?? ''); ?></div>
                    <div class="sig-line"></div>
                    <div class="sig-caption">Signature Over Printed Name</div>
                </td>
            </tr>
            <tr class="sig-footer-row">
                <td colspan="9" class="sig-cell" style="padding: 20px">
                    <div class="sig-label">Date Received:&nbsp;&nbsp;
                        <?php if (!empty($header['date_received'])): ?>
                            <span class="sig-date"><?php echo date('F d, Y', strtotime($header['date_received'])); ?></span>
                        <?php else: ?>
                            <span class="sig-date">______________________</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    </div>

</body>
</html>