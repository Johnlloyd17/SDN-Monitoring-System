<?php
include "../connection.php";

$filterProjectFundSource = isset($_GET['project_fund_source']) ? trim($_GET['project_fund_source']) : '';
$filterPaymentStatus = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';
$filterYear = isset($_GET['year']) ? trim($_GET['year']) : '';

$query = "SELECT
            pr_no, activity_id, activity_name, link_to_file,
            project_fund_source, type_of_items_procured, amount,
            name_of_supplier, jo_po, link_to_attachments, personnel_in_charge,
            date_forwarded_to_ro, transmittal_report, payment_status, remarks
          FROM procurement_tracking WHERE 1=1";

if ($filterProjectFundSource !== '') {
    $query .= " AND project_fund_source = '" . mysqli_real_escape_string($con, $filterProjectFundSource) . "'";
}
if ($filterPaymentStatus !== '') {
    $query .= " AND payment_status = '" . mysqli_real_escape_string($con, $filterPaymentStatus) . "'";
}
if ($filterYear !== '' && ctype_digit($filterYear)) {
    $query .= " AND YEAR(date_forwarded_to_ro) = " . (int)$filterYear;
}

$query .= " ORDER BY id DESC";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Procurement_Records.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array(
   'PR No.', 'Activity ID', 'Activity Name', 'Link to File',
   'Project Fund Source', 'Type of Items', 'Amount', 'Supplier', 'JO/PO',
   'Link to Attachments', 'Personnel In-charge', 'Date Forwarded to RO',
   'Transmittal Report', 'Payment Status', 'Remarks'
));

$result = mysqli_query($con, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['amount'] !== null && $row['amount'] !== '') {
            $row['amount'] = number_format((float)$row['amount'], 2, '.', '');
        }
        fputcsv($output, $row);
    }
} else {
    echo "Error: " . mysqli_error($con);
}

fclose($output);
