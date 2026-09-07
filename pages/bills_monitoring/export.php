<?php
include "../connection.php";

$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterType = isset($_GET['type_of_billing']) ? trim($_GET['type_of_billing']) : '';
$filterLocation = isset($_GET['location_office']) ? trim($_GET['location_office']) : '';
$filterDateFrom = isset($_GET['date_from']) ? mysqli_real_escape_string($con, $_GET['date_from']) : '';
$filterDateTo = isset($_GET['date_to']) ? mysqli_real_escape_string($con, $_GET['date_to']) : '';

$query = "SELECT
            date_received, type_of_billing, link_to_file, amount,
            location_office, due_date, disconnection_date, status, date_paid,
            remarks, link_to_or
          FROM bills_monitoring WHERE 1=1";

if ($filterStatus === '1' || $filterStatus === '0') {
    $query .= " AND status = " . (int)$filterStatus;
}
if ($filterType !== '' && in_array($filterType, ['Water Bill','Internet Bill','Electricity Bill'])) {
    $query .= " AND type_of_billing = '" . mysqli_real_escape_string($con, $filterType) . "'";
}
if ($filterLocation === '__none__') {
    $query .= " AND location_office IS NULL";
} elseif ($filterLocation !== '' && in_array($filterLocation, ['SDN Provincial Office','SDN Hill Relay Station'])) {
    $query .= " AND location_office = '" . mysqli_real_escape_string($con, $filterLocation) . "'";
}
if (!empty($filterDateFrom)) {
    $query .= " AND date_received >= '$filterDateFrom'";
}
if (!empty($filterDateTo)) {
    $query .= " AND date_received <= '$filterDateTo'";
}

$query .= " ORDER BY date_received DESC, id DESC";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Bills_Monitoring.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array(
   'Date Received', 'Type of Billing', 'Link to File', 'Amount',
   'Location/Office', 'Due Date', 'Disconnection Date', 'Status', 'Date Paid',
   'Remarks', 'Link to OR'
));

$result = mysqli_query($con, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['status'] = $row['status'] == 1 ? 'Paid' : 'Unpaid';
        fputcsv($output, $row);
    }
} else {
    echo "Error: " . mysqli_error($con);
}

fclose($output);
