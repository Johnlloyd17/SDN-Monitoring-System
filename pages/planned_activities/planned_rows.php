<?php
// Shared planned-activities view configuration and rendering.
// All six planned_activities_*.php pages read the single table
// `targets_initiatives` and are distinguished only by the `project` column,
// so one renderer serves all six views.

if (!function_exists('planned_escape')) {
    function planned_escape($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('planned_view_config')) {
    /**
     * Returns the view config for a view key, or null when the key is unknown.
     * Callers must treat null as a hard failure rather than defaulting to a
     * project, otherwise a bad `view` parameter would silently write to the
     * wrong project.
     */
    function planned_view_config($view)
    {
        $views = array(
            'cyber' => array(
                'key'       => 'cyber',
                'project'   => 'Cybersecurity',
                'heading'   => 'Planned Activities - CSB',
                'username'  => 'cybersecuritysdn',
                'facet'     => 'type',
                'facetLabel'=> 'Type',
                'order'     => 'type',
            ),
            'elgu' => array(
                'key'       => 'elgu',
                'project'   => 'eLGU BPLS',
                'heading'   => 'Planned Activities - eLGU BPLS',
                'username'  => 'elgusdn',
                'facet'     => 'indicator',
                'facetLabel'=> 'Indicator',
                'order'     => 'indicator',
            ),
            'fw4a' => array(
                'key'       => 'fw4a',
                'project'   => 'FWFA',
                'heading'   => 'Planned Activities - FW4A',
                'username'  => 'fwfasdn',
                'facet'     => 'indicator',
                'facetLabel'=> 'Indicator',
                'order'     => 'indicator',
            ),
            'gecs' => array(
                'key'       => 'gecs',
                'project'   => 'GECS',
                'heading'   => 'Planned Activities - GECS',
                'username'  => 'gecssdn',
                'facet'     => 'indicator',
                'facetLabel'=> 'Indicator',
                'order'     => 'indicator',
            ),
            'iidb' => array(
                'key'       => 'iidb',
                'project'   => 'IIDB',
                'heading'   => 'Planned Activities - IIDB',
                'username'  => 'iidbsdn',
                'facet'     => 'indicator',
                'facetLabel'=> 'Indicator',
                'order'     => 'indicator',
            ),
            'ilcdb' => array(
                'key'       => 'ilcdb',
                'project'   => 'ILCDB',
                'heading'   => 'Planned Activities - ILCDB',
                'username'  => 'ilcdbsdn',
                'facet'     => 'indicator',
                'facetLabel'=> 'Indicator',
                'order'     => 'indicator',
            ),
        );

        return isset($views[$view]) ? $views[$view] : null;
    }
}

if (!function_exists('planned_view_keys')) {
    function planned_view_keys()
    {
        return array('cyber', 'elgu', 'fw4a', 'gecs', 'iidb', 'ilcdb');
    }
}

if (!function_exists('planned_columns')) {
    /**
     * Single source of truth for the table layout. Headers and rows are both
     * generated from this, so a column can no longer drift between the two.
     *
     * NOTE: 'mode' is mapped to the `mode` field. The original pages showed
     * `subproject` under the "Mode of Implementation" header while also
     * showing `subproject` under "Project", so the real mode value was never
     * displayed anywhere.
     */
    function planned_columns()
    {
        return array(
            'start'        => 'Start Date',
            'end'          => 'End Date',
            'project'      => 'Bureau',
            'subproject'   => 'Project',
            'indicator'    => 'Indicator',
            'activity'     => 'Activity Name',
            'training'     => 'Training Venue',
            'municipality' => 'Municipality/City',
            'barangay'     => 'Barangay',
            'district'     => 'District',
            'agency'       => 'Requesting Agency',
            'mode'         => 'Mode of Implementation',
            'sector'       => 'Target Sector',
            'person'       => 'Responsible Person',
            'resource'     => 'Name of Resource Person',
            'participants' => 'No. of Participants',
            'completers'   => 'No. of Completers',
            'male'         => 'Male',
            'female'       => 'Female',
            'approved'     => 'Approved Activity Design',
            'mov'          => 'Link to MOVs',
            'remarks'      => 'Remarks',
        );
    }
}

if (!function_exists('planned_can_manage')) {
    function planned_can_manage($cfg)
    {
        if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
            return false;
        }
        return $_SESSION['role'] === 'Administrator'
            || $_SESSION['username'] === $cfg['username'];
    }
}

if (!function_exists('planned_where')) {
    /**
     * Base WHERE clause for a view. $params carries the current filter
     * selection ('facet' holds the view's facet value, 'year' the year).
     */
    function planned_where($con, $cfg, $params, $extraSql = '', $extraVals = array())
    {
        $where  = " WHERE project = '" . mysqli_real_escape_string($con, $cfg['project']) . "'";
        $vals   = array();

        if (isset($params['facet']) && $params['facet'] !== '') {
            $where .= " AND `" . $cfg['facet'] . "` = '" . mysqli_real_escape_string($con, $params['facet']) . "'";
        }
        if (isset($params['year']) && $params['year'] !== '' && ctype_digit((string) $params['year'])) {
            $where .= " AND YEAR(start) = '" . (int) $params['year'] . "'";
        }
        if ($extraSql !== '') {
            $where .= ' AND ' . $extraSql;
            $vals   = $extraVals;
        }

        return array($where, $vals);
    }
}

if (!function_exists('planned_filter_params')) {
    /** Normalises the request filter params for a view. */
    function planned_filter_params($cfg, $source = null)
    {
        if ($source === null) { $source = $_POST; }
        $facet = isset($source[$cfg['facet']]) ? (string) $source[$cfg['facet']] : '';
        $year  = isset($source['year']) ? (string) $source['year'] : '';
        if ($year !== '' && !ctype_digit($year)) { $year = ''; }
        return array('facet' => $facet, 'year' => $year);
    }
}

if (!function_exists('planned_render_filters')) {
    /**
     * Facet + year selects. The original pages tried to make each list depend
     * on the other selection but appended the year condition to an undefined
     * variable, so the facet list silently ignored the selected year. Both
     * directions are wired up here.
     */
    function planned_render_filters($con, $cfg, $params)
    {
        $project = mysqli_real_escape_string($con, $cfg['project']);
        $facet   = $cfg['facet'];

        // Facet options, narrowed by the selected year.
        $facetSql = "SELECT DISTINCT `$facet` AS v FROM targets_initiatives
                     WHERE project = '$project' AND `$facet` IS NOT NULL AND `$facet` != ''";
        if ($params['year'] !== '') {
            $facetSql .= " AND YEAR(start) = '" . (int) $params['year'] . "'";
        }
        $facetSql .= " ORDER BY v ASC";

        // Year options, narrowed by the selected facet.
        $yearSql = "SELECT DISTINCT YEAR(start) AS y FROM targets_initiatives
                    WHERE project = '$project' AND start IS NOT NULL AND start != ''";
        if ($params['facet'] !== '') {
            $fv = mysqli_real_escape_string($con, $params['facet']);
            $yearSql .= " AND `$facet` = '$fv'";
        }
        $yearSql .= " ORDER BY y DESC";

        $facetOpts = mysqli_query($con, $facetSql);
        $yearOpts  = mysqli_query($con, $yearSql);

        $out  = '<form method="post" id="filterForm">' . "\n";
        $out .= '  <div class="row">' . "\n";

        $out .= '    <div class="col-md-3 col-sm-6 col-xs-12">' . "\n";
        $out .= '      <div class="form-group">' . "\n";
        $out .= '        <label for="plannedFacetSelect">Select ' . planned_escape($cfg['facetLabel']) . '</label>' . "\n";
        $out .= '        <select id="plannedFacetSelect" name="' . planned_escape($facet) . '" class="form-control" data-planned-filter="1">' . "\n";
        $out .= '          <option value="">All ' . planned_escape($cfg['facetLabel']) . 's</option>' . "\n";
        if ($facetOpts) {
            while ($o = mysqli_fetch_assoc($facetOpts)) {
                $sel  = ($params['facet'] === (string) $o['v']) ? ' selected' : '';
                $out .= '          <option value="' . planned_escape($o['v']) . '"' . $sel . '>' . planned_escape($o['v']) . '</option>' . "\n";
            }
        }
        $out .= '        </select>' . "\n";
        $out .= '      </div>' . "\n";
        $out .= '    </div>' . "\n";

        $out .= '    <div class="col-md-3 col-sm-6 col-xs-12">' . "\n";
        $out .= '      <div class="form-group">' . "\n";
        $out .= '        <label for="plannedYearSelect">Select Year</label>' . "\n";
        $out .= '        <select id="plannedYearSelect" name="year" class="form-control" data-planned-filter="1">' . "\n";
        $out .= '          <option value="">All Years</option>' . "\n";
        if ($yearOpts) {
            while ($o = mysqli_fetch_assoc($yearOpts)) {
                $sel  = ($params['year'] === (string) $o['y']) ? ' selected' : '';
                $out .= '          <option value="' . planned_escape($o['y']) . '"' . $sel . '>' . planned_escape($o['y']) . '</option>' . "\n";
            }
        }
        $out .= '        </select>' . "\n";
        $out .= '      </div>' . "\n";
        $out .= '    </div>' . "\n";

        $out .= '  </div>' . "\n";
        $out .= '</form>' . "\n";
        return $out;
    }
}

if (!function_exists('planned_render_headers')) {
    function planned_render_headers($canManage)
    {
        $out = '<tr>' . "\n";
        if ($canManage) {
            $out .= '<th style="width: 20px !important;"><input type="checkbox" class="cbxMain" onchange="checkMain(this)"/></th>' . "\n";
            $out .= '<th>No.</th>' . "\n";
        }
        foreach (planned_columns() as $label) {
            $out .= '<th>' . planned_escape($label) . '</th>' . "\n";
        }
        if ($canManage) {
            $out .= '<th style="width: 40px !important;">Option</th>' . "\n";
        }
        $out .= '</tr>' . "\n";
        return $out;
    }
}

if (!function_exists('planned_render_rows')) {
    function planned_render_rows($rows, $canManage)
    {
        $out   = '';
        $count = count($rows);
        if ($count === 0) {
            // checkbox + No. + Option only exist for managers
            $span = count(planned_columns()) + ($canManage ? 3 : 0);
            return '<tr><td colspan="' . $span . '" class="text-center">No records found.</td></tr>';
        }

        $n = 1;
        foreach ($rows as $row) {
            $out .= '<tr>';
            if ($canManage) {
                $id   = (int) $row['id'];
                $out .= '<td><input type="checkbox" class="chk_delete" value="' . $id . '" /></td>';
                $out .= '<td>' . $n++ . '</td>';
            }
            foreach (planned_columns() as $field => $label) {
                $out .= '<td>' . planned_escape(isset($row[$field]) ? $row[$field] : '') . '</td>';
            }
            if ($canManage) {
                $id   = (int) $row['id'];
                $name = planned_escape(isset($row['activity']) ? $row['activity'] : '');
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

if (!function_exists('planned_fetch_rows')) {
    function planned_fetch_rows($con, $cfg, $params)
    {
        list($where, $vals) = planned_where($con, $cfg, $params);
        $order = ($cfg['order'] === 'type' || $cfg['order'] === 'indicator')
            ? $cfg['order'] : 'indicator';
        $sql   = "SELECT * FROM targets_initiatives" . $where . " ORDER BY `$order` ASC, start ASC";
        $res   = mysqli_query($con, $sql);
        if (!$res) { return array(); }
        $rows = array();
        while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }
}

if (!function_exists('planned_json_out')) {
    function planned_json_out($payload, $status = 200)
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload);
        exit;
    }
}
