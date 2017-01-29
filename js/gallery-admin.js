var resultsSelected = false;

$(document).ready(function() {
    $('#delete-image-btn').click(function() {
        deleteImage();
    });

    $("#sort-gallery-btn").click(function() {
        sortGallery();
    });

    $("#save-gallery-btn").click(function() {
        saveGallery(getQueryVariable('w'));
    });

    $("#edit-gallery-btn").click(function() {
        editGallery(getQueryVariable('w'));
    });
});

function deleteImage() {
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
                var $button = this;
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
}

function sortGallery() {
    // fix the buttons
    $("#sort-gallery-btn").parent().hide();
    $("#save-gallery-btn").parent().show();

    // setup sort
    $(".image-grid").sortable({
        items : 'div.gallery',
    });
}

function saveGallery(id) {
    $.blockUI({
        message : '<h1>Saving New Image Order...</h1>'
    });

    // determine the new order
    var imgs = [];
    $('div.gallery').each(function() {
        var img = {};
        img.id = $(this).attr('image-id');
        img.sequence = $(this).attr('sequence');
        img.col = $(this).parent().attr('id');
        img.height = $(this).position().top;
        imgs.push(img);
    });
    imgs.sort(function(a, b) {
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
    $
            .post("/api/update-gallery-order.php", {
                id : id,
                imgs : imgs,
            })
            .done(function(data) {
                if (data === "") {
                    $(".image-grid").sortable("destroy");
                    // fix the buttons
                    $("#sort-gallery-btn").parent().show();
                    $("#save-gallery-btn").parent().hide();
                } else {
                    $('.breadcrumb').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                }
            })
            .fail(
                    function() {
                        $('.breadcrumb')
                                .after(
                                        "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while saving your new gallery order.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }).always(function() {
                $.unblockUI();
            });
}

function editGallery(id) {
    $
            .get(
                    "/api/get-gallery.php",
                    {
                        id : id
                    },
                    function(data) {
                        BootstrapDialog
                                .show({
                                    draggable : true,
                                    size : BootstrapDialog.SIZE_WIDE,
                                    title : 'Edit Gallery <b>' + data.title + '</b>',
                                    message : function() {
                                        var inputs = '<input placeholder="Gallery Title" id="new-gallery-title" type="text" class="form-control" value="'
                                                + data.title
                                                + '" />'
                                                + '<p></p>'
                                                + '<div id="upload-container"></div>'
                                                + '<div id="resize-progress" class="progress">'
                                                + '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Checking files...</div>'
                                                + '</div>' + '<div id="new-gallery-error" class="error"></div>' + '<div id="new-gallery-message" class="success"></div>';
                                        return inputs;
                                    },
                                    buttons : [ {
                                        icon : 'glyphicon glyphicon-save',
                                        label : ' Save Details',
                                        cssClass : 'btn-success',
                                        action : function(dialogItself) {
                                            var $button = this; // 'this' here
                                            // is a jQuery
                                            // object that
                                            // wrapping the <button> DOM
                                            // element.
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
                                            sequentialCount : 1,
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
                                                $.each(files, function(i, val) {
                                                    total++;
                                                    loaded = gallery.loadImages(1);
                                                });

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