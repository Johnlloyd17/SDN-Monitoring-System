// Toastr Configuration
toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    preventDuplicates: true,
    onclick: null,
    showDuration: '300',
    hideDuration: '500',
    timeOut: '3000',
    extendedTimeOut: '1000',
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut'
};

// Global Toast Function
// Types: 'success', 'error', 'warning', 'info'
function showToast(msg, type) {
    type = type || 'info';
    console.log('[Toast ' + type.toUpperCase() + ']', msg);
    switch (type) {
        case 'success':
            toastr.success(msg);
            break;
        case 'error':
        case 'danger':
            toastr.error(msg);
            break;
        case 'warning':
            toastr.warning(msg);
            break;
        default:
            toastr.info(msg);
            break;
    }
}
