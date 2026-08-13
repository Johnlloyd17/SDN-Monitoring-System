<?php
session_start();
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

$query = "SELECT ps.*, i.description AS item_desc, i.property AS property_no, i.serial AS serial_no, i.ics AS ics_no 
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
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.items th,
        table.items td {
            border: 1.5px solid var(--line);
            padding: 12px 14px;
            font-size: 16px;
            vertical-align: top;
        }

        table.items th {
            text-align: center;
            font-weight: 700;
            background: #fff;
            font-size: 14px;
        }

        table.items td.desc {
            line-height: 1.9;
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

        .sig-line {
            border-bottom: 1px solid var(--line);
            height: 1px;
            margin-bottom: 6px;
        }

        .sig-name {
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            margin-bottom: 2px;
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
            .sheet {
                box-shadow: none;
                padding: 20px 40px;
                max-width: none;
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
            <col style="width: 5%;">
            <col style="width: 40%;">
            <col style="width: 8%;">
            <col style="width: 12%;">
            <col style="width: 17%;">
            <col style="width: 18%;">
        </colgroup>
        <thead>
            <tr>
                <th class="center">NO.</th>
                <th>ITEM DESCRIPTION</th>
                <th class="center">QTY.</th>
                <th class="center">UNIT</th>
                <th>PULLED&#8209;OUT DATE</th>
                <th>RETURNED DATE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $itemNum = 1;
            foreach ($allItems as $item):
            ?>
            <tr>
                <td class="center"><?php echo $itemNum++; ?></td>
                <td class="desc"><?php echo htmlspecialchars($item['item_description']); ?></td>
                <td class="center"><?php echo $item['qty']; ?></td>
                <td class="center"><?php echo $item['unit']; ?></td>
                <td><?php echo date('F d, Y', strtotime($item['pullout_date'])); ?></td>
                <td><?php echo $row['return_date'] ? date('F d, Y', strtotime($row['return_date'])) : ''; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="purpose">
        <strong>PURPOSE:</strong> <span class="purpose-text"><?php echo htmlspecialchars($row['purpose']); ?></span>
    </div>

    <div class="sig-group">
        <div class="sig-block">
            <div class="sig-label">Requested by:</div>
            <div class="sig-line"></div>
            <div class="sig-name"><?php echo htmlspecialchars($row['requested_by_out']); ?></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Inspected by:</div>
            <div class="sig-line"></div>
            <div class="sig-name"><?php echo htmlspecialchars($row['inspected_by_out']); ?></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Approved by:</div>
            <div class="sig-line"></div>
            <div class="sig-name"><?php echo htmlspecialchars($row['approved_by_out']); ?></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
    </div>

    <?php if ($row['status'] === 'returned'): ?>
    <div class="sig-group">
        <div class="sig-block">
            <div class="sig-label">Requested by (Return):</div>
            <div class="sig-line"></div>
            <div class="sig-name"><?php echo htmlspecialchars($row['requested_by_return']); ?></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Inspected by (Return):</div>
            <div class="sig-line"></div>
            <div class="sig-name"><?php echo htmlspecialchars($row['inspected_by_return']); ?></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Approved by (Return):</div>
            <div class="sig-line"></div>
            <div class="sig-name"><?php echo htmlspecialchars($row['approved_by_return']); ?></div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
    </div>
    <?php else: ?>
    <div class="sig-group">
        <div class="sig-block">
            <div class="sig-label">Requested by (Return):</div>
            <div class="sig-line"></div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Inspected by (Return):</div>
            <div class="sig-line"></div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">Approved by (Return):</div>
            <div class="sig-line"></div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-caption">Signature Over Printed Name</div>
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
