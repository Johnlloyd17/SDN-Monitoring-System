/*
 * SDN Monitoring System - client-side inactivity timeout.
 *
 * The server (pages/auth_check.php + pages/session_config.php) is the
 * authority: it destroys the session once 1 hour of genuine inactivity has
 * passed. But a server cannot act on a page that makes no requests at all --
 * an idle tab would sit there showing stale data until the user happened to
 * click something. This file closes that gap by expiring the tab proactively.
 *
 * WHAT COUNTS AS ACTIVITY
 * Deliberate actions only: mousedown, keydown, touchstart and form submit.
 * Bare mousemove, scrolling and wheel are deliberately NOT counted -- a mouse
 * resting against a screen, or a trackpad twitch, must not keep a session
 * alive. The 1-second date/time clock on the pages is pure client-side
 * setInterval writing to #dateTime; it dispatches no events and is naturally
 * excluded without any special-casing.
 *
 * KEEPING THE TWO SIDES IN SYNC
 * The server is what actually decides. This file is a mirror of the server's
 * remaining time (handed over in window.SDN_SESSION by pages/scripts.php) and
 * every deliberate action resets the local countdown. Requests the user
 * genuinely triggers are tagged sdn_act=1 so the server refreshes its own
 * timestamp too; background traffic is never tagged, so it can never keep an
 * idle session alive.
 *
 * EXPIRED AJAX HANDLING
 * Any endpoint guarded by require_auth_api() answers 401 with
 * {"expired": true, "redirect": "..."} once the session dies. A global
 * ajaxError / statusCode hook plus a fetch() wrapper catch that on every page
 * and redirect to login, instead of the user seeing a broken request or a
 * cryptic "Unauthorized" toast. Endpoints guarded by the HTML require_auth()
 * redirect to login instead -- the fetch wrapper detects that too, which is
 * what import.php (e.g. property_records.php) relies on.
 */
(function (window, document) {
    'use strict';

    if (window.SDN_SESSION_WATCHER) {
        return;
    }
    window.SDN_SESSION_WATCHER = true;

    var cfg = window.SDN_SESSION || {};
    var TIMEOUT = (typeof cfg.timeoutMs === 'number' && cfg.timeoutMs > 0) ? cfg.timeoutMs : 3600000;
    var LOGIN_URL = cfg.loginUrl || '../../login.php';
    var LOGOUT_URL = cfg.logoutUrl || '../../logout.php';
    var TICK_MS = 1000;

    var EXPIRED_URL = LOGIN_URL + (LOGIN_URL.indexOf('?') === -1 ? '?' : '&') + 'expired=1';

    /* Server's remaining time at page render, used to build the local deadline. */
    var deadline = Date.now() + ((typeof cfg.remainingMs === 'number' && cfg.remainingMs >= 0) ? cfg.remainingMs : TIMEOUT);

    /* Set by a deliberate action, consumed ONLY by the next outgoing request.
       Deliberately independent of `deadline`: a click usually fires its AJAX
       hundreds of milliseconds later (debounced search boxes, modal saves), and
       the activity must still be credited to the server when it lands. */
    var serverActivityPending = false;

    var expiring = false;

    function markActivity() {
        /* Reset this tab's idle countdown immediately. */
        deadline = Date.now() + TIMEOUT;
        serverActivityPending = true;
    }

    /* Silent, immediate logout. No warning dialog, per the agreed behaviour. */
    function expire() {
        if (expiring) {
            return;
        }
        expiring = true;

        /* Wait for the logout round trip before navigating. The server-side
           destroy is what actually kills the session, and starting a new
           navigation while it is still in flight can otherwise race with it.
           The timeout guarantees we still leave the page if it never answers. */
        var left = false;
        var go = function () {
            if (left) {
                return;
            }
            left = true;
            window.location.replace(EXPIRED_URL);
        };

        window.setTimeout(go, 3000);

        try {
            var request = new XMLHttpRequest();
            request.open('GET', LOGOUT_URL + (LOGOUT_URL.indexOf('?') === -1 ? '?' : '&') + 'ajax=1', true);
            request.onloadend = go;
            request.onerror = go;
            request.timeout = 2500;
            request.send();
        } catch (e) {
            go();
        }
    }

    /* Expiry check only. It deliberately does NOT touch serverActivityPending:
       that flag belongs to the request layer below and must survive until a
       request actually consumes it.

       Note the client is never authoritative. It can be briefly optimistic --
       a keypress with no request following it resets the local deadline but
       leaves the server's own countdown where it was. Nothing is ever exposed
       by that: the server check in require_auth()/require_auth_api() is what
       actually enforces the 1 hour, and the 401 path below catches the
       mismatch and redirects with the same "session expired" message. */
    function tick() {
        if (Date.now() >= deadline) {
            expire();
        }
    }

    /* ---- Deliberate activity -------------------------------------------------
       mousedown rather than click: it fires earlier and also covers the common
       case of a user pressing a button and never releasing it. keydown covers
       typing, Tab navigation and Enter. touchstart covers mobile. submit
       covers form posts. Nothing here fires for the clock or for polling. */
    ['mousedown', 'keydown', 'touchstart'].forEach(function (type) {
        document.addEventListener(type, markActivity, true);
    });
    document.addEventListener('submit', markActivity, true);

    /* A tab that was hidden has its timers throttled by the browser, so
       re-check the moment it comes back into view. */
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            tick();
        }
    });

    window.setInterval(tick, TICK_MS);

    /* ---- Tag genuine activity on outgoing requests -------------------------- */

    function withActivity(url) {
        if (!serverActivityPending || typeof url !== 'string' || url === '') {
            return url;
        }
        return url + (url.indexOf('?') === -1 ? '?' : '&') + 'sdn_act=1';
    }

    function handleExpiredPayload(text) {
        if (!text) {
            return;
        }

        var data = null;
        try {
            data = JSON.parse(text);
        } catch (e) {
            return;
        }

        if (data && data.expired === true) {
            expire();
        }
    }

    function urlLooksLikeLogin(url) {
        return typeof url === 'string' && /login\.php(\?|$)/.test(url);
    }

    if (window.jQuery) {
        var $ = window.jQuery;

        $.ajaxSetup({
            /* jQuery calls this as (jqXHR, settings). The signature matters:
               reading `settings` as the first argument gives the XHR object,
               which has no .url, and every user-triggered request would throw. */
            beforeSend: function (xhr, settings) {
                if (!settings || !serverActivityPending) {
                    return;
                }

                settings.url = withActivity(settings.url);
                settings.headers = settings.headers || {};
                settings.headers['X-SDN-Activity'] = '1';
                serverActivityPending = false;
            },
            statusCode: {
                401: function (xhr) {
                    handleExpiredPayload(xhr.responseText);
                }
            },
            error: function (xhr) {
                if (xhr && xhr.status === 401) {
                    handleExpiredPayload(xhr.responseText);
                    if (urlLooksLikeLogin(xhr.responseURL)) {
                        expire();
                    }
                }
            }
        });
    }

    /* fetch() wrapper: same tagging, plus detection of the HTML redirect that
       require_auth() issues for endpoints like import.php that are reached
       through fetch rather than jQuery. */
    if (typeof window.fetch === 'function' && !window.fetch.sdnPatched) {
        var nativeFetch = window.fetch;

        var patchedFetch = function (input, init) {
            var url = (typeof input === 'string') ? input : (input && input.url) || '';
            var isActivity = serverActivityPending;

            if (typeof init !== 'object' || init === null) {
                init = {};
            }

            /* Only ever mark genuine activity. An unconditional header here
               would let the 45-second notification poll look like a user and
               keep every session alive forever. */
            if (isActivity) {
                if (typeof url === 'string' && url) {
                    input = withActivity(url);
                }
                init.headers = init.headers || {};
                init.headers['X-SDN-Activity'] = '1';
                serverActivityPending = false;
            }

            return nativeFetch(input, init).then(function (response) {
                if (response.status === 401) {
                    response.clone().text().then(handleExpiredPayload);
                }
                if (response.redirected && urlLooksLikeLogin(response.url)) {
                    expire();
                }
                return response;
            });
        };

        patchedFetch.sdnPatched = true;
        window.fetch = patchedFetch;
    }
})(window, document);
