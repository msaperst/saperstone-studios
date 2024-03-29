var resultsSelected = false;

$(document).ready(function () {
    $('#delete-image-btn').click(function () {
        deleteImage();
    });
    $('#edit-image-btn').click(function () {
        editImage();
    });

    $("#sort-gallery-btn").click(function () {
        sortGallery();
    });

    $("#save-gallery-btn").click(function () {
        saveGallery(getQueryVariable('w'));
    });

    $("#edit-gallery-btn").click(function () {
        editGallery(getQueryVariable('w'));
    });

    $('[data-toggle="tooltip"]').tooltip();
});

function editImage() {
    $('.carousel').carousel("pause");
    var img = $('.carousel div.active div:first-child');
    BootstrapDialog.show({
        draggable: true,
        title: 'Edit Image Metadata',
        message: function () {
            var inputs = $('<div>');
            inputs.addClass('form-horizontal');

            var titleDiv = $('<div>');
            titleDiv.addClass('form-group');
            var titleLabel = $('<label>');
            titleLabel.addClass('control-label col-sm-2');
            titleLabel.attr('for', 'gallery-title');
            titleLabel.html('Title: ');
            var titleInputDiv = $('<div>');
            titleInputDiv.addClass('col-sm-10');
            var titleInput = $('<input>');
            titleInput.attr('id', 'gallery-title');
            titleInput.attr('type', 'text');
            titleInput.addClass('form-control');
            titleInput.attr('placeholder', 'Image Title');
            titleInput.val(img.attr('alt'));
            titleInput.on('keyup keypress blur change', function () {
                if ( $('#gallery-filename-match').prop('checked') ) {
                    updateFilename();
                }
            });
            inputs.append(titleDiv.append(titleLabel).append(titleInputDiv.append(titleInput)));

            var captionDiv = $('<div>');
            captionDiv.addClass('form-group');
            var captionLabel = $('<label>');
            captionLabel.addClass('control-label col-sm-2');
            captionLabel.attr('for', 'gallery-caption');
            captionLabel.html('Caption: ');
            var captionInputDiv = $('<div>');
            captionInputDiv.addClass('col-sm-10');
            var captionInput = $('<input>');
            captionInput.attr('id', 'gallery-caption');
            captionInput.attr('type', 'text');
            captionInput.addClass('form-control');
            captionInput.attr('placeholder', 'Image Caption');
            captionInput.val(img.next().children().html());
            inputs.append(captionDiv.append(captionLabel).append(captionInputDiv.append(captionInput)));

            var filenameDiv = $('<div>');
            filenameDiv.addClass('form-group');
            var filenameLabel = $('<label>');
            filenameLabel.addClass('control-label col-sm-2');
            filenameLabel.attr('for', 'gallery-filename');
            filenameLabel.html('Filename: ');
            var filenameInputDiv = $('<div>');
            filenameInputDiv.addClass('col-sm-9');
            var filenameInput = $('<input>');
            filenameInput.attr('id', 'gallery-filename');
            filenameInput.attr('type', 'text');
            filenameInput.addClass('form-control');
            filenameInput.attr('placeholder', 'Filename including path');
            filenameInput.val(img.attr('style').split("'")[1]);
            var filenameMatchDiv = $('<div>');
            filenameMatchDiv.addClass('col-sm-1');
            var filenameMatch = $('<input>');
            filenameMatch.attr('id', 'gallery-filename-match');
            filenameMatch.attr('type', 'checkbox');
            filenameMatch.attr('checked', 'true');
            filenameMatch.attr('title', 'Match title');
            filenameMatch.addClass('form-control');
            inputs.append(filenameDiv.append(filenameLabel).append(filenameInputDiv.append(filenameInput)).append(filenameMatchDiv.append(filenameMatch)));

            return inputs;
        },
        buttons: [{
            icon: 'glyphicon glyphicon-pencil',
            label: ' Update',
            cssClass: 'btn-info',
            action: function (dialogInItself) {
                var $button = this;
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/update-gallery-image.php", {
                    gallery: img.attr('gallery-id'),
                    image: img.attr('image-id'),
                    title: $('#gallery-title').val(),
                    caption: $('#gallery-caption').val(),
                    filename: $('#gallery-filename').val()
                }).done(function (data) {
                    if (data !== "") {
                        dialogInItself.getModal().find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    } else {
                        //update our image
                        img.attr('alt', $('#gallery-title').val());
                        img.attr('style', "background-image: url('" + $('#gallery-filename').val() + "');");
                        img.next().children().html($('#gallery-caption').val());
                        //close our dialog
                        dialogInItself.close();
                        // go to the next image
                        $('.carousel').carousel("next");
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        dialogInItself.getModal().find('.modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        dialogInItself.getModal().find('.modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        dialogInItself.getModal().find('.modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                }).always(function () {
                    $button.stopSpin();
                    dialogInItself.enableButtons(true);
                    dialogInItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }]
    });
}

function deleteImage() {
    $('.carousel').carousel("pause");
    var img = $('.carousel div.active div');
    BootstrapDialog.show({
        draggable: true,
        title: 'Are You Sure?',
        message: 'Are you sure you want to delete the image <b>' + img.attr('alt') + '</b>',
        buttons: [{
            icon: 'glyphicon glyphicon-trash',
            label: ' Delete',
            cssClass: 'btn-danger',
            action: function (dialogInItself) {
                var $button = this;
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/delete-gallery-image.php", {
                    gallery: img.attr('gallery-id'),
                    image: img.attr('image-id')
                }).done(function (data) {
                    if (data !== "") {
                        dialogInItself.getModal().find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    } else {
                        dialogInItself.close();
                        // go to the next image
                        $('.carousel').carousel("next");
                        // cleanup the dom
                        $('.gallery img[alt="' + img.attr('alt') + '"]').parent().remove();
                        img.parent().remove();
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        dialogInItself.getModal().find('.modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        dialogInItself.getModal().find('.modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        dialogInItself.getModal().find('.modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                }).always(function () {
                    $button.stopSpin();
                    dialogInItself.enableButtons(true);
                    dialogInItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }]
    });
}

function sortGallery() {
    // fix the buttons
    $("#sort-gallery-btn").parent().hide();
    $("#save-gallery-btn").parent().show();

    // setup sort
    $(".image-grid").sortable({
        items: 'div.gallery',
    });
}

function saveGallery(id) {
    $.blockUI({
        message: '<h1>Saving New Image Order...</h1>'
    });

    // determine the new order
    var imgs = [];
    $('div.gallery').each(function () {
        var img = {};
        img.id = $(this).attr('image-id');
        img.sequence = $(this).attr('sequence');
        img.col = $(this).parent().attr('id');
        img.height = $(this).position().top;
        imgs.push(img);
    });
    imgs.sort(function (a, b) {
        if (a.height === b.height) {
            var x = a.col.toLowerCase(), y = b.col.toLowerCase();

            return x < y ? -1 : x > y ? 1 : 0;
        }
        return a.height - b.height;
    });
    for (var i = 0; i < imgs.length; i++) {
        $("div.gallery[sequence='" + imgs[i].sequence + "'").attr('new-sequence', i);
    }

    // save our updates
    $.post("/api/update-gallery-order.php", {
        id: id,
        imgs: imgs,
    }).done(function (data) {
        if (data === "") {
            $(".image-grid").sortable("destroy");
            // fix the buttons
            $("#sort-gallery-btn").parent().show();
            $("#save-gallery-btn").parent().hide();
        } else {
            $('.breadcrumb').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('.breadcrumb').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('.breadcrumb').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('.breadcrumb').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while saving your new gallery order.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function () {
        $.unblockUI();
    });
}

function editGallery(id) {
    $.get("/api/get-gallery.php", {
        id: id
    }, function (data) {
        BootstrapDialog.show({
            draggable: true,
            size: BootstrapDialog.SIZE_WIDE,
            title: 'Edit Gallery <b>' + data.title + '</b>',
            message: function () {
                return '<input placeholder="Gallery Title" id="new-gallery-title" type="text" class="form-control" value="' + data.title + '" />' + '<p></p>' + '<div id="upload-container"></div>' + '<div id="resize-progress" class="progress">' + '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Checking files...</div>' + '</div>' + '<div id="new-gallery-error" class="error"></div>' + '<div id="new-gallery-message" class="success"></div>';
            },
            buttons: [{
                icon: 'glyphicon glyphicon-save',
                label: ' Save Details',
                cssClass: 'btn-success',
                action: function (dialogItself) {
                    var $button = this; // 'this' here
                    // is a jQuery
                    // object that
                    // wrapping the <button> DOM
                    // element.
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    $.post("/api/update-gallery.php", {
                        id: id,
                        title: $('#new-gallery-title').val()
                    }).done(function () {
                        dialogItself.close();
                    });
                }
            }, {
                label: 'Close',
                action: function (dialogItself) {
                    dialogItself.close();
                }
            }],
            onshown: function (dialogItself) {
                $('#upload-container').uploadFile({
                    url: "/api/upload-gallery-images.php",
                    uploadStr: "<span class='bootstrap-dialog-button-icon glyphicon glyphicon-upload'></span> Upload Images",
                    multiple: true,
                    dragDrop: true,
                    uploadButtonLocation: $('.bootstrap-dialog-footer-buttons'),
                    uploadContainer: $('#upload-container'),
                    uploadButtonClass: "btn btn-default btn-info",
                    statusBarWidth: "48%",
                    dragdropWidth: "100%",
                    fileName: "myfile",
                    sequential: true,
                    sequentialCount: 1,
                    acceptFiles: "image/*",
                    uploadQueueOrder: "bottom",
                    formData: {
                        "gallery": id
                    },
                    onSubmit: function () {
                        $('.ajax-file-upload-container').show();
                        dialogItself.$modalFooter.find('span.glyphicon').removeClass('glyphicon-upload').addClass('glyphicon-asterisk icon-spin');
                        disableDialogButtons(dialogItself);
                    },
                    onSuccess: function (files, data, xhr, pd) {
                        data = JSON.parse(data);
                        if ($.isArray(data)) {
                            pd.statusbar.remove();
                            $.each(files, function () {
                                total++;
                                loaded = gallery.loadImages(1);
                            });
                        } else {
                            pd.statusbar.parent().removeClass('ajax-file-upload-container');
                            pd.statusbar.prepend("<a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>");
                            pd.statusbar.addClass('alert alert-danger');
                            pd.progressDiv.hide();
                            pd.filename.after(data);
                        }
                    },
                    afterUploadAll: function () {
                        setTimeout(function () {
                            $('.ajax-file-upload-container').hide();
                        }, 5000);
                        dialogItself.$modalFooter.find('span.glyphicon').removeClass('glyphicon-asterisk icon-spin').addClass('glyphicon-upload');
                        enableDialogButtons(dialogItself);
                    },
                });
            }
        });
    }, "json");
}

function updateFilename() {
    var parts = $('#gallery-filename').val().split('/');
    var extension = parts[parts.length-1].split('.').pop();
    parts[parts.length-1] = $('#gallery-title').val().replace(/[^0-9a-z\s]/gi, '') + "." + extension;
    $('#gallery-filename').val(parts.join('/'));
}

function disableDialogButtons(dialog) {
    var uploadButton = dialog.$modalFooter.find('#add-images-button');
    uploadButton.addClass('disabled');
    uploadButton.prop("disabled", true);
    uploadButton.css({
        'cursor': 'not-allowed',
        'pointer-events': 'none'
    });
    dialog.enableButtons(false);
    dialog.setClosable(false);
}

function enableDialogButtons(dialog) {
    var uploadButton = dialog.$modalFooter.find('#add-images-button');
    uploadButton.removeClass('disabled');
    uploadButton.prop("disabled", false);
    uploadButton.css({
        'cursor': 'pointer',
        'pointer-events': 'inherit'
    });
    dialog.enableButtons(true);
    dialog.setClosable(true);
}
