function checkMain(x) {
	    var checked = $(x).prop('checked');
	    $('.cbxMain').prop('checked', checked)
	    $('tr:visible').each(function () {
	        $(this).find('.chk_delete').each(function () {
	            this.checked = checked;
	        });
	    });
	}

	function success(){
        $('#autoclosable-btn-success').prop('disabled', true);
        $('.alert-autocloseable-success').show();

        $('.alert-autocloseable-success').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-success').prop('disabled', false);
        });
    }

    function save_success(){
        $('#autoclosable-btn-add').prop('disabled', true);
        $('.alert-autocloseable-add').show();

        $('.alert-autocloseable-add').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-add').prop('disabled', false);
        });
    }

    function deleted(){
        $('#autoclosable-btn-danger').prop('disabled', true);
        $('.alert-autocloseable-danger').show();

        $('.alert-autocloseable-danger').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-danger').prop('disabled', false);
        });
    }

    function duplicate(){
        $('#autoclosable-btn-duplicate').prop('disabled', true);
        $('.alert-autocloseable-duplicate').show();

        $('.alert-autocloseable-duplicate').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-duplicate').prop('disabled', false);
        });
    }

    function duplicateuser(){
        $('#autoclosable-btn-duplicateuser').prop('disabled', true);
        $('.alert-autocloseable-duplicateuser').show();

        $('.alert-autocloseable-duplicateuser').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-duplicateuser').prop('disabled', false);
        });
    }

    function filesize(){
        $('#autoclosable-btn-filesize').prop('disabled', true);
        $('.alert-autocloseable-filesize').show();

        $('.alert-autocloseable-filesize').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-filesize').prop('disabled', false);
        });
    }

    function blotter(){
        $('#autoclosable-btn-blotter').prop('disabled', true);
        $('.alert-autocloseable-blotter').show();

        $('.alert-autocloseable-blotter').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-blotter').prop('disabled', false);
        });
    }

    function lengthstay(){
        $('#autoclosable-btn-length').prop('disabled', true);
        $('.alert-autocloseable-length').show();

        $('.alert-autocloseable-length').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-length').prop('disabled', false);
        });
    }

    function end(){
        $('#autoclosable-btn-end').prop('disabled', true);
        $('.alert-autocloseable-end').show();

        $('.alert-autocloseable-end').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-end').prop('disabled', false);
        });
    }

    function start(){
        $('#autoclosable-btn-start').prop('disabled', true);
        $('.alert-autocloseable-start').show();

        $('.alert-autocloseable-start').delay(3000).fadeOut( 'slow', function() {
            $('#autoclosable-btn-start').prop('disabled', false);
        });
    }

$(document).ready(function (){
	$('.chk_delete').click(function () {
        if ($('.chk_delete:checked').length == $('.chk_delete').length) {
            $('.cbxMain').prop('checked', true);
        }
        else {
            $('.cbxMain').prop('checked', false);
        }

        $('#check-all').click(function(){
		    $("input:checkbox").attr('checked', true);
		  });
    });

    $('.no-print').hide();
});