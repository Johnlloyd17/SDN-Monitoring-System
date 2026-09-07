<?php

echo '
<aside class="left-side sidebar-offcanvas">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left info">
                <h5>Hello, ' . htmlspecialchars($_SESSION['role']) . '</h5>
            </div>
        </div>
        <!-- /.search form -->
        <!-- sidebar menu: style can be found in sidebar.less -->
        ';

if ($_SESSION['role'] == "Administrator") {
    echo '
    <ul class="sidebar-menu">
        <li>
            <a href="../dashboard/dashboard.php">
                <i class="fa fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-shield-alt"></i> <span>Cybersecurity</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
    <li><a href="../activity/cybersecurity.php"><i class="fa fa-file-alt"></i> Activities Conducted</a></li>
    <li><a href="../participant/cybersecurity.php"><i class="fa fa-user"></i> Activity Participants</a></li>
    <li><a href="../report/cybersecurity.php"><i class="fa fa-chart-line"></i> Reports/Graphs</a></li>


</ul>

        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-cogs"></i> <span>eLGU BPLS</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../bpls_data/bpls_monitoring.php"><i class="fa fa-chart-line"></i> Monitoring Status</a></li>
                <li><a href="../activity/elgu.php"><i class="fa fa-file-alt"></i> Activities Conducted</a></li>
                <li><a href="../bpls_data/bpls.php"><i class="fa fa-database"></i> Database</a></li>
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-wifi"></i> <span>FreeWifi4All</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../fw4a_data/fw4a_data.php"><i class="fa fa-chart-line"></i> Monitoring</a></li>
                <li><a href="../fw4a_data/fw4a_strategy.php"><i class="fa fa-chart-bar"></i> Strategy</a></li>
                 <li><a href="../fwfa_letter/letters.php"><i class="fa fa-clipboard-list"></i> Letter Requests</a></li>
                <li><a href="../activity/fwfa.php"><i class="fa fa-file-alt"></i> Activities Conducted</a></li>
               

            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-clipboard-list"></i> <span>GECS</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../activity/gecs.php"><i class="fa fa-clipboard-list"></i> Activities Conducted</a></li>
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-network-wired"></i> <span>GovNet</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <!-- Add relevant links here -->
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-database"></i> <span>IIDB</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../activity/iidb.php"><i class="fa fa-clipboard-list"></i> Activities Conducted</a></li>
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-desktop"></i> <span>ILCDB</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../activity/ilcdb.php"><i class="fa fa-clipboard-list"></i> Activities Conducted</a></li>
                <li><a href="../participant/ilcdb.php"><i class="fa fa-user"></i> Activity Participants</a></li>
                <li><a href="../tech4ed/tech4ed.php"><i class="fa fa-building"></i> Tech4Ed DTC</a></li>
                <li><a href="../report/ilcdb.php"><i class="fa fa-chart-line"></i> Reports/Graphs</a></li>
            </ul>
        </li>
   <li>
            <a href="../property_items/property.php">
                <i class="fa fa-cart-plus"></i> <span>Procurement</span>
            </a>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-archive"></i> <span>Property Management</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../property_records/property_records.php"><i class="fa fa-database"></i> Inventory Records</a></li>
                <li><a href="../pass_slip/pass_slip.php"><i class="fa fa-file-text-o"></i> Office Equipment Pass Slip</a></li>
            </ul>
        </li>
        <li>
            <a href="../property_records/purchase_request.php">
                <i class="fa fa-shopping-cart"></i> <span>Purchase Request</span>
            </a>
        </li>
        <li>
            <a href="../bills_monitoring/bills_monitoring.php">
                <i class="fa fa-file-invoice-dollar"></i> <span>Bills Monitoring</span>
            </a>
        </li>
        <li>
            <a href="../letters_monitoring/letters_monitoring.php">
                <i class="fa fa-file-text-o"></i> <span>Letters Monitoring</span>
            </a>
        </li>
        <li>
            <a href="../credentials/credentials.php">
                <i class="fa fa-user-cog"></i> <span>Credentials</span>
            </a>
        </li>
        <li>
            <a href="../logs/logs.php">
                <i class="fa fa-history"></i> <span>Logs</span>
            </a>
        </li>
    </ul>';
} elseif (isset($_SESSION['staff'])) {
    echo '
    <ul class="sidebar-menu">
      <li>
            <a href="../dashboard/dashboard.php">
                <i class="fa fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-shield-alt"></i> <span>Cybersecurity</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
    <li><a href="../activity/cybersecurity.php"><i class="fa fa-file-alt"></i> Activities Conducted</a></li>
    <li><a href="../participant/cybersecurity.php"><i class="fa fa-user"></i> Activity Participants</a></li>
    <li><a href="../report/cybersecurity.php"><i class="fa fa-chart-line"></i> Reports/Graphs</a></li>


</ul>

        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-cogs"></i> <span>eLGU BPLS</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../bpls_data/bpls_monitoring.php"><i class="fa fa-chart-line"></i> Monitoring Status</a></li>
                <li><a href="../activity/elgu.php"><i class="fa fa-file-alt"></i> Activities Conducted</a></li>
                <li><a href="../bpls_data/bpls.php"><i class="fa fa-database"></i> Database</a></li>
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-wifi"></i> <span>FreeWifi4All</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../fw4a_data/fw4a_data.php"><i class="fa fa-chart-line"></i> Monitoring</a></li>
                <li><a href="../fw4a_data/fw4a_strategy.php"><i class="fa fa-chart-bar"></i> Strategy</a></li>
                 <li><a href="../fwfa_letter/letters.php"><i class="fa fa-clipboard-list"></i> Letter Requests</a></li>
                <li><a href="../activity/fwfa.php"><i class="fa fa-file-alt"></i> Activities Conducted</a></li>
               

            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-clipboard-list"></i> <span>GECS</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../activity/gecs.php"><i class="fa fa-clipboard-list"></i> Activities Conducted</a></li>
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-network-wired"></i> <span>GovNet</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <!-- Add relevant links here -->
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-database"></i> <span>IIDB</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../activity/iidb.php"><i class="fa fa-clipboard-list"></i> Activities Conducted</a></li>
            </ul>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-desktop"></i> <span>ILCDB</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../activity/ilcdb.php"><i class="fa fa-clipboard-list"></i> Activities Conducted</a></li>
                <li><a href="../participant/ilcdb.php"><i class="fa fa-user"></i> Activity Participants</a></li>
                <li><a href="../tech4ed/tech4ed.php"><i class="fa fa-building"></i> Tech4Ed DTC</a></li>
                <li><a href="../report/ilcdb.php"><i class="fa fa-chart-line"></i> Reports/Graphs</a></li>
            </ul>
        </li>
   <li>
            <a href="../property_items/property.php">
                <i class="fa fa-cart-plus"></i> <span>Procurement</span>
            </a>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-archive"></i> <span>Property Management</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a href="../property_records/property_records.php"><i class="fa fa-database"></i> Inventory Records</a></li>
                <li><a href="../pass_slip/pass_slip.php"><i class="fa fa-file-text-o"></i> Office Equipment Pass Slip</a></li>
            </ul>
        </li>
        <li>
            <a href="../property_records/purchase_request.php">
                <i class="fa fa-shopping-cart"></i> <span>Purchase Request</span>
            </a>
        </li>
        <li>
            <a href="../bills_monitoring/bills_monitoring.php">
                <i class="fa fa-file-invoice-dollar"></i> <span>Bills Monitoring</span>
            </a>
        </li>
        <li>
            <a href="../letters_monitoring/letters_monitoring.php">
                <i class="fa fa-file-text-o"></i> <span>Letters Monitoring</span>
            </a>
        </li>
    </ul>';
}

echo '
    </section>
    <!-- /.sidebar -->
</aside>
';
?>
<script>
(function() {
    document.body.classList.add('fixed');

    var currentPath = window.location.pathname;
    var links = document.querySelectorAll('.sidebar-menu a');
    for (var i = 0; i < links.length; i++) {
        var href = links[i].getAttribute('href');
        if (!href || href === '#') continue;
        var linkPath;
        try {
            linkPath = new URL(href, window.location.href).pathname;
        } catch (e) {
            continue;
        }
        if (linkPath === currentPath) {
            var li = links[i].closest('li');
            if (li) li.classList.add('active');
            var parentTreeview = links[i].closest('.treeview');
            if (parentTreeview) {
                parentTreeview.classList.add('active', 'menu-open');
                var submenu = parentTreeview.querySelector('.treeview-menu');
                if (submenu) submenu.style.display = 'block';
            }
            break;
        }
    }
})();
</script>