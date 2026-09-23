<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php
include "../connection.php"; // Include the database connection file

// Get filtering parameters from the query string (GET request)
$filterProject = isset($_GET['project']) ? mysqli_real_escape_string($con, $_GET['project']) : '';
$filterYear = isset($_GET['year']) ? mysqli_real_escape_string($con, $_GET['year']) : '';
$filterRemarks = isset($_GET['remarks']) ? mysqli_real_escape_string($con, $_GET['remarks']) : '';

// Initialize the base SQL query (column order matches the CSV header order)
$query = "SELECT
            project, item, quantity, unit, description,
            serial, cost, total_cost, date, received,
            inventory_item_no, assigned_to, life, remarks
          FROM inventory
          WHERE project != ''"; // Start with a base query that retrieves all records

// Apply filters if provided
if (!empty($filterProject)) {
    $query .= " AND project = '$filterProject'";
}
if (!empty($filterYear)) {
    $query .= " AND YEAR(date) = '$filterYear'";
}
if (!empty($filterRemarks)) {
    $query .= " AND remarks = '$filterRemarks'";
}

// Set headers to trigger download as a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=DICT-SDN Property Records.csv');

// Open output stream for writing CSV data
$output = fopen('php://output', 'w');

// Output column headers for the CSV file
fputcsv($output, array(
    'Project', 'Item No.', 'Quantity', 'Unit', 'Description',
    'Serial Number', 'Unit Cost', 'Total Cost', 'Date Acquired', 'Received From',
    'Inventory Item no.', 'Assigned/Deployed', 'Estimated Useful Life', 'Remarks'
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