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

    // Check file extension
    if (isValidFileExtension($filename)) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fileTmpName = $_FILES['file']['tmp_name'];

        if ($extension === 'csv') {
            $handle = fopen($fileTmpName, "r");
            if ($handle !== false) {
                // Skip header row if exists
                $header = fgetcsv($handle);
                $success = true;
                while (($data = fgetcsv($handle)) !== false) {
                    // Check if the data array matches the expected number of columns
                    if (count($data) >= 15) { // Ensure there are at least 15 columns
                        $region = mysqli_real_escape_string($con, $data[0]);
                        $province = mysqli_real_escape_string($con, $data[1]);
                        $district = mysqli_real_escape_string($con, $data[2]);
                        $municipality = mysqli_real_escape_string($con, $data[3]);
                        $barangay = mysqli_real_escape_string($con, $data[4]);
                        $street = mysqli_real_escape_string($con, $data[5]);
                        $location = mysqli_real_escape_string($con, $data[6]);
                        $cname = mysqli_real_escape_string($con, $data[7]);
                        $host = mysqli_real_escape_string($con, $data[8]);
                        $category = mysqli_real_escape_string($con, $data[9]);
                        $longitude = mysqli_real_escape_string($con, $data[10]);
                        $latitude = mysqli_real_escape_string($con, $data[11]);
                        $cmanager = mysqli_real_escape_string($con, $data[12]);
                        $cemail = mysqli_real_escape_string($con, $data[13]);
                        $cmobile = mysqli_real_escape_string($con, $data[14]);
                        $clandline = mysqli_real_escape_string($con, $data[15]);
                        $cgender = mysqli_real_escape_string($con, $data[16]);
                        $amanager = mysqli_real_escape_string($con, $data[17]);
                        $aemail = mysqli_real_escape_string($con, $data[18]);
                        $amobile = mysqli_real_escape_string($con, $data[19]);
                        $alandline = mysqli_real_escape_string($con, $data[20]);
                        $agender = mysqli_real_escape_string($con, $data[21]);
                        $launch = mysqli_real_escape_string($con, $data[22]);
                        $registration = mysqli_real_escape_string($con, $data[23]);
                        $operation = mysqli_real_escape_string($con, $data[24]);
                        $visited = mysqli_real_escape_string($con, $data[25]);
                        $desktop = mysqli_real_escape_string($con, $data[26]);
                        $laptop = mysqli_real_escape_string($con, $data[27]);
                        $printer = mysqli_real_escape_string($con, $data[28]);
                        $scanner = mysqli_real_escape_string($con, $data[29]);
                        $status = mysqli_real_escape_string($con, $data[30]);
                        $network = mysqli_real_escape_string($con, $data[31]);
                        $connectivity = mysqli_real_escape_string($con, $data[32]);
                        $speed = mysqli_real_escape_string($con, $data[33]);
                        $cmtmale = mysqli_real_escape_string($con, $data[34]);
                        $cmtfemale = mysqli_real_escape_string($con, $data[35]);
                        $straining = mysqli_real_escape_string($con, $data[36]);
                        $etraining = mysqli_real_escape_string($con, $data[37]);
                        $signing = mysqli_real_escape_string($con, $data[38]);
                        $partner = mysqli_real_escape_string($con, $data[39]);
                        $expiration = mysqli_real_escape_string($con, $data[40]);
                        $donation = mysqli_real_escape_string($con, $data[41]);
                        $datedonation = mysqli_real_escape_string($con, $data[42]);
                        $tcms = mysqli_real_escape_string($con, $data[43]);
                        $key_one = mysqli_real_escape_string($con, $data[44]);
                        $identifier = mysqli_real_escape_string($con, isset($data[45]) ? $data[45] : '');

                        $query = "INSERT INTO tblfinaltech (
                            region, province, district, municipality, barangay, street, location, cname, host, category, longitude, latitude, 
                            cmanager, cemail, cmobile, clandline, cgender, amanager, aemail, amobile, alandline, agender, launch, registration, 
                            operation, visited, desktop, laptop, printer, scanner, status, network, connectivity, speed, cmtmale, cmtfemale, 
                            straining, etraining, signing, partner, expiration, donation, datedonation, tcms, key_one, identifier
                        ) VALUES (
                            '$region', '$province', '$district', '$municipality', '$barangay', '$street', '$location', '$cname', '$host', 
                            '$category', '$longitude', '$latitude', '$cmanager', '$cemail', '$cmobile', '$clandline', '$cgender', '$amanager', 
                            '$aemail', '$amobile', '$alandline', '$agender', '$launch', '$registration', '$operation', '$visited', '$desktop', 
                            '$laptop', '$printer', '$scanner', '$status', '$network', '$connectivity', '$speed', '$cmtmale', '$cmtfemale', 
                            '$straining', '$etraining', '$signing', '$partner', '$expiration', '$donation', '$datedonation', '$tcms', 
                            '$key_one', '$identifier'
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
