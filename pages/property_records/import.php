<?php
include "../connection.php";

// Function to check if the file extension is valid
function isValidFileExtension($filename) {
    $validExtensions = ['csv', 'xls', 'xlsx'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $validExtensions);
}

// Check if a file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filename = $_FILES['file']['name'];

    // Validate file extension
    if (isValidFileExtension($filename)) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fileTmpName = $_FILES['file']['tmp_name'];

        if ($extension === 'csv') {
            $handle = fopen($fileTmpName, "r");
            if ($handle !== false) {
                // Skip header row
                $header = fgetcsv($handle);
                $success = true;
                $error = '';

                while (($data = fgetcsv($handle)) !== false) {
                    // Check if the data array matches the expected number of columns
                    if (count($data) >= 16) { // Ensure there are at least 16 columns based on the new data structure
                        $project = mysqli_real_escape_string($con, $data[0]);
                        $item = mysqli_real_escape_string($con, $data[1]);
                        $classification = mysqli_real_escape_string($con, $data[2]);
                        $quantity = mysqli_real_escape_string($con, $data[3]);
                        $unit = mysqli_real_escape_string($con, $data[4]);
                        $description = mysqli_real_escape_string($con, $data[5]);
                        $received = mysqli_real_escape_string($con, $data[6]);
                        $property = mysqli_real_escape_string($con, $data[7]);
                        $ics = mysqli_real_escape_string($con, $data[8]);
                        $serial = mysqli_real_escape_string($con, $data[9]);
                        $date = mysqli_real_escape_string($con, $data[10]);
                        $officer = mysqli_real_escape_string($con, $data[11]);
                        $cost = mysqli_real_escape_string($con, $data[12]);
                        $life = mysqli_real_escape_string($con, $data[13]);
                        $transferred = mysqli_real_escape_string($con, $data[14]);
                        $remarks = mysqli_real_escape_string($con, $data[15]);

                        // Insert into the inventory table
                        $query = "INSERT INTO inventory (
                            project, item, classification, quantity, unit, description, 
                            received, property, ics, serial, date, officer, cost, life, 
                            transferred, remarks
                        ) VALUES (
                            '$project', '$item', '$classification', '$quantity', '$unit', 
                            '$description', '$received', '$property', '$ics', '$serial', 
                            '$date', '$officer', '$cost', '$life', '$transferred', '$remarks'
                        )";

                        if (!mysqli_query($con, $query)) {
                            $success = false;
                            $error = mysqli_error($con);
                            break;
                        }
                    } else {
                        $success = false;
                        $error = 'CSV file does not have the correct number of columns.';
                        break;
                    }
                }
                fclose($handle);

                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => $error]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Error opening the CSV file.']);
            }
        } else {
            // Handling for XLS and XLSX files would require libraries such as PhpSpreadsheet.
            echo json_encode(['success' => false, 'error' => 'XLS and XLSX file handling is not implemented.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid file format. Only CSV, XLS, and XLSX files are allowed.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error.']);
}
?>
