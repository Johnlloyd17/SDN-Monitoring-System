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
                <i class="fa fa-project-diagram"></i> <span>DREAMS</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <!-- Add relevant links here -->
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
                <li><a href="../property_records/classification_list.php"><i class="fa fa-tags"></i> Classification List</a></li>
                <li><a href="../pass_slip/pass_slip.php"><i class="fa fa-file-text-o"></i> Office Equipment Pass Slip</a></li>
            </ul>
        </li>
        <li>
            <a href="../property_records/purchase_request.php">
                <i class="fa fa-shopping-cart"></i> <span>Purchase Request</span>
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
                <i class="fa fa-project-diagram"></i> <span>DREAMS</span> <i class="fa fa-angle-down pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <!-- Add relevant links here -->
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
                <li><a href="../property_records/classification_list.php"><i class="fa fa-tags"></i> Classification List</a></li>
                <li><a href="../pass_slip/pass_slip.php"><i class="fa fa-file-text-o"></i> Office Equipment Pass Slip</a></li>
            </ul>
        </li>
        <li>
            <a href="../property_records/purchase_request.php">
                <i class="fa fa-shopping-cart"></i> <span>Purchase Request</span>
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
    // Current page's absolute path (ignores query string/hash)
    var currentPath = window.location.pathname;

    // Find all sidebar links
    var links = document.querySelectorAll('.sidebar-menu a');
    for (var i = 0; i < links.length; i++) {
        var href = links[i].getAttribute('href');
        if (!href || href === '#') continue;

        // Resolve the link's href to a full absolute path relative to the
        // current location, so links sharing the same filename in different
        // folders (e.g. activity/cybersecurity.php vs report/cybersecurity.php)
        // are never confused with one another.
        var linkPath;
        try {
            linkPath = new URL(href, window.location.href).pathname;
        } catch (e) {
            continue;
        }

        if (linkPath === currentPath) {
            // Mark this <li> as active
            var li = links[i].closest('li');
            if (li) li.classList.add('active');

            // If inside a treeview submenu, open and highlight the parent
            var parentTreeview = links[i].closest('.treeview');
            if (parentTreeview) {
                parentTreeview.classList.add('active', 'menu-open');
                var submenu = parentTreeview.querySelector('.treeview-menu');
                if (submenu) submenu.style.display = 'block';
            }
            break; // exact match found, no need to keep looking
        }
    }
})();
</script>