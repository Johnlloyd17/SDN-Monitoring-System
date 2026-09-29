<?php
// Shared BPLS (tblbpls) view configuration and rendering.
//
// The list has a single filter (system) and no summary cards, so this file is
// the table layout, the one filter select and the role gate in one place. The
// table deliberately keeps the original rule of hiding rows whose system is
// blank, which is why the exclusion lives in bpls_where() rather than in the
// query that the page happens to write today.

if (!function_exists('bpls_escape')) {
    function bpls_escape($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('bpls_module')) {
    function bpls_module()
    {
        return array(
            'table'    => 'tblbpls',
            'photos'   => 'tblactivityphoto',
            'heading'  => 'BPLS',
            // The original page let exactly these two accounts manage the list.
            'username' => 'elgusdn',
            'photoDir' => __DIR__ . '/photo',
        );
    }
}

if (!function_exists('bpls_can_manage')) {
    /**
     * Whether the signed-in account may add, edit, delete or attach files.
     * The page only ever hid the buttons for other roles; the endpoints have to
     * make the same decision, because hiding a button is not authorisation.
     */
    function bpls_can_manage()
    {
        if (!isset($_SESSION['role'])) { return false; }
        $module = bpls_module();
        return $_SESSION['role'] === 'Administrator'
            || (isset($_SESSION['username']) && $_SESSION['username'] === $module['username']);
    }
}

if (!function_exists('bpls_columns')) {
    /**
     * Single source of truth for the table layout. Headers, rows and the posted
     * field names are all generated from these keys, so a column cannot drift
     * between the table, the modals and the endpoint.
     */
    function bpls_columns()
    {
        return array(
            'province'        => 'Province',
            'district'        => 'Congressional District',
            'municipality'    => 'City/Municipality',
            'lgu'             => 'LGU Name',
            'class'           => 'Class',
            'system'          => 'System Provider',
            'action'          => 'Remarks / Action Items',
            'businessyn'      => '(BP) Y/N',
            'businessstatus'  => '(BP) Status',
            'barangayyn'      => 'Integration of (BC) Y/N',
            'barangaystatus'  => 'Integration of (BC) Status',
            'buildingyn'      => '(BPCO) Y/N',
            'buildingstatus'  => '(BPCO) Status',
            'workingyn'       => '(WP) Y/N',
            'workingstatus'   => '(WP) Status',
            'bfpyn'           => 'Integration of (FSIC)',
            'bplyn'           => '(BPLS) Y/N',
            'bplstatus'       => '(BPLS) Status',
            'ecedulayn'       => '(eCEDULA) Y/N',
            'ecedulastatus'   => '(eCEDULA) Status',
            'elcryn'          => '(eLCR) Y/N',
            'elcrstatus'      => '(eLCR) Status',
            'enewsyn'         => 'eNEWS Y/N',
            'enewsstatus'     => 'eNEWS Status',
            'remark'          => 'Remarks',
        );
    }
}

if (!function_exists('bpls_filters')) {
    /** The one filter select. Nothing narrows it, because nothing else filters. */
    function bpls_filters()
    {
        return array(
            'system' => array('label' => 'System', 'all' => 'All System', 'narrowedBy' => array()),
        );
    }
}

if (!function_exists('bpls_filter_params')) {
    /** Only the keys the filter form declares are read, each as a string. */
    function bpls_filter_params($src)
    {
        $out = array();
        foreach (array_keys(bpls_filters()) as $key) {
            $out[$key] = (isset($src[$key]) && is_string($src[$key])) ? trim($src[$key]) : '';
        }
        return $out;
    }
}

if (!function_exists('bpls_conditions')) {
    /**
     * Escaped AND conditions for the active filters, skipping the named keys so
     * a dropdown can be asked for its options without filtering on itself.
     */
    function bpls_conditions($con, $params, $skip = array())
    {
        $extra = '';
        foreach (array_keys(bpls_filters()) as $key) {
            if (in_array($key, $skip, true)) { continue; }
            if (!isset($params[$key]) || $params[$key] === '') { continue; }
            $extra .= " AND `$key` = '" . mysqli_real_escape_string($con, $params[$key]) . "'";
        }
        return $extra;
    }
}

if (!function_exists('bpls_options')) {
    /** Distinct values for one filter select. Blanks are never offered. */
    function bpls_options($con, $key, $params)
    {
        $def = bpls_filters();
        if (!isset($def[$key])) { return array(); }
        $module = bpls_module();

        $sql = "SELECT DISTINCT `$key` AS v FROM {$module['table']} WHERE `$key` IS NOT NULL AND `$key` != ''";
        $sql .= bpls_conditions($con, $params, array($key));
        $sql .= " ORDER BY v ASC";

        $res = mysqli_query($con, $sql);
        if (!$res) { return array(); }
        $out = array();
        while ($r = mysqli_fetch_assoc($res)) { $out[] = $r['v']; }
        return $out;
    }
}

if (!function_exists('bpls_render_filters')) {
    /** The filter select, wired for ajax rather than submit. */
    function bpls_render_filters($con, $params)
    {
        $out  = '<form method="post" id="filterForm">' . "\n";
        $out .= '  <div class="row">' . "\n";

        foreach (bpls_filters() as $key => $def) {
            $id = 'bpls' . ucfirst($key) . 'Select';
            $out .= '    <div class="col-md-3 col-sm-6 col-xs-12">' . "\n";
            $out .= '        <div class="form-group">' . "\n";
            $out .= '            <label for="' . $id . '">Select ' . bpls_escape($def['label']) . '</label>' . "\n";
            $out .= '            <select id="' . $id . '" name="' . bpls_escape($key) . '" class="form-control" data-bpls-filter="1">' . "\n";
            $out .= '                <option value="">' . bpls_escape($def['all']) . '</option>' . "\n";
            foreach (bpls_options($con, $key, $params) as $opt) {
                $sel = (isset($params[$key]) && $params[$key] === (string) $opt) ? ' selected' : '';
                $out .= '                <option value="' . bpls_escape($opt) . '"' . $sel . '>' . bpls_escape($opt) . '</option>' . "\n";
            }
            $out .= '            </select>' . "\n";
            $out .= '        </div>' . "\n";
            $out .= '    </div>' . "\n";
        }

        $out .= '  </div>' . "\n";
        $out .= '</form>' . "\n";
        return $out;
    }
}

if (!function_exists('bpls_where')) {
    /**
     * Table WHERE clause. Rows with a blank system were never listed by the
     * original page, so that exclusion is preserved here.
     */
    function bpls_where($con, $params)
    {
        return " WHERE system != ''" . bpls_conditions($con, $params);
    }
}

if (!function_exists('bpls_fetch_rows')) {
    function bpls_fetch_rows($con, $params)
    {
        $module = bpls_module();
        $sql = "SELECT * FROM {$module['table']}" . bpls_where($con, $params) . " ORDER BY lgu ASC";
        $res = mysqli_query($con, $sql);
        if (!$res) { return array(); }
        $rows = array();
        while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }
}

if (!function_exists('bpls_render_headers')) {
    function bpls_render_headers($canManage)
    {
        $out = '<tr>' . "\n";
        if ($canManage) {
            $out .= '<th style="width: 20px !important;"><input type="checkbox" class="cbxMain" onchange="checkMain(this)"/></th>' . "\n";
            $out .= '<th>No.</th>' . "\n";
        }
        foreach (bpls_columns() as $label) {
            $out .= '<th>' . bpls_escape($label) . '</th>' . "\n";
        }
        if ($canManage) {
            $out .= '<th style="width: 40px !important;">Option</th>' . "\n";
        }
        $out .= '</tr>' . "\n";
        return $out;
    }
}

if (!function_exists('bpls_render_rows')) {
    function bpls_render_rows($rows, $canManage)
    {
        if (count($rows) === 0) {
            // checkbox + No. + Option only exist for managers
            $span = count(bpls_columns()) + ($canManage ? 3 : 0);
            return '<tr><td colspan="' . $span . '" class="text-center">No records found.</td></tr>';
        }

        $out   = '';
        $count = 1;
        foreach ($rows as $row) {
            $id   = (int) $row['id'];
            $name = bpls_escape(isset($row['lgu']) ? $row['lgu'] : '');
            $out .= '<tr>';
            if ($canManage) {
                $out .= '<td><input type="checkbox" class="chk_delete" value="' . $id . '" /></td>';
                $out .= '<td>' . $count++ . '</td>';
            }
            foreach (bpls_columns() as $field => $label) {
                $out .= '<td>' . bpls_escape(isset($row[$field]) ? $row[$field] : '') . '</td>';
            }
            if ($canManage) {
                $out .= '<td>'
                      . '<button class="btn btn-primary btn-sm btn-edit-item" data-id="' . $id . '" data-name="' . $name . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button> '
                      . '<button class="btn btn-primary btn-sm btn-view-item" data-id="' . $id . '" data-name="' . $name . '"><i class="fa fa-eye" aria-hidden="true"></i> View</button>'
                      . '</td>';
            }
            $out .= '</tr>';
        }
        return $out;
    }
}

if (!function_exists('bpls_filter_payload')) {
    /**
     * Everything the list needs for one filter selection. The page render and
     * the ajax endpoint both use this, so a filter change can never show stale
     * rows or a stale dropdown.
     */
    function bpls_filter_payload($con, $params)
    {
        $options = array();
        foreach (array_keys(bpls_filters()) as $key) {
            $options[$key] = bpls_options($con, $key, $params);
        }

        return array(
            'rows'    => bpls_fetch_rows($con, $params),
            'filters' => $options,
        );
    }
}
