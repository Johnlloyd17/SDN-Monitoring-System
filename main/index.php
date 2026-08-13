<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>DICT-SDN Activity Management and Monitoring System</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- bootstrap 3.0.2 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- font Awesome -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../js/morris/morris-0.4.3.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />

    <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="../css/select2.css" rel="stylesheet" type="text/css" />
    <script src="../js/jquery-1.12.3.js" type="text/javascript"></script>
    <style>
        .no-print {
            display: none;
        }
        .dataTables_filter input { 
            padding-top: 20px;
            padding-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            white-space: nowrap; /* Prevent text from wrapping */
        }
        .table td, .table th {
            text-align: center;
            vertical-align: middle;
        }
        .table th:nth-child(1) {
            width: 5%;
        }
        .table th:nth-child(2) {
            width: 15%;
        }
        .table th:nth-child(3) {
            width: 10%;
        }
        .table th:nth-child(4),
        .table th:nth-child(5),
        .table th:nth-child(6),
        .table th:nth-child(7),
        .table th:nth-child(8),
        .table th:nth-child(9) {
            width: 10%;
        }
        .table th:nth-child(10),
        .table th:nth-child(11) {
            width: 15%;
        }
        @media (max-width: 768px) {
            .table {
                width: 100%;
            }
            .table td, .table th {
                display: block;
                width: 100%;
            }
            .table th {
                text-align: left;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-inverse" style="border-radius:0px;">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php"><img alt="Brand" src="../img/logo1.png" style="width:50px; margin-top:-15px;"></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="active"><a href="index.php">Home <span class="sr-only">(current)</span></a></li>
                <li><a href="../login.php">Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="wrapper row-offcanvas row-offcanvas-left">
    <div class="container-fluid table-responsive">
        <table id="table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Population (2020)</th>
                    <th>Population (2015)</th>
                    <th>Annual Growth Rate (2015‑2020)</th>
                    <th>Area (2013) in km²</th>
                    <th>Density (2020) per km²</th>
                    <th>Brgy Count</th>
                    <th style="width: 15% !important;">Official Links</th>
                    <th style="width: 15% !important;">LGU Profile (DTI)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include "../pages/connection.php";
                $squery = mysqli_query($con, "SELECT * FROM tblmunicipal");
                $no = 1;
                while($row = mysqli_fetch_array($squery))
                {
                    // Fetch the official link and LGU Profile (DTI) link
                    $official_link = $row['official_link'];
                    $lgu_profile_link = $row['lgu_profile_link'];

                    // Generate link HTML
                    $official_link_html = !empty($official_link) ? 
                        '<a href="'.$official_link.'" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-link" aria-hidden="true"></i> Visit Link</a>' : 
                        '<span class="btn btn-default btn-sm" disabled>No Link Available</span>';

                    $lgu_profile_link_html = !empty($lgu_profile_link) ? 
                        '<a href="'.$lgu_profile_link.'" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-link" aria-hidden="true"></i> View Profile</a>' : 
                        '<span class="btn btn-default btn-sm" disabled>No Profile Available</span>';

                    echo '
                    <tr>
                        <td>'.$no.'</td>
                        <td>'.$row['name'].'</td>
                        <td>'.$row['type'].'</td>
                        <td>'.$row['population_2020'].'</td>
                        <td>'.$row['population_2015'].'</td>
                        <td>'.$row['growth_rate'].'</td>
                        <td>'.$row['area_km2'].'</td>
                        <td>'.$row['density_2020'].'</td>
                        <td>'.$row['brgy_count'].'</td>
                        <td>'.$official_link_html.'</td>
                        <td>'.$lgu_profile_link_html.'</td>
                    </tr>
                    ';
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/alert.js" type="text/javascript"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/morris/raphael-2.1.0.min.js" type="text/javascript"></script>
<script src="../js/morris/morris.js" type="text/javascript"></script>
<script src="../js/select2.full.js" type="text/javascript"></script>
<script src="../js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="../js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="../js/buttons.print.min.js" type="text/javascript"></script>
<script src="../js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
<script src="../js/AdminLTE/app.js" type="text/javascript"></script>

<script type="text/javascript">
  $(function() {
      $("#table").DataTable({
          "aoColumnDefs": [
              { "bSortable": false, "aTargets": [10, 11] }
          ],
          "aaSorting": [],
          "dom": '<"search"f><"top"l>rt<"bottom"ip><"clear">',
          "drawCallback": function() {
              // Adjust column widths based on content
              var table = $('#table').DataTable();
              table.columns().every(function() {
                  var column = this;
                  var header = $(column.header());
                  var maxWidth = Math.max.apply(null, column.nodes().map(function(node) {
                      return $(node).outerWidth();
                  }).toArray());
                  header.css('width', maxWidth + 'px');
              });
          }
      });
  });

  $(document).ready(function () {
      $('.dataTables_filter input[type="search"]').css(
         {'width':'350px','display':'inline-block'}
      );
  });
</script>
</body>
</html>
