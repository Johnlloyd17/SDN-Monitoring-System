<?php
/**
 * Shared renderer for the two participant views.
 *
 * Unlike the activity/tech4ed modules, both pages read the SAME table
 * (tblparticipant). Which page you are on is decided by the `project` column,
 * so the project is a fixed filter here and never a user-selectable one.
 */

/**
 * The 14 displayed columns. Both the header row and the body iterate this, so
 * they cannot drift apart.
 */
function participant_columns() {
    return array(
        'start'     => 'Start Date',
        'end'       => 'End Date',
        'activity'  => 'Activity Name',
        'indicator' => 'Indicators',
        'fullname'  => 'Fullname',
        'sex'       => 'Sex',
        'contact'   => 'Contact',
        'email'     => 'Email Address',
        'mode'      => 'Mode of Implementation',
        'agency'    => 'Agency',
        'sector'    => 'Target Sector',
        'project'   => 'Project',
        'person'    => 'Responsible Person',
        'remarks'   => 'Remarks',
    );
}

function participant_view_config($view) {
    $views = array(
        'cyber' => array(
            'project'  => 'Cybersecurity',
            'username' => 'cybersecuritysdn',
            'label'    => 'Cybersecurity',
            'filters'  => array('sector', 'mode', 'indicator', 'sex'),
            'export'   => 'exportcyber.php',
            // exportcyber.php has always received the SECTOR value under ?project=
            'exportParams' => array('sector' => 'project', 'mode' => 'mode',
                                    'indicator' => 'indicator', 'sex' => 'sex'),
            'cards'    => array(
                array('label' => 'Total Participants', 'icon' => 'participants_1.png', 'bg' => 'bg-red',   'size' => 54, 'report' => 'cyber_participant.php', 'where' => '',                                  'alias' => 'total_participants'),
                array('label' => 'Total Male',         'icon' => 'male_1.png',          'bg' => 'bg-blue',  'size' => 50, 'report' => 'cyber_male.php',        'where' => " AND sex = 'Male'",             'alias' => 'total_male'),
                array('label' => 'Total Female',       'icon' => 'female_1.png',        'bg' => 'bg-red',   'size' => 60, 'report' => 'cyber_female.php',      'where' => " AND sex = 'Female'",           'alias' => 'total_female'),
                array('label' => 'Total Face-to-Face', 'icon' => 'face-to-face_1.png',  'bg' => 'bg-yellow','size' => 53, 'report' => 'cyber_facetoface.php',  'where' => " AND mode = 'Face-to-Face'",     'alias' => 'total_face_to_face'),
                array('label' => 'Total Virtual',      'icon' => 'virtual_1.png',       'bg' => 'bg-blue',  'size' => 46, 'report' => 'cyber_virtual.php',     'where' => " AND mode = 'Virtual'",          'alias' => 'total_virtual'),
                array('label' => 'Total Awareness',    'icon' => 'awareness_1.png',     'bg' => 'bg-red',   'size' => 50, 'report' => 'cyber_awareness.php',   'where' => " AND indicator = 'Awareness'",    'alias' => 'total_awareness'),
                array('label' => 'Total Training',     'icon' => 'training_1.png',      'bg' => 'bg-blue',  'size' => 50, 'report' => 'cyber_training.php',    'where' => " AND indicator = 'Training'",     'alias' => 'total_training'),
                array('label' => 'Total Orientation',  'icon' => 'orientation_1.png',   'bg' => 'bg-yellow','size' => 60, 'report' => 'cyber_orientation.php', 'where' => " AND indicator = 'Orientation'",  'alias' => 'total_orientation'),
            ),
        ),
        'ilcdb' => array(
            'project'  => 'ILCDB',
            'username' => null,
            'label'    => 'ILCDB',
            'filters'  => array('sector'),
            'export'   => 'exportilcdb.php',
            'exportParams' => array('sector' => 'sector'),
            // The ILCDB cards were never links to a report page.
            'cards'    => array(
                array('label' => 'Total Participants', 'icon' => 'participants_1.png', 'bg' => 'bg-red',  'size' => 54, 'report' => null, 'where' => '',                     'alias' => 'total_participants'),
                array('label' => 'Male',               'icon' => 'male_1.png',        'bg' => 'bg-blue', 'size' => 50, 'report' => null, 'where' => " AND sex = 'Male'",   'alias' => 'total_male'),
                array('label' => 'Female',             'icon' => 'female_1.png',      'bg' => 'bg-red',  'size' => 60, 'report' => null, 'where' => " AND sex = 'Female'", 'alias' => 'total_female'),
            ),
        ),
    );

    return isset($views[$view]) ? $views[$view] : $views['cyber'];
}

/** Administrator, or the bureau account that owns this view. */
function participant_can_manage($view) {
    $cfg = participant_view_config($view);
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrator') {
        return true;
    }
    if ($cfg['username'] !== null
        && isset($_SESSION['username'])
        && $_SESSION['username'] === $cfg['username']) {
        return true;
    }
    return false;
}

/** Whitelisted filter keys for this view, in display order. */
function participant_filter_keys($view) {
    return participant_view_config($view)['filters'];
}

/**
 * Sanitised filter values for this view. Anything not in the whitelist for the
 * view is dropped, so a hostile ?mode[]=... cannot reach the query.
 */
function participant_filters($view, $post) {
    $filters = array();
    foreach (participant_filter_keys($view) as $key) {
        $filters[$key] = '';
        if (isset($post[$key]) && is_string($post[$key])) {
            $filters[$key] = trim($post[$key]);
            if (strlen($filters[$key]) > 225) {
                $filters[$key] = substr($filters[$key], 0, 225);
            }
        }
    }
    return $filters;
}

/**
 * WHERE fragment for the view + filters. $skip omits one filter (used when
 * populating that dropdown so it can offer the other selections' siblings).
 */
function participant_where($con, $view, $filters, $skip = '') {
    $cfg = participant_view_config($view);
    $sql = " WHERE project = '" . mysqli_real_escape_string($con, $cfg['project']) . "'";
    foreach (participant_filter_keys($view) as $key) {
        if ($key === $skip) {
            continue;
        }
        if (isset($filters[$key]) && $filters[$key] !== '') {
            $sql .= " AND `" . $key . "` = '" . mysqli_real_escape_string($con, $filters[$key]) . "'";
        }
    }
    return $sql;
}

/** Statistic cards. */
function participant_render_cards($con, $view, $filters) {
    $cfg = participant_view_config($view);
    $out = '';

    foreach ($cfg['cards'] as $card) {
        $sql = "SELECT COUNT(*) AS `" . $card['alias'] . "` FROM tblparticipant WHERE project = '"
             . mysqli_real_escape_string($con, $cfg['project']) . "'" . $card['where'];
        foreach (participant_filter_keys($view) as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $sql .= " AND `" . $key . "` = '" . mysqli_real_escape_string($con, $filters[$key]) . "'";
            }
        }

        $res = mysqli_query($con, $sql);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        $count = $row ? (int) $row[$card['alias']] : 0;

        $box  = '<div class="info-box">'
              .   '<span class="info-box-icon ' . $card['bg'] . '">'
              .     '<img src="icons/' . htmlspecialchars($card['icon'], ENT_QUOTES) . '" alt="' . htmlspecialchars($card['label'], ENT_QUOTES) . '" style="width: ' . (int) $card['size'] . 'px; height: ' . (int) $card['size'] . 'px;">'
              .   '</span>'
              .   '<div class="info-box-content">'
              .     '<span class="info-box-text">' . htmlspecialchars($card['label'], ENT_QUOTES) . '</span>'
              .     '<span class="info-box-number">' . $count . '</span>'
              .   '</div>'
              . '</div>';

        // Only a card that has a report to drill into is a link.
        if (!empty($card['report'])) {
            $box = '<a href="../participant/' . htmlspecialchars($card['report'], ENT_QUOTES) . '">' . $box . '</a>';
        }

        $out .= '<div class="col-md-3 col-sm-6 col-xs-12">' . $box . '</div>' . "\n";
    }

    return $out;
}

/** Filter selects. Each offers the values reachable under the OTHER filters. */
function participant_render_filters($con, $view, $filters) {
    $cfg  = participant_view_config($view);
    $meta = array(
        'sector'    => array('label' => 'Select Sector',                 'placeholder' => 'All Sectors',              'nonEmpty' => false),
        'mode'      => array('label' => 'Select Mode of Implementation', 'placeholder' => 'All Modes of Implementation', 'nonEmpty' => false),
        'indicator' => array('label' => 'Select Indicator',              'placeholder' => 'All Indicators',            'nonEmpty' => true),
        'sex'       => array('label' => 'Select Sex',                    'placeholder' => 'All Sexes',                 'nonEmpty' => true),
    );

    $out = '';
    foreach (participant_filter_keys($view) as $key) {
        $id       = $key . 'Select';
        $m        = $meta[$key];
        $selected = isset($filters[$key]) ? $filters[$key] : '';

        $sql = "SELECT DISTINCT `" . $key . "` AS v FROM tblparticipant WHERE project = '"
             . mysqli_real_escape_string($con, $cfg['project']) . "'";
        if ($m['nonEmpty']) {
            $sql .= " AND `" . $key . "` IS NOT NULL AND TRIM(`" . $key . "`) != ''";
        }
        foreach (participant_filter_keys($view) as $other) {
            if ($other === $key) {
                continue;
            }
            if (isset($filters[$other]) && $filters[$other] !== '') {
                $sql .= " AND `" . $other . "` = '" . mysqli_real_escape_string($con, $filters[$other]) . "'";
            }
        }
        $sql .= " ORDER BY v";

        $options = '<option value="">' . htmlspecialchars($m['placeholder'], ENT_QUOTES) . '</option>';
        $res = mysqli_query($con, $sql);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $val = $row['v'];
                $options .= '<option value="' . htmlspecialchars($val, ENT_QUOTES) . '"'
                         .  ($selected !== '' && $selected === $val ? ' selected' : '')
                         .  '>' . htmlspecialchars($val, ENT_QUOTES) . '</option>';
            }
        }

        $out .= '<div class="col-md-3 col-sm-6 col-xs-12">'
              .   '<div class="form-group">'
              .     '<label for="' . $id . '">' . htmlspecialchars($m['label'], ENT_QUOTES) . '</label>'
              .     '<select id="' . $id . '" name="' . $key . '" class="form-control filterSelect">' . $options . '</select>'
              .   '</div>'
              . '</div>';
    }

    return $out;
}

/** Table header. */
function participant_render_headers($view) {
    $canManage = participant_can_manage($view);
    $out = '';

    if ($canManage) {
        $out .= '<th style="width: 20px !important;"><input type="checkbox" class="cbxMain" onchange="checkMain(this)"/></th>' . "\n";
        $out .= '<th>No.</th>' . "\n";
    }
    foreach (participant_columns() as $label) {
        $out .= '<th>' . htmlspecialchars($label, ENT_QUOTES) . '</th>' . "\n";
    }
    if ($canManage) {
        $out .= '<th style="width: 80px !important;">Option</th>' . "\n";
    }

    return $out;
}

/** Table body. */
function participant_render_rows($con, $view, $filters, $canManage) {
    $sql = "SELECT * FROM tblparticipant"
         . participant_where($con, $view, $filters, '')
         . " ORDER BY `start` DESC";

    $res = mysqli_query($con, $sql);
    if (!$res) {
        return '<tr><td colspan="' . (count(participant_columns()) + ($canManage ? 3 : 0)) . '">'
             . 'Error: ' . htmlspecialchars(mysqli_error($con), ENT_QUOTES) . '</td></tr>';
    }

    $out   = '';
    $count = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $id  = (int) $row['id'];
        $out .= '<tr>';
        if ($canManage) {
            $out .= '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $id . '" /></td>';
            $out .= '<td>' . $count++ . '</td>';
        }
        foreach (array_keys(participant_columns()) as $col) {
            $out .= '<td>' . htmlspecialchars((string) $row[$col], ENT_QUOTES) . '</td>';
        }
        if ($canManage) {
            $name = htmlspecialchars((string) $row['fullname'], ENT_QUOTES);
            $out .= '<td>'
                  .   '<button class="btn btn-primary btn-xs btn-edit-item" data-id="' . $id . '" data-name="' . $name . '" title="Edit">'
                  .     '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'
                  .   '</button> '
                  .   '<button class="btn btn-info btn-xs btn-view-item" data-id="' . $id . '" data-name="' . $name . '" title="View">'
                  .     '<i class="fa fa-eye" aria-hidden="true"></i>'
                  .   '</button>'
                  . '</td>';
        }
        $out .= '</tr>';
    }

    if ($count === 1) {
        $out .= '<tr><td colspan="' . (count(participant_columns()) + ($canManage ? 3 : 0)) . '">No records found.</td></tr>';
    }

    return $out;
}
