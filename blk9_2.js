$(function () {
    $('#editProfileForm').on('submit', function (e) {
        e.preventDefault();

        var btn = document.getElementById('btn_saveeditProfile');
        var alertBox = $('#editProfileAlert');
        alertBox.html('');

        btn.disabled = true;
        btn.value = 'Saving...';

        $.ajax({
            url: '../../ajax/account_crud.php',
            type: 'POST',
            data: $(this).serialize() + '&action=update_account',
            dataType: 'json',
            success: function (res) {
                if (res && res.success) {
                    $('#editProfileModal').modal('hide');
                    document.getElementById('editProfileForm').reset();
                    if (typeof showToast === 'function') {
                        showToast('Your account has been updated.', 'success');
                    }
                } else {
                    alertBox.html('<div class="alert alert-danger">' +
                        (res && res.error ? res.error : 'The account could not be saved. Please try again.') +
                        '</div>');
                }
            },
            error: function () {
                alertBox.html('<div class="alert alert-danger">Network error. Please try again.</div>');
            },
            complete: function () {
                btn.disabled = false;
                btn.value = 'Save';
            }
        });
    });
});