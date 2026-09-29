// Submit over AJAX so the activity list refreshes in place.
    $(function () {
        $('#editActivityForm').on('submit', function (e) {
            e.preventDefault();

            var form = this;
            var btn = $(form).find('[name="btn_save"]');
            var label = btn.val();
            btn.prop('disabled', true).val('Saving...');

            $.ajax({
                url: 'function.php',
                type: 'POST',
                dataType: 'json',
                data: $(form).serialize()
            }).done(function (resp) {
                if (typeof showToast === 'function') {
                    showToast((resp && resp.message) || 'Saved.', (resp && resp.success) ? 'success' : 'error');
                }
                if (resp && resp.success) {
                    $('#editModal').modal('hide');
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