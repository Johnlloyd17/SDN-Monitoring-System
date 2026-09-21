/*
 * SDN Monitoring System - Due-date push notifications
 * Plain AJAX polling every 45s (no WebSockets / SSE).
 * Serves the header bell dropdown (ajax/notifications_data.php).
 */
(function () {
    'use strict';

    var cfg = window.SDN_NOTIF || {};
    var base = cfg.base || '';
    var ENDPOINT = base + 'ajax/notifications_data.php';
    var POLL_MS = (typeof cfg.pollMs === 'number' && cfg.pollMs > 0) ? cfg.pollMs : 45000;

    var known = {};      // source_type:id already seen (baseline seeded on first poll)
    var firstPoll = true;

    function keyOf(it) {
        return it.source_type + ':' + it.id;
    }

    /* ---- Render: header bell dropdown ---- */
    function renderBell(list) {
        var badge = $('#notifBadge');
        var countText = $('#notifCountText');
        var listEl = $('#notifList');
        var dismissAll = $('#notifDismissAll');
        if (!listEl.length) return;

        var n = list.length;
        badge.text(n).css('display', n > 0 ? '' : 'none');
        if (countText.length) countText.text(n);
        if (dismissAll.length) dismissAll.toggle(n > 0);

        listEl.empty();
        if (n === 0) {
            listEl.append('<li class="text-center text-muted" style="padding:10px;">No pending alerts</li>');
            return;
        }
        $.each(list, function (i, it) {
            listEl.append(bellItem(it));
        });
    }

    function bellItem(it) {
        var cls = it.overdue ? 'notif-overdue' : 'notif-due';
        var tag = it.overdue ? ' <span class="label label-danger">OVERDUE</span>' : '';
        return '<li class="notif-item ' + cls + '">' +
            '<a href="' + base + it.link + '" class="notif-title">' + escHtml(it.label) + tag + '</a>' +
            '<a href="#" class="notif-action notif-view" data-notif-view="' + keyOf(it) + '" title="View details">View</a>' +
            '<a href="#" class="notif-action notif-x" data-notif-dismiss="' + keyOf(it) + '" title="Dismiss">&times;</a>' +
            '</li>';
    }

    /* Render the same alert list into any widget (e.g. a dashboard panel).
       Reuses bellItem() so View/Dismiss actions work automatically via the
       delegated handlers bound on document. */
    function renderInto(selector, list) {
        var $el = $(selector);
        if (!$el.length) return;
        $el.empty();
        if (!list || !list.length) {
            $el.append('<li class="text-center text-muted" style="padding:10px;">No pending alerts</li>');
            return;
        }
        $.each(list, function (i, it) {
            $el.append(bellItem(it));
        });
    }

    /* Live-update hook: pages may register a callback to receive the newest
       alert list on every poll. */
    window.SDN_NOTIF = window.SDN_NOTIF || {};
    window.SDN_NOTIF.renderList = renderInto;

    /* ---- Toast only genuinely-new alerts (never on the first/baseline poll) ---- */
    function checkNew(list) {
        var fresh = [];
        if (!firstPoll) {
            $.each(list, function (i, it) {
                if (!known[keyOf(it)]) fresh.push(it);
            });
        }
        $.each(list, function (i, it) {
            known[keyOf(it)] = true;
        });
        firstPoll = false;
        return fresh;
    }

    function notify(it) {
        if (!(window.toastr && typeof showToast === 'function')) return;
        showToast(it.label, it.overdue ? 'error' : 'warning');
    }

    /* ---- Poll ---- */
    function poll() {
        $.ajax({
            url: ENDPOINT,
            method: 'GET',
            dataType: 'json',
            cache: false,
            timeout: 15000
        }).done(function (res) {
            var list = (res && res.items) || [];
            var fresh = checkNew(list);
            renderBell(list);
            if (typeof cfg.onList === 'function') {
                cfg.onList(list);
            }
            $.each(fresh, function (i, it) {
                notify(it);
            });
        }).fail(function () {
            // transient network/HTTP error: stay silent, next poll will retry
        });
    }

    function dismiss(sourceType, sourceId, minutes) {
        $.ajax({
            url: ENDPOINT,
            method: 'POST',
            data: { action: 'dismiss', source_type: sourceType, source_id: sourceId, duration_minutes: minutes },
            dataType: 'json'
        }).done(function (res) {
            if (res && res.success) {
                poll();
                if (typeof showToast === 'function') {
                    showToast('Notification snoozed for ' + durationLabel(minutes) + '.', 'success');
                }
            }
        });
    }

    function dismissAll(minutes) {
        $.ajax({
            url: ENDPOINT,
            method: 'POST',
            data: { action: 'dismiss_all', duration_minutes: minutes },
            dataType: 'json'
        }).done(function (res) {
            if (res && res.success) {
                poll();
                if (typeof showToast === 'function') {
                    showToast('All notifications snoozed for ' + durationLabel(minutes) + '.', 'success');
                }
            }
        });
    }

    var DISMISS_DURATIONS = [15, 60, 360, 720, 1440];
    var DURATION_LABELS = { 15: '15 minutes', 60: '1 hour', 360: '6 hours', 720: '12 hours', 1440: '24 hours' };

    function durationLabel(minutes) {
        return DURATION_LABELS[minutes] || (minutes + ' minutes');
    }
    var dismissTarget = null; // 'all' or 'bill:123' / 'letter:123'

    function pickDuration() {
        var m = parseInt(String($('input[name="notifDismissDur"]:checked').val() || ''), 10);
        return DISMISS_DURATIONS.indexOf(m) === -1 ? 1440 : m;
    }

    /* Opens the confirm modal asking which re-notify duration to apply.
       Falls back to an immediate 24h dismissal if Bootstrap modal isn't available. */
    function openDismiss(target) {
        if (typeof $.fn.modal !== 'function') {
            if (target === 'all') dismissAll(1440);
            else {
                var p = String(target || '').split(':');
                if (p.length === 2 && p[0] && parseInt(p[1], 10) > 0) dismiss(p[0], p[1], 1440);
            }
            return;
        }
        dismissTarget = target;
        $('#notifDismissModal').modal('show');
    }

    /* ---- Read-only detail modal builders (View) ---- */
    function escHtml(str) {
        if (str === null || str === undefined) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str)));
        return d.innerHTML;
    }

    function dash(v) {
        var s = (v === null || v === undefined) ? '' : String(v).trim();
        return s === '' ? '&mdash;' : escHtml(s);
    }

    function linkify(url, label) {
        url = (url || '').trim();
        if (!url) return '&mdash;';
        var href = url;
        if (!/^https?:\/\//i.test(href) && !/^mailto:/i.test(href)) href = 'https://' + href;
        var safe = escHtml(href);
        var text = (label || url).replace(/^https?:\/\//i, '');
        if (text.length > 45) text = text.substring(0, 45) + '&hellip;';
        return '<a href="' + safe + '" target="_blank" rel="noopener" title="' + safe + '">' + escHtml(text) + '</a>';
    }

    function field(label, html) {
        return '<div class="form-group">' +
            '<label>' + escHtml(label) + '</label>' +
            '<div class="notif-view-value">' + html + '</div>' +
            '</div>';
    }

    function twoCol(pairs) {
        var html = '<div class="row">';
        for (var i = 0; i < pairs.length; i++) html += '<div class="col-md-6">' + pairs[i] + '</div>';
        html += '</div>';
        return html;
    }

    function statusBadge(paid) {
        var ok = (paid === 1 || paid === '1' || paid === true);
        return ok ? '<span class="label label-success">Paid</span>' : '<span class="label label-warning">Unpaid</span>';
    }

    function amountHtml(v) {
        var s = (v === null || v === undefined) ? '' : String(v).trim();
        if (s === '' || isNaN(parseFloat(s))) return '&mdash;';
        var num = Number(parseFloat(s));
        return '&#8369; ' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function multilineHtml(v) {
        var s = (v === null || v === undefined) ? '' : String(v).trim();
        return s === '' ? '&mdash;' : escHtml(s).replace(/\n/g, '<br/>');
    }

    function billDetailHtml(row) {
        var html = twoCol([
            field('Date Received', dash(row.date_received)),
            field('Type of Billing', dash(row.type_of_billing)),
            field('Link to File', linkify(row.link_to_file, 'Open bill file')),
            field('Amount', amountHtml(row.amount)),
            field('Location/Office', dash(row.location_office)),
            field('Due Date', dash(row.due_date)),
            field('Disconnection Date', dash(row.disconnection_date)),
            field('Status', statusBadge(row.status)),
            field('Date Paid', dash(row.date_paid)),
            field('Link to OR', linkify(row.link_to_or, 'Open OR')),
            field('Remarks', multilineHtml(row.remarks))
        ]);
        if (row.status === 0 || row.status === '0') {
            html += '<div class="notif-view-actions">' +
                '<button type="button" class="btn btn-success btn-sm" data-notif-action="bill" data-notif-id="' + escHtml(row.id) + '" data-notif-amount="' + escHtml(row.amount) + '">' +
                '<i class="fa fa-check"></i> Mark as Paid</button></div>';
        }
        return html;
    }

    function letterDetailHtml(row) {
        var fr = row && row.for_response;
        var forResp = (!fr || fr === '') ? '&mdash;'
            : (fr === 'Y' ? '<span class="label label-warning">Yes</span>'
                : (fr === 'N' ? '<span class="label label-default">No</span>' : escHtml(fr)));
        var html = twoCol([
            field('Date', dash(row.date)),
            field('Type', dash(row.type)),
            field('Subject', multilineHtml(row.subject)),
            field('FW4A', dash(row.fw4a)),
            field('Drive Link - Incoming File', linkify(row.link_incoming, 'Open incoming file')),
            field('For Response?', forResp),
            field('Date Responded / Sent', dash(row.date_responded)),
            field('Drive Link - Outgoing File', linkify(row.link_outgoing, 'Open outgoing file')),
            field('Responsible Person', dash(row.responsible_person)),
            field('Who Attended', multilineHtml(row.who_attended)),
            field('Remarks', multilineHtml(row.remarks)),
            field('Post Activity Report', linkify(row.post_activity_report, 'Open activity report'))
        ]);
        var responded = row && row.date_responded;
        var needsResp = fr === 'Y' && (responded === null || responded === undefined || responded === '');
        if (needsResp) {
            html += '<div class="notif-view-actions">' +
                '<button type="button" class="btn btn-success btn-sm" data-notif-action="letter" data-notif-id="' + escHtml(row.id) + '">' +
                '<i class="fa fa-check"></i> Mark as Responded</button></div>';
        }
        return html;
    }

    function viewDetail(sourceType, sourceId) {
        var $m = $('#notifViewModal');
        var $body = $('#notifViewBody');
        if (!$m.length || !$body.length || typeof $.fn.modal !== 'function') return;
        $body.html('<p class="text-muted text-center" style="margin:0;"><i class="fa fa-spinner fa-spin"></i> Loading details&hellip;</p>');
        $('#notifViewTitle').html('<i class="fa fa-eye"></i> ' + (sourceType === 'bill' ? 'Bill Details' : 'Letter Details'));
        $m.modal('show');
        $.ajax({
            url: base + (sourceType === 'bill' ? 'ajax/bills_get_item.php' : 'ajax/letters_get_item.php'),
            method: 'GET',
            data: { id: sourceId },
            dataType: 'json',
            cache: false
        }).done(function (row) {
            if (!row || row.error) {
                var msg = (row && row.error) ? String(row.error) : 'Unable to load details.';
                $body.html('<p class="text-danger text-center" style="margin:0;">' + escHtml(msg) + '</p>');
                return;
            }
            viewCache = { sourceType: sourceType, row: row };
            $body.html(sourceType === 'bill' ? billDetailHtml(row) : letterDetailHtml(row));
        }).fail(function () {
            $body.html('<p class="text-danger text-center" style="margin:0;">Failed to load details.</p>');
        });
    }

    var viewCache = null;      // last-rendered { sourceType, row } so quick actions can update in place
    var actionTarget = null;   // { sourceType, id, amount } for the confirm dialog

    function todayStr() {
        var d = new Date();
        var m = ('0' + (d.getMonth() + 1)).slice(-2);
        var day = ('0' + d.getDate()).slice(-2);
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function openActionConfirm(sourceType, id, amount) {
        if (typeof $.fn.modal !== 'function') return;
        actionTarget = { sourceType: sourceType, id: id, amount: amount };
        var isBill = sourceType === 'bill';
        var row = viewCache && viewCache.row;
        $('#notifActionTitle').html(isBill
            ? '<i class="fa fa-check"></i> Mark as Paid'
            : '<i class="fa fa-check"></i> Mark as Responded');
        $('#notifActionMsg').text(isBill
            ? ((row && row.type_of_billing) ? ('Mark this ' + row.type_of_billing + ' as Paid?') : 'Mark this bill as Paid?')
            : 'Mark this letter as responded to?');
        $('#notifActionDateLabel').text(isBill ? 'Date Paid' : 'Date Responded');
        $('#notifActionDate').val(todayStr());
        $('#notifActionConfirmModal').modal('show');
    }

    function updateMoneyStat(selector, delta) {
        var $el = $(selector);
        if (!$el.length || !isFinite(delta)) return;
        var raw = $el.text().replace(/[^0-9.\-]/g, '');
        var cur = parseFloat(raw) || 0;
        var next = cur + delta;
        if (!isFinite(next)) return;
        $el.text('PHP ' + next.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    function updateCountStat(selector, delta) {
        var $el = $(selector);
        if (!$el.length || !isFinite(delta)) return;
        var cur = parseInt($el.text().replace(/[^0-9]/g, ''), 10);
        var next = (isNaN(cur) ? 0 : cur) + delta;
        $el.text(String(Math.max(0, next)));
    }

    /* Remove the resolved alert from the header bell and any dashboard panel
       list immediately (the subsequent poll() re-renders from the server). */
    function removeItemFromLists(key) {
        $('.notif-item').filter(function () {
            var k = $(this).find('[data-notif-view]').attr('data-notif-view');
            return k === key;
        }).remove();
    }

    function submitAction(target, dateVal) {
        var endpoint = base + (target.sourceType === 'bill' ? 'ajax/bills_crud.php' : 'ajax/letters_crud.php');
        var payload = target.sourceType === 'bill'
            ? { action: 'mark_paid', id: target.id, date_paid: dateVal }
            : { action: 'mark_responded', id: target.id, date_responded: dateVal };
        return $.ajax({
            url: endpoint,
            method: 'POST',
            data: payload,
            dataType: 'json'
        });
    }

    /* The list lives inside a slimScroll container (see js/AdminLTE/app.js) that only
       listens for legacy mousewheel/DOMMouseScroll events. Browsers that no longer emit
       those (e.g. Firefox) fall back to native scrolling which bleeds into the page at
       the list boundaries - so intercept the modern wheel event and stop the chain there.
       overscroll-behavior: contain (CSS) is the passive safety net. */
    document.addEventListener('wheel', function (e) {
        var el = e.target && e.target.closest ? e.target.closest('#notifList') : null;
        if (!el) return;
        var up = e.deltaY < 0;
        var down = e.deltaY > 0;
        var atTop = el.scrollTop <= 1 && up;
        var atBottom = (el.scrollHeight - el.scrollTop - el.clientHeight) <= 1 && down;
        if (atTop || atBottom) e.preventDefault();
    }, { passive: false, capture: true });

    $(function () {
        if (!base) return; // notifications wiring not present in this document

        $(document).on('click', '[data-notif-dismiss]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var key = String($(this).attr('data-notif-dismiss') || '');
            var parts = key.split(':');
            if (parts.length === 2 && parts[0] && parseInt(parts[1], 10) > 0) {
                openDismiss(key);
            }
        });

        $(document).on('click', '[data-notif-view]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var parts = String($(this).attr('data-notif-view') || '').split(':');
            if (parts.length === 2 && (parts[0] === 'bill' || parts[0] === 'letter') && parseInt(parts[1], 10) > 0) {
                viewDetail(parts[0], parts[1]);
            }
        });

        $(document).on('click', '[data-notif-action]', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var sourceType = String($(this).attr('data-notif-action') || '');
            var id = parseInt($(this).attr('data-notif-id'), 10);
            var amount = $(this).attr('data-notif-amount');
            if ((sourceType === 'bill' || sourceType === 'letter') && id > 0) {
                openActionConfirm(sourceType, id, amount);
            }
        });

        $('#notifActionConfirm').on('click', function () {
            var $btn = $(this);
            if ($btn.prop('disabled')) return;
            var target = actionTarget;
            if (!target) return;
            $btn.prop('disabled', true);

            var dateVal = $('#notifActionDate').val() || todayStr();
            submitAction(target, dateVal)
                .done(function (res) {
                    if (res && res.success) {
                        if (typeof $.fn.modal === 'function') $('#notifActionConfirmModal').modal('hide');
                        var key = target.sourceType + ':' + target.id;
                        if (viewCache) {
                            if (target.sourceType === 'bill') {
                                viewCache.row.status = 1;
                                viewCache.row.date_paid = dateVal;
                            } else {
                                viewCache.row.date_responded = dateVal;
                            }
                            var $body = $('#notifViewBody');
                            if ($body.length) {
                                $body.html(target.sourceType === 'bill' ? billDetailHtml(viewCache.row) : letterDetailHtml(viewCache.row));
                            }
                        }
                        if (target.sourceType === 'bill') {
                            var amt = parseFloat(target.amount);
                            if (isFinite(amt)) {
                                updateMoneyStat('#statUnpaidAmount', -amt);
                                updateMoneyStat('#statBillsPaid', amt);
                            }
                        } else {
                            updateCountStat('#statNeedsResponse', -1);
                        }
                        removeItemFromLists(key);
                        if (typeof showToast === 'function') {
                            showToast(target.sourceType === 'bill' ? 'Bill marked as Paid.' : 'Letter marked as Responded.', 'success');
                        }
                        poll();
                    } else {
                        if (typeof showToast === 'function') {
                            showToast((res && res.error) ? String(res.error) : 'Failed to update.', 'error');
                        }
                    }
                })
                .fail(function () {
                    if (typeof showToast === 'function') showToast('Update failed. Please try again.', 'error');
                })
                .always(function () {
                    actionTarget = null;
                    $btn.prop('disabled', false);
                });
        });

        $(document).on('hidden.bs.modal', '#notifViewModal', function () {
            $('#notifViewBody').empty();
        });

        $(document).on('click', '#notifDismissAll', function (e) {
            e.preventDefault();
            openDismiss('all');
        });

        $('#notifDismissConfirm').on('click', function () {
            var minutes = pickDuration();
            var target = dismissTarget;
            dismissTarget = null;
            if (typeof $.fn.modal === 'function') $('#notifDismissModal').modal('hide');
            if (target === 'all') {
                dismissAll(minutes);
            } else if (target) {
                var p = target.split(':');
                if (p.length === 2 && (p[0] === 'bill' || p[0] === 'letter') && parseInt(p[1], 10) > 0) {
                    dismiss(p[0], p[1], minutes);
                }
            }
        });

        poll();
        setInterval(poll, POLL_MS);
    });
})();