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
    <title>Property Sticker - <?php echo htmlspecialchars($row['property']); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #e9e9e9;
            padding: 20px;
        }
        .sticker {
            width: 5in;
            height: 3in;
            border: 1.5px solid #111;
            padding: 8px 10px;
            overflow: hidden;
            background: #fff;
            font-size: 9pt;
            line-height: 1.4;
            position: relative;
        }
        .sticker::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('../../img/logofinal.png') center/contain no-repeat;
            opacity: 0.12;
            z-index: 0;
            pointer-events: none;
        }
        .sticker-header {
            text-align: center;
            font-weight: 700;
            font-size: 11pt;
            border-bottom: 1.5px solid #111;
            padding-bottom: 3px;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }
        .sticker-row {
            display: flex;
            margin-bottom: 3px;
            position: relative;
            z-index: 1;
        }
        .sticker-label {
            font-weight: 700;
            width: 45%;
            flex-shrink: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sticker-value {
            width: 55%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sticker-row-line {
            border-bottom: 0.5px solid #ccc;
        }
        .sign-section {
            border-top: 1px solid #111;
            margin-top: 5px;
            padding-top: 4px;
            position: relative;
            z-index: 1;
        }
        .sign-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 7pt;
        }
        .sign-label {
            font-weight: 700;
            width: 20%;
            flex-shrink: 0;
        }
        .sign-value {
            width: 80%;
            border-bottom: 0.5px solid #999;
        }
        @media print {
            body { background: #fff; padding: 0; margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sticker { box-shadow: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="sticker">
    <div class="sticker-header">PROPERTY RECORD</div>

    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Property No:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['property']); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Classification:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['classification']); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Description/Model:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['description']); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Serial No:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['serial']); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Unit Cost:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['cost']); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Date Acquired:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['date']); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Location:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['location'] ?? ''); ?></span>
    </div>
    <div class="sticker-row sticker-row-line">
        <span class="sticker-label">Accountable Officer:</span>
        <span class="sticker-value"><?php echo htmlspecialchars($row['officer']); ?></span>
    </div>

    <div class="sign-section">
        <div class="sign-row">
            <span class="sign-label">DATE:</span>
            <span class="sign-value">&nbsp;</span>
        </div>
        <div class="sign-row">
            <span class="sign-label">DATE:</span>
            <span class="sign-value">&nbsp;</span>
        </div>
        <div class="sign-row">
            <span class="sign-label">DATE:</span>
            <span class="sign-value">&nbsp;</span>
        </div>
    </div>
</div>

<div class="no-print" style="text-align:center; margin-top:12px;">
    <button onclick="window.print();" style="padding:6px 20px; font-size:13px; cursor:pointer;">Print Sticker</button>
</div>
</body>
</html>
