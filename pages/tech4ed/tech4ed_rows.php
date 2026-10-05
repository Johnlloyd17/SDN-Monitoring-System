<?php
/**
 * Shared Tech4Ed table rendering.
 *
 * Included by pages/tech4ed/tech4ed.php (first page load) and by
 * ajax/tech4ed_data.php (in-place refresh after a save/delete) so both
 * produce identical markup. Requires a live $con mysqli connection and an
 * active session (for the role-gated checkbox / counter / Option columns).
 */

function tech4ed_filters($src = array())
{
    $f = array('municipality' => '', 'barangay' => '', 'category' => '', 'year' => '');
    foreach (array_keys($f) as $k) {
        if (isset($src[$k])) {
            $f[$k] = trim($src[$k]);
        }
    }
    return $f;
}

/**
 * True when the current user sees the row-management columns.
 */
function tech4ed_can_manage()
{
    return isset($_SESSION['role'])
        && ($_SESSION['role'] === 'Administrator' || (isset($_SESSION['username']) && $_SESSION['username'] === 'fwfasdn'));
}

/**
 * Fixed base conditions for the per-sector Tech4Ed pages.
 * Whitelisted only - never build this from raw user input.
 */
function tech4ed_base_condition($view)
{
    $map = array(
        'all'           => "municipality != ''",
        'center'        => "category = 'DICT Provincial Training Center'",
        'lgu'           => "category = 'LGU'",
        'nga'           => "category = 'NGA'",
        'private'       => "category = 'Private'",
        'ris'           => "category = 'RIS'",
        'school'        => "category = 'School'",
        'operational'   => "operation = 'Operational'",
        'nonoperational'=> "operation = 'Non-Operational'",
    );
    return isset($map[$view]) ? $map[$view] : $map['all'];
}

/**
 * Run the Tech4Ed list query for the given filters.
 */
function tech4ed_query($con, $filters, $base = null)
{
    if ($base === null) {
        $base = tech4ed_base_condition('all');
    }

    $sql = "SELECT * FROM tbltech4ed WHERE " . $base;

    if ($filters['municipality'] !== '') {
        $sql .= " AND municipality = '" . mysqli_real_escape_string($con, $filters['municipality']) . "'";
    }
    if ($filters['barangay'] !== '') {
        $sql .= " AND barangay = '" . mysqli_real_escape_string($con, $filters['barangay']) . "'";
    }
    if ($filters['category'] !== '') {
        $sql .= " AND category = '" . mysqli_real_escape_string($con, $filters['category']) . "'";
    }
    if ($filters['year'] !== '') {
        $year = mysqli_real_escape_string($con, $filters['year']);
        $sql .= " AND YEAR(launch) = '$year'";
    }

    $sql .= " ORDER BY municipality ASC";

    $result = mysqli_query($con, $sql);
    if (!$result) {
        return null;
    }
    return $result;
}

/**
 * Render the Tech4Ed table rows.
 */
function tech4ed_render_rows($con, $filters, $canManage, $view = 'all')
{
    $result = tech4ed_query($con, $filters, tech4ed_base_condition($view));
    if ($result === null) {
        return '<tr><td class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> Could not load records.</td></tr>';
    }

    $e = function ($v) {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    };

    ob_start();
    $counter = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        if ($canManage) {
            echo '<td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . intval($row['id']) . '" /></td>';
            echo ' <td>' . $counter++ . '</td>';
        }
        echo '
                    <td>' . $e($row['region']) . '</td>
                    <td>' . $e($row['province']) . '</td>
                    <td>' . $e($row['district']) . '</td>
                    <td>' . $e($row['municipality']) . '</td>
                    <td>' . $e($row['barangay']) . '</td>
                    <td>' . $e($row['street']) . '</td>
                    <td>' . $e($row['location']) . '</td>
                    <td>' . $e($row['cname']) . '</td>
                    <td>' . $e($row['host']) . '</td>
                    <td>' . $e($row['category']) . '</td>
                    <td>' . $e($row['longitude']) . '</td>
                    <td>' . $e($row['latitude']) . '</td>
                    <td>' . $e($row['cmanager']) . '</td>
                    <td>' . $e($row['cemail']) . '</td>
                    <td>' . $e($row['cmobile']) . '</td>
                    <td>' . $e($row['clandline']) . '</td>
                    <td>' . $e($row['cgender']) . '</td>
                    <td>' . $e($row['amanager']) . '</td>
                    <td>' . $e($row['aemail']) . '</td>
                    <td>' . $e($row['amobile']) . '</td>
                    <td>' . $e($row['alandline']) . '</td>
                    <td>' . $e($row['agender']) . '</td>
                    <td>' . $e($row['launch']) . '</td>
                    <td>' . $e($row['registration']) . '</td>
                    <td>' . $e($row['operation']) . '</td>
                    <td>' . $e($row['visited']) . '</td>
                    <td>' . $e($row['desktop']) . '</td>
                    <td>' . $e($row['laptop']) . '</td>
                    <td>' . $e($row['printer']) . '</td>
                    <td>' . $e($row['scanner']) . '</td>
                    <td>' . $e($row['status']) . '</td>
                    <td>' . $e($row['network']) . '</td>
                    <td>' . $e($row['connectivity']) . '</td>
                    <td>' . $e($row['speed']) . '</td>
                    <td>' . $e($row['cmtmale']) . '</td>
                    <td>' . $e($row['cmtfemale']) . '</td>
                    <td>' . $e($row['straining']) . '</td>
                    <td>' . $e($row['etraining']) . '</td>
                    <td>' . $e($row['signing']) . '</td>
                    <td>' . $e($row['partner']) . '</td>
                    <td>' . $e($row['expiration']) . '</td>
                    <td>' . $e($row['donation']) . '</td>
                    <td>' . $e($row['datedonation']) . '</td>
                    <td>' . $e($row['tcms']) . '</td>
                    <td>' . $e($row['key_one']) . '</td>
                    <td>' . $e($row['identifier']) . '</td>';

        if ($canManage) {
            echo '<td>
                    <button class="btn btn-primary btn-xs btn-edit-item" data-id="' . intval($row['id']) . '" title="Edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                    <button class="btn btn-info btn-xs btn-view-item" data-id="' . intval($row['id']) . '" data-name="' . $e($row['barangay']) . '" title="View"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </td>';
        }
        echo '</tr>';
    }
    return ob_get_clean();
}
