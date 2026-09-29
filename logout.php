<?php
/**
 * Explicit logout, and the server-side teardown used by the client-side
 * inactivity timer (js/session-timeout.js posts here with ?ajax=1 so an idle
 * tab destroys its session server-side too, not just visually).
 *
 * sdn_session_destroy() clears the session data, expires the browser cookie
 * and removes the session file, so an expired session is indistinguishable
 * from a deliberately logged-out one.
 */
require_once __DIR__ . '/pages/session_config.php';

sdn_session_boot();
sdn_session_destroy();

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if ($isAjax) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    http_response_code(200);
    echo json_encode(['success' => true, 'logged_out' => true]);
    exit;
}

header('Location: login.php');
exit;
