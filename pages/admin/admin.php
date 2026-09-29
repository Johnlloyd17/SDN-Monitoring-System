<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!DOCTYPE html>
<html>

    <?php

    if(!isset($_SESSION['role']))
    {
        header("Location: ../../login.php");
    }
    else
    {
    ob_start();
    include('../head_css.php'); ?>
    <body class="skin-black">
        <!-- header logo: style can be found in header.less -->
        <?php

        include "../connection.php";
        require_once __DIR__ . '/admin_rows.php';
        ?>
        <?php include('../header.php'); ?>

        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <?php include('../sidebar-left.php'); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        Zone Leader
                    </h1>

                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <!-- left column -->
                            <div class="box">
                                <div class="box-header">
                                    <div style="padding:10px;">

                                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addZoneModal"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Zone Leader</button>
                                        <?php
                                            if(!isset($_SESSION['staff']))
                                            {
                                        ?>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
                                        <?php
                                            }
                                        ?>

                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body table-responsive">
                                    <table id="table" class="table table-bordered table-striped">
                                        <thead>
                                            <?php echo adm_render_headers(adm_can_delete()); ?>
                                        </thead>
                                        <tbody>
                                            <?php echo adm_render_rows(adm_fetch_rows($con), adm_can_delete()); ?>
                                        </tbody>
                                    </table>

                                    <?php include "edit_modal.php"; ?>

                                    <?php include "../deleteModal.php"; ?>

                                    <?php include "add_modal.php"; ?>

                    </div>   <!-- /.row -->
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
        <?php }
        include "../footer.php"; ?>
<?php include "admin_list_js.php"; ?>
<script type="text/javascript">
    $(function() {
        if ($.fn.DataTable.isDataTable('#table')) {
            $('#table').DataTable().destroy();
        }
        $("#table").dataTable({
           "aoColumnDefs": [ { "bSortable": false, "aTargets": <?php echo adm_can_delete() ? '[0,3]' : '[2]'; ?> } ],"aaSorting": []
        });
    });
</script>
    </body>
</html>