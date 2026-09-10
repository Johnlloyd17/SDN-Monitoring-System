<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterLocality = isset($_GET['locality']) ? mysqli_real_escape_string($con, $_GET['locality']) : '';
$filterBarangay = isset($_GET['barangay']) ? mysqli_real_escape_string($con, $_GET['barangay']) : '';
$filterType = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';
$filterStrategy = isset($_GET['strategy']) ? mysqli_real_escape_string($con, $_GET['strategy']) : '';

// Initialize the base SQL query
$query = "SELECT
            item_no, locality, barangay, district, transport_location, transport_type,
            site_locations, transfer_new_locations, remarks, site_code, nationwide_id, site_type,
            date_of_activation, current_date_of_acceptance, latitude, longitude,
            procurement_initiative, installation_type, uat, conforme, strategy, status, link_type,
            replacement_form_file, conforme_file, uat_file, additional_uat,
            site_coordinator_name, contact_details
          FROM tblfwfa WHERE 1=1"; // Ensure the table and column names match your database schema

// Apply filters if provided
if (!empty($filterLocality)) {
    $query .= " AND locality = '$filterLocality'";
}
if (!empty($filterBarangay)) {
    $query .= " AND barangay = '$filterBarangay'";
}
if (!empty($filterType)) {
    $query .= " AND site_type = '$filterType'";
}
if (!empty($filterStrategy)) {
    $query .= " AND strategy = '$filterStrategy'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN_FWFA_Database.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel renders accents correctly

// Output column headers for the CSV file (A..AC, matching the spreadsheet layout)
fputcsv($output, array(
   'Item No.', 'Locality', 'Barangay', 'District', 'Transport Location', 'Transport Type',
   'Site Locations', 'Transfer/New Locations', 'Remarks', 'Site Code', 'Nationwide ID', 'Site Type',
   'Date of Activation', 'Current Date of Acceptance', 'Latitude', 'Longitude',
   'Procurement Initiative', 'Installation Type', 'UAT', 'Conforme', 'Strategy', 'Status', 'Link Type',
   'Replacement Form File', 'Conforme File', 'UAT File', 'Additional UAT',
   'Name (Site Coordinators)', 'Contact Details'
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