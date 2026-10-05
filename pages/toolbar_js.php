<?php
/**
 * Shared wiring for the list toolbar that sits above every list table:
 * "Show N records per page" on the left, the search box on the right.
 *
 * Both controls drive the page's own DataTable, so a list page only has to
 * initialise #table with pageLength 5 and dom "ltip" (which hides the
 * DataTables length dropdown and its built-in search box) before including
 * this file. Keeping it here means the toolbar behaves identically on every
 * page instead of drifting one copy at a time.
 */
?>
<script type="text/javascript">
    (function () {
        function getTable() {
            var t = $('#table');
            if (!t.length || !$.fn.DataTable.isDataTable(t)) return null;
            return t.DataTable();
        }

        $(function () {
            var perPage = $('#perPageSelect');
            if (perPage.length) {
                perPage.on('change', function () {
                    var dt = getTable();
                    if (!dt) return;
                    dt.page.len(parseInt(this.value, 10) || 5).draw(false);
                });
            }

            var input = document.getElementById('searchInput');
            if (!input) return;

            function applySearch() {
                var dt = getTable();
                if (!dt) return;
                dt.search(input.value).draw();
            }

            var searchBtn = document.getElementById('searchBtn');
            if (searchBtn) {
                searchBtn.addEventListener('click', applySearch);
            }

            var clearBtn = document.getElementById('clearSearchBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    input.value = '';
                    applySearch();
                });
            }

            var searchTimeout = null;
            input.addEventListener('keyup', function () {
                if (searchTimeout) clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applySearch, 400);
            });
        });
    })();
</script>
