var album_table;
var resultsSelected = false;

function thumbnailStatus(row) {
    if (parseInt(row.images, 10) === 0) {
        return '<span class="label label-default">N/A</span>';
    }
    if (String(row.thumbsCreated) === '1') {
        return '<span class="label label-success">Ready</span>';
    }
    return '<span class="label label-warning">Missing</span>';
}

$(document).ready(function () {
    if ($('#albums').length) {
        album_table = $('#albums').DataTable({
            "ajax": "/api/get-albums.php",
            "order": [[1, "asc"]],
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "data": function (row) {
                    return '<button type="button" class="btn btn-xs btn-warning edit-album-btn" data-toggle="tooltip" data-placement="right" title="Edit ' + row.name + ' Album Details"><i class="fa fa-pencil-square-o"></i></button>  <button type="button" class="btn btn-xs btn-success view-album-log-btn" data-toggle="tooltip" data-placement="right" title="View ' + row.name + ' Activities"><i class="fa fa-bars"></i></button>';
                },
                "targets": 0
            }, {
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
            }, {
                "data": "lastAccessed",
                "className": "album-last-accessed",
                "targets": 6
            }, {
                "data": "code",
                "className": "album-code",
                "targets": 7
            }],
            "fnCreatedRow": function (nRow, aData) {
                $(nRow).attr('album-id', aData.id);
            }
        });
        $('#thumbnail-status-filter-container').prependTo('#albums_filter').show();
    }
    $('#albums').on('draw.dt search.dt', function () {
        setupEdit();
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('#thumbnail-status-filter').change(function () {
        album_table.column(5).search($(this).val()).draw();
    });

    $('#add-album-btn').click(function () {
        BootstrapDialog.show({
            draggable: true,
            title: 'Add A New Album',
            message: function () {
                return '<input placeholder="Album Name" id="new-album-name" type="text" class="form-control"/>' + '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control"/>' + '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control"/>';
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
                    // send our update
                    $.post("/api/create-album.php", {
                        name: $('#new-album-name').val(),
                        description: $('#new-album-description').val(),
                        date: $('#new-album-date').val()
                    }).done(function (data) {
                        if ($.isNumeric(data) && data !== '0') {
                            var table = $('#albums').DataTable();
                            table.row.add({
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
                        $button.stopSpin();
                        dialogItself.enableButtons(true);
                        dialogItself.setClosable(true);
                    }).fail(function (xhr, status, error) {
                        if (xhr.responseText !== "") {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                        } else if (error === "Unauthorized") {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                        } else {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                        }
                    });
                }
            }, {
                label: 'Close',
                action: function (dialogItself) {
                    dialogItself.close();
                }
            }],
        });
    });
});

function setupEdit() {
    $('.edit-album-btn').off().click(function () {
        var id = $(this).closest('tr').attr('album-id');
        editAlbum(id);
    });
    $('.view-album-log-btn').off().click(function () {
        viewLogs($(this).closest('tr').attr('album-id'));
    });
}

function editAlbum(id) {
    $.get("/api/get-album.php", {
        id: id
    }, function (data) {
        BootstrapDialog.show({
            draggable: true,
            size: BootstrapDialog.SIZE_WIDE,
            title: 'Edit Album <b>' + data.name + '</b>',
            message: function () {
                return '<div id="album" album-id="' + id + '" class="hidden"><div id="album-title">' + data.name + '</div></div><input placeholder="Album Name" id="new-album-name" type="text" class="form-control" value="' + data.name + '" />' + '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control" value="' + data.description + '" />' + '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control" value="' + data.date + '" />' + '<p></p>' + '<input placeholder="Album Code" id="new-album-code" type="text" class="form-control" value="' + data.code + '" />' + '<p></p>' + '<div id="upload-container"></div>' + '<div id="resize-progress" class="progress">' + '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Checking files...</div></div>';
            },
            buttons: [{
                id: 'album-users-btn',
                icon: 'glyphicon glyphicon-picture',
                label: ' Set Access',
                cssClass: 'btn-info',
                action: function () {
                    setupAlbumAccess();
                }
            }, {
                icon: 'glyphicon glyphicon-trash',
                label: ' Delete Album',
                cssClass: 'btn-danger',
                action: function (dialogItself) {
                    var $button = this;
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    // send our update
                    BootstrapDialog.show({
                        draggable: true,
                        title: 'Are You Sure?',
                        message: 'Are you sure you want to delete the album <b>' + data.name + '</b>',
                        buttons: [{
                            icon: 'glyphicon glyphicon-trash',
                            label: ' Delete',
                            cssClass: 'btn-danger',
                            action: function (dialogInItself) {
                                var $button = this;
                                var modal = $button.closest('.modal-content');
                                $button.spin();
                                dialogInItself.enableButtons(false);
                                dialogInItself.setClosable(false);
                                // send our update
                                $.post("/api/delete-album.php", {
                                    id: id,
                                }).done(function () {
                                    if ($('#albums').length) {
                                        album_table.ajax.reload(null, false);
                                    }
                                    dialogInItself.close();
                                    dialogItself.close();
                                }).fail(function (xhr, status, error) {
                                    if (xhr.responseText !== "") {
                                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                                    } else if (error === "Unauthorized") {
                                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                                    } else {
                                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while deleting your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                                    }
                                });
                            }
                        }, {
                            label: 'Close',
                            action: function (dialogInItself) {
                                $button.stopSpin();
                                enableDialogButtons(dialogItself);
                                dialogInItself.close();
                            }
                        }]
                    });
                }
            }, {
                icon: 'glyphicon glyphicon-refresh',
                label: ' Make Thumbnails',
                cssClass: 'btn-warning',
                action: function (dialogItself) {
                    var $button = this;
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    chooseThumbnailScope(id, data.imageCount, data.needsThumbnails, $button, dialogItself);
                }
            }, {
                icon: 'glyphicon glyphicon-save',
                label: ' Save Details',
                cssClass: 'btn-success',
                action: function (dialogItself) {
                    var $button = this;
                    var modal = $button.closest('.modal-content');
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    $.post("/api/update-album.php", {
                        id: id,
                        name: $('#new-album-name').val(),
                        description: $('#new-album-description').val(),
                        date: $('#new-album-date').val(),
                        code: $('#new-album-code').val(),
                    }).done(function (data) {
                        if (data === "") {
                            dialogItself.close();
                            if ($('#albums').length) {
                                album_table.ajax.reload(null, false);
                            }
                            enableDialogButtons(dialogItself);
                        } else {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                        }
                    }).fail(function (xhr, status, error) {
                        if (xhr.responseText !== "") {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                        } else if (error === "Unauthorized") {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                        } else {
                            modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your album users.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                        }
                    }).always(function () {
                        $button.stopSpin();
                        enableDialogButtons(dialogItself);
                    });
                }
            }, {
                label: 'Close',
                action: function (dialogItself) {
                    dialogItself.close();
                    if ($('#albums').length) {
                        album_table.ajax.reload(null, false);
                    }
                }
            }],
            onshown: function (dialogItself) {
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
                        dialogItself.$modalFooter.find('span.glyphicon').removeClass('glyphicon-asterisk icon-spin').addClass('glyphicon-upload');
                        enableDialogButtons(dialogItself);
                    },
                });
            },
            onhide: function () {
                if ($('#albums').length) {
                    album_table.ajax.reload(null, false);
                }
            },
        });
    }, "json");
}

function viewLogs(id) {
    var dialog = new BootstrapDialog({
        title: 'Album Activity',
        message: function () {
            var $message = $('<div>Loading...</div>');
            $.get("/api/get-album-log.php", {
                id: id
            }, function (data) {
                var message = $('<div>');
                message.addClass('album-logs');
                for (var i = 0, len = data.length; i < len; i++) {
                    var logHolder = $('<div>');
                    logHolder.addClass('row');
                    var log = data[i];

                    var time = $('<div>');
                    time.addClass('col-md-4');
                    time.html(log.time);
                    logHolder.append(time);

                    var activity = $('<div>');
                    activity.addClass('col-md-8');
                    var action = "User <i>" + log.usr + "</i> " + log.action;
                    if (log.what !== null) {
                        action += " " + log.what;
                    }
                    activity.html(action);
                    logHolder.append(activity);
                    message.append(logHolder)
                }
                dialog.setMessage(message)
            }, "json");
            return $message;
        },
        buttons: [{
            label: 'Close',
            action: function (dialogItself) {
                dialogItself.close();
            }
        }]
    });
    dialog.open();
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
    var buttons = [];

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
        buttons.push({
            icon: 'glyphicon glyphicon-plus',
            label: ' Missing Only',
            cssClass: 'btn-warning',
            action: function (scopeDialog) {
                scopeDialog.close();
                chooseThumbnailMarkup(id, button, dialog, "missing");
            }
        });
    }

    buttons.push({
        icon: 'glyphicon glyphicon-refresh',
        label: ' Recreate All',
        cssClass: 'btn-danger',
        action: function (scopeDialog) {
            scopeDialog.close();
            chooseThumbnailMarkup(id, button, dialog, "all");
        }
    });

    buttons.push({
        label: 'Close',
        action: function (scopeDialog) {
            button.stopSpin();
            enableDialogButtons(dialog);
            scopeDialog.close();
        }
    });

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
    $("#resize-progress").show();
    $.post("/api/make-thumbs.php", {
        id: id,
        markup: markup,
        mode: mode
    }).done(function () {
        var myVar = setInterval(function () {
            $.get("/tmp/status.txt", function (data) {
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
                }
            });
        }, 100);
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
