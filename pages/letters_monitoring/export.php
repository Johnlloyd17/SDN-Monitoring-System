<?php
include "../connection.php";

$filterType = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';
$filterForResponse = isset($_GET['for_response']) ? mysqli_real_escape_string($con, $_GET['for_response']) : '';
$filterDateFrom = isset($_GET['date_from']) ? mysqli_real_escape_string($con, $_GET['date_from']) : '';
$filterDateTo = isset($_GET['date_to']) ? mysqli_real_escape_string($con, $_GET['date_to']) : '';

$query = "SELECT
            date, type, subject, fw4a, link_incoming, for_response,
            date_responded, link_outgoing, responsible_person, who_attended,
            remarks, post_activity_report
          FROM letters_monitoring WHERE 1=1";

if (!empty($filterType) && in_array($filterType, ['Incoming','Outgoing'])) {
    $query .= " AND type = '$filterType'";
}
if (!empty($filterForResponse) && in_array($filterForResponse, ['Y','N'])) {
    $query .= " AND for_response = '$filterForResponse'";
}
if (!empty($filterDateFrom)) {
    $query .= " AND date >= '$filterDateFrom'";
}
if (!empty($filterDateTo)) {
    $query .= " AND date <= '$filterDateTo'";
}

$query .= " ORDER BY date DESC, id DESC";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Letters_Monitoring.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array(
   'Date', 'Type', 'Subject', 'FW4A', 'Drive link to File "Incoming"',
   'For Response?', 'Date Responded/Sent', 'Drive link to File "Outgoing"',
   'Responsible Person', 'If meeting/events, who attended?', 'Remarks', 'Post Activity Report'
));

$result = mysqli_query($con, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
} else {
    echo "Error: " . mysqli_error($con);
}

fclose($output);
