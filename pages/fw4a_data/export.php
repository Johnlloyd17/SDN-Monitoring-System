<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

include "../connection.php";

// Get filtering parameters from the query string (GET request)
$filterLocality = isset($_GET['locality']) ? mysqli_real_escape_string($con, $_GET['locality']) : '';
$filterBarangay = isset($_GET['barangay']) ? mysqli_real_escape_string($con, $_GET['barangay']) : '';
$filterType = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : '';
$filterStrategy = isset($_GET['strategy']) ? mysqli_real_escape_string($con, $_GET['strategy']) : '';
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv'; // csv or xlsx

// Initialize the base SQL query - matching spreadsheet column order
$query = "SELECT
            item_no, locality, barangay, district, transport_location, transport_type,
            site_code, new_renamed, site_locations, transfer_new_locations, remarks,
            nationwide_id, site_type,
            date_of_activation, current_date_of_acceptance, latitude, longitude,
            procurement_initiative, installation_type, uat, conforme, strategy, link_type,
            status_2026_10_06_am, status_2026_10_06_pm,
            status_2026_10_07_am, status_2026_10_07_pm,
            status_2026_10_08_am, status_2026_10_08_pm,
            status_2026_10_12_am, status_2026_10_12_pm,
            status_2026_10_13_am, status_2026_10_13_pm,
            status_2026_10_14_am, status_2026_10_14_pm,
            status_2026_10_15_am, status_2026_10_15_pm,
            contact_details, connection_type, down_time, connectivity_status,
            area_classification, with_dict_free_wifi, last_monitored_at, monitoring_notes,
            replacement_form_file, conforme_file, uat_file, additional_uat,
            site_coordinator_name, contact_details, source_sheet
          FROM tblfwfa WHERE 1=1";

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

$result = mysqli_query($con, $query);
if (!$result) {
    http_response_code(500);
    echo "Error: " . mysqli_error($con);
    exit;
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
mysqli_free_result($result);

// Define column headers matching the spreadsheet layout (A..AL)
$headers = [
    'Item No.' => 'item_no',
    'Locality' => 'locality',
    'Barangay' => 'barangay',
    'District' => 'district',
    'Transport Location' => 'transport_location',
    'Transport Type' => 'transport_type',
    'Site Code' => 'site_code',
    'New Renamed' => 'new_renamed',
    'Site Locations' => 'site_locations',
    'Transfer/New Locations' => 'transfer_new_locations',
    'Remarks' => 'remarks',
    'Nationwide ID' => 'nationwide_id',
    'Site Type' => 'site_type',
    'Date of Activation' => 'date_of_activation',
    'Current Date of Acceptance' => 'current_date_of_acceptance',
    'Latitude' => 'latitude',
    'Longitude' => 'longitude',
    'Procurement Initiative' => 'procurement_initiative',
    'Installation Type' => 'installation_type',
    'UAT' => 'uat',
    'Conforme' => 'conforme',
    'Strategy' => 'strategy',
    'Link Type' => 'link_type',
    // Status columns (7 dates x AM/PM)
    '2026-10-06 AM' => 'status_2026_10_06_am',
    '2026-10-06 PM' => 'status_2026_10_06_pm',
    '2026-10-07 AM' => 'status_2026_10_07_am',
    '2026-10-07 PM' => 'status_2026_10_07_pm',
    '2026-10-08 AM' => 'status_2026_10_08_am',
    '2026-10-08 PM' => 'status_2026_10_08_pm',
    '2026-10-12 AM' => 'status_2026_10_12_am',
    '2026-10-12 PM' => 'status_2026_10_12_pm',
    '2026-10-13 AM' => 'status_2026_10_13_am',
    '2026-10-13 PM' => 'status_2026_10_13_pm',
    '2026-10-14 AM' => 'status_2026_10_14_am',
    '2026-10-14 PM' => 'status_2026_10_14_pm',
    '2026-10-15 AM' => 'status_2026_10_15_am',
    '2026-10-15 PM' => 'status_2026_10_15_pm',
    'Contact Details' => 'contact_details',
    'Connection Type' => 'connection_type',
    'Down Time' => 'down_time',
    'Connectivity Status' => 'connectivity_status',
    'Area Classification' => 'area_classification',
    'With DICT Free WiFi' => 'with_dict_free_wifi',
    'Last Monitored' => 'last_monitored_at',
    'Monitoring Notes' => 'monitoring_notes',
    'Replacement Form File' => 'replacement_form_file',
    'Conforme File' => 'conforme_file',
    'UAT File' => 'uat_file',
    'Additional UAT' => 'additional_uat',
    'Name (Site Coordinators)' => 'site_coordinator_name',
    'Contact Details (Coord)' => 'contact_details',
    'Source Sheet' => 'source_sheet',
];

$headerLabels = array_keys($headers);
$headerFields = array_values($headers);

if ($format === 'xlsx') {
    // Check if PhpSpreadsheet is available
    $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        // Fallback to CSV with notice
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=DICT-SDN_FWFA_Database_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['NOTE: XLSX export requires PhpSpreadsheet library. Run "composer require phpoffice/phpspreadsheet" to enable. Falling back to CSV.']);
        fputcsv($output, []);
        fputcsv($output, $headerLabels);
        foreach ($rows as $row) {
            $csvRow = [];
            foreach ($headerFields as $field) {
                $value = $row[$field] ?? '';
                if (in_array($field, ['uat', 'conforme'])) {
                    if ($value === '1' || $value === 1 || $value === true) $value = 'Yes';
                    elseif ($value === '0' || $value === 0 || $value === false) $value = 'No';
                    else $value = '';
                }
                $csvRow[] = $value;
            }
            fputcsv($output, $csvRow);
        }
        fclose($output);
        exit;
    }
    
    require_once $autoloadPath;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('FW4A Export');
    
    $colCount = count($headerLabels);
    
    // Row 1: Group headers (merged) - matching spreadsheet structure
    $groupHeaders = [
        ['label' => '', 'start' => 0, 'end' => 0],            // A Item No.
        ['label' => '', 'start' => 1, 'end' => 1],            // B Locality
        ['label' => '', 'start' => 2, 'end' => 2],            // C Barangay
        ['label' => '', 'start' => 3, 'end' => 3],            // D District
        ['label' => '', 'start' => 4, 'end' => 4],            // E Transport Location
        ['label' => '', 'start' => 5, 'end' => 5],            // F Transport Type
        ['label' => '', 'start' => 6, 'end' => 6],            // G Site Code
        ['label' => '', 'start' => 7, 'end' => 7],            // H New Renamed
        ['label' => '', 'start' => 8, 'end' => 8],            // I Site Locations
        ['label' => '', 'start' => 9, 'end' => 9],            // J Transfer/New Locations
        ['label' => '', 'start' => 10, 'end' => 10],          // K Remarks
        ['label' => '', 'start' => 11, 'end' => 11],          // L Nationwide ID
        ['label' => '', 'start' => 12, 'end' => 12],          // M Site Type
        ['label' => 'DATE OF ACTIVATION', 'start' => 13, 'end' => 14], // N-O
        ['label' => 'COORDINATES', 'start' => 15, 'end' => 16],         // P-Q
        ['label' => 'PROCUREMENT INITIATIVE', 'start' => 17, 'end' => 18], // R-S
        ['label' => 'DOCUMENTS', 'start' => 19, 'end' => 20],            // T-U
        ['label' => '', 'start' => 21, 'end' => 21],            // V Strategy
        ['label' => '', 'start' => 22, 'end' => 22],            // W Link Type
        ['label' => 'STATUS', 'start' => 23, 'end' => 36],    // X-AK (14 status cols)
        ['label' => '', 'start' => 37, 'end' => 37],            // AL Contact Details
        // Extra columns
        ['label' => '', 'start' => 38, 'end' => 38],            // Connection Type
        ['label' => '', 'start' => 39, 'end' => 39],            // Down Time
        ['label' => '', 'start' => 40, 'end' => 40],            // Connectivity Status
        ['label' => '', 'start' => 41, 'end' => 41],            // Area Classification
        ['label' => '', 'start' => 42, 'end' => 42],            // With DICT Free WiFi
        ['label' => '', 'start' => 43, 'end' => 43],            // Last Monitored
        ['label' => '', 'start' => 44, 'end' => 44],            // Monitoring Notes
        ['label' => '', 'start' => 45, 'end' => 45],            // Replacement Form File
        ['label' => '', 'start' => 46, 'end' => 46],            // Conforme File
        ['label' => '', 'start' => 47, 'end' => 47],            // UAT File
        ['label' => '', 'start' => 48, 'end' => 48],            // Additional UAT
        ['label' => 'SITE COORDINATORS', 'start' => 49, 'end' => 50], // Site Coordinators
        ['label' => '', 'start' => 51, 'end' => 51],            // Source Sheet
    ];
    
    // Write group headers (row 1)
    foreach ($groupHeaders as $gh) {
        $colStart = $gh['start'];
        $colEnd = $gh['end'];
        $colLetterStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colStart + 1);
        $colLetterEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colEnd + 1);
        
        if ($gh['label'] !== '') {
            $sheet->setCellValue($colLetterStart . '1', $gh['label']);
            if ($colStart !== $colEnd) {
                $sheet->mergeCells($colLetterStart . '1:' . $colLetterEnd . '1');
            }
            $sheet->getStyle($colLetterStart . '1:' . $colLetterEnd . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3C8DBC']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '32739E']]],
            ]);
        } else {
            $sheet->getStyle($colLetterStart . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3C8DBC']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '32739E']]],
            ]);
        }
    }
    
    // Row 2: Sub-headers
    foreach ($headerLabels as $idx => $label) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
        $sheet->setCellValue($colLetter . '2', $label);
        $sheet->getStyle($colLetter . '2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7F9FC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);
    }
    
    // Data rows (starting row 3)
    $rowNum = 3;
    foreach ($rows as $row) {
        foreach ($headerFields as $idx => $field) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $value = $row[$field] ?? '';
            
            // Format boolean fields as Yes/No
            if (in_array($field, ['uat', 'conforme'])) {
                if ($value === '1' || $value === 1 || $value === true) $value = 'Yes';
                elseif ($value === '0' || $value === 0 || $value === false) $value = 'No';
                else $value = '';
            }
            $sheet->setCellValue($colLetter . $rowNum, $value);
        }
        $rowNum++;
    }
    
    // Auto-size columns
    foreach (range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount)) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Freeze first two rows
    $sheet->freezePane('A3');
    
    // Output
    $filename = 'DICT-SDN_FWFA_Database_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} else {
    // CSV output
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=DICT-SDN_FWFA_Database_' . date('Ymd_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
    
    // Header row
    fputcsv($output, $headerLabels);
    
    // Data rows
    foreach ($rows as $row) {
        $csvRow = [];
        foreach ($headerFields as $field) {
            $value = $row[$field] ?? '';
            // Format boolean fields
            if (in_array($field, ['uat', 'conforme'])) {
                if ($value === '1' || $value === 1 || $value === true) $value = 'Yes';
                elseif ($value === '0' || $value === 0 || $value === false) $value = 'No';
                else $value = '';
            }
            $csvRow[] = $value;
        }
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
    exit;
}