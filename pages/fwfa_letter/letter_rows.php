<?php
// Shared FWFA letter (locationrequests) view configuration and rendering.
//
// The four summary cards and the four filter selects all have their own,
// deliberately asymmetric filter rules, and the original page encoded each of
// them separately. Those rules are captured here as data so the table, the
// cards and the dropdowns cannot drift apart again.

if (!function_exists('letter_escape')) {
    function letter_escape($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('letter_module')) {
    function letter_module()
    {
        return array(
            'table'    => 'locationrequests',
            'photos'   => 'tblactivityphoto',
            'heading'  => 'Letter Requests Status Report',
            'username' => 'fwfasdn',
            'photoDir' => __DIR__ . '/photo',
        );
    }
}

if (!function_exists('letter_columns')) {
    /**
     * Single source of truth for the table layout. Headers and rows are both
     * generated from this so a column cannot drift between the two.
     */
    function letter_columns()
    {
        return array(
            'locality'    => 'Locality',
            'barangay'    => 'Barangay',
            'district'    => 'District',
            'location'    => 'Location Name',
            'date'        => 'Date Requested',
            'year'        => 'Year',
            'type'        => 'Type',
            'status'      => 'Status',
            'accomplished'=> 'Accomplished Date',
            'remarks'     => 'Remarks',
        );
    }
}

if (!function_exists('letter_filters')) {
    /**
     * The four filter selects. `narrowedBy` lists the other filters that
     * constrain this dropdown's own option list; the original dropdowns each
     * excluded only themselves, so the pattern is preserved here.
     */
    function letter_filters()
    {
        return array(
            'locality' => array('label' => 'Locality',  'all' => 'All Localities', 'narrowedBy' => array('barangay', 'type', 'year')),
            'barangay' => array('label' => 'Barangay',  'all' => 'All Barangays', 'narrowedBy' => array('locality', 'type', 'year')),
            'type'     => array('label' => 'Type',      'all' => 'All Types',     'narrowedBy' => array('locality', 'barangay', 'year')),
            // The year list was never narrowed in the original page.
            'year'     => array('label' => 'Year',      'all' => 'All Years',     'narrowedBy' => array()),
        );
    }
}

if (!function_exists('letter_can_manage')) {
    function letter_can_manage()
    {
        $m = letter_module();
        if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
            return false;
        }
        return $_SESSION['role'] === 'Administrator'
            || $_SESSION['username'] === $m['username'];
    }
}

if (!function_exists('letter_filter_params')) {
    /** Normalises the request filter params. */
    function letter_filter_params($source = null)
    {
        if ($source === null) { $source = $_POST; }
        $out = array();
        foreach (array_keys(letter_filters()) as $key) {
            $out[$key] = isset($source[$key]) ? (string) $source[$key] : '';
        }
        return $out;
    }
}

if (!function_exists('letter_conditions')) {
    /**
     * Builds `field = ?`-free SQL fragments for the active filters, skipping
     * any field named in $exclude. Returns array($sql, $vals) where $sql is
     * either '' or a leading ' AND ...' fragment.
     */
    function letter_conditions($con, $params, $exclude = array())
    {
        $sql  = '';
        $vals = array();
        foreach (array_keys(letter_filters()) as $key) {
            if (in_array($key, $exclude, true)) { continue; }
            if (!isset($params[$key]) || $params[$key] === '') { continue; }
            $sql   .= " AND `$key` = '" . mysqli_real_escape_string($con, $params[$key]) . "'";
            $vals[] = $params[$key];
        }
        return array($sql, $vals);
    }
}

if (!function_exists('letter_options')) {
    /**
     * Distinct values for one filter select, narrowed by the other filters.
     * Empty values are never offered because they mean "no filter".
     */
    function letter_options($con, $key, $params)
    {
        $def = letter_filters();
        if (!isset($def[$key])) { return array(); }

        $module = letter_module();
        $sql = "SELECT DISTINCT `$key` AS v FROM {$module['table']} WHERE `$key` IS NOT NULL AND `$key` != ''";
        list($extra) = letter_conditions($con, $params, array($key));
        $sql .= $extra;
        $sql .= " ORDER BY v ASC";

        $res = mysqli_query($con, $sql);
        if (!$res) { return array(); }
        $out = array();
        while ($r = mysqli_fetch_assoc($res)) { $out[] = $r['v']; }
        return $out;
    }
}

if (!function_exists('letter_stats')) {
    /**
     * The four summary cards. `filters` records which active filters each card
     * honours, which is intentionally not the same set for all four.
     */
    function letter_stats()
    {
        return array(
            array(
                'key'     => 'localities',
                'label'   => 'Total Localities',
                'icon'    => 'icons/municipality_1.png',
                'size'    => '50px 50px',
                'bg'      => 'bg-blue',
                'link'    => '../fwfa_letter/fw4a_localities.php',
                'linkMode'=> 'box',
                'expr'    => 'COUNT(DISTINCT locality)',
                'guard'   => "locality != ''",
                'filters' => array('barangay', 'type', 'year'),
            ),
            array(
                'key'     => 'barangays',
                'label'   => 'Total Barangays',
                'icon'    => 'icons/barangays_1.png',
                'size'    => '60px 60px',
                'bg'      => 'bg-red',
                'link'    => '../fwfa_letter/fw4a_barangays.php',
                'linkMode'=> 'box',
                'expr'    => 'COUNT(DISTINCT barangay)',
                'guard'   => "barangay != ''",
                'filters' => array('locality', 'type', 'year'),
            ),
            array(
                'key'     => 'requests',
                'label'   => 'Total Requests',
                'icon'    => 'icons/requests_1.png',
                'size'    => '48px 48px',
                'bg'      => 'bg-yellow',
                'link'    => '../fwfa_letter/fw4a_requests.php',
                // The original wrapped only the number in the link, not the box.
                'linkMode'=> 'number',
                'expr'    => 'COUNT(*)',
                'guard'   => "type = 'Request'",
                'filters' => array(),
            ),
            array(
                'key'     => 'provisions',
                'label'   => 'Total Provisions',
                'icon'    => 'icons/provision_1.png',
                'size'    => '45px 45px',
                'bg'      => 'bg-blue',
                'link'    => '../fwfa_letter/fw4a_provision.php',
                'linkMode'=> 'box',
                'expr'    => 'COUNT(*)',
                'guard'   => "type = 'Provision'",
                'filters' => array(),
            ),
        );
    }
}

if (!function_exists('letter_stat_values')) {
    /** Current count for every summary card, honouring each card's own filters. */
    function letter_stat_values($con, $params)
    {
        $out = array();
        foreach (letter_stats() as $card) {
            $skip = $card['filters'];
            // A card that counts only one type must not also be narrowed by the
            // type filter, so the guard wins over an incoming type selection.
            $extra = '';
            foreach ($skip as $key) {
                if (!isset($params[$key]) || $params[$key] === '') { continue; }
                $extra .= " AND `$key` = '" . mysqli_real_escape_string($con, $params[$key]) . "'";
            }
            $module = letter_module();
            $sql = "SELECT " . $card['expr'] . " AS n FROM {$module['table']} WHERE " . $card['guard'] . $extra;
            $res = mysqli_query($con, $sql);
            $out[$card['key']] = ($res && ($r = mysqli_fetch_assoc($res))) ? (int) $r['n'] : 0;
        }
        return $out;
    }
}

if (!function_exists('letter_render_stats')) {
    /**
     * The four info boxes. Each number carries an id so the ajax refresh can
     * update the counts without reloading the page.
     */
    function letter_render_stats($con, $params)
    {
        $values = letter_stat_values($con, $params);

        $out  = '    <div class="row">' . "\n";
        foreach (letter_stats() as $card) {
            $value = isset($values[$card['key']]) ? (int) $values[$card['key']] : 0;
            $id    = 'stat' . ucfirst($card['key']);
            $inner = '';

            if ($card['linkMode'] === 'number') {
                $number = '<a class="info-box-number" id="' . $id . '" href="' . letter_escape($card['link']) . '">' . $value . '</a>';
            } else {
                $number = '<span class="info-box-number" id="' . $id . '">' . $value . '</span>';
            }

            $box  = '        <div class="info-box">' . "\n";
            $box .= '            <span class="info-box-icon ' . letter_escape($card['bg']) . '">' . "\n";
            $box .= '                <img src="' . letter_escape($card['icon']) . '" alt="' . letter_escape($card['label']) . '" style="width: ' . letter_escape($card['size']) . ';">' . "\n";
            $box .= '            </span>' . "\n";
            $box .= '            <div class="info-box-content">' . "\n";
            $box .= '                <span class="info-box-text">' . letter_escape($card['label']) . '</span>' . "\n";
            $box .= '                ' . $number . "\n";
            $box .= '            </div>' . "\n";
            $box .= '        </div>';

            if ($card['linkMode'] === 'box') {
                $inner .= '        <a href="' . letter_escape($card['link']) . '">' . $box . '</a>' . "\n";
            } else {
                $inner .= $box . "\n";
            }

            $out .= '    <div class="col-md-3 col-sm-6 col-xs-12">' . "\n" . $inner . '    </div>' . "\n";
        }
        $out .= '    </div>' . "\n";
        return $out;
    }
}

if (!function_exists('letter_render_filters')) {
    /** The four cascading filter selects, wired for ajax rather than submit. */
    function letter_render_filters($con, $params)
    {
        $out  = '<form method="post" id="filterForm">' . "\n";
        $out .= '  <div class="row">' . "\n";

        foreach (letter_filters() as $key => $def) {
            $out .= '    <div class="col-md-3 col-sm-6 col-xs-12">' . "\n";
            $out .= '        <div class="form-group">' . "\n";
            $out .= '            <label for="letter' . ucfirst($key) . 'Select">Select ' . letter_escape($def['label']) . '</label>' . "\n";
            $out .= '            <select id="letter' . ucfirst($key) . 'Select" name="' . letter_escape($key) . '" class="form-control" data-letter-filter="1">' . "\n";
            $out .= '                <option value="">' . letter_escape($def['all']) . '</option>' . "\n";
            foreach (letter_options($con, $key, $params) as $opt) {
                $sel = (isset($params[$key]) && $params[$key] === (string) $opt) ? ' selected' : '';
                $out .= '                <option value="' . letter_escape($opt) . '"' . $sel . '>' . letter_escape($opt) . '</option>' . "\n";
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

if (!function_exists('letter_where')) {
    /**
     * Table WHERE clause. Rows with a blank locality were never listed by the
     * original page, so that exclusion is preserved here.
     */
    function letter_where($con, $params)
    {
        $where = " WHERE locality != ''";
        list($extra) = letter_conditions($con, $params);
        $where .= $extra;
        return $where;
    }
}

if (!function_exists('letter_fetch_rows')) {
    function letter_fetch_rows($con, $params)
    {
        $module = letter_module();
        $sql = "SELECT * FROM {$module['table']}" . letter_where($con, $params) . " ORDER BY locality ASC";
        $res = mysqli_query($con, $sql);
        if (!$res) { return array(); }
        $rows = array();
        while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }
}

if (!function_exists('letter_render_headers')) {
    function letter_render_headers($canManage)
    {
        $out = '<tr>' . "\n";
        if ($canManage) {
            $out .= '<th style="width: 20px !important;"><input type="checkbox" class="cbxMain" onchange="checkMain(this)"/></th>' . "\n";
            $out .= '<th>No.</th>' . "\n";
        }
        foreach (letter_columns() as $label) {
            $out .= '<th>' . letter_escape($label) . '</th>' . "\n";
        }
        if ($canManage) {
            $out .= '<th style="width: 80px !important;">Option</th>' . "\n";
        }
        $out .= '</tr>' . "\n";
        return $out;
    }
}

if (!function_exists('letter_render_rows')) {
    function letter_render_rows($rows, $canManage)
    {
        if (count($rows) === 0) {
            // checkbox + No. + Option only exist for managers
            $span = count(letter_columns()) + ($canManage ? 3 : 0);
            return '<tr><td colspan="' . $span . '" class="text-center">No records found.</td></tr>';
        }

        $out   = '';
        $count = 1;
        foreach ($rows as $row) {
            $id   = (int) $row['id'];
            $name = letter_escape(isset($row['location']) ? $row['location'] : '');
            $out .= '<tr>';
            if ($canManage) {
                $out .= '<td><input type="checkbox" class="chk_delete" value="' . $id . '" /></td>';
                $out .= '<td>' . $count++ . '</td>';
            }
            foreach (letter_columns() as $field => $label) {
                $out .= '<td>' . letter_escape(isset($row[$field]) ? $row[$field] : '') . '</td>';
            }
            if ($canManage) {
                $out .= '<td>'
                      . '<button class="btn btn-primary btn-xs btn-edit-item" data-id="' . $id . '" data-name="' . $name . '" title="Edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button> '
                      . '<button class="btn btn-info btn-xs btn-view-item" data-id="' . $id . '" data-name="' . $name . '" title="View"><i class="fa fa-eye" aria-hidden="true"></i></button>'
                      . '</td>';
            }
            $out .= '</tr>';
        }
        return $out;
    }
}

if (!function_exists('letter_filter_payload')) {
    /**
     * Everything the list needs for one filter selection: the table rows, the
     * summary counts and the current option lists. The page render and the
     * ajax endpoint both use this, so a filter change can never show stale
     * cards or stale dropdowns.
     */
    function letter_filter_payload($con, $params)
    {
        $options = array();
        foreach (array_keys(letter_filters()) as $key) {
            $options[$key] = letter_options($con, $key, $params);
        }

        return array(
            'rows'    => letter_fetch_rows($con, $params),
            'stats'   => letter_stat_values($con, $params),
            'filters' => $options,
        );
    }
}

if (!function_exists('letter_json_out')) {
    function letter_json_out($payload, $status = 200)
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload);
        exit;
    }
}
