<?php
$selected_year = isset($_POST['selected_year']) ? $_POST['selected_year'] : date("Y"); // Default to current year if no year is selected

$year_condition = "AND YEAR(start) = $selected_year"; // Filter by the selected year
?>

<script>
    Morris.Bar({
        element: 'face-to-face',
        data: [
            <?php
            // Fetch actual accomplishments filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'Cybersecurity'
                 AND indicator = 'CSB Awareness'
                 AND mode = 'Face-to-face' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'Number of Cybersecurity Advocacy and Awareness conducted (face-to-face)'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual Accomplishments', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#00008b', '#00008b'],
        resize: true
    });

    Morris.Bar({
        element: 'reachf',
        data: [
            <?php
            // Fetch actual accomplishments
            $actualQuery = mysqli_query($con, "
                SELECT SUM(completers) AS actual_count
                FROM tblactivity
                WHERE project = 'Cybersecurity'
                AND mode = 'Face-to-Face'
                AND indicator = 'CSB Awareness' $year_condition;"
            );
            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;

            // Fetch target values
            $targetQuery = mysqli_query($con, "
                SELECT target 
                FROM cybersecurity_metrics 
                WHERE subcategory = 'Number of individuals reached for Advocacy and Awareness conducted (face-to-face)'
                AND year LIKE '%$selected_year%'
            ");
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;

            echo "{ label: 'Actual Accomplishments', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#00008b', '#00008b'],
        resize: true
    });

    Morris.Bar({
        element: 'pki-awareness',
        data: [
            <?php
            // Fetch actual accomplishments filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'Cybersecurity'
                 AND indicator = 'PKI Awareness' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'Number of PKI awareness campaigns conducted'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual Accomplishments', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#8B0000', '#00008b'],
        resize: true
    });

    Morris.Bar({
        element: 'pki-certificates',
        data: [
            <?php
            // Fetch actual accomplishments filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT SUM(completers) AS actual_count
                 FROM tblactivity
                 WHERE project = 'Cybersecurity'
                 AND indicator = 'PKI Training' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'Number of Issued Digital Certificates'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual Accomplishments', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#8B0000', '#00008b'],
        resize: true
    });

    Morris.Bar({
        element: 'pki-training',
        data: [
            <?php
            // Fetch actual accomplishments filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'Cybersecurity'
                 AND indicator = 'PKI Training' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'Number of PNPKI Users Training conducted'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual Accomplishments', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#8B0000', '#00008b'],
        resize: true
    });

    
</script>