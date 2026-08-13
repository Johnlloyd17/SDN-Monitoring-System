<?php
session_start();
include "../connection.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Record ID.");
}

$id = intval($_GET['id']);
$stmt = mysqli_prepare($con, "SELECT * FROM inventory WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Record - <?php echo htmlspecialchars($row['property']); ?></title>
    <style>
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
            background: #fff;
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
        .logo { width: 74px; height: 74px; border-radius: 50%; flex-shrink: 0; }
        .header-text { text-align: left; }
        .republic {
            font-size: 15px; letter-spacing: 1px; color: #1b3a6b;
            font-weight: 700; border-bottom: 2px solid #1b3a6b;
            padding-bottom: 4px; margin-bottom: 4px; display: inline-block;
        }
        .dept {
            font-size: 15px; font-weight: 700; color: #1b3a6b;
            line-height: 1.25; text-transform: uppercase;
        }
        .form-title {
            text-align: center; font-size: 26px; font-weight: 700;
            letter-spacing: 1px; margin: 34px 0;
        }
        table.detail {
            width: 100%; border-collapse: collapse; margin-bottom: 30px;
        }
        table.detail th, table.detail td {
            border: 1.5px solid #111; padding: 12px 14px; font-size: 15px;
            vertical-align: top;
        }
        table.detail th {
            text-align: left; font-weight: 700; background: #f5f5f5;
            width: 35%; font-size: 14px;
        }
        table.detail td { font-size: 15px; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { box-shadow: none; padding: 20px 40px; max-width: none; }
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

    <div class="form-title">INVENTORY PROPERTY RECORD</div>

    <table class="detail">
        <tr><th>Project</th><td><?php echo htmlspecialchars($row['project']); ?></td></tr>
        <tr><th>Item No.</th><td><?php echo htmlspecialchars($row['item']); ?></td></tr>
        <tr><th>Classification</th><td><?php echo htmlspecialchars($row['classification']); ?></td></tr>
        <tr><th>Quantity</th><td><?php echo htmlspecialchars($row['quantity']); ?></td></tr>
        <tr><th>Unit</th><td><?php echo htmlspecialchars($row['unit']); ?></td></tr>
        <tr><th>Description / Model</th><td><?php echo htmlspecialchars($row['description']); ?></td></tr>
        <tr><th>Received From</th><td><?php echo htmlspecialchars($row['received']); ?></td></tr>
        <tr><th>Property Number</th><td><?php echo htmlspecialchars($row['property']); ?></td></tr>
        <tr><th>ICS / PAR Number</th><td><?php echo htmlspecialchars($row['ics']); ?></td></tr>
        <tr><th>Serial Number</th><td><?php echo htmlspecialchars($row['serial']); ?></td></tr>
        <tr><th>Date Acquired</th><td><?php echo htmlspecialchars($row['date']); ?></td></tr>
        <tr><th>Accountable Officer</th><td><?php echo htmlspecialchars($row['officer']); ?></td></tr>
        <tr><th>Unit Cost</th><td><?php echo htmlspecialchars($row['cost']); ?></td></tr>
        <tr><th>Estimated Useful Life</th><td><?php echo htmlspecialchars($row['life']); ?></td></tr>
        <tr><th>Received / Transferred</th><td><?php echo htmlspecialchars($row['transferred']); ?></td></tr>
        <tr><th>Remarks</th><td><?php echo htmlspecialchars($row['remarks']); ?></td></tr>
    </table>

    <div style="text-align:center; margin-top:30px;">
        <button onclick="window.print();" style="padding:8px 24px; font-size:15px; cursor:pointer;">Print</button>
    </div>
</div>
</body>
</html>
