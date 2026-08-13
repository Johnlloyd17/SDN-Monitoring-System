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
                    if (count($data) >= 25) { // Ensure there are at least 24 columns
                        $province = mysqli_real_escape_string($con, $data[0]);
                        $district = mysqli_real_escape_string($con, $data[1]);
                        $municipality = mysqli_real_escape_string($con, $data[2]);
                        $lgu = mysqli_real_escape_string($con, $data[3]);
                        $class = mysqli_real_escape_string($con, $data[4]);
                        $system = mysqli_real_escape_string($con, $data[5]);
                        $action = mysqli_real_escape_string($con, $data[6]);
                        $businessyn = mysqli_real_escape_string($con, $data[7]);
                        $businessstatus = mysqli_real_escape_string($con, $data[8]);
                        $barangayyn = mysqli_real_escape_string($con, $data[9]);
                        $barangaystatus = mysqli_real_escape_string($con, $data[10]);
                        $buildingyn = mysqli_real_escape_string($con, $data[11]);
                        $buildingstatus = mysqli_real_escape_string($con, $data[12]);
                        $workingyn = mysqli_real_escape_string($con, $data[13]);
                        $workingstatus = mysqli_real_escape_string($con, $data[14]);
                        $bfpyn = mysqli_real_escape_string($con, $data[15]);
                        $bplyn = mysqli_real_escape_string($con, $data[16]);
                        $bplstatus = mysqli_real_escape_string($con, $data[17]);
                        $ecedulayn = mysqli_real_escape_string($con, $data[18]);
                        $ecedulastatus = mysqli_real_escape_string($con, $data[19]);
                        $elcryn = mysqli_real_escape_string($con, $data[20]);
                        $elcrstatus = mysqli_real_escape_string($con, $data[21]);
                        $enewsyn = mysqli_real_escape_string($con, $data[22]);
                        $enewsstatus = mysqli_real_escape_string($con, $data[23]);
                        $remark = mysqli_real_escape_string($con, $data[24]);

                        // Prepare the SQL query to insert the data
                        $query = "INSERT INTO tblbpls (
                            province, district, municipality, lgu, class, system, action, businessyn, businessstatus, 
                            barangayyn, barangaystatus, buildingyn, buildingstatus, workingyn, workingstatus, bfpyn, 
                            bplyn, bplstatus, ecedulayn, ecedulastatus, elcryn, elcrstatus, enewsyn, enewsstatus, remark
                        ) VALUES (
                            '$province', '$district', '$municipality', '$lgu', '$class', '$system', '$action', '$businessyn', 
                            '$businessstatus', '$barangayyn', '$barangaystatus', '$buildingyn', '$buildingstatus', '$workingyn', 
                            '$workingstatus', '$bfpyn', '$bplyn', '$bplstatus', '$ecedulayn', '$ecedulastatus', '$elcryn', 
                            '$elcrstatus', '$enewsyn', '$enewsstatus', '$remark'
                        )";

                        // Execute the query and check for success
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
