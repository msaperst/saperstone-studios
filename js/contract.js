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
});
