<?php
include "../connection.php"; // Include the database connection file

// Initialize counter
$counter = 1;

// Get filtering parameters from the query string (GET request)
$filterProject = isset($_GET['project']) ? mysqli_real_escape_string($con, $_GET['project']) : '';
$filterMode = isset($_GET['mode']) ? mysqli_real_escape_string($con, $_GET['mode']) : '';
$filterIndicator = isset($_GET['indicator']) ? mysqli_real_escape_string($con, $_GET['indicator']) : '';
$filterSex = isset($_GET['sex']) ? mysqli_real_escape_string($con, $_GET['sex']) : '';

// Initialize the base SQL query
$query = "SELECT 
           start, end, activity, indicator, fullname, sex, contact, 
           email, mode, agency, sector, project, person, remarks 
          FROM tblparticipant WHERE project = 'Cybersecurity'";

// Apply project filter if provided
if (!empty($filterProject)) {
    $query .= " AND sector = '$filterProject'";
}

// Apply mode filter if provided
if (!empty($filterMode)) {
    $query .= " AND mode = '$filterMode'";
}

// Apply indicator filter if provided
if (!empty($filterIndicator)) {
    $query .= " AND indicator = '$filterIndicator'";
}

// Apply sex filter if provided
if (!empty($filterSex)) {
    $query .= " AND sex = '$filterSex'";
}

// Order by start date in descending order
$query .= " ORDER BY start DESC";

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN Cybersecurity Participant Data.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Start Date', 'End Date', 'Activity Name', 'Indicators', 'Fullname', 'Sex', 'Contact', 
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
