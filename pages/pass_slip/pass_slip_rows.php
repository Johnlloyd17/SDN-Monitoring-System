<?php
/**
 * Shared Pass Slip / ICS table rendering.
 *
 * Included by pages/pass_slip/pass_slip.php (first page load) and by
 * ajax/pass_slip_data.php (in-place refresh after a save/delete) so both
 * produce identical markup. Requires a live $con mysqli connection.
 */

/**
 * Build the WHERE-clause filters used by the Pass Slip list.
 * Accepts either a $_POST-style array or a plain array.
 */
function ps_slip_filters($src = array())
{
    $f = array('status' => '', 'date_from' => '', 'date_to' => '', 'search' => '');
    if (isset($src['status'])) {
        $f['status'] = trim($src['status']);
    }
    if (isset($src['date_from'])) {
        $f['date_from'] = trim($src['date_from']);
    }
    if (isset($src['date_to'])) {
        $f['date_to'] = trim($src['date_to']);
    }
    if (isset($src['search_borrower'])) {
        $f['search'] = trim($src['search_borrower']);
    }
    return $f;
}

function ps_ics_filters($src = array())
{
    $f = array('date_from' => '', 'date_to' => '', 'search' => '');
    if (isset($src['ics_date_from'])) {
        $f['date_from'] = trim($src['ics_date_from']);
    }
    if (isset($src['ics_date_to'])) {
        $f['date_to'] = trim($src['ics_date_to']);
    }
    if (isset($src['ics_search'])) {
        $f['search'] = trim($src['ics_search']);
    }
    return $f;
}

/**
 * Run the Pass Slip list query and return the raw result set.
 */
function ps_slip_query($con, $filters)
{
    $check = @mysqli_query($con, "SELECT 1 FROM pass_slip LIMIT 0");
    if (!$check) {
        return null;
    }

    $sql = "SELECT
                ps.pass_slip_no,
                MIN(ps.id) AS first_id,
                MIN(ps.pullout_date) AS pullout_date,
                MIN(ps.requested_by_out) AS requested_by_out,
                MIN(ps.inspected_by_out) AS inspected_by_out,
                MIN(ps.approved_by_out) AS approved_by_out,
                MIN(ps.status) AS status,
                MIN(ps.purpose) AS purpose,
                MIN(ps.condition_out) AS condition_out,
                MIN(ps.return_date) AS return_date,
                MIN(ps.requested_by_return) AS requested_by_return,
                MIN(ps.inspected_by_return) AS inspected_by_return,
                MIN(ps.approved_by_return) AS approved_by_return,
                MIN(ps.condition_return) AS condition_return,
                MIN(ps.remarks) AS remarks,
                COUNT(*) AS item_count,
                GROUP_CONCAT(ps.item_description SEPARATOR ', ') AS items_summary
            FROM pass_slip ps WHERE 1=1";

    if ($filters['status'] !== '') {
        $status = mysqli_real_escape_string($con, $filters['status']);
        $sql .= " AND ps.status = '$status'";
    }
    if ($filters['date_from'] !== '') {
        $dateFrom = mysqli_real_escape_string($con, $filters['date_from']);
        $sql .= " AND ps.pullout_date >= '$dateFrom'";
    }
    if ($filters['date_to'] !== '') {
        $dateTo = mysqli_real_escape_string($con, $filters['date_to']);
        $sql .= " AND ps.pullout_date <= '$dateTo'";
    }
    if ($filters['search'] !== '') {
        $search = mysqli_real_escape_string($con, $filters['search']);
        $sql .= " AND (ps.requested_by_out LIKE '%$search%' OR ps.approved_by_out LIKE '%$search%' OR ps.pass_slip_no LIKE '%$search%')";
    }

    $sql .= " GROUP BY ps.pass_slip_no ORDER BY MIN(ps.pullout_date) DESC";

    return mysqli_query($con, $sql);
}

/**
 * Render the Pass Slip table rows.
 */
function ps_render_slip_rows($con, $filters, $isStaff)
{
    $result = ps_slip_query($con, $filters);
    if ($result === null) {
        return '<tr><td colspan="9" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> Pass Slip table not found. Please run the database migration first.</td></tr>';
    }

    ob_start();
    $counter = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $statusClass = '';
        $statusLabel = '';
        switch ($row['status']) {
            case 'borrowed':
                $statusClass = 'label label-warning';
                $statusLabel = 'Borrowed';
                break;
            case 'deployed':
                $statusClass = 'label label-primary';
                $statusLabel = 'Deployed';
                break;
            case 'returned':
                $statusClass = 'label label-success';
                $statusLabel = 'Returned';
                break;
            case 'overdue':
                $statusClass = 'label label-danger';
                $statusLabel = 'Overdue';
                break;
        }

        $slipNo = htmlspecialchars($row['pass_slip_no']);
        $itemsSummary = htmlspecialchars($row['items_summary']);
        $itemCount = $row['item_count'];

        echo '
                        <tr>
                            <td><input type="checkbox" name="chk_delete[]" class="chk_delete" value="' . $slipNo . '" /></td>
                            <td>' . $counter++ . '</td>
                            <td><strong>' . $slipNo . '</strong></td>
                            <td title="' . $itemsSummary . '">' . (strlen($itemsSummary) > 50 ? substr($itemsSummary, 0, 50) . '...' : $itemsSummary) . '</td>
                            <td>' . $itemCount . '</td>
                            <td>' . $row['pullout_date'] . '</td>
                            <td>' . htmlspecialchars($row['requested_by_out']) . '</td>
                            <td><span class="' . $statusClass . '">' . $statusLabel . '</span></td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">';

        if (!$isStaff) {
            echo '<button type="button" class="btn btn-primary btn-xs" onclick="openEditSlip(\'' . $slipNo . '\')" title="Edit Purpose / Remarks"><i class="fa fa-pencil"></i></button>';
        }

        if ($row['status'] == 'borrowed' && !$isStaff) {
            echo '<button type="button" class="btn btn-success btn-xs" onclick="openReturnModal(\'' . $slipNo . '\')" title="Process Return"><i class="fa fa-undo"></i></button>';
        }
        if (!$isStaff) {
            echo '<button type="button" class="btn btn-default btn-xs" onclick="openChangeStatus(\'' . $slipNo . '\')" title="Change Status"><i class="fa fa-exchange"></i></button>';
        }
        echo '
                                    <button type="button" class="btn btn-default btn-xs" onclick="openPrintSlip(\'' . $slipNo . '\')" title="Print Pass Slip"><i class="fa fa-print"></i></button>
                                    <button type="button" class="btn btn-info btn-xs" onclick="openAcknowledgement(\'' . $slipNo . '\')" title="Print Acknowledgement"><i class="fa fa-file-text"></i></button>
                                    <button type="button" class="btn btn-warning btn-xs" onclick="openUploadModal(\'' . $slipNo . '\')" title="Upload Scanned Copy"><i class="fa fa-paperclip"></i></button>
                                </div>
                            </td>
                        </tr>';
    }
    return ob_get_clean();
}

/**
 * Build the passSlipNo => row map consumed by the page JavaScript.
 */
function ps_build_slip_data($con, $filters)
{
    $map = array();
    $result = ps_slip_query($con, $filters);
    if ($result === null) {
        return $map;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $map[$row['pass_slip_no']] = $row;
    }
    return $map;
}

/**
 * Render the Inventory Custodian Slip table rows.
 */
function ps_render_ics_rows($con, $filters)
{
    $check = @mysqli_query($con, "SELECT 1 FROM ics LIMIT 0");
    if (!$check) {
        return '<tr><td colspan="7" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> ICS table not found. Please run the database migration first.</td></tr>';
    }

    $sql = "SELECT i.*, COUNT(it.id) AS item_count
            FROM ics i
            LEFT JOIN ics_items it ON it.ics_id = i.id
            WHERE 1=1";

    if ($filters['date_from'] !== '') {
        $icsDateFrom = mysqli_real_escape_string($con, $filters['date_from']);
        $sql .= " AND i.date_issued >= '$icsDateFrom'";
    }
    if ($filters['date_to'] !== '') {
        $icsDateTo = mysqli_real_escape_string($con, $filters['date_to']);
        $sql .= " AND i.date_issued <= '$icsDateTo'";
    }
    if ($filters['search'] !== '') {
        $icsSearch = mysqli_real_escape_string($con, $filters['search']);
        $sql .= " AND i.ics_no LIKE '%$icsSearch%'";
    }

    $sql .= " GROUP BY i.id ORDER BY i.date_issued DESC, i.id DESC";

    $icsResult = mysqli_query($con, $sql);
    if (!$icsResult) {
        return '<tr><td colspan="7" class="text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> ICS table not found. Please run the database migration first.</td></tr>';
    }

    ob_start();
    $icsCounter = 1;
    while ($row = mysqli_fetch_assoc($icsResult)) {
        $icsId = intval($row['id']);
        $icsNo = htmlspecialchars($row['ics_no']);
        $dateIssued = $row['date_issued'] ? date('F d, Y', strtotime($row['date_issued'])) : '';
        $dateReceived = !empty($row['date_received']) ? date('F d, Y', strtotime($row['date_received'])) : '';
        echo '
                                                                    <tr>
                                                                            <td><input type="checkbox" class="chk_delete_ics" name="ics_chk_delete[]" value="' . $icsId . '" onchange="updateIcsRecordsDeleteBtn()" /></td>
                                                                            <td>' . $icsCounter++ . '</td>
                                        <td><strong>' . $icsNo . '</strong></td>
                                        <td>' . intval($row['item_count']) . '</td>
                                        <td>' . $dateIssued . '</td>
                                        <td>&#8369; ' . number_format(floatval($row['total']), 2) . '</td>
                                        <td>' . ($dateReceived ? $dateReceived : '<span class="text-muted">Not yet filled</span>') . '</td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                                                <button type="button" class="btn btn-warning btn-xs" onclick="openUploadIcs(' . $icsNo . ')" title="Upload Scanned Copy"><i class="fa fa-paperclip"></i></button>
                                                <button type="button" class="btn btn-default btn-xs" onclick="openPrintIcs(' . $icsId . ')" title="Print ICS"><i class="fa fa-print"></i></button>
                                            </div>
                                        </td>
                                    </tr>';
    }
    return ob_get_clean();
}
