<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterMunicipality = isset($_GET['municipality']) ? mysqli_real_escape_string($con, $_GET['municipality']) : '';

// Initialize the base SQL query
$query = "SELECT 
            region, province, district, municipality, barangay, street, location, cname, host, category, longitude, latitude, 
    cmanager, cemail, cmobile, clandline, cgender, amanager, aemail, amobile, alandline, agender, launch, registration, 
    operation, visited, desktop, laptop, printer, scanner, status, network, connectivity, speed, cmtmale, cmtfemale, 
    straining, etraining, signing, partner, expiration, donation, datedonation, tcms, key_one, identifier
          FROM tbltech4ed WHERE 1=1"; // Start with a base query that retrieves all records

// Apply municipality filter if provided
if (!empty($filterMunicipality)) {
    $query .= " AND municipality = '$filterMunicipality'";
}


// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN Cybersecurity Activity Data.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Region', 'Province', 'District', 'Municipality/City', 'Barangay', 'Street Address', 'Specific Center Location', 
    'Center Name', 'Host', 'Category', 'Longitude', 'Latitude', 'Center Managers Name', 'Email', 'Mobile', 
    'Landline', 'Gender', 'Assistant Center Managers Name', 'Email', 'Mobile', 'Landline', 
    'AM Gender', 'Date of Launching', 'Date of Platform Registration', 'Operational Status', 'Date Last Visited', 
    '# of Functional Desktop Units', '# of Functional Laptop Units', '# of Functional Printer Units', '# of Functional Scanner', 
    'Status', 'Types of Network', 'Internet Connectivity', 'Internet Speed', 'CMT (# of pax) Male', 'CMT (# of pax) Female', 
    'Start Date of Training', 'End Date of Training', 'Date of Signing', 'Partner', 'Expiration', 'Type of Donation', 
    'Date of Donation', 'TCMS', 'Key', 'Identifier'
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
