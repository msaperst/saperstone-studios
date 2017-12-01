var contract_table;
var resultsSelected = false;
var BootstrapDialog;

$(document).ready(function() {
    contract_table = $('#contracts').DataTable({
        "ajax" : "/api/get-contracts.php",
        "order" : [ [ 3, "asc" ] ],
        "columnDefs" : [ {
            "orderable" : false,
            "searchable" : false,
            "data" : function(row) {
                var buttons;
                if (row.signature === "" || row.signature === null) {
                    buttons = '<button type="button" class="btn btn-xs btn-warning edit-contract-btn" data-toggle="tooltip" data-placement="right" title="Edit ' + row.name + ' ' + row.session + ' Details"><i class="fa fa-pencil-square-o"></i></button> <button type="button" class="btn btn-xs btn-info view-contract-btn" data-toggle="tooltip" data-placement="right" title="View ' + row.name + ' ' + row.session + ' Details"><i class="fa fa-file-text-o"></i></button>';
                } else {
                    buttons = '<button type="button" class="btn btn-xs btn-info dl-contract-btn" data-toggle="tooltip" data-placement="right" title="Download ' + row.name + ' ' + row.session + ' Contract"><i class="fa fa-file-pdf-o"></i></button>';
                }
                return buttons;
            },
            "targets" : 0
        }, {
            "data" : "id",
            "className" : "user-id",
            "visible" : false,
            "searchable" : false,
            "targets" : 1
        }, {
            "data" : "name",
            "className" : "contract-name",
            "targets" : 2
        }, {
            "data" : function(row) {
                return row.type.replace(/\b\w/g, function(l) {
                    return l.toUpperCase()
                });
            },
            "className" : "contract-type",
            "targets" : 3
        }, {
            "data" : "session",
            "className" : "contract-session",
            "targets" : 4
        }, {
            "data" : "date",
            "className" : "contract-date",
            "targets" : 5
        }, {
            "data" : function(row) {
                return (row.signature === "" || row.signature === null) ? false : true;
            },
            "className" : "contract-signed",
            "targets" : 6
        }, {
            "data" : function(row) {
                return "$" + row.amount;
            },
            "className" : "contract-amount",
            "targets" : 7
        } ],
        "fnCreatedRow" : function(nRow, aData) {
            $(nRow).attr('contract-id', aData.id);
            $(nRow).attr('contract-link', aData.link);
            $(nRow).attr('contract-file', aData.file);
        }
    });
    $('#contracts').on('draw.dt search.dt', function() {
        setupEdit();
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('#add-contract-btn').click(function() {
        addContract();
    });
});

function setupEdit() {
    $('.edit-contract-btn').off().click(function() {
        var id = $(this).closest('tr').attr('contract-id');
        $.get("/api/get-contract.php", {
            id : id
        }, function(data) {
            editContract(data);
        }, "json");
    });
    $('.view-contract-btn').off().click(function() {
        var link = $(this).closest('tr').attr('contract-link');
        window.location.href = '/contract.php?c=' + link;
    });
    $('.dl-contract-btn').off().click(function() {
        var link = $(this).closest('tr').attr('contract-file');
        window.open(link);
    });
}

function setupAddLineItem() {
    $('[data-toggle="tooltip"]').tooltip();
    $('#add-contract-line-item-btn').off().click(function() {
        var itemInput = $('<input>');
        itemInput.addClass('form-control contract-item');
        itemInput.css({
            'width' : 'initial',
            'display' : 'initial'
        });
        itemInput.attr({
            'type' : 'text',
            'placeholder' : 'Item',
            'value' : ''
        });

        var amountInput = $('<input>');
        amountInput.addClass('form-control contract-amount');
        amountInput.css({
            'width' : 'initial',
            'display' : 'initial'
        });
        amountInput.attr({
            'type' : 'number',
            'step' : '0.01',
            'min' : '0',
            'placeholder' : 'Amount',
            'value' : ''
        });

        var unitInput = $('<input>');
        unitInput.addClass('form-control contract-unit');
        unitInput.css({
            'width' : 'initial',
            'display' : 'initial'
        });
        unitInput.attr({
            'type' : 'text',
            'placeholder' : 'Unit',
            'value' : ''
        });

        var removeButton = $('<button>');
        removeButton.addClass('btn btn-xs btn-danger remove-contract-line-item-btn');
        removeButton.attr({
            'type' : 'button',
            'data-toggle' : 'tooltip',
            'data-placement' : 'right',
            'title' : 'Remove Line Item'
        });
        var icon = $('<em>');
        icon.addClass('fa fa-minus');
        removeButton.append(icon);

        var span = $('<span>');
        span.addClass('contract-line-item');
        span.append($('<br>'));
        span.append(itemInput);
        span.append(": $");
        span.append(amountInput);
        span.append(" / ");
        span.append(unitInput);
        span.append(" ");
        span.append(removeButton);

        $(this).before(span);
        setupAddLineItem();
    });
    $('.remove-contract-line-item-btn').click(function() {
        $(this).parent().remove();
    });
}

function setupFormFill() {
    $('input,textarea,select').on('keyup keypress blur change', function() {
        $('[id$=-dup]').each(function() {
            var updateId = $(this).attr('id').replace(new RegExp('-dup$'), '');
            $(this).val($('#' + updateId).val());
        });
    });
    $('select#contract-session').change(function() {
        $('#contract-amount').val($("option:selected", this).attr('cost'));
        $('#contract-details').val($("option:selected", this).attr('details'));
    });
}

function addContract() {
    BootstrapDialog.show({
        message : function(dialog) {
            var message = $('<div>Loading...</div>');
            $.get('/api/get-contract-types.php', {}, function(data) {
                var select = $('<select id="contract-type">');
                select.addClass('form-control');
                $.each(data, function(key, type) {
                    var option = $('<option>');
                    option.html(type.replace(/\b\w/g, function(l) {
                        return l.toUpperCase()
                    }));
                    option.attr('type-id', key);
                    select.append(option);
                });
                var content = $('<div></div>').append(select);
                dialog.setMessage(content);
            }, "json");
            return message;
        },
        buttons : [ {
            icon : 'glyphicon glyphicon-file',
            label : 'Create Contract',
            cssClass : 'btn-success',
            action : function(dialogRef) {
                dialogRef.close();
                editContract($('#contract-type').val());
            }
        }, {
            label : 'Close',
            action : function(dialogRef) {
                dialogRef.close();
            }
        } ]
    });
}

function editContract(data) {
    var content = "";
    var title;
    var type;
    var id = 0;
    var post = "/api/create-contract.php";
    if (typeof data === 'object') {
        id = data.id;
        type = data.type;
        post = "/api/update-contract.php";
        content = 'id=' + data.id;
        title = 'Edit Contract <b>' + data.name + ' ' + data.session + '</b>';
    } else {
        type = data.toLowerCase();
        title = 'Add New ' + data + ' Contract';
    }
    BootstrapDialog.show({
        closable: false,
        draggable : true,
        size : BootstrapDialog.SIZE_WIDE,
        title : title,
        message : $('<div></div>').load('contracts/' + type + '.php?' + content),
        buttons : [ {
            icon : 'glyphicon glyphicon-save',
            label : ' Save',
            cssClass : 'btn-success',
            action : function(dialogItself) {
                var button = this;
                if(requiredInfo(button)) {
                    var inputs = previewContract(id, post, dialogItself, button);
                    submitContract(inputs, post, dialogItself, button);
                }
            }
        }, {
            label : 'Close',
            action : function(dialogItself) {
                dialogItself.close();
            }
        } ],
        onshown : function() {
            setupAddLineItem();
            setupFormFill();
        }
    });
}

function requiredInfo(button) {
    var modal = button.closest('.modal-content');
    if( $('#contract-type').val() === "" ) {
        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred with the form.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        return false;
    }
    if( $('#contract-name').val() === "" ) {
        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Please enter a Client Name before submitting this form.</div>");
        return false;
    }
    if( $('#contract-session').val() === "" ) {
        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Please enter a Session before submitting this form.</div>");
        return false;
    }
    return true;
}

function previewContract(id, post, dialogItself, button) {
    button.spin();
    disableDialogButtons(dialogItself);
    // gather our line items
    var lineItems = [];
    $('.contract-line-item').each(function(index) {
        var lineItem = {};
        lineItem.item = $('.contract-item', this).val();
        lineItem.amount = $('.contract-amount', this).val();
        lineItem.unit = $('.contract-unit', this).val();
        lineItems[index] = lineItem;
    });
    // gather our inputs
    var inputs = {};
    inputs.id = id;
    inputs.type = $('#contract-type').val();
    inputs.name = $('#contract-name').val();
    inputs.address = $('#contract-address').val();
    inputs.number = $('#contract-number').val();
    inputs.email = $('#contract-email').val();
    inputs.date = $('#contract-date').val();
    inputs.location = $('#contract-location').val();
    inputs.session = $('#contract-session').val();
    inputs.details = $('#contract-details').val();
    inputs.amount = $('#contract-amount').val();
    inputs.deposit = $('#contract-deposit').val();
    inputs.invoice = $('#contract-invoice').val();
    inputs.lineItems = lineItems;
    // make our viewable replacements
    $('input[type=hidden]').remove();
    $('#contract-name-signature').prop('disabled', false);
    $('#contract-signature').replaceWith('<div id="contract-signature" class="signature"></div>');
    $('#contract-invoice').remove();
    $('textarea').not('.keep').each(function() {
        $(this).val($(this).val().replace(/(?:\r\n|\r|\n)/g, '<br />'));
    });
    $('input,select,textarea').not('.keep').each(function() {
        $(this).replaceWith($(this).val());
    });
    $('input.keep').each(function() {
        $(this).attr('value', $(this).val());
    });
    $('textarea.keep').each(function() {
        $(this).append($(this).val());
    });
    $('.remove-contract-line-item-btn').each(function() {
        $(this).replaceWith("<br/>");
    });
    $('#add-contract-line-item-btn').remove();
    var content = $('.bootstrap-dialog-message>div>div', dialogItself.getModalBody().html()).html();
    inputs.content = content;

    return inputs;
}

function submitContract(inputs, post, dialogItself, button) {
    var modal = button.closest('.modal-content');
    // send our update
    $.post(post, inputs).done(function(data) {
        if (data !== "") {
            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        } else {
            dialogItself.close();
        }
        contract_table.ajax.reload(null, false);
    }).fail(function(xhr, status, error) {
        if ( xhr.responseText !== "" ) {
            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if ( error === "Unauthorized" ) {
            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while submitting your contract.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function() {
        button.stopSpin();
        enableDialogButtons(dialogItself);
    });
}

function disableDialogButtons(dialog) {
    dialog.enableButtons(false);
    dialog.setClosable(false);
}
function enableDialogButtons(dialog) {
    dialog.enableButtons(true);
    dialog.setClosable(true);
}
