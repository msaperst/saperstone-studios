var album_table;

$(document).ready(function () {
    if ($('#albums').length) {
        album_table = $('#albums').DataTable({
            "ajax": "/api/get-albums.php",
            "order": [[1, "asc"]],
            "columnDefs": albumManagementColumns({
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
            }),
            "fnCreatedRow": setAlbumRowId
        });
        $('#thumbnail-status-filter-container').prependTo('#albums_filter').show();
    }
    $('#albums').on('draw.dt search.dt', function () {
        setupEdit();
    });

    $('#thumbnail-status-filter').change(function () {
        album_table.column(5).search($(this).val()).draw();
    });

    $('#edit-album-btn').click(function () {
        editAlbum($('#favorites').attr('album-id'));
    })

    $('#add-album-btn').click(function () {
        openCreateAlbumDialog();
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
                    $.get("/api/get-album.php", {
                        id: id
                    }, function (currentAlbum) {
                        chooseThumbnailScope(id, currentAlbum.imageCount, currentAlbum.needsThumbnails, currentAlbum.hasAnyThumbnails, $button, dialogItself);
                    }, "json").fail(function (xhr) {
                        var message = xhr.responseText || 'Unable to refresh album details';
                        $('#resize-progress .progress-bar')
                            .html('Error: ' + message)
                            .removeClass('active')
                            .addClass('progress-bar-danger');
                        $("#resize-progress").show();
                        $button.stopSpin();
                        enableDialogButtons(dialogItself);
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
                configureAlbumImageUpload(id, dialogItself);
            },
            onhide: function () {
                if ($('#albums').length) {
                    album_table.ajax.reload(null, false);
                }
            },
        });
    }, "json");
}

