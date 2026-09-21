<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['role']) || !isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../pages/connection.php';

$adminId = (int)$_SESSION['userid'];

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'list');

/* Re-notify durations (minutes): the alert reappears after the chosen time. */
define('NOTIF_DURATIONS', [15, 60, 360, 720, 1440]);

$durationMinutes = isset($_POST['duration_minutes']) ? (int)$_POST['duration_minutes'] : 1440;
if (!in_array($durationMinutes, NOTIF_DURATIONS, true)) $durationMinutes = 1440;

/*
 * Dismiss a single alert (or all current alerts) for this admin.
 * Writes to notification_dismissals so it is not re-notified until the chosen
 * duration has elapsed (re-notify duration per dismissal).
 */
if ($action === 'dismiss') {
    $sourceType = isset($_POST['source_type']) ? trim($_POST['source_type']) : '';
    $sourceId   = isset($_POST['source_id'])   ? (int)$_POST['source_id']        : 0;

    if (!in_array($sourceType, ['bill', 'letter'], true) || $sourceId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    $stmt = mysqli_prepare($con, "INSERT INTO notification_dismissals (admin_id, source_type, source_id, dismissed_at, re_notify_minutes)
                                  VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)
                                  ON DUPLICATE KEY UPDATE dismissed_at = CURRENT_TIMESTAMP, re_notify_minutes = ?");
    mysqli_stmt_bind_param($stmt, 'isiii', $adminId, $sourceType, $sourceId, $durationMinutes, $durationMinutes);
    mysqli_stmt_execute($stmt);
    $ok = mysqli_stmt_affected_rows($stmt) >= 0 || mysqli_errno($con) === 0;
    mysqli_stmt_close($stmt);

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'dismiss_all') {
    $combos = [];

    $bq = mysqli_query($con, "SELECT id FROM bills_monitoring WHERE status = 0 AND due_date IS NOT NULL AND due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
    if ($bq) {
        while ($br = mysqli_fetch_assoc($bq)) $combos[] = ['bill', (int)$br['id']];
    }

    $lq = mysqli_query($con, "SELECT id FROM letters_monitoring WHERE for_response = 'Y' AND date_responded IS NULL");
    if ($lq) {
        while ($lr = mysqli_fetch_assoc($lq)) $combos[] = ['letter', (int)$lr['id']];
    }

    $inserted = 0;
    foreach ($combos as $c) {
        $stmt = mysqli_prepare($con, "INSERT INTO notification_dismissals (admin_id, source_type, source_id, dismissed_at, re_notify_minutes)
                                      VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)
                                      ON DUPLICATE KEY UPDATE dismissed_at = CURRENT_TIMESTAMP, re_notify_minutes = ?");
        mysqli_stmt_bind_param($stmt, 'isiii', $adminId, $c[0], $c[1], $durationMinutes, $durationMinutes);
        mysqli_stmt_execute($stmt);
        $inserted += mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
    }

    echo json_encode(['success' => true, 'dismissed' => $inserted]);
    exit;
}

/*
 * Default: list undismissed alerts for this admin.
 */
$dismissed = [];
$dq = mysqli_query($con, "SELECT source_type, source_id, dismissed_at, re_notify_minutes FROM notification_dismissals WHERE admin_id = $adminId");
if ($dq) {
    $now = time();
    while ($dr = mysqli_fetch_assoc($dq)) {
        $reappearsAt = strtotime($dr['dismissed_at']) + ((int)$dr['re_notify_minutes'] * 60);
        if ($reappearsAt > $now) {
            $dismissed[$dr['source_type'] . ':' . $dr['source_id']] = true;
        }
    }
}

$items = [];
$today = strtotime(date('Y-m-d'));

$bq = mysqli_query($con, "SELECT id, type_of_billing, amount, due_date FROM bills_monitoring WHERE status = 0 AND due_date IS NOT NULL AND due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
if ($bq) {
    while ($br = mysqli_fetch_assoc($bq)) {
        $key = 'bill:' . $br['id'];
        if (isset($dismissed[$key])) continue;

        $dueTs     = strtotime($br['due_date']);
        $days      = (int)ceil(($dueTs - $today) / 86400);
        $overdue   = $days < 0;
        $type      = $br['type_of_billing'];
        $amountStr = $br['amount'] !== null && $br['amount'] !== '' ? ' ₱' . number_format((float)$br['amount'], 2) : '';
        $dueFmt    = date('M j, Y', $dueTs);

        if ($overdue) {
            $label = $type . $amountStr . ' — overdue since ' . $dueFmt;
        } elseif ($days === 0) {
            $label = $type . $amountStr . ' — due today';
        } elseif ($days === 1) {
            $label = $type . $amountStr . ' — due tomorrow';
        } else {
            $label = $type . $amountStr . ' — due in ' . $days . ' days';
        }

        $items[] = [
            'source_type' => 'bill',
            'id'          => (int)$br['id'],
            'label'       => $label,
            'subject'     => $type,
            'amount'      => $br['amount'] !== null ? (float)$br['amount'] : null,
            'due_date'    => $br['due_date'],
            'days'        => $days,
            'overdue'     => $overdue,
            'link'        => 'pages/bills_monitoring/bills_monitoring.php'
        ];
    }
}

$lq = mysqli_query($con, "SELECT id, subject FROM letters_monitoring WHERE for_response = 'Y' AND date_responded IS NULL");
if ($lq) {
    while ($lr = mysqli_fetch_assoc($lq)) {
        $key = 'letter:' . $lr['id'];
        if (isset($dismissed[$key])) continue;

        $subject = $lr['subject'];

        $items[] = [
            'source_type' => 'letter',
            'id'          => (int)$lr['id'],
            'label'       => 'Letter needs response',
            'subject'     => $subject,
            'amount'      => null,
            'due_date'    => null,
            'days'        => null,
            'overdue'     => false,
            'link'        => 'pages/letters_monitoring/letters_monitoring.php'
        ];
    }
}

usort($items, function ($a, $b) {
    return ($a['overdue'] === $b['overdue'])
        ? (($a['due_date'] ?? '9999-12-31') <=> ($b['due_date'] ?? '9999-12-31'))
        : ($b['overdue'] ? 1 : -1);
});

echo json_encode(['count' => count($items), 'items' => $items], JSON_UNESCAPED_UNICODE);