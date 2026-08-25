<?php if (!isset($con)) include "../connection.php";
$selected_year = isset($_POST['selected_year']) ? $_POST['selected_year'] : date("Y"); // Default to current year if no year is selected

$year_condition = "AND YEAR(start) = $selected_year"; // Filter by the selected year
?>

<script>
    Morris.Bar({
        element: 'ilcdb-diagnostic',
        data: [
            <?php
            // Fetch Actual filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'ILCDB'
                 AND indicator = 'Diagnostic Examination' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'ICT Proficiency Diagnostic Examination'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#00008b', '#8B0000'], // Blue for actual, red for target
        resize: true
    });

    Morris.Bar({
        element: 'diagnostic-examinees',
        data: [
            <?php
            // Fetch Actual filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT SUM(completers) AS actual_count
                 FROM tblactivity
                 WHERE project = 'ILCDB'
                 AND indicator = 'Diagnostic Examination' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'ICT Proficiency Diagnostic Examination Examinee'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#00008b', '#8B0000'], // Blue for actual, red for target
        resize: true
    });

    Morris.Bar({
        element: 'ilcdb-spark',
        data: [
            <?php
            // Fetch Actual filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'ILCDB'
                 AND indicator = 'SPARK Technical Training' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'SPARK Technical Training conducted'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#00008b', '#8B0000'], // Blue for actual, red for target
        resize: true
    });

    Morris.Bar({
        element: 'spark-completers',
        data: [
            <?php
            // Fetch Actual filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT SUM(completers) AS actual_count
                 FROM tblactivity
                 WHERE project = 'ILCDB'
                 AND indicator = 'SPARK Technical Training' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'SPARK Technical Training Completers'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#00008b', '#8B0000'], // Blue for actual, red for target
        resize: true
    });

    Morris.Bar({
        element: 'dlt',
        data: [
            <?php
            // Fetch Actual filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'ILCDB'
                 AND indicator = 'DLT' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'Capacity Development'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#8B0000', '#8B0000'], // Blue for actual, red for target
        resize: true
    });


    Morris.Bar({
        element: 'transformative',
        data: [
            <?php
            // Fetch Actual filtered by the selected year and start date
            $actualQuery = mysqli_query(
                $con,
                "SELECT COUNT(*) AS actual_count
                 FROM tblactivity
                 WHERE project = 'ILCDB'
                 AND indicator = 'Transformative Technology' $year_condition;"
            );

            $actualRow = mysqli_fetch_assoc($actualQuery);
            $actualCount = $actualRow['actual_count'] ?? 0;  // Default to 0 if NULL

            // Fetch target values filtered by the selected year
            $targetQuery = mysqli_query(
                $con,
                "SELECT target 
                 FROM cybersecurity_metrics 
                 WHERE subcategory = 'Transformative Technology'
                 AND year = '$selected_year'"
            );
            $targetRow = mysqli_fetch_assoc($targetQuery);
            $targetCount = $targetRow['target'] ?? 0;  // Default to 0 if NULL

            // Prepare data for the bar chart
            echo "{ label: 'Actual', value: $actualCount },";
            echo "{ label: 'Target', value: $targetCount }";
            ?>
        ],
        xkey: 'label',
        ykeys: ['value'],
        labels: ['Count'],
        barColors: ['#8B0000', '#8B0000'], // Blue for actual, red for target
        resize: true
    });
</script>