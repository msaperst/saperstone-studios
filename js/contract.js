var contract_table;
var resultsSelected = false;
var BootstrapDialog;

$(document).ready(function() {
    contract_table = $('#contracts').DataTable({
        "ajax" : "/api/get-contracts.php",
        "order" : [ [ 2, "asc" ] ],
        "columnDefs" : [ {
            "orderable" : false,
            "searchable" : false,
            "data" : function(row) {
                return '<button type="button" class="btn btn-xs btn-warning edit-contract-btn" data-toggle="tooltip" data-placement="right" title="Edit ' + row.usr + ' Details"><i class="fa fa-pencil-square-o"></i></button>';
            },
            "targets" : 0
        }, {
            "data" : "id",
            "className" : "user-id",
            "visible" : false,
            "searchable" : false,
            "targets" : 1
        }, {
            "data" : "type",
            "className" : "contract-type",
            "targets" : 2
        }, {
            "data" : "title",
            "className" : "contract-title",
            "targets" : 3
        }, {
            "data" : "name",
            "className" : "contract-name",
            "targets" : 4
        }, {
            "data" : "date",
            "className" : "contract-date",
            "targets" : 5
        }, {
            "data" : function(row) {
                return row.signature == "" ? false : true;
            },
            "className" : "contract-signed",
            "targets" : 6
        }, {
            "data" : "amount",
            "className" : "contract-amount",
            "targets" : 7
        } ],
        "fnCreatedRow" : function(nRow, aData) {
            $(nRow).attr('contract-id', aData.id);
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
        var id = $(this).closest('tr').attr('user-id');
        $.get("/api/get-contract.php", {
            id : id
        }, function(data) {
            editContract(data);
        }, "json");
    });
}

function setupAddLineItem() {
    $('[data-toggle="tooltip"]').tooltip();
    $('#add-contract-line-item-btn').off().click(function() {
        var itemInput = $('<input>');
        itemInput.addClass('form-control');
        itemInput.css({'width':'initial','display':'initial'});
        itemInput.attr({'type':'text','placeholder':'Item','value':''});

        var amountInput = $('<input>');
        amountInput.addClass('form-control');
        amountInput.css({'width':'initial','display':'initial'});
        amountInput.attr({'type':'number','step':'0.01','min':'0','placeholder':'Amount','value':''});
        
        var unitInput = $('<input>');
        unitInput.addClass('form-control');
        unitInput.css({'width':'initial','display':'initial'});
        unitInput.attr({'type':'text','placeholder':'Unit','value':''});
        
        var removeButton = $('<button>');
        removeButton.addClass('btn btn-xs btn-danger remove-contract-line-item-btn');
        removeButton.attr({'type':'button','data-toggle':'tooltip','data-placement':'right','title':'Remove Line Item'});
        var icon = $('<em>');
        icon.addClass('fa fa-minus');
        removeButton.append(icon);
        
        var span = $('<span>');
        span.append( $('<br>') );
        span.append( itemInput );
        span.append( ": $" );
        span.append( amountInput );
        span.append( " / " );
        span.append( unitInput );
        span.append( " " );
        span.append( removeButton );
        
        $(this).before(span);
        setupAddLineItem();
    });
    $('.remove-contract-line-item-btn').click(function(){
        $(this).parent().remove();
    });
}

function addContract() {
    BootstrapDialog.show({
        message: function(dialog) {
            var message = $('<div>Loading...</div>');
            $.get('/api/get-contract-types.php', {
            }, function(data) {
                var select = $('<select id="contract-type">');
                select.addClass('form-control');
                $.each( data, function( key, type ) {
                    var option = $('<option>');
                    option.html( type.replace(/\b\w/g, function(l){ return l.toUpperCase() }) );
                    option.attr('type-id',key);
                    select.append(option);
                });
                var content = $('<div></div>').append(select);
                dialog.setMessage(content);
            }, "json");
            return message;
        },
        buttons: [{
            icon : 'glyphicon glyphicon-file',
            label: 'Create Contract',
            cssClass : 'btn-success',
            action: function(dialogRef) {
                dialogRef.close();
                editContract($('#contract-type').val());
            }
        },{
            label: 'Close',
            action: function(dialogRef) {
                dialogRef.close();
            }
        }]
    });
}

function editContract(data) {
    var content = "";
    var title;
    var id = 0;
    if (typeof data === 'object') {
        id = data.id;
        content = 'id=' + data.id;
        title = 'Edit Contract <b>' + data.title + '</b>';
    } else {
        title = 'Add New ' + data + ' Contract';
    }
    BootstrapDialog.show({
        draggable : true,
        size : BootstrapDialog.SIZE_WIDE,
        title : title,
        message: $('<div></div>').load('contracts/' + data.toLowerCase() + '.php?' + content),
        
        buttons : [ {
            icon : 'glyphicon glyphicon-save',
            label : ' Save',
            cssClass : 'btn-success',
            action : function(dialogItself) {
                var $button = this;
                var modal = $button.closest('.modal-content');
                $button.spin();
                disableDialogButtons(dialogItself);
                // send our update
                $.post("/api/update-contract.php", {
                    contract : id,
                }).done(function(data) {
                    if (data !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    } else {
                        dialogItself.close();
                    }
                }).fail(function() {
                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your user's albums.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                }).always(function(){
                    $button.stopSpin();
                    enableDialogButtons(dialogItself);
                });
            }
        }, {
            label : 'Close',
            action : function(dialogItself) {
                dialogItself.close();
            }
        } ],
        onshown : function() {
            setupAddLineItem();
        }
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
