<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterActivity = isset($_GET['activity']) ? mysqli_real_escape_string($con, $_GET['activity']) : '';
$filterSector = isset($_GET['sector']) ? mysqli_real_escape_string($con, $_GET['sector']) : '';

// Initialize the base SQL query
$query = "SELECT 
           start, end, activity, activity, fullname, sex, contact, 
            email, mode, agency, sector, project, person, remarks 
          FROM tblparticipant WHERE project = 'ILCDB'"; // Start with a base query that retrieves all records

// Apply activity filter if provided
if (!empty($filterActivity)) {
    $query .= " AND activity = '$filterActivity'";
}

// Apply sector filter if provided
if (!empty($filterSector)) {
    $query .= " AND sector = '$filterSector'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN ILCDB Participant Data.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Start Date', 'End Date', 'Activity Name', 'Fullname', 'Sex', 'Contact', 
    'Email Address', 'Mode of Implementation', 'Agency', 'Target Sector', 
    'Project', 'Responsible Person', 'Remarks'
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
