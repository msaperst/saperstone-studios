var signature;
var initial;

$(document).ready(function() {
    $("#contract-signature").wrap("<div id='contract-signature-holder' class='signature-holder'>Sign inside the dotted area</div>");
    var buttonSig = $('<button type="button" id="contract-clear-signature" class="btn btn-danger" data-toggle="tooltip" data-placement="right" title="Clear Current Signature"><i class="fa fa-eraser"></i> Clear</button>');
    signature = $("#contract-signature").jSignature();
    $("#contract-signature").append(buttonSig);
    $("#contract-signature").addClass('text-right');

    var buttonIni = $('<button type="button" id="contract-clear-initial" class="btn btn-danger" data-toggle="tooltip" data-placement="right" title="Clear Current Signature"><i class="fa fa-eraser"></i> Clear</button>');
    initial = $("#contract-initial").jSignature();
    $("#contract-initial").append(buttonIni);

    $('#contract-clear-signature').click(function() {
        signature.jSignature("reset");
    });

    $('#contract-clear-initial').click(function() {
        initial.jSignature("reset");
    });

    $('input,textarea,#contract-signature,#contract-initial').change(function() {
        checkInputs();
    });
    
    $('#contract-submit').click(function(){
        submitContract();
    });
});

function checkInputs() {
    var okToSubmit = true;
    $('.keep').each(function() {
        if ($(this).val() === "") {
            okToSubmit = false;
        }
    });
    if (signature.jSignature("getData", "native").length === 0 || initial.jSignature("getData", "native").length === 0) {
        okToSubmit = false;
    }
    if (okToSubmit) {
        $('#contract-submit').removeClass('disabled').prop('disabled', false);
    } else {
        $('#contract-submit').addClass('disabled').prop('disabled', true);
    }
    return okToSubmit;
}

function submitContract() {
    if ( ! checkInputs() ) {
        BootstrapDialog.alert('Please finish signing the contract before submitting.');
    }
    
    $('#contract-submit').prop('disabled',true);
    $('#contract-submit em').addClass('fa-spinner fa-spin').removeClass('fa-paper-plane');
    
    var inputs = previewContract();
    inputs.content = $('#contract').html();
    
    $.post('/api/sign-contract.php', inputs).done(function(data) {
        if (data !== "") {
            $('#contract-messages').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        } else {
            $('#contract-submit').remove();
            $('#contract-messages').append("<div class='alert alert-success'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Thank you for signing the contract. You will recieve a confirmation email with the final contract attached shortly.</div>");
            setTimeout(function () {
                location.reload(true);
             }, 10000);
        }
    }).fail(function(xhr, status, error) {
        if ( xhr.responseText !== "" ) {
            $('#contract-messages').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if ( error === "Unauthorized" ) {
            $('#contract-messages').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#contract-messages').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while submitting your contract.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function(){
        $('#contract-submit').prop('disabled',false);
        $('#contract-submit em').removeClass('fa-spinner fa-spin').addClass('fa-paper-plane');
    });
}

function previewContract() {
    var inputs = {};
    inputs.id = $('#contract-id').val();
    inputs.name = $('#contract-name-signature').val();
    inputs.address = $('#contract-address').val();
    inputs.number = $('#contract-number').val();
    inputs.email = $('#contract-email').val();
    inputs.signature = signature.jSignature("getData");
    inputs.initial = initial.jSignature("getData");
    
    $('input[type=hidden]').remove();
    $('textarea.keep').each(function() {
        $(this).val($(this).val().replace(/(?:\r\n|\r|\n)/g, '<br />'));
    });
    $('.keep[type=tel]').each(function() {
        var tel = $(this).val();
        $(this).replaceWith("<a href='tel:" + tel + "' target='_blank'>" + tel + "</a>");
    });
    $('.keep[type=email]').each(function() {
        var email = $(this).val();
        $(this).replaceWith("<a href='mailto:" + email + "' target='_blank'>" + email + "</a>");
    });
    $('.keep').each(function() {
        $(this).replaceWith($(this).val());
    });
    var sig = $('<img>');
    sig.attr('src','data:'+signature.jSignature("getData","svgbase64").join(","));
    $('#contract-signature-holder').html(sig).removeClass('signature-holder');
    var ini = $('<img>');
    ini.attr('src','data:'+initial.jSignature("getData","svgbase64").join(","));
    $('#contract-initial-holder').html(ini).removeClass('signature-holder');
    
    return inputs;
}