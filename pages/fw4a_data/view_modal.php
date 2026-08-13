<?php 
echo '<div id="viewModal'.$row['id'].'" class="modal fade" role="dialog">
<form method="post" enctype="multipart/form-data">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">View Files for Activity: '.$row['locality'].'</h4>
        </div>
        <div class="modal-body">
            <input type="hidden" name="hidden_id" value="'.$row['id'].'">
            <input type="checkbox" id="cbxMainphoto" /> <label>Select All</label>
            <div class="row">' ;

            // Fetch the files for the activity
            $p = mysqli_query($con,"SELECT * from tblactivityphoto where activityid = '".$row['id']."' ");
            while($row1 = mysqli_fetch_array($p)){
                // Determine the file extension and show a preview accordingly
                $filePath = "photo/" . basename($row1['filename']);
                $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                // Remove numbers from filename (if any) using preg_replace
                $fileNameWithoutNumbers = preg_replace('/\d+/', '', $row1['filename']);
                
                echo '<div class="col-md-4">
                        <input type="checkbox" name="chk_deletephoto[]" class="chk_deletephoto" value="'.$row1['id'].'" />
                        <div class="file-item">' ;

                // Image files (jpg, jpeg, png, gif)
                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo '<img src="'.$filePath.'" alt="'.$row1['filename'].'" class="file-thumbnail"/>';
                } 
                // PDF files
                elseif ($fileExtension == 'pdf') {
                    echo '<div class="file-thumbnail-pdf">
                            <embed src="'.$filePath.'" type="application/pdf" width="100%" height="100%" />
                          </div>';
                } 
                // Office files (docx, xlsx, pptx)
                elseif (in_array($fileExtension, ['docx', 'xlsx', 'pptx'])) {
                    echo '<div class="file-thumbnail-office">
                            <i class="fas fa-file-word"></i> <!-- Word icon -->
                          </div>';
                } 
                else {
                    // If it's an unsupported file, show an icon or text
                    echo '<div class="file-thumbnail">File type not previewable</div>';
                }

                // File info section with filename and download button
                echo '<div class="file-info">
                        <span class="filename">'.$fileNameWithoutNumbers.'</span>
                        <a href="'.$filePath.'" download class="download-btn">
                            <i class="fas fa-download"></i>
                        </a>
                      </div>
                    </div>
                </div>';
            }

        echo '</div>
        </div>
        <div class="modal-footer">
            <div class="col-md-6">
                <input name="photos[]" class="form-control input-sm" type="file" multiple/>
            </div>
            <input type="submit" class="btn btn-primary btn-sm" name="btn_addimage" value="Add"/>
            <input type="submit" class="btn btn-danger btn-sm" name="btn_remove" value="Remove Selected"/>
            <input type="button" class="btn btn-default btn-sm" data-dismiss="modal" value="Close"/>
        </div>
    </div>
  </div>
</form>
</div>';
?>
