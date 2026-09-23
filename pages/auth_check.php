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
 * Session note: an expired session behaves identically to "never logged in"
 * here -- PHP garbage collection removes the idled session, so $_SESSION is
 * empty and these guards redirect/401 exactly as if the user had never
 * authenticated.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Compute a relative path to login.php from the currently executing script,
 * including any sub-directory where this application is deployed.
 */
function auth_login_relative()
{
    if (empty($_SERVER['SCRIPT_NAME'])) {
        return 'login.php';
    }

    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $idx = max(strrpos($dir, '/pages'), strrpos($dir, '/ajax'));

    if ($idx === false) {
        return 'login.php';
    }

    $sub = ltrim(substr($dir, $idx + 1), '/');
    $depth = substr_count($sub, '/') + 1;
    return str_repeat('../', $depth) . 'login.php';
}

/**
 * Guard for regular (HTML) pages: redirect to the login page when the
 * user is not authenticated. Adds no-cache headers so browsers do not
 * serve a stale copy of a protected page.
 */
function require_auth()
{
    if (isset($_SESSION['role'])) {
        return;
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    if (!headers_sent()) {
        header('Location: ' . auth_login_relative());
    }
    exit;
}

/**
 * Guard for AJAX/API endpoints: return a 401 JSON error instead of an
 * HTML redirect, so fetch()/jQuery callers get a parseable response.
 */
function require_auth_api()
{
    if (isset($_SESSION['role'])) {
        return;
    }

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}