<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
require_once __DIR__ . '/letter_rows.php';
// Same reasoning as import.php: the export button is manager-only on the page,
// so a viewer must not be able to pull the file by typing the URL. This has to
// run before the CSV headers below are sent.
if (!letter_can_manage()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not authorized.';
    exit;
}
?>
<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterLocality = isset($_GET['locality']) ? mysqli_real_escape_string($con, $_GET['locality']) : '';
$filterBarangay = isset($_GET['barangay']) ? mysqli_real_escape_string($con, $_GET['barangay']) : '';
$filterType = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';
$filterYear = isset($_GET['year']) ? mysqli_real_escape_string($con, $_GET['year']) : '';

// Initialize the base SQL query
$query = "SELECT 
            locality, barangay, district, location, date, year, type, status, accomplished, remarks
          FROM locationrequests WHERE 1=1"; // Ensure 'location_name' is the correct column name

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
if (!empty($filterYear)) {
    $query .= " AND year = '$filterYear'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN_FWFA_Database.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Locality', 'Barangay', 'District', 'Location Name', 'Date Requested', 'Year', 'Type', 'Status', 
    'Accomplished Date', 'Remarks'
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