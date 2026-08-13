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
                    if (count($data) >= 14) { // Ensure there are at least 19 columns
                        $start = mysqli_real_escape_string($con, $data[0]);
                        $end = mysqli_real_escape_string($con, $data[1]);
                        $activity = mysqli_real_escape_string($con, $data[2]);
                        $indicator = mysqli_real_escape_string($con, $data[3]);
                        $fullname = mysqli_real_escape_string($con, $data[4]);
                        $sex = mysqli_real_escape_string($con, $data[5]);
                        $contact = mysqli_real_escape_string($con, $data[6]);
                        $email = mysqli_real_escape_string($con, $data[7]);
                        $mode = mysqli_real_escape_string($con, $data[8]);
                        $agency = mysqli_real_escape_string($con, $data[9]);
                        $sector = mysqli_real_escape_string($con, $data[10]);
                        $project = mysqli_real_escape_string($con, $data[11]);
                        $person = mysqli_real_escape_string($con, $data[12]);
                        $remarks = mysqli_real_escape_string($con, $data[13]);

                        // Prepare the SQL query to insert the data
                        $query = "INSERT INTO tblparticipant (
                            start, end, activity, indicator, fullname, sex, contact, email, mode, 
                            agency, sector, project, person, remarks
                        ) VALUES (
                            '$start', '$end', '$activity','$indicator', '$fullname', '$sex', '$contact', '$email', '$mode', 
                            '$agency', '$sector', '$project', '$person', '$remarks'
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
