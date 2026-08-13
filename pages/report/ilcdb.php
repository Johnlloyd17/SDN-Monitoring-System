<!DOCTYPE html>
<html>
<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
?>

    <body class="skin-black">
        <!-- header logo: style can be found in header.less -->
        <?php
        include "../connection.php";
        include('../header.php');
        ?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <?php include('../sidebar-left.php'); ?>

            <aside class="right-side">
                <section class="content-header">
                    <div class="header-title">
                        <img src="report.png" alt="Logo" class="header-logo" />
                        <div class="header-info">
                            <h3 style="color: darkwhite; font-weight: bold;">Reports</h3>
                            <p class="header-address">Cybersecurity Programs</p>
                        </div>
                        <div class="header-date-time" id="dateTime"></div>
                    </div>
                </section>

            <section class="content">
                <div class="row">
                    <div class="box">
                        <div class="box-header">
                            <div class="col-md-12 col-sm-12 col-xs-12"><br>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                    Actual Accomplishments Vs. Targets
                                    </div>
                              
    
                                    <div class="box-body table-responsive">

<div class="form-group col-md-12">
    <form method="POST" action="ilcdb.php" id="yearForm">
        <label>Filter Year:</label>
        <select name="selected_year" class="form-control" style="width: 25%;" onchange="this.form.submit()">
            <?php
            // Get the current year
            $current_year = date("Y");

            $year_dropdown_options = [];
            $sql_options = "SELECT DISTINCT year FROM cybersecurity_metrics WHERE year IS NOT NULL AND year != '' ORDER BY year DESC";
            $result_options = mysqli_query($con, $sql_options);

            if ($result_options && mysqli_num_rows($result_options) > 0) {
                while ($row_option = mysqli_fetch_assoc($result_options)) {
                    $year_dropdown_options[] = htmlspecialchars($row_option['year']);
                }
                mysqli_free_result($result_options);
            } else {
                $year_dropdown_options[] = "No year Available";
            }

            // Set the default selected year to the current year if no year is selected
            $selected_year = isset($_POST['selected_year']) ? $_POST['selected_year'] : $current_year;

            // Output the year options and set the default to the selected year
            foreach ($year_dropdown_options as $optionValue) {
                $selected = ($optionValue == $selected_year) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($optionValue) . '" ' . $selected . '>' . htmlspecialchars($optionValue) . '</option>';
            }
            ?>
        </select>
    </form>


    <br>
    <div class="row ">
        <div class="col-md-3 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">ICT Proficiency Diagnostic Examination</div>
                <div class="panel-body">
                    <div id="ilcdb-diagnostic"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">Diagnostic Examination Examinees</div>
                <div class="panel-body">
                    <div id="diagnostic-examinees"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">SPARK Technical Training Conducted</div>
                <div class="panel-body">
                    <div id="ilcdb-spark"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">SPARK Technical Training Completers</div>
                <div class="panel-body">
                    <div id="spark-completers"></div>
                </div>
            </div>
        </div>
        </div>
        <div class="row ">
        <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">Capacity Development</div>
                <div class="panel-body">
                    <div id="dlt"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">Training on Digital Transformative Technologies</div>
                <div class="panel-body">
                    <div id="transformative"></div>
                </div>
            </div>
        </div>
 

</div>
<!-- /.box-body -->
</div>
</div>
</div>
</div>
</div>
<!-- /.row -->
</section>
<!-- /.content -->
</aside>
<!-- /.right-side -->
</div>
<!-- ./wrapper -->

<!-- jQuery 2.0.2 -->
<?php
}
include "../footer.php";
include "ilcdb_bar-chart.php";
?>

<script type="text/javascript">
// Function to update the date and time
function updateDateTime() {
const now = new Date();
const options = {
year: 'numeric',
month: 'long',
day: 'numeric',
hour: '2-digit',
minute: '2-digit',
second: '2-digit',
hour12: true
};
document.getElementById('dateTime').innerText = now.toLocaleString('en-US', options);
}

// Update the date and time every second
setInterval(updateDateTime, 1000);
updateDateTime(); // Initial call to display immediately
</script>

<style>
.header-date-time {
font-size: 16px;
/* Adjust font size as needed */
color: #555;
/* Optional: Change color for better visibility */
margin-left: auto;
/* Push the date/time to the right */
}

.header-title {
display: flex;
align-items: center;
/* Align items vertically */
}

.header-logo {
height: 55px;
/* Adjust the size as needed */
width: auto;
/* Maintain aspect ratio */
margin-right: 10px;
/* Space between logo and title */
}

.header-info {
display: flex;
flex-direction: column;
/* Stack title and address vertically */
}

h3 {
margin: 0;
/* Remove default margin */
font-weight: 600;
/* Set to semi-bold */
}

.header-address {
margin: 0;
/* Remove default margin */
font-size: 14px;
/* Adjust font size as needed */
color: #555;
/* Optional: Change color for better visibility */
}
</style>
</body>

</html>
