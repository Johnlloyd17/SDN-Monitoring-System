<?php
/**
 * Shared authentication guard for the SDN Monitoring System.
 * Every page/endpoint should include this file at the top via:
 *
 *   require_once __DIR__ . '/auth_check.php';   // (or relative path)
 *   require_auth();         // HTML pages  -> redirect to login.php
 *   require_auth_api();     // AJAX/API    -> 401 JSON response
 *
 * Usage note: only ONE of require_auth() / require_auth_api() should be
 * called per request. require_auth_api() is intended for endpoints whose
 * frontend expects JSON rather than an HTML redirect.
 *
 * Inactivity timeout: a signed-in session dies after SDN_SESSION_TIMEOUT
 * (1 hour, see pages/session_config.php) without genuine user interaction.
 * "Genuine" is enforced on both sides of the wire:
 *
 *   - Page loads (require_auth) always count -- loading a page IS the user
 *     acting.
 *   - AJAX (require_auth_api) counts ONLY when the client flags the request
 *     as user-driven (sdn_act=1 / X-SDN-Activity header). Background polling
 *     such as the 45s notification bell does not flag itself, so an idle tab
 *     with nothing but a ticking clock still expires.
 *
 * On expiry the session is destroyed and:
 *   - HTML pages redirect to login.php?expired=1, which shows the user a
 *     "session expired" toast instead of looking like an unexplained logout.
 *   - AJAX endpoints answer 401 with {"expired": true, "redirect": "..."} so
 *     the global handler in js/session-timeout.js can bounce the page to
 *     login cleanly rather than surfacing a broken/failed request.
 */

require_once __DIR__ . '/session_config.php';

sdn_session_boot();

/**
 * Compute a relative path to login.php from the currently executing script,
 * including any sub-directory where this application is deployed.
 *
 * @param string $query Optional query string, e.g. 'expired=1'.
 */
function auth_login_relative($query = '')
{
    if (empty($_SERVER['SCRIPT_NAME'])) {
        $url = 'login.php';
    } else {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $idx = max(strrpos($dir, '/pages'), strrpos($dir, '/ajax'));

        if ($idx === false) {
            $url = 'login.php';
        } else {
            $sub = ltrim(substr($dir, $idx + 1), '/');
            $depth = substr_count($sub, '/') + 1;
            $url = str_repeat('../', $depth) . 'login.php';
        }
    }

    return $query === '' ? $url : $url . '?' . $query;
}

/** No-cache headers shared by both the plain and the expired response paths. */
function auth_no_cache_headers()
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

/**
 * Shared response for a session that is signed in but has timed out. Kills the
 * session first, so an expired session is indistinguishable from a logged-out
 * one no matter which guard noticed.
 *
 * @param bool $api True for AJAX callers (JSON 401), false for page loads.
 */
function auth_session_expired_response($api)
{
    sdn_session_destroy();

    $url = auth_login_relative('expired=1');

    if ($api) {
        header('Content-Type: application/json');
        auth_no_cache_headers();
        http_response_code(401);
        echo json_encode(['error' => 'Session expired', 'expired' => true, 'redirect' => $url]);
        exit;
    }

    auth_no_cache_headers();
    if (!headers_sent()) {
        header('Location: ' . $url);
    }
    exit;
}

/**
 * Guard for regular (HTML) pages: redirect to the login page when the
 * user is not authenticated. Adds no-cache headers so browsers do not
 * serve a stale copy of a protected page.
 */
function require_auth()
{
    if (!isset($_SESSION['role'])) {
        auth_no_cache_headers();

        if (!headers_sent()) {
            header('Location: ' . auth_login_relative());
        }
        exit;
    }

    if (sdn_session_is_expired()) {
        auth_session_expired_response(false);
    }

    sdn_session_touch();
}

/**
 * Guard for AJAX/API endpoints: return a 401 JSON error instead of an
 * HTML redirect, so fetch()/jQuery callers get a parseable response.
 *
 * The inactivity timestamp is only refreshed when the client flags the request
 * as user-driven, so an idle tab's background polling cannot hold the session
 * open indefinitely.
 */
function require_auth_api()
{
    if (!isset($_SESSION['role'])) {
        header('Content-Type: application/json');
        auth_no_cache_headers();
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    if (sdn_session_is_expired()) {
        auth_session_expired_response(true);
    }

    if (sdn_request_is_user_activity()) {
        sdn_session_touch();
    }
}