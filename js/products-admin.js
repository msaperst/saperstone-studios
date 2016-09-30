$(document).ready(function() {
    setupSize();
    setupProduct();
});

function setupSize() {
    editSize();
    deleteSize();
    saveSize();
    cancelEditSize();
    addSize();
}
function setupProduct() {
    editProduct();
    deleteProduct();
    saveProduct();
    cancelEditProduct();
    addProduct();
    setupSize();
    addOption();
    deleteOption();
}

function editSize() {
    $('.edit-size-button').off().click(function() {
        var row = $(this).closest('tr');
        $('.edit-size-button', row).addClass('hidden');
        $('.delete-size-button', row).addClass('hidden');
        $('.save-size-button', row).removeClass('hidden');
        $('.cancel-size-button', row).removeClass('hidden');

        var size = $('.product-size', row);
        var sizeInput = $('<input>');
        sizeInput.addClass('form-control input-sm');
        sizeInput.attr('orig', size.html());
        sizeInput.val(size.html());
        size.empty().append(sizeInput);

        var cost = $('.product-cost', row);
        var costInput = $('<input>');
        costInput.addClass('form-control input-sm');
        costInput.attr('orig', cost.html());
        costInput.attr('type', 'number');
        costInput.attr('step', '0.01');
        costInput.attr('min', '0');
        costInput.val(parseFloat(cost.html().replace(/\$|,/g, '')));
        cost.empty().append(costInput);

        var price = $('.product-price', row);
        var priceInput = $('<input>');
        priceInput.addClass('form-control input-sm');
        priceInput.attr('orig', price.html());
        priceInput.attr('type', 'number');
        priceInput.attr('step', '0.01');
        priceInput.attr('min', '0');
        priceInput.val(parseFloat(price.html().replace(/\$|,/g, '')));
        price.empty().append(priceInput);
    });
}

function cancelEditSize() {
    $('.cancel-size-button').off().click(function() {
        var row = $(this).closest('tr');
        $('.edit-size-button', row).removeClass('hidden');
        $('.delete-size-button', row).removeClass('hidden');
        $('.save-size-button', row).addClass('hidden');
        $('.cancel-size-button', row).addClass('hidden');

        var size = $('.product-size input', row);
        size.closest('td').html(size.attr('orig'));

        var cost = $('.product-cost input', row);
        cost.closest('td').html(cost.attr('orig'));

        var price = $('.product-price input', row);
        price.closest('td').html(price.attr('orig'));
    });
}

function deleteSize() {
    $('.delete-size-button').off().click(function() {
        var row = $(this).closest('tr');
        BootstrapDialog.show({
            draggable : true,
            title : 'Are You Sure?',
            message : 'Are you sure you want to delete <b>' + $('.product-size', row).html() + '</b> from <b>' + row.closest('div').find('h3').html() + '</b>',
            buttons : [ {
                icon : 'glyphicon glyphicon-trash',
                label : ' Delete',
                cssClass : 'btn-danger',
                action : function(dialogInItself) {
                    var $button = this; // 'this' here is a jQuery object that
                    // wrapping the <button> DOM element.
                    $button.spin();
                    dialogInItself.enableButtons(false);
                    dialogInItself.setClosable(false);
                    // send our update
                    $.post("/api/delete-product-size.php", {
                        id : row.attr('product-id'),
                    }).done(function() {
                        row.remove();
                        dialogInItself.close();
                    });
                }
            }, {
                label : 'Close',
                action : function(dialogInItself) {
                    dialogInItself.close();
                }
            } ]
        });
    });
}

function saveSize() {
    $('.save-size-button').off().click(function() {
        var button = $(this);
        var row = $(this).closest('tr');
        $('em', button).removeClass('fa-save').addClass('fa-asterisk icon-spin');
        button.prop("disabled", true);
        $.post("/api/update-product-size.php", {
            id : row.attr('product-id'),
            size : $('.product-size input', row).val(),
            cost : $('.product-cost input', row).val(),
            price : $('.product-price input', row).val(),
        }).done(function(data) {
            $('em', button).addClass('fa-save').removeClass('fa-asterisk icon-spin');
            button.prop("disabled", false);
            if (data === "") {
                $('.edit-size-button', row).removeClass('hidden');
                $('.delete-size-button', row).removeClass('hidden');
                $('.save-size-button', row).addClass('hidden');
                $('.cancel-size-button', row).addClass('hidden');
                $('.product-size-error', row.closest('div')).html("");

                var size = $('.product-size input', row);
                size.closest('td').html(size.val());

                var cost = $('.product-cost input', row);
                cost.closest('td').html("$" + cost.val());

                var price = $('.product-price input', row);
                price.closest('td').html("$" + price.val());
            } else {
                $('.product-size-error', row.closest('div')).html(data);
            }
        });
    });
}

function addSize() {
    $('.add-size-button')
            .off()
            .click(
                    function() {
                        var button = $(this);
                        var row = $(this).closest('tr');
                        $('em', button).removeClass('fa-save').addClass('fa-asterisk icon-spin');
                        button.prop("disabled", true);
                        $
                                .post("/api/create-product-size.php", {
                                    type : row.closest('div').attr('product-type'),
                                    size : $('.product-size input', row).val(),
                                    cost : $('.product-cost input', row).val(),
                                    price : $('.product-price input', row).val(),
                                })
                                .done(
                                        function(data) {
                                            $('em', button).addClass('fa-save').removeClass('fa-asterisk icon-spin');
                                            button.prop("disabled", false);
                                            if ($.isNumeric(data) && data !== '0') {
                                                var tr = $('<tr>');

                                                var buttons = $('<td><button type="button" class="btn btn-xs btn-warning edit-size-button"><i class="fa fa-pencil-square-o"></i></button> <button type="button" class="btn btn-xs btn-danger delete-size-button"><i class="fa fa-trash-o"></i></button><button type="button" class="btn btn-xs btn-success save-size-button hidden"><i class="fa fa-save"></i></button> <button type="button" class="btn btn-xs btn-warning cancel-size-button hidden"><i class="fa fa-ban"></i></button></td>');
                                                tr.append(buttons);

                                                var size = $('<td>');
                                                size.addClass('product-size');
                                                size.append($('.product-size input', row).val());
                                                tr.append(size);

                                                var cost = $('<td>');
                                                cost.addClass('product-cost');
                                                cost.append("$" + $('.product-cost input', row).val());
                                                tr.append(cost);

                                                var price = $('<td>');
                                                price.addClass('product-price');
                                                price.append("$" + $('.product-price input', row).val());
                                                tr.append(price);

                                                row.before(tr);
                                                setupSize();

                                                $('.product-size input', row).val("");
                                                $('.product-cost input', row).val("");
                                                $('.product-price input', row).val("");
                                                $('.product-size-error', row.closest('div')).html("");
                                            } else {
                                                $('.product-size-error', row.closest('div')).html(data);
                                            }
                                        });
                    });
}

function editProduct() {
    $('.edit-product-button').off().click(function() {
        var div = $(this).closest('div');
        $('.edit-product-button', div).addClass('hidden');
        $('.delete-product-button', div).addClass('hidden');
        $('.save-product-button', div).removeClass('hidden');
        $('.cancel-product-button', div).removeClass('hidden');

        var name = $('h3', div);
        var nameInput = $('<input>');
        nameInput.addClass('form-control input-lg');
        nameInput.attr('orig', name.html());
        nameInput.val(name.html());
        name.empty().append(nameInput);
    });
}

function cancelEditProduct() {
    $('.cancel-product-button').off().click(function() {
        var div = $(this).closest('div');
        $('.edit-product-button', div).removeClass('hidden');
        $('.delete-product-button', div).removeClass('hidden');
        $('.save-product-button', div).addClass('hidden');
        $('.cancel-product-button', div).addClass('hidden');

        var name = $('h3 input', div);
        name.closest('h3').html(name.attr('orig'));
    });
}

function deleteProduct() {
    $('.delete-product-button').off().click(function() {
        var div = $(this).closest('div');
        BootstrapDialog.show({
            draggable : true,
            title : 'Are You Sure?',
            message : 'Are you sure you want to delete <b>' + $('h3', div).html() + '</b>',
            buttons : [ {
                icon : 'glyphicon glyphicon-trash',
                label : ' Delete',
                cssClass : 'btn-danger',
                action : function(dialogInItself) {
                    var $button = this; // 'this' here is a jQuery object that
                    // wrapping the <button> DOM element.
                    $button.spin();
                    dialogInItself.enableButtons(false);
                    dialogInItself.setClosable(false);
                    // send our update
                    $.post("/api/delete-product.php", {
                        id : div.attr('product-type'),
                    }).done(function() {
                        div.remove();
                        dialogInItself.close();
                    });
                }
            }, {
                label : 'Close',
                action : function(dialogInItself) {
                    dialogInItself.close();
                }
            } ]
        });
    });
}

function saveProduct() {
    $('.save-product-button').off().click(function() {
        var button = $(this);
        var div = $(this).closest('div');
        $('em', button).removeClass('fa-save').addClass('fa-asterisk icon-spin');
        button.prop("disabled", true);
        $.post("/api/update-product.php", {
            id : div.attr('product-type'),
            name : $('h3 input', div).val(),
        }).done(function(data) {
            $('em', button).addClass('fa-save').removeClass('fa-asterisk icon-spin');
            button.prop("disabled", false);
            if (data === "") {
                $('.edit-product-button', div).removeClass('hidden');
                $('.delete-product-button', div).removeClass('hidden');
                $('.save-product-button', div).addClass('hidden');
                $('.cancel-product-button', div).addClass('hidden');
                $('.product-size-error', row.closest('div')).html("");

                var name = $('h3 input', div);
                name.closest('h3').html(name.val());
            } else {
                $('.product-size-error', row.closest('div')).html(data);
            }
        });
    });
}

function addProduct() {
    $('.add-product-button').off().click(
            function() {
                var header = $(this).closest('h2');
                BootstrapDialog.show({
                    draggable : true,
                    title : 'Name Your Product',
                    message : '<input id="product-name" class="form-control" />' + '<div id="product-error" class="error" />',
                    buttons : [
                            {
                                icon : 'glyphicon glyphicon-save',
                                label : ' Save',
                                hotkey : 13,
                                cssClass : 'btn-success',
                                action : function(dialogInItself) {
                                    var $button = this; // 'this' here is a
                                    // jQuery object that
                                    // wrapping the <button>
                                    // DOM element.
                                    $button.spin();
                                    dialogInItself.enableButtons(false);
                                    dialogInItself.setClosable(false);
                                    // send our update
                                    $.post("/api/create-product.php", {
                                        category : header.text().toLowerCase(),
                                        name : $('#product-name').val(),
                                    }).done(
                                            function(data) {
                                                if ($.isNumeric(data) && data !== '0') {

                                                    header.after('<div class="col-md-4 col-sm-6 bootstrap-dialog" product-type="' + data + '">' +
                                                            '<button type="button" class="btn btn-xs btn-warning edit-product-button"><i class="fa fa-pencil-square-o"></i></button> ' +
                                                            '<button type="button" class="btn btn-xs btn-danger delete-product-button"><i class="fa fa-trash-o"></i></button>' +
                                                            '<button type="button" class="btn btn-xs btn-success save-product-button hidden"><i class="fa fa-save"></i></button> ' +
                                                            '<button type="button" class="btn btn-xs btn-warning cancel-product-button hidden"><i class="fa fa-ban"></i></button>' + '<h3 product-type="' + data +
                                                            '" class="text-center editable-header">' + $('#product-name').val() + '</h3>' + '<table class="table table-striped">' + '<thead>' +
                                                            '<tr><th style="width:67px;"></th><th>Size</th><th>Cost</th><th>Price</th></tr>' + '</thead>' + '<tbody>' + '<tr>' + '<td>' +
                                                            '<button type="button" class="btn btn-xs btn-success add-size-button"><i class="fa fa-save"></i></button>' + '</td>' + '<td class="product-size"><input class="form-control input-sm"></td>' +
                                                            '<td class="product-cost"><input class="form-control input-sm" type="number" step="0.01" min="0"></td>' +
                                                            '<td class="product-price"><input class="form-control input-sm" type="number" step="0.01" min="0"></td>' + '</tr>' + '</tbody>' + '</table>' +
                                                            '<div id="product-size-error" class="error"></div>' + '</div>');
                                                    dialogInItself.close();
                                                    setupProduct();
                                                } else {
                                                    $('#product-error').html(data);
                                                    $button.stopSpin();
                                                    dialogInItself.enableButtons(true);
                                                    dialogInItself.setClosable(true);
                                                }
                                            });
                                }
                            }, {
                                label : 'Close',
                                action : function(dialogInItself) {
                                    dialogInItself.close();
                                }
                            } ]
                });
            });
}
function addOption() {
    $('.add-option-button').off().click(function() {
        var button = $(this);
        var input = $(button).next();
        var product_type = $(this).closest('.bootstrap-dialog').attr('product-type');
        $('em', button).removeClass('fa-save').addClass('fa-asterisk icon-spin');
        button.prop("disabled", true);
        $.post("/api/create-product-option.php", {
            type : product_type,
            option : input.val()
        }).done(function(data) {
            $('em', button).addClass('fa-save').removeClass('fa-asterisk icon-spin');
            button.prop("disabled", false);
            if (data === "") {
                var option = $("<span>");
                option.html(input.val());
                option.addClass('selected-album');
                button.closest('.bootstrap-dialog').find('.product-options').append(option);
                input.val("");
                deleteOption();
            } else {
                button.closest('.bootstrap-dialog').find('.product-size-error').html(data);
            }
        });
    });

}
function deleteOption() {
    $('.selected-album').off().click(function() {
        var option = $(this).html();
        var product_type = $(this).closest('.bootstrap-dialog').attr('product-type');
        $(this).remove();
        $.post("/api/delete-product-option.php", {
            type : product_type,
            option : option
        });
    });
}