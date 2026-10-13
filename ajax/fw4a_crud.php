<?php
require_once __DIR__ . '/../pages/auth_check.php'; require_auth_api();

header('Content-Type: application/json');

include '../pages/connection.php';

$photoDir = __DIR__ . '/../pages/fw4a_data/photo/';
if (!file_exists($photoDir)) {
    mkdir($photoDir, 0777, true);
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

function escf($con, $name) {
    return mysqli_real_escape_string($con, $_POST[$name] ?? '');
}

$VALID_STATUS = ['Active', 'Inactive', 'Ongoing', 'Assist', 'Terminated', 'Deactivated', 'Ongoing Acceptance', 'For Installation', 'For Transfer'];
$VALID_PROCUREMENT_INITIATIVE = ['Centrally Procured', 'Regional Procured'];
$VALID_INSTALLATION_TYPE = ['Region Initiated', 'Manage Service'];

function validateEnumField($value, $allowed, $label) {
    if ($value === '') {
        return null;
    }
    return in_array($value, $allowed, true) ? null : "Invalid $label value: \"$value\"";
}

function validateCoord($value, $label, $min, $max) {
    if ($value === '') {
        return null;
    }
    if (!is_numeric($value)) {
        return "Invalid $label value: \"$value\" (must be numeric)";
    }
    $n = floatval($value);
    if ($n < $min || $n > $max) {
        return "$label out of range: \"$value\" (expected $min..$max)";
    }
    return null;
}

/**
 * Write access follows the page's own gate: the page only draws the Add, Edit,
 * Delete, checkbox and photo controls for the Administrator role or the
 * fwfasdn account, so the endpoint refuses the same writes for everyone else.
 */
function fw4a_can_manage()
{
    return isset($_SESSION['role'])
        && ($_SESSION['role'] === 'Administrator' || $_SESSION['username'] === 'fwfasdn');
}

/** Same-session CSRF token; the page reads it through a script variable. */
function fw4a_csrf_token()
{
    if (empty($_SESSION['fw4a_csrf'])) {
        $_SESSION['fw4a_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['fw4a_csrf'];
}

function fw4a_csrf_ok()
{
    $sent = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    return isset($_SESSION['fw4a_csrf'])
        && $sent !== ''
        && hash_equals($_SESSION['fw4a_csrf'], $sent);
}

/** Whether another row already holds the given value in a natural-key column.
 * For site_code, checks composite (site_code, source_sheet).
 * For nationwide_id, checks globally (as before). */
function fw4a_dup_value($con, $field, $value, $excludeId = 0, $sourceSheet = '')
{
    if ($value === '') {
        return false;
    }
    $sql = "SELECT id FROM tblfwfa WHERE $field = ? AND id <> ?";
    $params = [$value, $excludeId];
    $types = 'si';
    
    // For site_code, also check source_sheet to allow same code in different sheets
    if ($field === 'site_code' && $sourceSheet !== '') {
        $sql .= " AND source_sheet = ?";
        $params[] = $sourceSheet;
        $types .= 's';
    }
    $sql .= " LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ? true : false;
}

$WRITE_ACTIONS = ['add', 'edit', 'delete', 'add_photo', 'remove_photo'];
if (in_array($action, $WRITE_ACTIONS, true)) {
    if (!fw4a_can_manage()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Not authorized.']);
        exit;
    }
    if (!fw4a_csrf_ok()) {
        http_response_code(419);
        echo json_encode(['success' => false, 'error' => 'Invalid or missing security token. Reload the page and try again.']);
        exit;
    }
}

if ($action === 'add') {
    $item_no = escf($con, 'txt_item_no');
    $locality = escf($con, 'txt_locality');
    $barangay = escf($con, 'txt_barangay');
    $district = escf($con, 'txt_district');
    $transport_location = escf($con, 'txt_transport_location');
    $transport_type = escf($con, 'txt_transport_type');
    $site_locations = escf($con, 'txt_site_locations');
    $transfer_new_locations = escf($con, 'txt_transfer_new_locations');
    $site_code = escf($con, 'txt_site_code');
    $nationwide_id = escf($con, 'txt_nationwide_id');
    $site_type = escf($con, 'txt_site_type');
    $date_of_activation = !empty($_POST['txt_date_of_activation']) ? "'" . escf($con, 'txt_date_of_activation') . "'" : 'NULL';
    $current_date_of_acceptance = !empty($_POST['txt_current_date_of_acceptance']) ? "'" . escf($con, 'txt_current_date_of_acceptance') . "'" : 'NULL';
    $latitude = escf($con, 'txt_latitude');
    $longitude = escf($con, 'txt_longitude');
    $procurement_initiative = escf($con, 'txt_procurement_initiative');
    $installation_type = escf($con, 'txt_installation_type');
    $uat = (isset($_POST['uat']) && $_POST['uat'] !== '0' && $_POST['uat'] !== '') ? 1 : 0;
    $conforme = (isset($_POST['conforme']) && $_POST['conforme'] !== '0' && $_POST['conforme'] !== '') ? 1 : 0;
    $strategy = escf($con, 'txt_strategy');
    $status = escf($con, 'txt_status');
    $link_type = escf($con, 'txt_link_type');
    $replacement_form_file = escf($con, 'txt_replacement_form_file');
    $conforme_file = escf($con, 'txt_conforme_file');
    $uat_file = escf($con, 'txt_uat_file');
    $additional_uat = escf($con, 'txt_additional_uat');
    $name = escf($con, 'txt_site_coordinator_name');
    $contact_details = escf($con, 'txt_contact_details');
    $remarks = escf($con, 'txt_remarks');
    $source_sheet = escf($con, 'txt_source_sheet');
    if ($source_sheet === '') $source_sheet = 'Manual Entry';

    $enumError = validateEnumField($status, $VALID_STATUS, 'Status')
        ?? validateEnumField($procurement_initiative, $VALID_PROCUREMENT_INITIATIVE, 'Procurement Initiative')
        ?? validateEnumField($installation_type, $VALID_INSTALLATION_TYPE, 'Installation Type')
        ?? validateCoord($latitude, 'Latitude', -90, 90)
        ?? validateCoord($longitude, 'Longitude', -180, 180);
    if ($enumError !== null) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => $enumError]);
        exit;
    }

    if (fw4a_dup_value($con, 'site_code', $site_code, 0, $source_sheet)) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => 'Site code already exists for this source.']);
        exit;
    }
    if (fw4a_dup_value($con, 'nationwide_id', $nationwide_id)) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => 'Nationwide ID already exists.']);
        exit;
    }

    // Status columns
    $status_2026_10_06_am = escf($con, 'txt_status_2026_10_06_am');
    $status_2026_10_06_pm = escf($con, 'txt_status_2026_10_06_pm');
    $status_2026_10_07_am = escf($con, 'txt_status_2026_10_07_am');
    $status_2026_10_07_pm = escf($con, 'txt_status_2026_10_07_pm');
    $status_2026_10_08_am = escf($con, 'txt_status_2026_10_08_am');
    $status_2026_10_08_pm = escf($con, 'txt_status_2026_10_08_pm');
    $status_2026_10_12_am = escf($con, 'txt_status_2026_10_12_am');
    $status_2026_10_12_pm = escf($con, 'txt_status_2026_10_12_pm');
    $status_2026_10_13_am = escf($con, 'txt_status_2026_10_13_am');
    $status_2026_10_13_pm = escf($con, 'txt_status_2026_10_13_pm');
    $status_2026_10_14_am = escf($con, 'txt_status_2026_10_14_am');
    $status_2026_10_14_pm = escf($con, 'txt_status_2026_10_14_pm');
    $status_2026_10_15_am = escf($con, 'txt_status_2026_10_15_am');
    $status_2026_10_15_pm = escf($con, 'txt_status_2026_10_15_pm');

    $new_renamed = escf($con, 'txt_new_renamed');
    $connection_type = escf($con, 'txt_connection_type');
    $down_time = escf($con, 'txt_down_time');
    $connectivity_status = escf($con, 'txt_connectivity_status');
    $area_classification = escf($con, 'txt_area_classification');
    $with_dict_free_wifi = escf($con, 'txt_with_dict_free_wifi');
    $last_monitored_at = escf($con, 'txt_last_monitored_at');
    $monitoring_notes = escf($con, 'txt_monitoring_notes');

    $query = "INSERT INTO tblfwfa (
        item_no, locality, barangay, district, transport_location, transport_type,
        site_code, new_renamed, site_locations, transfer_new_locations, remarks,
        nationwide_id, site_type,
        date_of_activation, current_date_of_acceptance, latitude, longitude,
        procurement_initiative, installation_type, uat, conforme, strategy, link_type,
        status_2026_10_06_am, status_2026_10_06_pm,
        status_2026_10_07_am, status_2026_10_07_pm,
        status_2026_10_08_am, status_2026_10_08_pm,
        status_2026_10_12_am, status_2026_10_12_pm,
        status_2026_10_13_am, status_2026_10_13_pm,
        status_2026_10_14_am, status_2026_10_14_pm,
        status_2026_10_15_am, status_2026_10_15_pm,
        contact_details, connection_type, down_time, connectivity_status,
        area_classification, with_dict_free_wifi, last_monitored_at, monitoring_notes,
        replacement_form_file, conforme_file, uat_file, additional_uat,
        site_coordinator_name, contact_details, remarks, source_sheet
    ) VALUES (
        " . ($item_no !== '' ? intval($item_no) : 'NULL') . ", '$locality', '$barangay', '$district', '$transport_location', '$transport_type',
        '$site_code', '$new_renamed', '$site_locations', '$transfer_new_locations', '$remarks',
        '$nationwide_id', '$site_type',
        $date_of_activation, $current_date_of_acceptance, " . ($latitude !== '' ? floatval($latitude) : 'NULL') . ", " . ($longitude !== '' ? floatval($longitude) : 'NULL') . ",
        " . ($procurement_initiative !== '' ? "'$procurement_initiative'" : 'NULL') . ", " . ($installation_type !== '' ? "'$installation_type'" : 'NULL') . ", $uat, $conforme, '$strategy', '$link_type',
        " . ($status_2026_10_06_am !== '' ? "'$status_2026_10_06_am'" : 'NULL') . ", " . ($status_2026_10_06_pm !== '' ? "'$status_2026_10_06_pm'" : 'NULL') . ",
        " . ($status_2026_10_07_am !== '' ? "'$status_2026_10_07_am'" : 'NULL') . ", " . ($status_2026_10_07_pm !== '' ? "'$status_2026_10_07_pm'" : 'NULL') . ",
        " . ($status_2026_10_08_am !== '' ? "'$status_2026_10_08_am'" : 'NULL') . ", " . ($status_2026_10_08_pm !== '' ? "'$status_2026_10_08_pm'" : 'NULL') . ",
        " . ($status_2026_10_12_am !== '' ? "'$status_2026_10_12_am'" : 'NULL') . ", " . ($status_2026_10_12_pm !== '' ? "'$status_2026_10_12_pm'" : 'NULL') . ",
        " . ($status_2026_10_13_am !== '' ? "'$status_2026_10_13_am'" : 'NULL') . ", " . ($status_2026_10_13_pm !== '' ? "'$status_2026_10_13_pm'" : 'NULL') . ",
        " . ($status_2026_10_14_am !== '' ? "'$status_2026_10_14_am'" : 'NULL') . ", " . ($status_2026_10_14_pm !== '' ? "'$status_2026_10_14_pm'" : 'NULL') . ",
        " . ($status_2026_10_15_am !== '' ? "'$status_2026_10_15_am'" : 'NULL') . ", " . ($status_2026_10_15_pm !== '' ? "'$status_2026_10_15_pm'" : 'NULL') . ",
        '$contact_details', '$connection_type', " . ($down_time !== '' ? "'$down_time'" : 'NULL') . ", '$connectivity_status',
        '$area_classification', " . ($with_dict_free_wifi !== '' ? "'$with_dict_free_wifi'" : 'NULL') . ", " . ($last_monitored_at !== '' ? "'$last_monitored_at'" : 'NULL') . ", '$monitoring_notes',
        " . ($replacement_form_file !== '' ? "'$replacement_form_file'" : 'NULL') . ", " . ($conforme_file !== '' ? "'$conforme_file'" : 'NULL') . ", " . ($uat_file !== '' ? "'$uat_file'" : 'NULL') . ", " . ($additional_uat !== '' ? "'$additional_uat'" : 'NULL') . ",
        '$name', '$contact_details', '$remarks', '$source_sheet'
    )";

    if (mysqli_query($con, $query)) {
        $action_log = 'Added Item ' . $locality;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => true, 'message' => 'Access point added successfully']);
    } else {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => 'Failed to add: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['hidden_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit;
    }
    $item_no = escf($con, 'txt_edit_item_no');
    $locality = escf($con, 'txt_edit_locality');
    $barangay = escf($con, 'txt_edit_barangay');
    $district = escf($con, 'txt_edit_district');
    $transport_location = escf($con, 'txt_edit_transport_location');
    $transport_type = escf($con, 'txt_edit_transport_type');
    $site_locations = escf($con, 'txt_edit_site_locations');
    $transfer_new_locations = escf($con, 'txt_edit_transfer_new_locations');
    $site_code = escf($con, 'txt_edit_site_code');
    $nationwide_id = escf($con, 'txt_edit_nationwide_id');
    $site_type = escf($con, 'txt_edit_site_type');
    $date_of_activation = !empty($_POST['txt_edit_date_of_activation']) ? "'" . escf($con, 'txt_edit_date_of_activation') . "'" : 'NULL';
    $current_date_of_acceptance = !empty($_POST['txt_edit_current_date_of_acceptance']) ? "'" . escf($con, 'txt_edit_current_date_of_acceptance') . "'" : 'NULL';
    $latitude = escf($con, 'txt_edit_latitude');
    $longitude = escf($con, 'txt_edit_longitude');
    $procurement_initiative = escf($con, 'txt_edit_procurement_initiative');
    $installation_type = escf($con, 'txt_edit_installation_type');
    $uat = (isset($_POST['uat']) && $_POST['uat'] !== '0' && $_POST['uat'] !== '') ? 1 : 0;
    $conforme = (isset($_POST['conforme']) && $_POST['conforme'] !== '0' && $_POST['conforme'] !== '') ? 1 : 0;
    $strategy = escf($con, 'txt_edit_strategy');
    $status = escf($con, 'txt_edit_status');
    $link_type = escf($con, 'txt_edit_link_type');
    $replacement_form_file = escf($con, 'txt_edit_replacement_form_file');
    $conforme_file = escf($con, 'txt_edit_conforme_file');
    $uat_file = escf($con, 'txt_edit_uat_file');
    $additional_uat = escf($con, 'txt_edit_additional_uat');
    $name = escf($con, 'txt_edit_site_coordinator_name');
    $contact_details = escf($con, 'txt_edit_contact_details');
    $remarks = escf($con, 'txt_edit_remarks');
    $source_sheet = escf($con, 'txt_edit_source_sheet');
    if ($source_sheet === '') $source_sheet = 'Manual Entry';

    // Status columns
    $status_2026_10_06_am = escf($con, 'txt_edit_status_2026_10_06_am');
    $status_2026_10_06_pm = escf($con, 'txt_edit_status_2026_10_06_pm');
    $status_2026_10_07_am = escf($con, 'txt_edit_status_2026_10_07_am');
    $status_2026_10_07_pm = escf($con, 'txt_edit_status_2026_10_07_pm');
    $status_2026_10_08_am = escf($con, 'txt_edit_status_2026_10_08_am');
    $status_2026_10_08_pm = escf($con, 'txt_edit_status_2026_10_08_pm');
    $status_2026_10_12_am = escf($con, 'txt_edit_status_2026_10_12_am');
    $status_2026_10_12_pm = escf($con, 'txt_edit_status_2026_10_12_pm');
    $status_2026_10_13_am = escf($con, 'txt_edit_status_2026_10_13_am');
    $status_2026_10_13_pm = escf($con, 'txt_edit_status_2026_10_13_pm');
    $status_2026_10_14_am = escf($con, 'txt_edit_status_2026_10_14_am');
    $status_2026_10_14_pm = escf($con, 'txt_edit_status_2026_10_14_pm');
    $status_2026_10_15_am = escf($con, 'txt_edit_status_2026_10_15_am');
    $status_2026_10_15_pm = escf($con, 'txt_edit_status_2026_10_15_pm');

    $new_renamed = escf($con, 'txt_edit_new_renamed');
    $connection_type = escf($con, 'txt_edit_connection_type');
    $down_time = escf($con, 'txt_edit_down_time');
    $connectivity_status = escf($con, 'txt_edit_connectivity_status');
    $area_classification = escf($con, 'txt_edit_area_classification');
    $with_dict_free_wifi = escf($con, 'txt_edit_with_dict_free_wifi');
    $last_monitored_at = escf($con, 'txt_edit_last_monitored_at');
    $monitoring_notes = escf($con, 'txt_edit_monitoring_notes');

    $enumError = validateEnumField($status, $VALID_STATUS, 'Status')
        ?? validateEnumField($procurement_initiative, $VALID_PROCUREMENT_INITIATIVE, 'Procurement Initiative')
        ?? validateEnumField($installation_type, $VALID_INSTALLATION_TYPE, 'Installation Type')
        ?? validateCoord($latitude, 'Latitude', -90, 90)
        ?? validateCoord($longitude, 'Longitude', -180, 180);
    if ($enumError !== null) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => $enumError]);
        exit;
    }

    // Only block an edit when the submitted value is a NEW one that another
    // row already holds. Keeping a stored value must keep working even when a
    // legacy duplicate exists, otherwise editing such a row would break.
    $stmt = mysqli_prepare($con, "SELECT site_code, nationwide_id, source_sheet FROM tblfwfa WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $cur = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    $curCode = $cur ? (string) $cur['site_code'] : '';
    $curNid  = $cur ? (string) $cur['nationwide_id'] : '';
    $curSheet = $cur ? (string) $cur['source_sheet'] : '';

    // Use current source_sheet if not provided
    if ($source_sheet === 'Manual Entry' && $curSheet !== '') {
        $source_sheet = $curSheet;
    }

    if ($site_code !== '' && $site_code !== $curCode && fw4a_dup_value($con, 'site_code', $site_code, $id, $source_sheet)) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => 'Site code already exists for this source.']);
        exit;
    }
    if ($nationwide_id !== '' && $nationwide_id !== $curNid && fw4a_dup_value($con, 'nationwide_id', $nationwide_id, $id)) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => 'Nationwide ID already exists.']);
        exit;
    }

    $query = "UPDATE tblfwfa SET
        item_no = " . ($item_no !== '' ? intval($item_no) : 'NULL') . ",
        locality = '$locality',
        barangay = '$barangay',
        district = '$district',
        transport_location = '$transport_location',
        transport_type = '$transport_type',
        site_code = '$site_code',
        new_renamed = '$new_renamed',
        site_locations = '$site_locations',
        transfer_new_locations = '$transfer_new_locations',
        remarks = '$remarks',
        nationwide_id = '$nationwide_id',
        site_type = '$site_type',
        date_of_activation = $date_of_activation,
        current_date_of_acceptance = $current_date_of_acceptance,
        latitude = " . ($latitude !== '' ? floatval($latitude) : 'NULL') . ",
        longitude = " . ($longitude !== '' ? floatval($longitude) : 'NULL') . ",
        procurement_initiative = " . ($procurement_initiative !== '' ? "'$procurement_initiative'" : 'NULL') . ",
        installation_type = " . ($installation_type !== '' ? "'$installation_type'" : 'NULL') . ",
        uat = $uat,
        conforme = $conforme,
        strategy = '$strategy',
        link_type = '$link_type',
        status_2026_10_06_am = " . ($status_2026_10_06_am !== '' ? "'$status_2026_10_06_am'" : 'NULL') . ",
        status_2026_10_06_pm = " . ($status_2026_10_06_pm !== '' ? "'$status_2026_10_06_pm'" : 'NULL') . ",
        status_2026_10_07_am = " . ($status_2026_10_07_am !== '' ? "'$status_2026_10_07_am'" : 'NULL') . ",
        status_2026_10_07_pm = " . ($status_2026_10_07_pm !== '' ? "'$status_2026_10_07_pm'" : 'NULL') . ",
        status_2026_10_08_am = " . ($status_2026_10_08_am !== '' ? "'$status_2026_10_08_am'" : 'NULL') . ",
        status_2026_10_08_pm = " . ($status_2026_10_08_pm !== '' ? "'$status_2026_10_08_pm'" : 'NULL') . ",
        status_2026_10_12_am = " . ($status_2026_10_12_am !== '' ? "'$status_2026_10_12_am'" : 'NULL') . ",
        status_2026_10_12_pm = " . ($status_2026_10_12_pm !== '' ? "'$status_2026_10_12_pm'" : 'NULL') . ",
        status_2026_10_13_am = " . ($status_2026_10_13_am !== '' ? "'$status_2026_10_13_am'" : 'NULL') . ",
        status_2026_10_13_pm = " . ($status_2026_10_13_pm !== '' ? "'$status_2026_10_13_pm'" : 'NULL') . ",
        status_2026_10_14_am = " . ($status_2026_10_14_am !== '' ? "'$status_2026_10_14_am'" : 'NULL') . ",
        status_2026_10_14_pm = " . ($status_2026_10_14_pm !== '' ? "'$status_2026_10_14_pm'" : 'NULL') . ",
        status_2026_10_15_am = " . ($status_2026_10_15_am !== '' ? "'$status_2026_10_15_am'" : 'NULL') . ",
        status_2026_10_15_pm = " . ($status_2026_10_15_pm !== '' ? "'$status_2026_10_15_pm'" : 'NULL') . ",
        contact_details = '$contact_details',
        connection_type = '$connection_type',
        down_time = " . ($down_time !== '' ? "'$down_time'" : 'NULL') . ",
        connectivity_status = '$connectivity_status',
        area_classification = '$area_classification',
        with_dict_free_wifi = " . ($with_dict_free_wifi !== '' ? "'$with_dict_free_wifi'" : 'NULL') . ",
        last_monitored_at = " . ($last_monitored_at !== '' ? "'$last_monitored_at'" : 'NULL') . ",
        monitoring_notes = '$monitoring_notes',
        replacement_form_file = " . ($replacement_form_file !== '' ? "'$replacement_form_file'" : 'NULL') . ",
        conforme_file = " . ($conforme_file !== '' ? "'$conforme_file'" : 'NULL') . ",
        uat_file = " . ($uat_file !== '' ? "'$uat_file'" : 'NULL') . ",
        additional_uat = " . ($additional_uat !== '' ? "'$additional_uat'" : 'NULL') . ",
        site_coordinator_name = '$name',
        contact_details = '$contact_details',
        remarks = '$remarks',
        source_sheet = '$source_sheet'
        WHERE id = $id";

    if (mysqli_query($con, $query)) {
        $action_log = 'Updated Item ' . $locality;
        mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . $_SESSION['role'] . "', NOW(), '$action_log')");
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => true, 'message' => 'Access point updated successfully']);
    } else {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'error' => 'Failed to update: ' . mysqli_error($con)]);
    }
    exit;
}

if ($action === 'delete') {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    if (!is_array($ids)) $ids = [$ids];

    $intIds = [];
    foreach ($ids as $rawId) {
        $id = intval($rawId);
        if ($id > 0) { $intIds[] = $id; }
    }

    if (empty($intIds)) {
        if (ob_get_level()) { ob_clean(); }
        echo json_encode(['success' => false, 'message' => 'No valid IDs provided']);
        exit;
    }

    $idList = implode(',', $intIds);
    $count = count($intIds);

    @mysqli_query($con, "DELETE FROM tblfwfa WHERE id IN ($idList)");

    $logMsg = "Batch deleted $count item(s) (IDs: $idList)";
    $logMsgEsc = mysqli_real_escape_string($con, $logMsg);
    @mysqli_query($con, "INSERT INTO tbllogs (user, logdate, action) VALUES ('" . mysqli_real_escape_string($con, $_SESSION['role']) . "', NOW(), '$logMsgEsc')");

    if (ob_get_level()) { ob_clean(); }
    echo json_encode(['success' => true, 'deleted' => $count, 'message' => "$count item(s) deleted"]);
    exit;
}

if ($action === 'add_photo') {
    $id = intval($_POST['hidden_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
        exit;
    }

    $added = 0;
    if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($tmp_name === '') continue;
            $milliseconds = round(microtime(true) * 1000);
            $name = $milliseconds . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", $_FILES['photos']['name'][$key]);
            $target = $photoDir . $name;
            if (move_uploaded_file($tmp_name, $target)) {
                mysqli_query($con, "INSERT INTO tblactivityphoto (activityid, filename) VALUES ('$id', '$name')");
                $added++;
            }
        }
    }

    echo json_encode(['success' => $added > 0, 'added' => $added, 'message' => "$added file(s) uploaded"]);
    exit;
}

if ($action === 'remove_photo') {
    $photoIds = isset($_POST['photo_ids']) ? $_POST['photo_ids'] : [];
    if (!is_array($photoIds)) $photoIds = [$photoIds];

    $removed = 0;
    foreach ($photoIds as $rawId) {
        $pid = intval($rawId);
        if ($pid <= 0) continue;

        $fileQ = mysqli_query($con, "SELECT filename FROM tblactivityphoto WHERE id = $pid");
        $fileRow = mysqli_fetch_assoc($fileQ);
        if ($fileRow) {
            $filePath = $photoDir . $fileRow['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            mysqli_query($con, "DELETE FROM tblactivityphoto WHERE id = $pid");
            $removed++;
        }
    }

    echo json_encode(['success' => $removed > 0, 'removed' => $removed, 'message' => "$removed file(s) removed"]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
