// Submit over AJAX so the activity list refreshes in place.
    // refreshActivityData() is defined by pages/activity/activity_list_js.php.
    $(function () {
        $('#addActivityForm').on('submit', function (e) {
            e.preventDefault();

            var form = this;
            var btn = $(form).find('[name="btn_add"]');
            var label = btn.val();
            btn.prop('disabled', true).val('Saving...');

            $.ajax({
                url: 'function.php',
                type: 'POST',
                dataType: 'json',
                data: new FormData(form)
            }).done(function (resp) {
                if (typeof showToast === 'function') {
                    showToast((resp && resp.message) || 'Saved.', 'success');
                }
                if (resp && resp.success) {
                    form.reset();
                    $('#addModal').modal('hide');
                    if (typeof refreshActivityData === 'function') refreshActivityData();
                }
            }).fail(function (xhr) {
                var message = 'Network error while saving.';
                try {
                    message = JSON.parse(xhr.responseText).message || message;
                } catch (err) { /* keep default */ }
                if (typeof showToast === 'function') showToast(message, 'error');
            }).always(function () {
                btn.prop('disabled', false).val(label);
            });
        });
    });