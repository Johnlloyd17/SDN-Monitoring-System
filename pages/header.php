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
                            <li class="footer"><a href="#" id="notifDismissAll" style="display:none;">Dismiss all</a></li>
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
    #notifList .notif-item > a.notif-title { flex: 1; padding: 10px 0; color: #333; white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    #notifList .notif-item .notif-view,
    #notifList .notif-item .notif-x,
    .notif-action { color: #999; font-size: 12px; padding: 2px 6px; white-space: nowrap; text-decoration: none; background: transparent; border: 0; }
    .notif-action:hover, .notif-action:focus { text-decoration: underline; }
    .notif-action.notif-view:hover, .notif-action.notif-view:focus { color: #3c8dbc; }
    .notif-action.notif-x:hover, .notif-action.notif-x:focus { color: #dd4b39; }
    #notifList .notif-item.notif-overdue > a.notif-title { color: #dd4b39; font-weight: bold; }
    #notifList .notif-item.notif-due > a.notif-title { color: #f39c12; }
    #notifList .notif-item .label { display: inline-block; margin-left: 4px; white-space: nowrap; }
    #notifViewModal .notif-view-value { font-size: 13px; color: #333; padding-top: 6px; word-break: break-word; }
    #notifViewModal .notif-view-actions { margin-top: 14px; padding-top: 12px; border-top: 1px solid #eee; text-align: right; }
    #notifViewModal .notif-view-actions .btn { margin-left: 6px; }

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
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
