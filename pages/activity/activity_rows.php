<?php
/**
 * Shared Activity table rendering for the six sector pages.
 *
 * Included by each pages/activity/<sector>.php (first page load) and by
 * ajax/activity_data.php (in-place refresh after a save/delete) so both
 * produce identical markup.
 *
 * Requires a live $con mysqli connection and an active session (the
 * checkbox / No. / Option columns are role-gated per sector account).
 *
 * Every sector page used to carry its own copy of the same SQL, which is how
 * the pages drifted apart. The per-page differences are now data, not code:
 * the bureau name, the sector account, which column the "project" select
 * really filters, and which optional column the table hides.
 */

function activity_views()
{
    return array(
        // secondColumn: filter shown next to the sector/project select
        // distinctCard:  4th info box, counted with COUNT(DISTINCT ...)
        'cyber' => array(
            'project'         => 'Cybersecurity',
            'username'        => 'cybersecuritysdn',
            'slug'            => 'cyber',
            'plannedSlug'     => 'cyber',
            'plannedLabel'    => 'cyber',
            'firstColumn'     => 'sector',
            'firstLabel'      => 'Select Sector',
            'firstAll'        => 'All Sectors',
            'secondColumn'    => 'indicator',
            'secondLabel'     => 'Select Indicator',
            'secondAll'       => 'All Indicators',
            'distinctCard'    => 'sector',
            'distinctLabel'   => 'Total Sectors',
            'hideIndicator'   => false,
        ),
        'elgu' => array(
            'project'         => 'eLGU BPLS',
            'username'        => 'elgusdn',
            'slug'            => 'elgu',
            'plannedSlug'     => 'elgu',
            'plannedLabel'    => 'eLGU',
            'firstColumn'     => 'sector',
            'firstLabel'      => 'Select Sector',
            'firstAll'        => 'All Sectors',
            'secondColumn'    => 'mode',
            'secondLabel'     => 'Select Mode of Implementation',
            'secondAll'       => 'All Mode of Implementation',
            'distinctCard'    => 'sector',
            'distinctLabel'   => 'Total Sectors',
            'hideIndicator'   => false,
        ),
        'fwfa' => array(
            'project'         => 'FWFA',
            'username'        => 'fwfasdn',
            'slug'            => 'fwfa',
            'plannedSlug'     => 'fw4a',
            'plannedLabel'    => 'FW4A',
            'firstColumn'     => 'sector',
            'firstLabel'      => 'Select Sector',
            'firstAll'        => 'All Sectors',
            'secondColumn'    => 'mode',
            'secondLabel'     => 'Select Mode of Implementation',
            'secondAll'       => 'All Mode of Implementation',
            'distinctCard'    => 'sector',
            'distinctLabel'   => 'Total Sectors',
            'hideIndicator'   => false,
        ),
        'gecs' => array(
            'project'         => 'GECS',
            'username'        => 'gecssdn',
            'slug'            => 'gecs',
            'plannedSlug'     => 'gecs',
            'plannedLabel'    => 'GECS',
            'firstColumn'     => 'sector',
            'firstLabel'      => 'Select Sector',
            'firstAll'        => 'All Sectors',
            'secondColumn'    => 'mode',
            'secondLabel'     => 'Select Mode of Implementation',
            'secondAll'       => 'All Mode of Implementation',
            'distinctCard'    => 'sector',
            'distinctLabel'   => 'Total Sectors',
            'hideIndicator'   => false,
        ),
        'iidb' => array(
            'project'         => 'IIDB',
            'username'        => 'iidbsdn',
            'slug'            => 'iidb',
            'plannedSlug'     => 'iidb',
            'plannedLabel'    => 'IIDB',
            'firstColumn'     => 'sector',
            'firstLabel'      => 'Select Sector',
            'firstAll'        => 'All Sectors',
            'secondColumn'    => 'mode',
            'secondLabel'     => 'Select Mode of Implementation',
            'secondAll'       => 'All Mode of Implementation',
            'distinctCard'    => 'sector',
            'distinctLabel'   => 'Total Sectors',
            'hideIndicator'   => false,
        ),
        'ilcdb' => array(
            'project'         => 'ILCDB',
            'username'        => 'ilcdbsdn',
            'slug'            => 'ilcdb',
            'plannedSlug'     => 'ilcdb',
        'plannedLabel'  => 'ILCDB',
            'firstColumn'     => 'subproject',
            'firstLabel'      => 'Select Project',
            'firstAll'        => 'All Projects',
            'secondColumn'    => 'indicator',
            'secondLabel'     => 'Select Indicator',
            'secondAll'       => 'All Indicators',
            'distinctCard'    => 'subproject',
            'distinctLabel'   => 'Total Subprojects',
            'hideIndicator'   => true,
        ),
    );
}

/**
 * Config for one sector page. Whitelisted only - an unknown view falls back
 * to the Cybersecurity page rather than being interpolated into SQL.
 */
function activity_view_config($view)
{
    $views = activity_views();
    return isset($views[$view]) ? $views[$view] : $views['cyber'];
}

/**
 * Normalise the four filter inputs. The "project" input is a misnomer in the
 * original markup: it carries a sector (or subproject) value, not a bureau.
 */
function activity_filters($view, $src = array())
{
    $cfg = activity_view_config($view);
    $f = array(
        'project'      => '',
        'second'       => '',
        'municipality' => '',
        'barangay'     => '',
    );
    if (isset($src['project']) && is_string($src['project'])) {
        $f['project'] = trim($src['project']);
    }
    if (isset($src[$cfg['secondColumn']]) && is_string($src[$cfg['secondColumn']])) {
        $f['second'] = trim($src[$cfg['secondColumn']]);
    }
    if (isset($src['municipality']) && is_string($src['municipality'])) {
        $f['municipality'] = trim($src['municipality']);
    }
    if (isset($src['barangay']) && is_string($src['barangay'])) {
        $f['barangay'] = trim($src['barangay']);
    }
    return $f;
}

/**
 * True when the current user sees the row-management columns and buttons.
 */
function activity_can_manage($view)
{
    $cfg = activity_view_config($view);
    return isset($_SESSION['role'])
        && ($_SESSION['role'] === 'Administrator'
            || (isset($_SESSION['username']) && $_SESSION['username'] === $cfg['username']));
}

/**
 * The bureau + filter conditions shared by the cards, the filter dropdowns
 * and the table. Column names come from the whitelist, values are escaped.
 */
function activity_where($con, $view, $filters, $skip = array())
{
    $cfg = activity_view_config($view);

    $sql = " WHERE project = '" . mysqli_real_escape_string($con, $cfg['project']) . "'";

    if ($filters['project'] !== '' && !in_array('project', $skip, true)) {
        $sql .= " AND " . $cfg['firstColumn'] . " = '"
              . mysqli_real_escape_string($con, $filters['project']) . "'";
    }
    if ($filters['second'] !== '' && !in_array('second', $skip, true)) {
        $sql .= " AND " . $cfg['secondColumn'] . " = '"
              . mysqli_real_escape_string($con, $filters['second']) . "'";
    }
    if ($filters['municipality'] !== '' && !in_array('municipality', $skip, true)) {
        $sql .= " AND municipality = '"
              . mysqli_real_escape_string($con, $filters['municipality']) . "'";
    }
    if ($filters['barangay'] !== '' && !in_array('barangay', $skip, true)) {
        $sql .= " AND barangay = '"
              . mysqli_real_escape_string($con, $filters['barangay']) . "'";
    }

    return $sql;
}

function activity_scalar($con, $sql, $default = 0)
{
    $result = mysqli_query($con, $sql);
    if (!$result) {
        return $default;
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        return $default;
    }
    $value = reset($row);
    return ($value === null || $value === '') ? $default : $value;
}

/**
 * The eight info boxes. The 4th box links to a drill-down report; ILCDB has
 * no subprojects report page, so that box renders without a link rather than
 * pointing at a file that does not exist.
 */
function activity_render_cards($con, $view, $filters)
{
    $cfg = activity_view_config($view);
    $where = activity_where($con, $view, $filters);
    $slug = $cfg['slug'];
    $e = function ($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    };

    $totalActivities = activity_scalar($con, "SELECT COUNT(*) FROM tblactivity" . $where);
    $totalMunicipalities = activity_scalar($con, "SELECT COUNT(DISTINCT municipality) FROM tblactivity" . $where);
    $totalBarangays = activity_scalar(
        $con,
        "SELECT COUNT(DISTINCT barangay) FROM tblactivity"
        . $where
        . " AND barangay IS NOT NULL AND barangay != ''"
    );
    $totalDistinct = activity_scalar(
        $con,
        "SELECT COUNT(DISTINCT " . $cfg['distinctCard'] . ") FROM tblactivity"
        . $where
        . " AND " . $cfg['distinctCard'] . " IS NOT NULL AND " . $cfg['distinctCard'] . " != ''"
    );
    $totalCompleters = activity_scalar($con, "SELECT SUM(completers) FROM tblactivity" . $where);
    $totalMale = activity_scalar($con, "SELECT SUM(male) FROM tblactivity" . $where);
    $totalFemale = activity_scalar($con, "SELECT SUM(female) FROM tblactivity" . $where);

    $distinctHref = '';
    if ($cfg['distinctCard'] === 'sector') {
        $distinctHref = '../activity/sectors_data_' . $slug . '.php';
    }

    ob_start();
    ?>
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/activities_data_<?php echo $e($slug); ?>.php"><div class="info-box">
                    <span class="info-box-icon bg-blue">
                        <img src="img/icons/activity-1.png" alt="Total Activities" style="width: auto; height: 50px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Activities</span>
                        <span class="info-box-number" id="totalParticipants"><?php echo $e($totalActivities); ?></span>
                    </div>
                </div>
            </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/municipalities_data_<?php echo $e($slug); ?>.php"><div class="info-box">
                    <span class="info-box-icon bg-red">
                        <img src="img/icons/municipality-1.png" alt="Total Activities" style="width: auto; height: 50px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Municipalities</span>
                        <span class="info-box-number"><?php echo $e($totalMunicipalities); ?></span>
                    </div>
                </div>
            </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../activity/barangays_data_<?php echo $e($slug); ?>.php"><div class="info-box">
                    <span class="info-box-icon bg-yellow">
                        <img src="img/icons/barangay-1.png" alt="Total Activities" style="width: auto; height: 60px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Barangay</span>
                        <span class="info-box-number"><?php echo $e($totalBarangays); ?></span>
                    </div>
                </div>
            </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
            <?php if ($distinctHref !== '') { ?><a href="<?php echo $e($distinctHref); ?>"><?php } ?><div class="info-box">
                    <span class="info-box-icon bg-blue">
                        <img src="img/icons/sector-1.png" alt="Total Activities" style="width: auto; height: 58px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $e($cfg['distinctLabel']); ?></span>
                        <span class="info-box-number"><?php echo $e($totalDistinct); ?></span>
                    </div>
                </div>
            <?php if ($distinctHref !== '') { ?></a><?php } ?>
            </div>
        </div> <!-- End of first row -->

        <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <a href="../planned_activities/planned_activities_<?php echo $e($cfg['plannedSlug']); ?>.php">
            <div class="info-box">
                <span class="info-box-icon bg-white">
                    <img src="img/logo/dict.png" alt="Total Activities" style="width: auto; height: 58px;">
                </span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-weight: bold;">Planned Activities - <?php echo $e($cfg['plannedLabel']); ?></span>
                    <p>
                     Targets and Initiatives in Surigao del Norte
                        <span style="padding-left: 5px;"></span>
                    </p>
                </div>
            </div>
        </a>
    </div>

            <div class="col-md-3 col-sm-6 col -xs-12">
            <div class="info-box">
                    <span class="info-box-icon bg-yellow">
                        <img src="img/icons/completers-1.png" alt="Total Activities" style="width: auto; height: 55px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Completers</span>
                        <span class="info-box-number"><?php echo $e($totalCompleters); ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-blue">
                        <img src="img/icons/male-1.png" alt="Total Activities" style="width: auto; height: 53px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Male Completers</span>
                        <span class="info-box-number"><?php echo $e($totalMale); ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red">
                        <img src="img/icons/female-1.png" alt="Total Activities" style="width: auto; height: 53px;">
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Female Completers</span>
                        <span class="info-box-number"><?php echo $e($totalFemale); ?></span>
                    </div>
                </div>
            </div>
        </div> <!-- End of second row -->
    <?php
    return ob_get_clean();
}

/**
 * One cascading dropdown. Each select lists the distinct values of its own
 * column narrowed by the *other* active filters, which is what the original
 * pages did.
 *
 * $key is the normalised filter key; $postName is the form field name. They
 * differ for the second dropdown, whose field is named after its column
 * ("indicator" / "mode") in the original markup.
 */
function activity_render_select($con, $view, $filters, $key, $postName, $column, $label, $allLabel, $selectId)
{
    // The dropdown a given filter controls must not filter itself. activity_where()
    // skips by logical key, not by column name.
    $skip = array($key);

    $where = activity_where($con, $view, $filters, $skip);
    $sql = "SELECT DISTINCT " . $column . " FROM tblactivity" . $where
         . " AND " . $column . " IS NOT NULL AND " . $column . " != ''";

    $selected = $filters[$key];
    $e = function ($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    };

    $options = '';
    $result = mysqli_query($con, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $value = $row[$column];
            $options .= '<option value="' . $e($value) . '"'
                      . ($selected !== '' && $selected === $value ? ' selected' : '') . '>'
                      . $e($value) . '</option>';
        }
    }

    return '<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="form-group">
        <label for="' . $e($selectId) . '">' . $e($label) . '</label>
        <select id="' . $e($selectId) . '" name="' . $e($postName) . '" class="form-control" onchange="this.form.submit()">
            <option value="">' . $e($allLabel) . '</option>'
        . $options . '
        </select>
    </div>
</div>';
}

function activity_render_filters($con, $view, $filters)
{
    $cfg = activity_view_config($view);

    // Keep the original element ids (indicatorSelect / modeSelect): the
    // inline export handler in each page reads the filter by id.
    $secondId = lcfirst($cfg['secondColumn']) . 'Select';

    $html = activity_render_select($con, $view, $filters, 'project', 'project', $cfg['firstColumn'], $cfg['firstLabel'], $cfg['firstAll'], 'projectSelect');
    $html .= activity_render_select($con, $view, $filters, 'second', $cfg['secondColumn'], $cfg['secondColumn'], $cfg['secondLabel'], $cfg['secondAll'], $secondId);
    $html .= activity_render_select($con, $view, $filters, 'municipality', 'municipality', 'municipality', 'Select Municipality', 'All Municipalities', 'municipalitySelect');
    $html .= activity_render_select($con, $view, $filters, 'barangay', 'barangay', 'barangay', 'Select Barangay', 'All Barangays', 'barangaySelect');
    return $html;
}

/**
 * The visible columns, in order. ILCDB's table omits Indicator even though it
 * still offers an Indicator filter.
 */
function activity_columns($view)
{
    $cfg = activity_view_config($view);
    $columns = array(
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
    if ($cfg['hideIndicator']) {
        unset($columns['indicator']);
    }
    return $columns;
}

function activity_render_headers($view, $canManage)
{
    $e = function ($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    };

    $html = '<tr>';
    if ($canManage) {
        $html .= '<th style="width: 20px !important;"><input type="checkbox" name="chk_delete[]" class="cbxMain" onchange="checkMain(this)"/></th>';
        $html .= '<th>No.</th>';
    }
    foreach (activity_columns($view) as $label) {
        $html .= '<th>' . $e($label) . '</th>';
    }
    if ($canManage) {
        $html .= '<th style="width: 40px !important;">Option</th>';
    }
    $html .= '</tr>';

    return $html;
}

function activity_render_rows($con, $view, $filters, $canManage)
{
    $sql = "SELECT * FROM tblactivity" . activity_where($con, $view, $filters)
         . " ORDER BY start DESC";

    $result = mysqli_query($con, $sql);
    if (!$result) {
        return '<tr><td class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> Could not load records.</td></tr>';
    }

    $e = function ($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    };

    $html = '';
    $counter = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>';
        if ($canManage) {
            $html .= '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . intval($row['id']) . '" /></td>';
            $html .= ' <td>' . $counter++ . '</td>';
        }
        foreach (activity_columns($view) as $column => $label) {
            $html .= '<td>' . $e(isset($row[$column]) ? $row[$column] : '') . '</td>';
        }
        if ($canManage) {
            $html .= '<td>
                    <button class="btn btn-primary btn-sm btn-edit-activity" data-id="' . intval($row['id']) . '" data-activity="' . $e($row['activity']) . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button>
                    <button class="btn btn-primary btn-sm btn-view-activity" data-id="' . intval($row['id']) . '" data-activity="' . $e($row['activity']) . '"><i class="fa fa-eye" aria-hidden="true"></i> View</button>
                </td>';
        }
        $html .= '</tr>';
    }
    return $html;
}
