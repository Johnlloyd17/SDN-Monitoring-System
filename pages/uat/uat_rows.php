<?php
/**
 * Shared UAT table rendering.
 *
 * Included by pages/uat/uat.php (first page load) and by ajax/uat_data.php
 * (in-place refresh after a save/delete) so both produce identical markup.
 * Keeping one copy means an add/edit/delete can refresh the table and the
 * statistics cards without reloading the page.
 */

if (!function_exists('uat_coord_fmt')) {
    function uat_coord_fmt($lat, $lng) {
        $parts = array();
        if ($lat !== null && $lat !== '' && !is_nan((float)$lat)) {
            $v = (float)$lat;
            $parts[] = number_format(abs($v), 6, '.', '') . ($v < 0 ? 'S' : 'N');
        }
        if ($lng !== null && $lng !== '' && !is_nan((float)$lng)) {
            $w = (float)$lng;
            $parts[] = number_format(abs($w), 6, '.', '') . ($w < 0 ? 'W' : 'E');
        }
        return $parts ? implode('  ', $parts) : '';
    }
}

/**
 * Echo the <tbody> rows for the UAT records table.
 *
 * @param mysqli  $con
 * @param string  $searchTerm  Optional free-text filter (municipality / strategy / transport location).
 * @param bool    $isAdmin     Whether to show the Edit button.
 */
function uat_render_rows($con, $searchTerm = '', $isAdmin = false) {
    $tableCheck = @mysqli_query($con, "SELECT 1 FROM uat LIMIT 0");
    if (!$tableCheck) {
        echo '<tr><td colspan="8" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> UAT table not found. Please run the database migration first.</td></tr>';
        return;
    }

    $query = "SELECT u.*, COUNT(it.id) AS item_count
              FROM uat u
              LEFT JOIN uat_items it ON it.uat_id = u.id
              WHERE 1=1";

    $searchTerm = trim((string)$searchTerm);
    if ($searchTerm !== '') {
        $esc = mysqli_real_escape_string($con, $searchTerm);
        $query .= " AND (u.municipality LIKE '%$esc%'
                          OR u.strategy LIKE '%$esc%'
                          OR u.transport_location LIKE '%$esc%')";
    }

    $query .= " GROUP BY u.id ORDER BY u.created_at DESC, u.id DESC";

    $result = mysqli_query($con, $query);
    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<tr><td colspan="8" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> No UAT records found.</td></tr>';
        return;
    }

    $counter = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $uatId = intval($row['id']);
        $municipality = htmlspecialchars($row['municipality']);
        $strategy = htmlspecialchars($row['strategy']);
        $transportLocation = htmlspecialchars($row['transport_location']);
        $coords = uat_coord_fmt($row['latitude'], $row['longitude']);
        $coordDisplay = $coords !== '' ? htmlspecialchars($coords) : '<span class="text-muted">&mdash;</span>';
        $detailMode = $isAdmin ? 'edit' : 'view';
        $editBtn = $isAdmin
            ? '<button type="button" class="btn btn-warning btn-xs" onclick="openUatDetail(' . $uatId . ', \'edit\')" title="Edit"><i class="fa fa-pencil"></i></button>'
            : '';

        echo '
        <tr>
            <td><input type="checkbox" class="chk_delete_uat" name="uat_chk_delete[]" value="' . $uatId . '" onchange="updateUatDeleteBtn()" /></td>
            <td>' . $counter++ . '</td>
            <td>' . $municipality . '</td>
            <td>' . $strategy . '</td>
            <td><a href="javascript:void(0);" class="uat-location-link" onclick="openUatDetail(' . $uatId . ', \'' . $detailMode . '\')" title="Open equipment list">' . $transportLocation . '</a></td>
            <td>' . $coordDisplay . '</td>
            <td>' . intval($row['item_count']) . '</td>
            <td>
                <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                    <button type="button" class="btn btn-default btn-xs" onclick="openUatDetail(' . $uatId . ', \'view\')" title="View"><i class="fa fa-eye"></i></button>
                    ' . $editBtn . '
                    <button type="button" class="btn btn-info btn-xs" onclick="openPrintUat(' . $uatId . ')" title="Print"><i class="fa fa-print"></i></button>
                </div>
            </td>
        </tr>';
    }
}

/**
 * Total / item / location counts for the statistics cards.
 *
 * @return array{total:int,items:int,locations:int,table_missing:bool}
 */
function uat_render_stats($con) {
    if (!@mysqli_query($con, "SELECT 1 FROM uat LIMIT 0")) {
        return array('total' => 0, 'items' => 0, 'locations' => 0, 'table_missing' => true);
    }
    $rTotal = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM uat"));
    $rItems = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) AS total FROM uat_items"));
    $rLocs  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(DISTINCT uat_id) AS total FROM uat_items"));
    return array(
        'total' => intval($rTotal['total']),
        'items' => intval($rItems['total']),
        'locations' => intval($rLocs['total']),
        'table_missing' => false,
    );
}
