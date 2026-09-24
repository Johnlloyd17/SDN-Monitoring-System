<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php

include "../connection.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid UAT ID.");
}

$id = mysqli_real_escape_string($con, $_GET['id']);

$headerQuery = mysqli_query($con, "SELECT * FROM uat WHERE id = '$id'");
if (!$headerQuery || mysqli_num_rows($headerQuery) == 0) {
    die("UAT record not found.");
}
$header = mysqli_fetch_assoc($headerQuery);

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

$coordinates = uatCoordFmt($header['latitude'], $header['longitude']);

$itemQuery = mysqli_query($con, "SELECT * FROM uat_items WHERE uat_id = '$id' ORDER BY id ASC");
$allItems = array();
if ($itemQuery) {
    while ($row = mysqli_fetch_assoc($itemQuery)) {
        $allItems[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Acceptance Form (UAT) - <?php echo htmlspecialchars($header['transport_location']); ?></title>
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

        .print-header {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid var(--line);
            margin-bottom: 16px;
        }

        .print-header td {
            border: 1.5px solid var(--line);
            padding: 6px 10px;
            vertical-align: middle;
        }

        .print-header td.ph-left {
            width: 20%;
            text-align: left;
        }

        .print-header td.ph-center {
            width: 55%;
            text-align: center;
        }

        .print-header td.ph-right {
            width: 25%;
            text-align: right;
        }

        .ph-dict-logo {
            width: 130px;
            height: auto;
            max-height: 46px;
            vertical-align: middle;
        }

        .ph-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .ph-subtitle {
            font-size: 12px;
            color: #555;
            margin-top: 2px;
        }

        .ph-fwfa-logo {
            width: 90px;
            height: auto;
            vertical-align: middle;
        }

        .info-block {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .info-block td {
            border: 1.5px solid var(--line);
            padding: 10px 14px;
        }

        .info-block td.label {
            width: 190px;
            font-weight: 700;
            background: #f5f7fa;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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

        table.items td.center,
        table.items th.center { text-align: center; }

        table.items td.serial { line-height: 1.7; white-space: pre-line; }

        .empty-note {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 18px 0;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            @page { margin: 12mm; }
            .sheet {
                box-shadow: none;
                padding: 20px 40px;
                max-width: none;
                min-height: calc(100vh - 24mm);
            }
        }
    </style>
</head>
<body>

<div class="sheet">

    <table class="print-header">
        <tr>
            <td class="ph-left">
                <img class="ph-dict-logo" src="../../img/dict_logo.png" alt="DICT Logo">
            </td>
            <td class="ph-center">
                <div class="ph-title">USER ACCEPTANCE FORM (UAT) ATTACHMENTS</div>
                <div class="ph-subtitle">Free Wifi for All Province of Surigao del Norte</div>
            </td>
            <td class="ph-right">
                <img class="ph-fwfa-logo" src="../../img/freewifi_logo.png" alt="Free WiFi FOR ALL">
            </td>
        </tr>
    </table>

    <table class="info-block">
        <tr>
            <td class="label">Municipality</td>
            <td><?php echo htmlspecialchars($header['municipality']); ?></td>
            <td class="label">Strategy</td>
            <td><?php echo htmlspecialchars($header['strategy']); ?></td>
        </tr>
        <tr>
            <td class="label">Transport Location</td>
            <td><?php echo htmlspecialchars($header['transport_location']); ?></td>
            <td class="label">Coordinates</td>
            <td><?php echo htmlspecialchars($coordinates ?: ''); ?></td>
        </tr>
    </table>

    <table class="items">
        <colgroup>
            <col style="width: 6%;">
            <col style="width: 7%;">
            <col style="width: 10%;">
            <col style="width: 22%;">
            <col style="width: 27%;">
            <col style="width: 28%;">
        </colgroup>
        <thead>
            <tr>
                <th class="center">NO.</th>
                <th class="center">QTY</th>
                <th class="center">UNIT</th>
                <th>ITEM NAME</th>
                <th>DESCRIPTION / BRAND / MODEL</th>
                <th>SERIAL NUMBERS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($allItems) === 0): ?>
                <tr><td colspan="6" class="empty-note">No equipment listed yet.</td></tr>
            <?php else: ?>
                <?php
                $itemNum = 1;
                foreach ($allItems as $item):
                ?>
                <tr>
                    <td class="center"><?php echo $itemNum++; ?></td>
                    <td class="center"><?php echo htmlspecialchars($item['qty']); ?></td>
                    <td class="center"><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td class="serial"><?php echo htmlspecialchars($item['serial_numbers']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>