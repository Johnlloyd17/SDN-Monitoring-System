/* ============================================
   SDN Monitoring System - Shared File Preview
   js/sdm-preview.js
   ============================================
   One preview implementation (PDF / image / spreadsheet) shared by every
   page that lists uploaded attachments, so behaviour and markup stay
   identical across modules.

   Requires the markup from pages/sheet_preview_modal.php.

   Usage from a page's card renderer:
       SDMPreview.open(fileUrl, fileName, ext, src)
   where `src` is the allowlist key understood by ajax/sheet_preview.php
   (e.g. 'property_records', 'pass_slip', 'tech4ed').

   When the page prefers an inline handler (some cards use onclick="..."),
   call window.SDMPreview.open from a thin page-local shim.
   ============================================ */

window.SDMPreview = (function () {
    'use strict';

    var CFG = {
        // Override per page only if the relative path differs.
        endpoint: '../../ajax/sheet_preview.php'
    };

    var SHEET_EXT = ['xlsx', 'csv'];
    var IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    var MODAL_ID = 'sdmPreviewModal';
    var el = {};

    function byId(id) { return document.getElementById(id); }

    function cacheEls() {
        el.modal    = byId(MODAL_ID);
        el.title    = byId('sdmPreviewTitle');
        el.spinner  = byId('sdmPreviewSpinner');
        el.frame    = byId('sdmPreviewFrame');
        el.img      = byId('sdmPreviewImg');
        el.sheetWrap = byId('sdmPreviewSheetWrap');
        el.tabs     = byId('sdmSheetTabs');
        el.scroll   = byId('sdmSheetScroll');
        el.table    = byId('sdmSheetTable');
        el.note     = byId('sdmSheetNote');
    }

    function has() { if (!el.modal) cacheEls(); return !!el.modal; }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function isSheet(ext) { return SHEET_EXT.indexOf(String(ext || '').toLowerCase()) >= 0; }
    function isImage(ext) { return IMAGE_EXT.indexOf(String(ext || '').toLowerCase()) >= 0; }

    // -----------------------------------------------------------------
    // Panel switching
    // -----------------------------------------------------------------
    function showSpinner() {
        el.spinner.style.display = 'block';
        el.spinner.innerHTML = '<i class="fa fa-spinner fa-spin" style="font-size:36px; color:#888;"></i>' +
                               '<p style="margin-top:10px; color:#888;">Loading preview...</p>';
    }

    function hideAllPanels() {
        el.frame.style.display = 'none';
        el.img.style.display = 'none';
        el.sheetWrap.style.display = 'none';
        el.frame.src = '';
        el.img.src = '';
        el.table.innerHTML = '';
        el.tabs.innerHTML = '';
        el.note.textContent = '';
        el.sheetWrap._sheets = null;
    }

    function sheetMessage(icon, text, isError) {
        el.tabs.innerHTML = '';
        el.table.innerHTML = '<tbody><tr><td class="sdm-sheet-msg' + (isError ? ' is-error' : '') + '">' +
                             '<i class="fa ' + icon + '"></i>' + esc(text) + '</td></tr></tbody>';
        el.note.textContent = '';
        el.sheetWrap.style.display = 'flex';
        el.spinner.style.display = 'none';
    }

    // -----------------------------------------------------------------
    // Spreadsheet rendering
    // -----------------------------------------------------------------
    /** Build a column letter label (A, B, ... Z, AA, AB ...) */
    function colLabel(n) {
        var s = '';
        n = n + 1;
        while (n > 0) {
            var rem = (n - 1) % 26;
            s = String.fromCharCode(65 + rem) + s;
            n = Math.floor((n - 1) / 26);
        }
        return s;
    }

    function renderSheet(sheet, showTabs) {
        var rows = sheet.rows || [];
        var cols = sheet.cols || 0;

        var html = '<thead><tr><th class="sdm-rownum">#</th>';
        for (var c = 0; c < cols; c++) {
            html += '<th>' + colLabel(c) + '</th>';
        }
        html += '</tr></thead><tbody>';

        for (var r = 0; r < rows.length; r++) {
            var line = rows[r] || [];
            html += '<tr><td class="sdm-rownum">' + (r + 1) + '</td>';
            for (var c2 = 0; c2 < cols; c2++) {
                html += '<td>' + esc(line[c2]) + '</td>';
            }
            html += '</tr>';
        }
        html += '</tbody>';
        el.table.innerHTML = html;

        // Tab strip only when the workbook has more than one sheet.
        if (showTabs) {
            el.tabs.style.display = '';
        } else {
            el.tabs.innerHTML = '';
        }
        el.scroll.scrollTop = 0;
        el.scroll.scrollLeft = 0;

        if (sheet.truncated) {
            el.note.textContent = 'Preview limited to the first rows/columns of this sheet.';
        } else {
            el.note.textContent = '';
        }
    }

    function buildTabs(sheets, activeIndex) {
        if (!sheets || sheets.length < 2) {
            el.tabs.innerHTML = '';
            el.tabs.style.display = 'none';
            return;
        }
        var html = '';
        for (var i = 0; i < sheets.length; i++) {
            html += '<button type="button" class="sdm-sheet-tab' + (i === activeIndex ? ' active' : '') +
                    '" data-sheet-index="' + i + '" title="' + esc(sheets[i].name) + '">' +
                    esc(sheets[i].name) + '</button>';
        }
        el.tabs.innerHTML = html;
        el.tabs.style.display = 'flex';
    }

    function loadSheet(fileName, src) {
        var url = CFG.endpoint + '?src=' + encodeURIComponent(src || '') +
                  '&file=' + encodeURIComponent(fileName || '');

        sheetMessage('fa-file-excel-o', 'Loading spreadsheet...', false);
        showSpinner();

        $.getJSON(url)
            .done(function (res) {
                if (!res || !res.success || !res.sheets || !res.sheets.length) {
                    sheetMessage('fa-exclamation-triangle',
                        (res && res.error) ? res.error : 'This spreadsheet appears to be empty.', true);
                    return;
                }
                // Hide the spinner only now that we have real content to show.
                el.spinner.style.display = 'none';
                el.sheetWrap.style.display = 'flex';
                el.table.innerHTML = '';
                el.sheetWrap._sheets = res.sheets;
                buildTabs(res.sheets, 0);
                renderSheet(res.sheets[0], res.sheets.length > 1);
            })
            .fail(function (xhr) {
                var msg = 'Could not load the spreadsheet.';
                try {
                    var j = JSON.parse(xhr.responseText);
                    if (j && j.error) msg = j.error;
                } catch (e) { /* not JSON - keep the default message */ }
                sheetMessage('fa-exclamation-triangle', msg, true);
            });
    }

    // -----------------------------------------------------------------
    // Public: open the modal for any supported file
    // -----------------------------------------------------------------
    function open(fileUrl, fileName, ext, src) {
        if (!has()) return;
        var type = String(ext || '').toLowerCase();
        if (!type && fileName) {
            var m = String(fileName).match(/\.([a-z0-9]+)$/i);
            type = m ? m[1].toLowerCase() : '';
        }

        el.title.textContent = fileName || 'Preview';
        hideAllPanels();
        showSpinner();

        if (isSheet(type)) {
            loadSheet(fileName, src);
        } else if (type === 'pdf') {
            el.frame.onload = function () {
                el.spinner.style.display = 'none';
                el.frame.style.display = 'block';
                el.frame.onload = null;
            };
            el.frame.src = fileUrl;
        } else if (isImage(type)) {
            var preload = new Image();
            preload.onload = function () {
                el.img.src = fileUrl;
                el.spinner.style.display = 'none';
                el.img.style.display = 'block';
            };
            preload.onerror = function () {
                sheetMessage('fa-exclamation-triangle', 'Failed to load image.', true);
            };
            preload.src = fileUrl;
        } else {
            sheetMessage('fa-file-o', 'File type not previewable.', true);
        }

        $(el.modal).modal('show');
    }

    // -----------------------------------------------------------------
    // Public: print whichever panel is showing
    // -----------------------------------------------------------------
    function print() {
        if (!has()) return;

        if (el.frame.style.display !== 'none' && el.frame.contentWindow) {
            el.frame.contentWindow.focus();
            el.frame.contentWindow.print();
            return;
        }

        if (el.img.style.display !== 'none' && el.img.src) {
            var w = window.open('', '_blank');
            if (!w) return;
            w.document.write('<html><head><title>Print</title><style>body{margin:0;display:flex;' +
                'justify-content:center;align-items:center;min-height:100vh;} img{max-width:100%;max-height:100vh;}' +
                '</style></head><body>');
            w.document.write('<img src="' + el.img.src + '" onload="window.print();window.close();">');
            w.document.write('</body></html>');
            w.document.close();
            return;
        }

        if (el.sheetWrap.style.display !== 'none' && el.table.innerHTML !== '') {
            var title = el.title ? el.title.textContent : 'Spreadsheet';
            var w2 = window.open('', '_blank');
            if (!w2) return;
            // The sheet CSS is scoped to #sdmPreviewModal, so re-declare the
            // subset that the standalone print window needs.
            w2.document.write('<html><head><title>' + esc(title) + '</title><style>' +
                'body{font-family:Arial,Helvetica,sans-serif;margin:16px;color:#333;}' +
                'h1{font-size:15px;margin:0 0 4px;}' +
                '.meta{font-size:11px;color:#888;margin-bottom:10px;}' +
                'table{border-collapse:collapse;width:100%;font-size:11px;}' +
                'th,td{border:1px solid #ccc;padding:3px 6px;vertical-align:top;white-space:pre-wrap;}' +
                'th{background:#1b3a6b;color:#fff;font-weight:600;text-align:left;}' +
                'td.rn{background:#f0f3f7;color:#888;text-align:right;font-weight:600;}' +
                'tr:nth-child(even) td:not(.rn){background:#fbfcfd;}' +
                '</style></head><body>');
            w2.document.write('<h1>' + esc(title) + '</h1>');
            if (el.note && el.note.textContent) {
                w2.document.write('<div class="meta">' + esc(el.note.textContent) + '</div>');
            }
            // Clone the grid and swap the scope-specific class names.
            var clone = el.table.cloneNode(true);
            var rns = clone.querySelectorAll('.sdm-rownum');
            for (var i = 0; i < rns.length; i++) { rns[i].className = 'rn'; }
            w2.document.write(clone.outerHTML);
            w2.document.write('</body></html>');
            w2.document.close();
            w2.focus();
            setTimeout(function () { w2.print(); }, 250);
        }
    }

    // -----------------------------------------------------------------
    // Public: reset panels when the modal closes
    // -----------------------------------------------------------------
    function reset() {
        if (!has()) return;
        el.frame.onload = null;
        el.frame.style.display = 'none';
        el.frame.src = '';
        el.img.style.display = 'none';
        el.img.src = '';
        el.sheetWrap.style.display = 'none';
        el.table.innerHTML = '';
        el.tabs.innerHTML = '';
        el.note.textContent = '';
        el.sheetWrap._sheets = null;
        el.spinner.style.display = 'block';
    }

    // -----------------------------------------------------------------
    // Uniform thumbnail markup for any page that lists uploads. The click
    // handlers are delegated in init() on [data-sdm-preview], so filenames
    // never need manual quote escaping.
    // -----------------------------------------------------------------
    function previewCard(fileUrl, fileName, ext, src) {
        var e = String(ext || '').toLowerCase();
        var d = ' data-sdm-preview="1" data-url="' + esc(fileUrl) +
                '" data-name="' + esc(fileName) + '" data-ext="' + esc(e) +
                '" data-src="' + esc(src) + '"';
        if (e === 'pdf') {
            return '<div class="file-thumbnail-pdf"' + d + ' title="Preview PDF">' +
                   '<i class="fa fa-file-pdf-o" style="font-size:50px; color:#e74c3c;"></i></div>';
        }
        if (e === 'xlsx' || e === 'csv') {
            return '<div class="file-thumbnail-office file-thumbnail-sheet"' + d + ' title="Preview spreadsheet">' +
                   '<i class="fas fa-file-excel" style="font-size:50px; color:#217346;"></i></div>';
        }
        return '<img src="' + esc(fileUrl) + '" alt="" class="file-thumbnail"' + d + ' title="Preview image" />';
    }

    // -----------------------------------------------------------------
    // Wire up once the DOM is ready
    // -----------------------------------------------------------------
    function init() {
        if (!has()) return;

        $(el.modal).on('hidden.bs.modal', reset);
        $(el.modal).on('show.bs.modal', cacheEls);

        // Delegated so it survives re-rendering of the tabs strip.
        $(document).on('click', '#sdmSheetTabs .sdm-sheet-tab', function () {
            var idx = parseInt($(this).attr('data-sheet-index'), 10);
            var holder = byId('sdmPreviewSheetWrap')._sheets;
            if (!holder || isNaN(idx) || !holder[idx]) return;
            $('#sdmSheetTabs .sdm-sheet-tab').removeClass('active');
            $(this).addClass('active');
            renderSheet(holder[idx], true);
        });

        $('#sdmPreviewPrintBtn').on('click', print);

        // Any page that renders cards via SDMPreview.previewCard() reuses this
        // single delegated opener - no inline onclick / quoting anywhere.
        $(document).on('click', '[data-sdm-preview="1"]', function () {
            var $t = $(this);
            open($t.attr('data-url'), $t.attr('data-name'), $t.attr('data-ext'), $t.attr('data-src'));
        });
    }

    $(function () { init(); });

    return {
        open: open,
        print: print,
        reset: reset,
        isSheet: isSheet,
        isImage: isImage,
        previewCard: previewCard,
        setEndpoint: function (u) { CFG.endpoint = u; }
    };
})();