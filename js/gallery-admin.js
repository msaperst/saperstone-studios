var resultsSelected = false;

$(document).ready(function() {
    $('#delete-image-btn').click(function() {
        var img = $('.carousel div.active div');
        BootstrapDialog.show({
            draggable : true,
            title : 'Are You Sure?',
            message : 'Are you sure you want to delete the image <b>' + img.attr('alt') + '</b>',
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
                    $.post("/api/delete-gallery-image.php", {
                        gallery : img.attr('gallery-id'),
                        image : img.attr('image-id')
                    }).done(function() {
                        dialogInItself.close();
                        // go to the next image
                        $('.carousel').carousel("next");
                        // cleanup the dom
                        $('.gallery img[alt="' + img.attr('alt') + '"]').parent().remove();
                        img.parent().remove();
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

    $("#edit-gallery-btn").click(function() {
        editGallery(getQueryVariable('w'));
    });
});

function editGallery(id) {
    $.get("/api/get-gallery.php", {
        id : id
    }, function(data) {
        BootstrapDialog.show({
            draggable : true,
            size : BootstrapDialog.SIZE_WIDE,
            title : 'Edit Gallery <b>' + data.title + '</b>',
            message : function() {
                var inputs = '<input placeholder="Gallery Title" id="new-gallery-title" type="text" class="form-control" value="' + data.title + '" />' + '<p></p>' + '<div id="upload-container"></div>' +
                        '<div id="resize-progress" class="progress">' + '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Checking files...</div>' +
                        '</div>' + '<div id="new-gallery-error" class="error"></div>' + '<div id="new-gallery-message" class="success"></div>';
                return inputs;
            },
            buttons : [ {
                icon : 'glyphicon glyphicon-refresh',
                label : ' Make Thumbnails',
                cssClass : 'btn-warning',
                action : function(dialogItself) {
                    var $button = this; // 'this' here is a jQuery object that
                                        // wrapping the <button> DOM element.
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    // need to determine how to make thumbs, with proof all
                    // over, watermark in corner, or no watermark
                    BootstrapDialog.show({
                        draggable : true,
                        title : 'Make Thumbnails How?',
                        message : 'What do you want to put on your viewable thumbnails?',
                        buttons : [ {
                            icon : 'glyphicon glyphicon-eye-close',
                            label : ' Proof',
                            cssClass : 'btn-warning',
                            action : function(dialogInItself) {
                                dialogInItself.close();
                                makeThumbs(id, $button, dialogItself, "proof");
                            }
                        }, {
                            icon : 'glyphicon glyphicon-eye-open',
                            label : ' Watermark',
                            cssClass : 'btn-info',
                            action : function(dialogInItself) {
                                dialogInItself.close();
                                makeThumbs(id, $button, dialogItself, "watermark");
                            }
                        }, {
                            icon : 'glyphicon glyphicon-globe',
                            label : ' Nothing',
                            cssClass : 'btn-danger',
                            action : function(dialogInItself) {
                                dialogInItself.close();
                                makeThumbs(id, $button, dialogItself, "none");
                            }
                        }, {
                            label : 'Close',
                            action : function(dialogInItself) {
                                $button.stopSpin();
                                enableDialogButtons(dialogItself);
                                dialogInItself.close();
                            }
                        } ]
                    });
                }
            }, {
                icon : 'glyphicon glyphicon-save',
                label : ' Save Details',
                cssClass : 'btn-success',
                action : function(dialogItself) {
                    var $button = this; // 'this' here is a jQuery object that
                                        // wrapping the <button> DOM element.
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    $.post("/api/update-gallery.php", {
                        id : id,
                        title : $('#new-gallery-title').val()
                    }).done(function() {
                        dialogItself.close();
                    });
                }
            }, {
                label : 'Close',
                action : function(dialogItself) {
                    dialogItself.close();
                }
            } ],
            onshown : function(dialogItself) {
                $('#upload-container').uploadFile({
                    url : "/api/upload-gallery-images.php",
                    uploadStr : "<span class='bootstrap-dialog-button-icon glyphicon glyphicon-upload'></span> Upload Images",
                    multiple : true,
                    dragDrop : true,
                    uploadButtonLocation : $('.bootstrap-dialog-footer-buttons'),
                    uploadContainer : $('#upload-container'),
                    uploadButtonClass : "btn btn-default btn-info",
                    statusBarWidth : "48%",
                    dragdropWidth : "100%",
                    fileName : "myfile",
                    sequential : true,
                    sequentialCount : 5,
                    acceptFiles : "image/*",
                    uploadQueueOrder : "bottom",
                    formData : {
                        "gallery" : id
                    },
                    onSubmit : function() {
                        $('.ajax-file-upload-container').show();
                        dialogItself.$modalFooter.find('span.glyphicon').removeClass('glyphicon-upload').addClass('glyphicon-asterisk icon-spin');
                        disableDialogButtons(dialogItself);
                    },
                    onSuccess : function(files, data, xhr, pd) {
                        setTimeout(function() {
                            pd.statusbar.remove();
                        }, 5000);
                    },
                    afterUploadAll : function() {
                        setTimeout(function() {
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

function disableDialogButtons(dialog) {
    var uploadButton = dialog.$modalFooter.find('#add-images-button');
    uploadButton.addClass('disabled');
    uploadButton.prop("disabled", true);
    uploadButton.css({
        'cursor' : 'not-allowed',
        'pointer-events' : 'none'
    });
    dialog.enableButtons(false);
    dialog.setClosable(false);
}
function enableDialogButtons(dialog) {
    var uploadButton = dialog.$modalFooter.find('#add-images-button');
    uploadButton.removeClass('disabled');
    uploadButton.prop("disabled", false);
    uploadButton.css({
        'cursor' : 'pointer',
        'pointer-events' : 'inherit'
    });
    dialog.enableButtons(true);
    dialog.setClosable(true);
}