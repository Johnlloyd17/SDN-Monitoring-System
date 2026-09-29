<!-- ==================== SHARED FILE PREVIEW MODAL (js/sdm-preview.js) ==================== -->
<div id="sdmPreviewModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sdm-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-image-o"></i> <span id="sdmPreviewTitle">Preview</span></h4>
            </div>
            <div class="modal-body" style="padding: 0; height: 72vh; background: #e9e9e9; display: flex; align-items: flex-start; justify-content: center; overflow: hidden;">
                <div id="sdmPreviewSpinner" style="text-align:center; margin-top: 25vh;">
                    <i class="fa fa-spinner fa-spin" style="font-size:36px; color:#888;"></i>
                    <p style="margin-top:10px; color:#888;">Loading preview...</p>
                </div>
                <iframe id="sdmPreviewFrame" src="" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                <img id="sdmPreviewImg" src="" style="max-width: 100%; max-height: 100%; display: none;">
                <div id="sdmPreviewSheetWrap" style="display: none; width: 100%; height: 100%;">
                    <div id="sdmSheetTabs"></div>
                    <div id="sdmSheetScroll">
                        <table id="sdmSheetTable"></table>
                    </div>
                    <p class="sdm-sheet-note" id="sdmSheetNote"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="sdmPreviewPrintBtn"><i class="fa fa-print"></i> Print</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>