<?php
require_once __DIR__ . '/../auth_check.php'; require_auth();
?>
<!DOCTYPE html>
<html>
<?php

if (!isset($_SESSION['role'])) {
    header("Location: ../../login.php");
} else {
    ob_start();
    include('../head_css.php');
?>

<body class="skin-black">
	<?php include '../connection.php'; ?>
	<?php include('../header.php'); ?>

	<div class="wrapper row-offcanvas row-offcanvas-left">
		<?php include('../sidebar-left.php'); ?>

		<aside class="right-side">
			<section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
				<h1 style="margin: 0;">Series Register</h1>
				<div class="header-date-time" id="dateTime"></div>
			</section>
			<section class="content">
				<div class="row">
					<div class="box">
						<div class="box-header">
							<div class="col-md-12 col-sm-12 col-xs-12"><br>

								<div class="panel panel-default">
									<div class="panel-heading">
										Property No. Series Register
										<span class="pull-right text-muted" id="registerSummary" style="font-size:12px;"></span>
									</div>
									<div class="panel-body">

										<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
											<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
												<input type="text" id="srSearch" class="form-control input-sm" placeholder="Search no., description, serial or location" style="width:320px;" />
												<label style="margin:0; font-weight:normal;"><input type="checkbox" id="srFormatBOnly" style="margin-right:4px;" />Format B only</label>
											</div>
										</div>

										<div class="box-body table-responsive">
											<table class="table table-bordered table-striped">
												<thead>
													<tr>
														<th style="width: 40px;">No.</th>
														<th>Inventory Item no.</th>
														<th>Description</th>
														<th>Serial Number</th>
														<th style="width: 50px;">Qty</th>
														<th>Date Acquired</th>
														<th>Received</th>
														<th>Assigned / Deployed</th>
														<th style="width: 70px;">Location</th>
													</tr>
												</thead>
												<tbody id="registerBody">
													<tr>
														<td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td>
													</tr>
												</tbody>
											</table>
										</div>

									</div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</section>
		</aside>
	</div>

	<?php include dirname(__DIR__) . '/scripts.php'; ?>

	<script>
		(function() {
			var basePath = '../../ajax/';

			function escHtml(str) {
				if (str === null || str === undefined) return '';
				return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
			}

			var registerData = null;

			function fmtCell(v) {
				if (v === null || v === undefined || String(v).trim() === '') return '<span class="text-muted">&ndash;</span>';
				return escHtml(v);
			}

			function render() {
				if (!registerData) return;
				var box = document.getElementById('registerBody');
				var q = String(document.getElementById('srSearch').value || '').trim().toLowerCase();
				var onlyB = document.getElementById('srFormatBOnly').checked;

				var html = '';
				var index = 1;

				function rowHtml(r) {
					var hit = true;
					if (q) {
						var hay = [r.inventory_item_no, r.description, r.serial, r.f_loc || '', r.assigned_to].join(' ').toLowerCase();
						hit = hay.indexOf(q) !== -1;
					}
					if (onlyB && !r.is_format_b) hit = false;
					if (!hit) return '';
					var out = '<tr class="sr-row">' +
						'<td>' + index + '</td>' +
						'<td>' + escHtml(r.inventory_item_no) + '</td>' +
						'<td>' + escHtml(r.description) + '</td>' +
						'<td>' + escHtml(r.serial) + '</td>' +
						'<td>' + escHtml(r.quantity) + '</td>' +
						'<td>' + escHtml(r.date) + '</td>' +
						'<td>' + escHtml(r.received) + '</td>' +
						'<td>' + escHtml(r.assigned_to) + '</td>' +
						'<td>' + (r.is_format_b ? '<code>' + escHtml(r.f_loc) + '</code>' : '<span class="text-muted">&ndash;</span>') + '</td>' +
						'</tr>';
					index++;
					return out;
				}

				var currentYear = null;
				registerData.data.forEach(function(r) {
					if (currentYear !== r.f_year) {
						currentYear = r.f_year;
						if (onlyB || q) {
							// count matching rows under this header for the status message is overkill; always render header if any row matches
						}
						html += '<tr class="active"><td colspan="9"><strong>Year ' + escHtml(r.f_year) + '</strong></td></tr>';
						index = 1;
					}
					html += rowHtml(r);
				});

				if (registerData.others.length > 0 && !onlyB) {
					var othersHit = false;
					var before = html.length;
					html += '<tr class="active"><td colspan="9"><strong>Other formats</strong></td></tr>';
					index = 1;
					registerData.others.forEach(function(r) { html += rowHtml(r); });
					othersHit = html.length > before;
					if (!othersHit) html = html.substring(0, before);
				}

				if (!html) {
					html = '<tr><td colspan="9" class="text-center text-muted">' +
						(registerData.data.length + registerData.others.length === 0 ? 'No property numbers have been registered yet.' : 'No records match the current filter.') +
						'</td></tr>';
				}
				box.innerHTML = html;
			}

			function load() {
				$.getJSON(basePath + 'series_register_data.php', function(res) {
					if (!res || !res.success) {
						document.getElementById('registerBody').innerHTML =
							'<tr><td colspan="9" class="text-center text-danger">Failed to load the series register.</td></tr>';
						return;
					}
					registerData = res;
					var total = (res.data || []).length + (res.others || []).length;
					document.getElementById('registerSummary').textContent =
						total + ' numbered record(s) \u00b7 ' + (res.years || 0) + ' year(s) \u00b7 ' +
						(res.others || []).length + ' other format(s)';
					render();
				}).fail(function() {
					document.getElementById('registerBody').innerHTML =
						'<tr><td colspan="9" class="text-center text-danger">Failed to load the series register (network error).</td></tr>';
				});
			}

			document.getElementById('srSearch').addEventListener('input', render);
			document.getElementById('srFormatBOnly').addEventListener('change', render);

			function updateDateTime() {
				var now = new Date();
				document.getElementById('dateTime').innerText = now.toLocaleString('en-US', {
					year: 'numeric',
					month: 'long',
					day: 'numeric',
					hour: '2-digit',
					minute: '2-digit',
					second: '2-digit',
					hour12: true
				});
			}
			setInterval(updateDateTime, 1000);
			updateDateTime();

			load();
		})();
	</script>

</body>

</html>
<?php } ?>