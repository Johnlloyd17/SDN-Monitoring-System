<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterProject = isset($_GET['project']) ? mysqli_real_escape_string($con, $_GET['project']) : '';
$filterICS = isset($_GET['ics']) ? mysqli_real_escape_string($con, $_GET['ics']) : '';
$filterYear = isset($_GET['year']) ? mysqli_real_escape_string($con, $_GET['year']) : '';
$filterRemarks = isset($_GET['remarks']) ? mysqli_real_escape_string($con, $_GET['remarks']) : '';
$filterStatus = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';

// Initialize the base SQL query
$query = "SELECT 
            project, item, classification, quantity, unit, description, 
            received, property, ics, serial, date, officer, cost, life, 
            transferred, remarks, status 
          FROM inventory 
          WHERE project != ''"; // Start with a base query that retrieves all records

// Apply filters if provided
if (!empty($filterProject)) {
    $query .= " AND project = '$filterProject'";
}
if (!empty($filterICS)) {
    $query .= " AND ics = '$filterICS'";
}
if (!empty($filterYear)) {
    $query .= " AND YEAR(date) = '$filterYear'";
}
if (!empty($filterRemarks)) {
    $query .= " AND remarks = '$filterRemarks'";
}
if (!empty($filterStatus)) {
    $query .= " AND status = '$filterStatus'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN Property Records.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Project', 'Item No.', 'Classification', 'Quantity', 'Unit', 
    'Description/Model', 'Received From', 'Property Number', 
    'ICS/PAR Number', 'Serial Number', 'Date Acquired', 
    'Accountable Officer', 'Unit Cost', 'Estimated Useful Life', 
    'Received/Transferred', 'Remarks', 'Status'
));

// Execute the query
$result = mysqli_query($con, $query);

// Check if the query was successful and output the data to the CSV
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row); // Write each row to the CSV without the ID
    }
} else {
    // Handle query error and display a message
    echo "Error: " . mysqli_error($con);
}

// Close the output stream after all data has been written
fclose($output);
?>