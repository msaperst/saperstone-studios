var album_table;

$(document).ready(function () {
    if ($('#albums').length) {
        album_table = $('#albums').DataTable({
            "ajax": "/api/get-albums.php",
            "order": [[1, "asc"]],
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "data": function (row) {
                    var buttons = "";
                    if (row.owner === my_id) {
                        buttons = '<button type="button" class="btn btn-xs btn-warning edit-album-btn">' + '<i class="fa fa-pencil-square-o"></i></button>';
                    }
                    return buttons;
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
            }],
            "fnCreatedRow": function (nRow, aData) {
                $(nRow).attr('album-id', aData.id);
            }
        });
    }
    $('#albums').on('draw.dt search.dt', function () {
        setupEdit();
    });

    $('#edit-album-btn').click(function () {
        editAlbum($('#favorites').attr('album-id'));
    })

    $('#add-album-btn').click(function () {
        BootstrapDialog.show({
            draggable: true,
            title: 'Add A New Album',
            message: function () {
                var inputs = '<input placeholder="Album Name" id="new-album-name" type="text" class="form-control"/>' + '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control"/>' + '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control"/>';
                return inputs;
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

    $('#add-album-div').keypress(function (e) {
        if (e.which === 13) {
            addAlbum();
        }
    });
    $('#album-code-add').click(function () {
        addAlbum();
    });
});

function addAlbum() {
    // spin our button
    $("#album-code-add").prop("disabled", true);
    $("#album-code-add em").removeClass('fa fa-plus-circle').addClass('glyphicon glyphicon-asterisk icon-spin');
    // make our call
    $.get("/api/find-album.php", {
        code: $('#album-code').val(),
        albumAdd: 1,
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

function setupEdit() {
    $('.edit-album-btn').off().click(function () {
        var id = $(this).closest('tr').attr('album-id');
        editAlbum(id);
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
                var inputs = '<div id="album" album-id="' + id + '" class="hidden"><div id="album-title">' + data.name + '</div></div><input placeholder="Album Name" id="new-album-name" type="text" class="form-control" value="' + data.name + '" />' + '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control" value="' + data.description + '" />' + '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control" value="' + data.date + '" />' + '<p></p>' + '<div id="upload-container"></div>' + '<div id="resize-progress" class="progress">' + '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Checking files...</div></div>';
                return inputs;
            },
            buttons: [{
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
                    // send our update
                    $("#resize-progress").show();
                    $.post("/api/make-thumbs.php", {
                        id: id,
                        markup: "watermark"
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
                                    $button.stopSpin();
                                    enableDialogButtons(dialogItself);
                                }
                                if (data.indexOf("Error") === 0) {
                                    clearInterval(myVar);
                                    $('#resize-progress .progress-bar').removeClass('active').addClass('progress-bar-danger');
                                }
                            });
                        }, 100);
                    });
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
                        setTimeout(function () {
                            pd.statusbar.remove();
                        }, 5000);
                    },
                    afterUploadAll: function () {
                        setTimeout(function () {
                            $('.ajax-file-upload-container').hide();
                        }, 5000);
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