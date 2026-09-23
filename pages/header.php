<?php
require_once __DIR__ . '/auth_check.php'; require_auth();
?>
<?php if (!isset($con)) include "connection.php";
$_notifBase = '../../';
$settingsRow = '';
if ($_SESSION['role'] == 'Administrator') {
    $settingsRow = '<li class="user-row">
                            <a href="../settings/settings.php"><i class="fa fa-cog"></i> Settings</a>
                        </li>';
}
echo '<header class="header">
        <a href="#" class="logo">
           
            <span>DICT Surigao del Norte</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">

                    <!-- Notifications: due-date alerts (bell) -->
                    <li class="dropdown notifications-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-bell"></i>
                            <span class="label label-warning" id="notifBadge" style="display:none;">0</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have <span id="notifCountText">0</span> pending alert(s)</li>
                            <li>
                                <ul class="menu" id="notifList"></ul>
                            </li>
                            <li class="footer" style="padding:0;">
                                <div class="notif-footer-row">
                                    <a href="#" id="notifViewAll" class="btn btn-xs btn-notif-view" style="display:none;"><i class="fa fa-list"></i> View All</a>
                                    <a href="#" id="notifDismissAll" class="btn btn-xs btn-warning" style="display:none;"><i class="fa fa-bell-slash"></i> Dismiss all</a>
                                </div>
                            </li>
                        </ul>
                    </li>

                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="glyphicon glyphicon-user"></i><span>'.$_SESSION['role'].'<i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- Identity header -->
                            <li class="user-identity">
                                <span class="user-avatar">'.(isset($_SESSION['role']) ? strtoupper(substr($_SESSION['role'], 0, 1)) : 'U').'</span>
                                <span class="user-meta">
                                    <span class="user-name">'.$_SESSION['role'].'</span>
                                    <span class="user-subtext">'.(isset($_SESSION['username']) ? $_SESSION['username'] : '').'</span>
                                </span>
                            </li>
                            <!-- Menu Body -->
                            
                            '.$settingsRow.'
                            <li class="user-row">
                                <a href="#" data-toggle="modal" data-target="#editProfileModal"><i class="fa fa-exchange"></i> Change Account</a>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-divider"></li>
                            <li class="user-row user-signout">
                                <a href="../../logout.php"><i class="fa fa-sign-out"></i> Sign out</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>';

?>
<style>
    .navbar-nav > .notifications-menu > .dropdown-menu {
        width: 400px;
        max-width: calc(100vw - 24px);
        right: 0;
        left: auto;
        z-index: 2999;
    }
    #notifList {
        max-height: 280px;
        overflow-y: auto;
        overscroll-behavior: contain;
        -ms-scroll-chaining: none;
    }
    #notifList .notif-item { display: flex; align-items: center; padding: 0 10px; border-bottom: 1px solid #f4f4f4; }
    #notifList .notif-item > a.notif-title { flex: 1; padding: 10px 0; white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    #notifList .notif-item .notif-view,
    #notifList .notif-item .notif-x,
    .notif-action { color: #999; font-size: 12px; padding: 2px 6px; white-space: nowrap; text-decoration: none; background: transparent; border: 0; }
    .notif-action:hover, .notif-action:focus { text-decoration: underline; }
    .notif-action.notif-view:hover, .notif-action.notif-view:focus { color: #3c8dbc; }
    .notif-action.notif-x:hover, .notif-action.notif-x:focus { color: #dd4b39; }
    #notifList .notif-item .label { display: inline-block; margin-left: 4px; white-space: nowrap; }
    .notif-footer-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 8px 12px; border-top: 1px solid #f4f4f4; background: #fff; }
    .notif-footer-row .btn { font-size: 12px; font-weight: 600; white-space: nowrap; }
    .notif-footer-row .btn:hover,
    .notif-footer-row .btn:focus { text-decoration: none; }
    .notif-footer-row .btn > .fa { margin-right: 4px; }
    /* "Dismiss all" reuses the modal's btn.btn-warning (orange, white text). */
    /* "View All" — brand navy outline button, visually distinct from Dismiss all. */
    .notif-footer-row .btn-notif-view { color: #001f3f; background: #fff; border: 1px solid #001f3f; }
    .notif-footer-row .btn-notif-view:hover,
    .notif-footer-row .btn-notif-view:focus { color: #001f3f; background: #eaeff7; border-color: #001f3f; }
    .notif-all-list { list-style: none; margin: 0; padding: 0; max-height: 60vh; overflow-y: auto; overscroll-behavior: contain; }
    .notif-all-list .notif-item { display: flex; align-items: center; padding: 0 12px; border-bottom: 1px solid #f4f4f4; }
    .notif-all-list .notif-item > a.notif-title { flex: 1; padding: 10px 0; white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    .notif-all-list .notif-item .label { display: inline-block; margin-left: 4px; white-space: nowrap; }
    #notifAllModal .modal-body.modal-tabs .nav-tabs { padding: 12px 24px 0; margin: 0; }
    /* Bills = blue, Letters = green (tab pills + row text). Overdue bills stay red. */
    #notifAllModal .notif-tab-bills > a { color: #3c8dbc; }
    #notifAllModal .notif-tab-letters > a { color: #00a65a; }
    #notifAllModal .notif-tab-bills.active > a,
    #notifAllModal .notif-tab-bills.active > a:hover,
    #notifAllModal .notif-tab-bills.active > a:focus { background: #3c8dbc; color: #fff; }
    #notifAllModal .notif-tab-letters.active > a,
    #notifAllModal .notif-tab-letters.active > a:hover,
    #notifAllModal .notif-tab-letters.active > a:focus { background: #00a65a; color: #fff; }
    /* SHARED source of truth for alert row TEXT color-coding. The color lives on the
       row's <li> (classes emitted by js/notifications.js bellItem()) and the
       title link inherits it, so the bell dropdown, the "View All" modal and the
       dashboard all reference ONE rule set:
           bills due (incl. no due date)  -> #3c8dbc (blue)
           bills overdue                  -> #dd4b39 (red)
           letters                        -> #00a65a (green) */
    .notif-item.notif-bill.notif-due { color: #3c8dbc; }
    .notif-item.notif-bill.notif-overdue { color: #dd4b39; }
    .notif-item.notif-letter { color: #00a65a; }
    .notif-item > a.notif-title { color: inherit; }
    /* Dropdown only: neutralizes AdminLTE's .notifications-menu a{color:#444}
       (specificity 0,4,3), which otherwise wins over the shared class rules
       inside the navbar. The ID scope is what beats that selector; color
       composition still comes solely from the shared <li> rules above. */
    #notifList .notif-item > a.notif-title { color: inherit; }
    #notifViewModal .notif-view-value { font-size: 13px; color: #333; padding-top: 6px; word-break: break-word; }

    /* ============ Administrator account dropdown (list-style menu) ============ */
    .navbar-nav > .user-menu > .dropdown-menu {
        width: 230px;
        padding: 0;
    }
    .navbar-nav > .user-menu > .dropdown-menu:after {
        border-bottom-color: #f8f8f8;
    }
    .user-menu > .dropdown-menu {
        padding: 0;
    }
    .user-identity {
        display: flex;
        align-items: center;
        padding: 13px 16px;
        background: #f8f8f8;
        border-bottom: 1px solid #eeeeee;
    }
    .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #3c8dbc;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .user-meta {
        display: inline-flex;
        flex-direction: column;
        line-height: 1.35;
        overflow: hidden;
    }
    .user-name {
        font-size: 14px;
        font-weight: 700;
        color: #333333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-subtext {
        font-size: 12px;
        color: #999999;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-row {
        padding: 0;
    }
    .user-row > a {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        color: #333333;
        font-size: 13px;
        text-decoration: none;
        white-space: nowrap;
    }
    .user-row > a > i {
        width: 16px;
        margin-right: 10px;
        text-align: center;
        color: #777777;
        font-size: 14px;
    }
    .user-row > a:hover,
    .user-row > a:focus {
        background: #f5f5f5;
        color: #333333;
        text-decoration: none;
    }
    .user-row > a:hover > i,
    .user-row > a:focus > i {
        color: #3c8dbc;
    }
    .user-divider {
        border-top: 1px solid #e5e5e5;
        margin: 3px 0;
    }
    .user-row.user-signout > a {
        color: #a94442;
    }
    .user-row.user-signout > a > i {
        color: #a94442;
    }
    .user-row.user-signout > a:hover,
    .user-row.user-signout > a:focus {
        background: #fdf3f2;
        color: #a94442;
    }
    .user-row.user-signout > a:hover > i,
    .user-row.user-signout > a:focus > i {
        color: #a94442;
    }
</style>
<script>
    window.SDN_NOTIF = window.SDN_NOTIF || { base: '<?php echo $_notifBase; ?>', pollMs: 45000 };
</script>
<script src="<?php echo $_notifBase; ?>js/notifications.js"></script>

<!-- ========================= NOTIFICATION DETAIL MODAL (read-only View) ======================= -->
<div id="notifViewModal" class="modal fade">
    <div class="modal-dialog modal-sdm-lg modal-view">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="notifViewTitle"><i class="fa fa-eye"></i> Details</h4>
            </div>
            <div class="modal-body" id="notifViewBody"></div>
            <div class="modal-footer" id="notifViewFooter"></div>
        </div>
    </div>
</div>

<!-- ========================= ALL PENDING ALERTS MODAL (View all) ======================= -->
<div id="notifAllModal" class="modal fade">
    <div class="modal-dialog modal-sdm-lg modal-view">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-bell"></i> All Pending Alerts <span id="notifAllCount" class="label label-warning" style="display:none;"></span></h4>
            </div>
            <div class="modal-body modal-tabs" style="padding:0;">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active notif-tab-bills"><a href="#notifAllBillsPane" data-toggle="tab"><i class="fa fa-file-text-o"></i> Bills <span id="notifAllBillsCount" class="label label-warning" style="display:none;"></span></a></li>
                    <li class="notif-tab-letters"><a href="#notifAllLettersPane" data-toggle="tab"><i class="fa fa-envelope-o"></i> Letters <span id="notifAllLettersCount" class="label label-warning" style="display:none;"></span></a></li>
                </ul>
                <div class="tab-content" style="padding:0;">
                    <div class="tab-pane active" id="notifAllBillsPane" style="padding:0;">
                        <ul class="notif-all-list" id="notifAllBillsList"></ul>
                    </div>
                    <div class="tab-pane" id="notifAllLettersPane" style="padding:0;">
                        <ul class="notif-all-list" id="notifAllLettersList"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="notifAllDismissAll"><i class="fa fa-bell-slash"></i> Dismiss all</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================= NOTIFICATION DISMISS MODAL (choose re-notify duration) ======================= -->
<div id="notifDismissModal" class="modal fade">
    <div class="modal-dialog modal-sdm-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-bell-slash"></i> Dismiss notification</h4>
            </div>
            <div class="modal-body">
                <p id="notifDismissMsg">Keep this alert hidden for:</p>
                <div class="radio">
                    <label><input type="radio" name="notifDismissDur" value="15" /> 15 minutes</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="notifDismissDur" value="60" /> 1 hour</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="notifDismissDur" value="360" /> 6 hours</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="notifDismissDur" value="720" /> 12 hours</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="notifDismissDur" value="1440" checked="checked" /> 24 hours</label>
                </div>
                <p class="text-muted" style="margin-top:10px;">The alert will reappear after the chosen duration has elapsed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="notifDismissConfirm">Dismiss</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= NOTIFICATION QUICK-ACTION CONFIRM MODAL (Mark Paid / Mark Responded) ================= -->
<div id="notifActionConfirmModal" class="modal fade">
    <div class="modal-dialog modal-sdm-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="notifActionTitle"><i class="fa fa-check"></i> Confirm action</h4>
            </div>
            <div class="modal-body">
                <p id="notifActionMsg"></p>
                <div class="form-group" id="notifActionDateWrap">
                    <label id="notifActionDateLabel">Date Paid</label>
                    <input type="date" class="form-control input-sm" id="notifActionDate" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="notifActionConfirm">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div id="editProfileModal" class="modal fade">
    <form method="post">
        <div class="modal-dialog modal-sdm-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-user"></i> Change Account</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            if($_SESSION['role'] == "Administrator"){
                                $user = mysqli_query($con,"SELECT * from tbluser where id = '".$_SESSION['userid']."' ");
                                while($row = mysqli_fetch_array($user)){
                                    echo '
                                        <div class="form-group">
                                            <label>Username:</label>
                                            <input name="txt_username" id="txt_username" class="form-control input-sm" type="text" value="'.$row['username'].'" />
                                        </div>
                                        <div class="form-group">
                                            <label>Password:</label>
                                            <input name="txt_password" id="txt_password" class="form-control input-sm" type="password"  value="'.$row['password'].'"/>
                                        </div>';
                                } 
                            }
                            else{
                                $user = mysqli_query($con,"SELECT * from tblstaff where id = '".$_SESSION['userid']."' ");
                                while($row = mysqli_fetch_array($user)){
                                    echo '
                                        <div class="form-group">
                                            <label>Username:</label>
                                            <input name="txt_username" id="txt_username" class="form-control input-sm" type="text" value="'.$row['username'].'" />
                                        </div>
                                        <div class="form-group">
                                            <label>Password:</label>
                                            <input name="txt_password" id="txt_password" class="form-control input-sm" type="password"  value="'.$row['password'].'"/>
                                        </div>';
                                } 
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel"/>
                    <input type="submit" class="btn btn-primary btn-sm" id="btn_saveeditProfile" name="btn_saveeditProfile" value="Save"/>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
if(isset($_POST['btn_saveeditProfile'])){
    $username = $_POST['txt_username'];
    $password = $_POST['txt_password'];

    if($_SESSION['role'] == "Administrator"){
        $updadmin = mysqli_query($con,"UPDATE tbluser set username = '$username', password = '$password' where id = '".$_SESSION['userid']."' ");
        if($updadmin == true){
            header ("location: ".$_SERVER['REQUEST_URI']);
        }
    }
    elseif($_SESSION['staff'] == "Staff"){
        $updstaff = mysqli_query($con,"UPDATE tblstaff set username = '$username', password = '$password' where id = '".$_SESSION['userid']."' ");
        if($updstaff == true){
            header ("location: ".$_SERVER['REQUEST_URI']);
        }
    }
}
?>
