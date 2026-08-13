<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterLocality = isset($_GET['locality']) ? mysqli_real_escape_string($con, $_GET['locality']) : '';
$filterBarangay = isset($_GET['barangay']) ? mysqli_real_escape_string($con, $_GET['barangay']) : '';
$filterType = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';
$filterStrategy = isset($_GET['strategy']) ? mysqli_real_escape_string($con, $_GET['strategy']) : '';

// Initialize the base SQL query
$query = "SELECT 
            locality, barangay, district, transport_location, transport_type, locations,
            code, nationwide_id, type, date_of_activation, current_date_of_acceptance,
            latitude, longitude, strategy, status, remarks
          FROM tblfwfa WHERE 1=1"; // Ensure the table and column names match your database schema

// Apply filters if provided
if (!empty($filterLocality)) {
    $query .= " AND locality = '$filterLocality'";
}
if (!empty($filterBarangay)) {
    $query .= " AND barangay = '$filterBarangay'";
}
if (!empty($filterType)) {
    $query .= " AND type = '$filterType'";
}
if (!empty($filterStrategy)) {
    $query .= " AND strategy = '$filterStrategy'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN_FWFA_Database.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
   'Locality', 'Barangay', 'District', 'Transport Location', 'Transport Type', 'Locations',
   'Site Code', 'Nationwide ID', 'Site Type', 'Date of Activation', 'Date of Acceptance',
   'Latitude', 'Longitude', 'Strategy', 'Status', 'Remarks'
));

// Execute the query
$result = mysqli_query($con, $query);

// Check if the query was successful and output the data to the CSV
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Ensure only the columns selected in the query are written to the CSV
        fputcsv($output, $row); // Write each row to the CSV
    }
} else {
    // Handle query error and display a message
    echo "Error: " . mysqli_error($con);
}

// Close the output stream after all data has been written
fclose($output);
?>
