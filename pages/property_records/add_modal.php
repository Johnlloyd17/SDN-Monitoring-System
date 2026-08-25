<?php if (!isset($con)) include "../connection.php";
<!-- ========================= MODAL ======================= -->
<div id="addModal" class="modal fade">
    <form method="post" enctype="multipart/form-data">
        <div class="modal-dialog modal-sdm-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle"></i> Add Property Record</h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project:</label>
                                <input name="txt_project" class="form-control input-sm" type="text" placeholder="Project" />
                            </div>
                            <div class="form-group">
                                <label>Item No.:</label>
                                <input name="txt_item" class="form-control input-sm" type="text" placeholder="Item No." />
                            </div>
                            <div class="form-group">
                                <label>Classification:</label>
                                <select name="txt_classification" class="form-control input-sm select2" style="width:100%;">
                                    <option value="">-- Select Classification --</option>
                                    <?php
                                    $classResult = mysqli_query($con, "SELECT category, sub_item FROM classifications WHERE status = 'active' ORDER BY category ASC, sub_item ASC");
                                    if ($classResult) {
                                        $currentCat = '';
                                        while ($classRow = mysqli_fetch_assoc($classResult)) {
                                            if ($classRow['category'] !== $currentCat) {
                                                if ($currentCat !== '') echo '</optgroup>';
                                                $currentCat = $classRow['category'];
                                                echo '<optgroup label="' . htmlspecialchars($currentCat) . '">';
                                            }
                                            echo '<option value="' . htmlspecialchars($classRow['sub_item']) . '">' . htmlspecialchars($classRow['sub_item']) . '</option>';
                                        }
                                        if ($currentCat !== '') echo '</optgroup>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Quantity:</label>
                                <input name="txt_quantity" class="form-control input-sm" type="number" placeholder="Quantity" />
                            </div>
                            <div class="form-group">
                                <label>Unit:</label>
                                <input name="txt_unit" class="form-control input-sm" type="text" placeholder="Unit" />
                            </div>
                            <div class="form-group">
                                <label>Description/Model:</label>
                                <input name="txt_description" class="form-control input-sm" type="text" placeholder="Description/Model" />
                            </div>
                            <div class="form-group">
                                <label>Received From:</label>
                                <input name="txt_received" class="form-control input-sm" type="text" placeholder="Received From" />
                            </div>
                            <div class="form-group">
                                <label>Property Number:</label>
                                <input name="txt_property" class="form-control input-sm" type="text" placeholder="Property Number" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ICS/PAR Number:</label>
                                <input name="txt_ics" class="form-control input-sm" type="text" placeholder="ICS/PAR Number" />
                            </div>
                            <div class="form-group">
                                <label>Serial Number:</label>
                                <input name="txt_serial" class="form-control input-sm" type="text" placeholder="Serial Number" />
                            </div>
                            <div class="form-group">
                                <label>Date Acquired:</label>
                                <input name="txt_date" class="form-control input-sm" type="date" placeholder="Date Acquired" />
                            </div>
                            <div class="form-group">
                                <label>Accountable Officer:</label>
                                <input name="txt_officer" class="form-control input-sm" type="text" placeholder="Accountable Officer" />
                            </div>
                            <div class="form-group">
                                <label>Unit Cost:</label>
                                <input name="txt_cost" class="form-control input-sm" type="text" placeholder="e.g. 43,904.00" />
                            </div>
                            <div class="form-group">
                                <label>Estimated Useful Life:</label>
                                <input name="txt_life" class="form-control input-sm" type="number" placeholder="Estimated Useful Life" />
                            </div>
                            <div class="form-group">
                                <label>Received/Transferred:</label>
                                <input name="txt_transferred" class="form-control input-sm" type="text" placeholder="Received/Transferred" />
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea name="txt_remarks" class="form-control input-sm" placeholder="Remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Cancel" />
                    <input type="submit" class="btn btn-primary btn-sm" name="btn_add" value="Add Item" />
                </div>
            </div>
        </div>
    </form>
</div>
