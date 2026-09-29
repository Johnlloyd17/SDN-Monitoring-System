<?php
/**
 * Minimal client-side session watcher bootstrap.
 *
 * pages/scripts.php includes this for normal app pages. Standalone print views
 * (pages/pass_slip/print_*.php and friends) include it directly instead of
 * scripts.php, because they have their own <head> and deliberately avoid the
 * AdminLTE/DataTables bundle -- loading that would disturb their print layout.
 * session-timeout.js is self-contained: it only uses jQuery and fetch when they
 * are present, so it is safe on a page that has neither.
 *
 * SCRIPT_NAME reflects the page actually being requested, not this partial, so
 * auth_login_relative() resolves the right ../ depth for pages/<module>/*.php.
 */
require_once __DIR__ . '/auth_check.php';
$assetPrefix = isset($sdn_asset_prefix) ? $sdn_asset_prefix : '../../';
?>
<script>
    window.SDN_SESSION = {
        timeoutMs: <?php echo (int) SDN_SESSION_TIMEOUT * 1000; ?>,
        remainingMs: <?php echo (int) sdn_session_remaining_seconds() * 1000; ?>,
        loginUrl: <?php echo json_encode(auth_login_relative()); ?>,
        logoutUrl: <?php echo json_encode(substr(auth_login_relative(), 0, -strlen('login.php')) . 'logout.php'); ?>
    };
</script>
<script src="<?php echo htmlspecialchars($assetPrefix, ENT_QUOTES); ?>js/session-timeout.js?v=20260929b" type="text/javascript"></script>
