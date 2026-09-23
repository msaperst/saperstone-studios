// Shared album management helpers.

function thumbnailStatus(row) {
    if (Number.parseInt(row.images, 10) === 0) {
        return '<span class="label label-default">N/A</span>';
    }
    if (String(row.thumbsCreated) === '1') {
        return '<span class="label label-success">Ready</span>';
    }
    return '<span class="label label-warning">Missing</span>';
}

function albumManagementColumns(actionColumn) {
    return [actionColumn, {
        "data": function (row) {
            return "<a href='album.php?album=" + row.id + "'>" + row.name + "</a>";
        },
        "className": "album-name",
        "targets": 1
    }, {
        "data": "description",
        "className": "album-description",
        "targets": 2
    }, {
        "data": "date",
        "className": "album-date",
        "targets": 3
    }, {
        "data": "images",
        "className": "album-images",
        "targets": 4
    }, {
        "data": thumbnailStatus,
        "className": "album-thumbnails",
        "targets": 5
    }];
}

function setAlbumRowId(nRow, aData) {
    $(nRow).attr('album-id', aData.id);
}

function openCreateAlbumDialog() {
    BootstrapDialog.show({
        draggable: true,
        title: 'Add A New Album',
        message: function () {
            return '<input placeholder="Album Name" id="new-album-name" type="text" class="form-control"/>' +
                '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control"/>' +
                '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control"/>';
        },
        buttons: [{
            icon: 'glyphicon glyphicon-folder-close',
            label: ' Create Album',
            cssClass: 'btn-success',
            action: function (dialogItself) {
                var $button = this;
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogItself.enableButtons(false);
                dialogItself.setClosable(false);

                $.post("/api/create-album.php", {
                    name: $('#new-album-name').val(),
                    description: $('#new-album-description').val(),
                    date: $('#new-album-date').val()
                }).done(function (data) {
                    if ($.isNumeric(data) && data !== '0') {
                        $('#albums').DataTable().row.add({
                            "id": data,
                            "name": $('#new-album-name').val(),
                            "description": $('#new-album-description').val(),
                            "date": $('#new-album-date').val(),
                            "images": "0",
                            "thumbsCreated": "0",
                            "lastAccessed": "0000-00-00 00:00:00",
                            "location": "",
                            "code": ""
                        }).draw(false);
                        dialogItself.close();
                        editAlbum(data);
                    } else if (data === '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                }).always(function () {
                    $button.stopSpin();
                    dialogItself.enableButtons(true);
                    dialogItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogItself) {
                dialogItself.close();
            }
        }]
    });
}

function addAlbum() {
    // spin our button
    $("#album-code-add").prop("disabled", true);
    $("#album-code-add em").removeClass('fa fa-plus-circle').addClass('glyphicon glyphicon-asterisk icon-spin');
    // make our call
    $.post("/api/add-album.php", {
        code: $('#album-code').val(),
    }).done(function (data) {
        // goto album url if it exists
        if ($.isNumeric(data) && data !== '0') {
            $('#add-album-div').append("<div id='album-code-add-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Added album to your list</div>");
            // refresh our tables
            album_table.ajax.reload(null, false);
            setTimeout(function () {
                $('#album-code-add-message').remove();
            }, 10000);
        } else if (data === '0') {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        } else {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function () {
        //fix our button
        $("#album-code-add").prop("disabled", false);
        $("#album-code-add em").addClass('fa fa-plus-circle').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}

function addUser(id) {
    var albumSpan = $('<span>');
    albumSpan.addClass('selected-album');
    albumSpan.attr('user-id', id);
    albumSpan.click(function () {
        $(this).remove();
    });
    $.get("/api/get-user.php", {
        id: id
    }, function (data) {
        albumSpan.html(data.usr);
        $('#album-users').append(albumSpan);
    }, "json");

}

function chooseThumbnailScope(id, imageCount, needsThumbnails, button, dialog) {
    var buttons = [{
        icon: 'glyphicon glyphicon-refresh',
        label: ' Recreate All',
        cssClass: 'btn-danger',
        action: function (scopeDialog) {
            scopeDialog.close();
            chooseThumbnailMarkup(id, button, dialog, "all");
        }
    }, {
        label: 'Close',
        action: function (scopeDialog) {
            button.stopSpin();
            enableDialogButtons(dialog);
            scopeDialog.close();
        }
    }];

    if (imageCount === 0) {
        BootstrapDialog.show({
            draggable: true,
            title: 'Create Thumbnails',
            message: 'This album does not have any images to process.',
            buttons: [{
                label: 'Close',
                action: function (scopeDialog) {
                    button.stopSpin();
                    enableDialogButtons(dialog);
                    scopeDialog.close();
                }
            }]
        });
        return;
    }

    if (needsThumbnails) {
        buttons.unshift({
            icon: 'glyphicon glyphicon-plus',
            label: ' Missing Only',
            cssClass: 'btn-warning',
            action: function (scopeDialog) {
                scopeDialog.close();
                chooseThumbnailMarkup(id, button, dialog, "missing");
            }
        });
    }

    BootstrapDialog.show({
        draggable: true,
        title: 'Create Thumbnails',
        message: needsThumbnails
            ? 'Some thumbnails are missing. Create only the missing thumbnails, or recreate every thumbnail from the original images.'
            : 'All thumbnails already exist. Recreate all thumbnails if you want to change the proof or watermark treatment.',
        buttons: buttons
    });
}

function chooseThumbnailMarkup(id, button, dialog, mode) {
    BootstrapDialog.show({
        draggable: true,
        title: 'Thumbnail Treatment',
        message: 'What do you want to put on the thumbnails?',
        buttons: [{
            icon: 'glyphicon glyphicon-eye-close',
            label: ' Proof',
            cssClass: 'btn-warning',
            action: function (markupDialog) {
                markupDialog.close();
                makeThumbs(id, button, dialog, "proof", mode);
            }
        }, {
            icon: 'glyphicon glyphicon-eye-open',
            label: ' Watermark',
            cssClass: 'btn-info',
            action: function (markupDialog) {
                markupDialog.close();
                makeThumbs(id, button, dialog, "watermark", mode);
            }
        }, {
            icon: 'glyphicon glyphicon-globe',
            label: ' Nothing',
            cssClass: 'btn-danger',
            action: function (markupDialog) {
                markupDialog.close();
                makeThumbs(id, button, dialog, "none", mode);
            }
        }, {
            label: 'Close',
            action: function (markupDialog) {
                button.stopSpin();
                enableDialogButtons(dialog);
                markupDialog.close();
            }
        }]
    });
}

function makeThumbs(id, button, dialog, markup, mode) {
    $('#resize-progress .progress-bar')
        .removeClass('progress-bar-danger')
        .addClass('active')
        .html('Starting thumbnail generation...');
    $("#resize-progress").show();
    $.post("/api/make-thumbs.php", {
        id: id,
        markup: markup,
        mode: mode
    }).done(function () {
        var myVar = setInterval(function () {
            $.get("/api/get-thumbnail-status.php", function (data) {
                $('#resize-progress .progress-bar').html(data);
                if (data.indexOf("Done") === 0) {
                    clearInterval(myVar);
                    $('#resize-progress .progress-bar').removeClass('active');
                    setTimeout(function () {
                        $('#resize-progress').hide();
                    }, 5000);
                    button.stopSpin();
                    enableDialogButtons(dialog);
                    $('#thumbnail-warning').hide();
                    if (typeof refreshAlbumThumbnailImages === "function") {
                        refreshAlbumThumbnailImages();
                    }
                    if ($('#albums').length) {
                        album_table.ajax.reload(null, false);
                    }
                }
                if (data.indexOf("Error") === 0) {
                    clearInterval(myVar);
                    $('#resize-progress .progress-bar').removeClass('active').addClass('progress-bar-danger');
                    button.stopSpin();
                    enableDialogButtons(dialog);
                }
            }).fail(function () {
                // The status file is transient. Keep polling unless the API request itself failed.
            });
        }, 100);
    }).fail(function (xhr) {
        var message = xhr.responseText || 'Unable to start thumbnail generation';
        $('#resize-progress .progress-bar')
            .html('Error: ' + message)
            .removeClass('active')
            .addClass('progress-bar-danger');
        button.stopSpin();
        enableDialogButtons(dialog);
    });
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


function configureAlbumImageUpload(id, dialogItself) {
    $('#upload-container').uploadFile({
        url: "/api/upload-album-images.php",
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
        sequentialCount: 5,
        acceptFiles: "image/*,.nef,.cr2",
        uploadQueueOrder: "bottom",
        formData: {
            "album": id
        },
        onSubmit: function () {
            $('.ajax-file-upload-container').show();
            dialogItself.$modalFooter.find('span.glyphicon').removeClass('glyphicon-upload').addClass('glyphicon-asterisk icon-spin');
            disableDialogButtons(dialogItself);
        },
        onSuccess: function (files, data, xhr, pd) {
            $('#thumbnail-warning').show();
            setTimeout(function () {
                pd.statusbar.remove();
            }, 5000);
        },
        afterUploadAll: function () {
            setTimeout(function () {
                $('.ajax-file-upload-container').hide();
            }, 5000);
            if ($('#albums').length) {
                album_table.ajax.reload(null, false);
            }
            if (window.album && String(window.album.albumId) === String(id)) {
                $.get("/api/get-album.php", {
                    id: id
                }, function (albumInfo) {
                    window.album.refreshImages(Number(albumInfo.imageCount));
                }, "json");
            }
            dialogItself.$modalFooter.find('span.glyphicon').removeClass('glyphicon-asterisk icon-spin').addClass('glyphicon-upload');
            enableDialogButtons(dialogItself);
        }
    });
}
