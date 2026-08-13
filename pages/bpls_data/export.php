<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameter from the query string (GET request)
$filterSystem = isset($_GET['system']) ? mysqli_real_escape_string($con, $_GET['system']) : '';

// Initialize the base SQL query
$query = "SELECT 
    province, district, municipality, lgu, class, system, action, businessyn, businessstatus, 
    barangayyn, barangaystatus, buildingyn, buildingstatus, workingyn, workingstatus, bfpyn, 
    bplyn, bplstatus, ecedulayn, ecedulastatus, elcryn, elcrstatus, enewsyn, enewsstatus, remark
    FROM tblbpls WHERE 1=1"; // Ensure your table name is correct

// Apply system filter if provided
if (!empty($filterSystem)) {
    $query .= " AND system = '$filterSystem'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN eLGU BPLS Databaase.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Province', 'Congressional District', 'City/Municipality', 'LGU Name', 'Class',
    'System Provider', 'Remarks / Action Items', '(BP) Y/N', '(BP) Status',
    'Integration of (BC) Y/N', 'Integration of (BC) Status', '(BPCO) Y/N', '(BPCO) Status',
    '(WP) Y/N', '(WP) Status', 'Integration of (FSIC)', '(BPLS) Y/N', '(BPLS) Status',
    '(eCEDULA) Y/N', '(eCEDULA) Status', '(eLCR) Y/N', '(eLCR) Status', 'eNEWS Y/N',
    'eNEWS Status', 'Remarks'
));

// Execute the query
$result = mysqli_query($con, $query);

// Check if the query was successful and output the data to the CSV
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row); // Write each row to the CSV
    }
} else {
    // Handle query error and display a message
    echo "Error: " . mysqli_error($con);
}

// Close the output stream after all data has been written
fclose($output);
?>
