<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterIndicator = isset($_GET['indicator']) ? mysqli_real_escape_string($con, $_GET['indicator']) : '';
$filterSector = isset($_GET['sector']) ? mysqli_real_escape_string($con, $_GET['sector']) : '';
$filterMunicipality = isset($_GET['municipality']) ? mysqli_real_escape_string($con, $_GET['municipality']) : '';
$filterBarangay = isset($_GET['barangay']) ? mysqli_real_escape_string($con, $_GET['barangay']) : '';

// Initialize the base SQL query
$query = "SELECT 
            start, end, project, sector, indicator, activity, training, municipality, barangay, 
            district, agency, indicator, sector, person, resource, participants, completers, 
            male, female, approved, mov, remarks 
          FROM tblactivity 
          WHERE project = 'Cybersecurity'"; // Start with a base query that retrieves all records

// Apply filters if provided
if (!empty($filterIndicator)) {
    $query .= " AND indicator = '$filterIndicator'";
}
if (!empty($filterSector)) {
    $query .= " AND sector = '$filterSector'";
}
if (!empty($filterMunicipality)) {
    $query .= " AND municipality = '$filterMunicipality'";
}
if (!empty($filterBarangay)) {
    $query .= " AND barangay = '$filterBarangay'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN Cybersecurity Activity Data.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Start', 'End', 'Project', 'sector', 'Indicator', 'Activity', 'Training', 'Municipality', 
    'Barangay', 'District', 'Agency', 'indicator', 'sector', 'Person', 'Resource', 
    'Participants', 'Completers', 'Male', 'Female', 'Approved', 'MOV', 'Remarks'
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
