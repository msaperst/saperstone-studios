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
    $('input.keep,textarea.keep').each(function() {
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
    $.post('/api/sign-contract.php', {
        id : id,
        name : name,
        address : address,
        number : number,
        email : email,
        signature : signature,
        initial : signature,
    }).done(function(data) {
        if (data !== "") {
            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        } else {
            dialogItself.close();
        }
        contract_table.ajax.reload(null, false);
    }).fail(function() {
        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your user's albums.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
    }).always(function() {
        button.stopSpin();
        enableDialogButtons(dialogItself);
    });
}