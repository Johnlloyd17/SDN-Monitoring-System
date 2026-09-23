<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../../login.php");
    exit;
}
require_once 'backup_lib.php';
$storedBackups = sdn_list_backups();
$newest = sdn_newest_backup();
$lastBackupText = 'No backup has been made yet.';
if ($newest) {
    $base = basename($newest);
    if (preg_match('/^sdn_backup_([0-9]{4})-([0-9]{2})-([0-9]{2})_([0-9]{2})([0-9]{2})/', $base, $m)) {
        try {
            $dt = new DateTime($m[1] . '-' . $m[2] . '-' . $m[3] . ' ' . $m[4] . ':' . $m[5]);
            $lastBackupText = $dt->format('F j, Y, g:i A');
        } catch (Exception $e) {
            $lastBackupText = $base;
        }
    } else {
        $lastBackupText = $base;
    }
}
?>
<!DOCTYPE html>
<html>
<?php include('../head_css.php'); ?>
<body class="skin-black">
    <?php include('../header.php'); ?>

    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('../sidebar-left.php'); ?>

        <aside class="right-side">
            <section class="content-header">
                <h1>Settings <small>Backup and restore</small></h1>
            </section>

            <section class="content">
                <div class="callout callout-info">
                    <h4><i class="fa fa-info-circle"></i> What this page is for</h4>
                    <p>This page lets you save a copy of everything in the system, and bring an earlier saved copy back if you ever need to. No technical knowledge is needed.</p>
                </div>

                <div id="settingsAlert"></div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-download"></i> Back up everything in the system</h3>
                    </div>
                    <div class="box-body">
                        <p>
                            This will save a copy of everything in the system (your records, accounts, and history) into one file
                            that you download. Keep this file somewhere safe, like a USB drive or your email.
                        </p>
                        <p class="text-muted">
                            <i class="fa fa-clock-o"></i> Most recent backup:
                            <span id="lastBackupText"><strong><?php echo htmlspecialchars($lastBackupText); ?></strong></span>
                        </p>
                        <button type="button" id="btnExport" class="btn btn-primary btn-lg">
                            <i class="fa fa-download"></i> Download backup
                        </button>
                        <span id="exportStatus" class="text-primary" style="display:none; margin-left:10px;">
                            <i class="fa fa-spinner fa-spin"></i> Preparing your backup&hellip;
                        </span>
                    </div>
                </div>

                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> Restore from a backup</h3>
                    </div>
                    <div class="box-body">
                        <p>
                            If you have a backup file you downloaded earlier, you can use it to put that saved copy back into the
                            system. This is only for restoring an older saved copy of your own data.
                        </p>
                        <form id="restoreForm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="backupFile">Choose the backup file</label>
                                <input type="file" id="backupFile" name="backup_file" class="form-control" accept=".sql">
                                <span class="help-block" id="selectedFileInfo">No file chosen yet.</span>
                            </div>
                            <div class="alert alert-warning">
                                <p>
                                    <strong>Please read this before restoring.</strong>
                                </p>
                                <p>
                                    This will replace everything currently in the system with the data from this backup file.
                                    This cannot be undone unless you have a backup of the current data.
                                    A safety copy of the current data will be saved automatically before restoring.
                                </p>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="restoreConfirm">
                                        I understand this will replace current data
                                    </label>
                                </div>
                            </div>
                            <button type="submit" id="btnRestore" class="btn btn-danger btn-lg" disabled>
                                <i class="fa fa-upload"></i> Restore from backup
                            </button>
                        </form>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-folder-open"></i> Stored backups</h3>
                        <span class="label label-primary" style="margin-left:10px;"><?php echo count($storedBackups); ?></span>
                    </div>
                    <div class="box-body">
                        <p>
                            Backups stay safely on this server and can only be retrieved here, not by a web address.
                            This includes the automatic safety copies that are made by themselves before each restore.
                        </p>
                        <?php if (count($storedBackups) === 0): ?>
                        <p class="text-muted">No backups have been stored yet.</p>
                        <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Backup file</th>
                                    <th>Type</th>
                                    <th>Saved</th>
                                    <th>Size</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($storedBackups as $sb): ?>
                                <tr>
                                    <td>
                                        <code><?php echo htmlspecialchars($sb['filename']); ?></code>
                                        <?php if ($sb['filename'] === ($newest ? basename($newest) : null)): ?>
                                        <span class="label label-success">Latest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sb['kind'] === 'safety'): ?>
                                        Safety copy
                                        <?php else: ?>
                                        Backup
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', $sb['mtime']); ?></td>
                                    <td><?php echo $sb['size'] >= 1048576 ? number_format($sb['size'] / 1048576, 2) . ' MB' : number_format($sb['size'] / 1024, 1) . ' KB'; ?></td>
                                    <td>
                                        <a class="btn btn-xs btn-primary"
                                           href="../../ajax/settings_crud.php?action=download&amp;file=<?php echo urlencode($sb['filename']); ?>">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <div id="restoreOverlay" style="display:none;">
        <div style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:9999; text-align:center;">
            <div style="position:relative; top:40%; background:#fff; max-width:460px; margin:0 auto; padding:25px 30px; border-radius:6px;">
                <p style="font-size:18px;"><i class="fa fa-spinner fa-spin"></i> Restoring&hellip; this may take a moment.</p>
                <p class="text-muted">Please do not close this window or turn off the computer while this is running.</p>
            </div>
        </div>
    </div>


    <script>
    $(function () {
        var base = '../../ajax/settings_crud.php';
        var EXPORT_RE = /^sdn_backup_\d{4}-\d{2}-\d{2}_\d{4}(-\d+)?\.sql$/;

        function updateExportButton(enabled) {
            $('#btnExport').prop('disabled', !enabled);
            $('#exportStatus').toggle(!enabled);
        }

        function updateRestoreButton() {
            var filePicked = $('#backupFile').val() !== '';
            var confirmed = $('#restoreConfirm').is(':checked');
            $('#btnRestore').prop('disabled', !(filePicked && confirmed));
        }

        $('#backupFile').change(function () {
            var name = this.files && this.files.length ? this.files[0].name : '';
            $('#selectedFileInfo').text(name ? 'Selected file: ' + name : 'No file chosen yet.');
            updateRestoreButton();
        });

        $('#restoreConfirm').change(function () {
            updateRestoreButton();
        });

        $('#btnExport').click(function () {
            updateExportButton(false);
            $.post(base, { action: 'export' })
                .done(function (res) {
                    if (res && res.success && res.file && EXPORT_RE.test(res.file)) {
                        $('<a>').attr({
                            href: base + '?action=download&file=' + encodeURIComponent(res.file),
                            download: res.file
                        }).appendTo('body')[0].click();
                        showToast('Backup downloaded. Keep the file somewhere safe.', 'success');
                        setTimeout(function () { window.location.reload(); }, 1200);
                    } else {
                        var msg = (res && res.error) ? res.error : 'The backup could not be made. Please try again.';
                        showToast(msg, 'error');
                        updateExportButton(true);
                    }
                })
                .fail(function () {
                    showToast('The backup could not be made. Please try again.', 'error');
                    updateExportButton(true);
                });
        });

        $('#restoreForm').submit(function (e) {
            e.preventDefault();

            var file = $('#backupFile')[0].files[0];
            if (!file) {
                showToast('Please choose a backup file first.', 'warning');
                return;
            }
            if (!$('#restoreConfirm').is(':checked')) {
                showToast('Please tick the box to confirm you understand this will replace current data.', 'warning');
                return;
            }
            if (file.size <= 0) {
                showToast('The file you chose is empty. Please choose the backup file you downloaded from this page.', 'error');
                return;
            }

            $('#restoreOverlay').show();
            $('#btnRestore').prop('disabled', true);

            var fd = new FormData();
            fd.append('action', 'restore');
            fd.append('backup_file', file);

            $.ajax({
                url: base,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false
            })
                .done(function (res) {
                    if (res && res.success) {
                        showToast(res.message || 'Restore complete.', 'success');
                    } else {
                        var s = (res && res.safety_file) ? ' A safety copy of the earlier data was saved as ' + res.safety_file + '.' : '';
                        showToast(((res && res.error) ? res.error : 'Restore could not be completed. Please try again.') + s, 'error');
                    }
                })
                .fail(function (xhr, status, err) {
                    var msg = 'Restore could not be completed. Please try again.';
                    try {
                        var j = JSON.parse(xhr.responseText);
                        if (j && j.error) msg = j.error;
                    } catch (ex) {}
                    showToast(msg, 'error');
                })
                .always(function () {
                    $('#restoreOverlay').hide();
                    setTimeout(function () { window.location.reload(); }, 1800);
                });
        });
    });
    </script>
</body>
</html>