<?php
/**
 * Central session bootstrap + inactivity-timeout policy.
 *
 * WHY THIS FILE EXISTS
 * --------------------
 * The project previously had NO application-level session timeout at all and
 * relied entirely on XAMPP's php.ini defaults. Two of those defaults collide
 * with a 1-hour inactivity rule:
 *
 *   1. session.gc_maxlifetime = 1440 (24 minutes)
 *      PHP deletes a session file once its mtime is older than this. A truly
 *      idle user would be logged out at ~24 minutes, never reaching 1 hour.
 *
 *   2. The notification bell polls ajax/notifications_data.php every 45s
 *      (js/notifications.js, SDN_NOTIF.pollMs in pages/header.php). Every one
 *      of those requests bumps the session file mtime, so garbage collection
 *      never fires and the session stays alive indefinitely with nobody at the
 *      keyboard -- the exact opposite of what we want.
 *
 * So the authoritative timeout is enforced here, in application code, and
 * gc_maxlifetime is raised above it purely to stop the built-in GC from
 * racing the application check. The result is deterministic: a session dies
 * at exactly SDN_SESSION_TIMEOUT seconds of inactivity and not before.
 *
 * "Activity" means genuine user interaction only. A page load, or an AJAX
 * request explicitly flagged by the client (sdn_act=1 / X-SDN-Activity), marks
 * activity. Unflagged background traffic -- the notification poll, any
 * auto-refresh -- deliberately does NOT, so a tab sitting open with nothing but
 * a ticking clock still expires.
 *
 * USAGE
 * -----
 *   require_once __DIR__ . '/session_config.php';   // or a relative path
 *   sdn_session_boot();
 *
 * See pages/auth_check.php for the require_auth() / require_auth_api() guards
 * that consume this policy, and js/session-timeout.js for the client-side half.
 */

if (!defined('SDN_SESSION_TIMEOUT')) {
    /** Seconds of genuine inactivity before the session expires. 1 hour. */
    define('SDN_SESSION_TIMEOUT', 3600);

    /**
     * Must stay comfortably ABOVE SDN_SESSION_TIMEOUT. PHP's session GC uses
     * the session FILE's mtime, which is bumped by every single request the
     * browser makes -- including the 45s notification poll. Raising the GC
     * window means GC effectively never fires mid-session, and expiry is
     * decided solely by the application check below.
     */
    define('SDN_SESSION_GC', 7200);
}

/**
 * Start the PHP session with this project's cookie/GC policy applied.
 *
 * The ini_set() calls MUST run before session_start(): that is when PHP reads
 * them to build the session cookie and when the GC is armed. Safe to call
 * repeatedly -- it is a no-op once the session is already active, which matters
 * because header.php and sidebar-left.php both pull in auth_check.php within a
 * single page render.
 */
function sdn_session_boot()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    @ini_set('session.gc_maxlifetime', (string) SDN_SESSION_GC);
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

/**
 * Unix timestamp of the last genuine user interaction.
 *
 * A session created before this feature shipped has no marker. Initialise it on
 * first read rather than treating it as expired, so deploying this file does
 * not mass-log-out everyone who is currently signed in.
 */
function sdn_session_last_activity()
{
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }

    return (int) $_SESSION['last_activity'];
}

/**
 * True when the session is signed in but has been idle past the timeout.
 * A session with no role is not "expired", it is simply unauthenticated --
 * the auth guards deal with that case separately.
 */
function sdn_session_is_expired()
{
    if (empty($_SESSION['role'])) {
        return false;
    }

    return (time() - sdn_session_last_activity()) > SDN_SESSION_TIMEOUT;
}

/**
 * Seconds left before expiry, floored at 0. Handed to the client so the
 * browser-side timer and the server-side check expire at the same moment.
 */
function sdn_session_remaining_seconds()
{
    $remaining = SDN_SESSION_TIMEOUT - (time() - sdn_session_last_activity());

    return $remaining > 0 ? (int) $remaining : 0;
}

/** Record genuine user interaction, resetting the inactivity countdown. */
function sdn_session_touch()
{
    $_SESSION['last_activity'] = time();
}

/**
 * True when the current request was triggered by the user rather than fired by
 * a background timer.
 *
 * The client (js/session-timeout.js) flips a flag on deliberate interaction --
 * click, keypress, form submit -- and rides it along on the next request. The
 * flag deliberately survives until some request actually consumes it, so an
 * action whose AJAX call is debounced or delayed is still credited later. The
 * notification poll never sets it, so it can never mask an idle session.
 */
function sdn_request_is_user_activity()
{
    if (isset($_REQUEST['sdn_act']) && $_REQUEST['sdn_act'] === '1') {
        return true;
    }

    return isset($_SERVER['HTTP_X_SDN_ACTIVITY'])
        && $_SERVER['HTTP_X_SDN_ACTIVITY'] === '1';
}

/**
 * Tear the session down completely: clear the data, expire the browser cookie
 * and delete the session file. Used both by explicit logout and by the
 * inactivity check, so an expired session leaves nothing usable behind.
 */
function sdn_session_destroy()
{
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
