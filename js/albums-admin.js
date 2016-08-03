var album_table;
var resultsSelected = false;

$(document).ready(
        function() {
            album_table = $('#albums').DataTable({
                "ajax" : "/api/get-albums.php",
                "order" : [ [ 1, "asc" ] ],
                "columnDefs" : [ {
                    "orderable" : false,
                    "searchable" : false,
                    "data" : function() {
                        var buttons = '<button type="button" class="btn btn-xs btn-warning edit-album-btn">' + '<i class="fa fa-pencil-square-o"></i></button>';
                        return buttons;
                    },
                    "targets" : 0
                }, {
                    "data" : function(row) {
                        return "<a href='album.php?album=" + row.id + "'>" + row.name + "</a>";
                    },
                    "className" : "album-name",
                    "targets" : 1
                }, {
                    "data" : "description",
                    "className" : "album-description",
                    "targets" : 2
                }, {
                    "data" : "date",
                    "className" : "album-date",
                    "targets" : 3
                }, {
                    "data" : "images",
                    "className" : "album-images",
                    "targets" : 4
                }, {
                    "data" : "lastAccessed",
                    "className" : "album-last-accessed",
                    "targets" : 5
                }, {
                    "data" : "code",
                    "className" : "album-code",
                    "targets" : 6
                } ],
                "fnCreatedRow" : function(nRow, aData) {
                    $(nRow).attr('album-id', aData.id);
                }
            });
            $('#albums').on('draw.dt search.dt', function() {
                setupEdit();
            });

            $('#add-album-btn').click(
                    function() {
                        BootstrapDialog.show({
                            draggable : true,
                            title : 'Add A New Album',
                            message : function() {
                                var inputs = '<input placeholder="Album Name" id="new-album-name" type="text" class="form-control"/>' + 
                                        '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control"/>' + 
                                        '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control"/>' + 
                                        '<div id="new-album-error" class="error"></div>' + 
                                        '<div id="new-album-message" class="success"></div>';
                                return inputs;
                            },
                            buttons : [ {
                                icon : 'glyphicon glyphicon-folder-close',
                                label : ' Create Album',
                                cssClass : 'btn-success',
                                action : function(dialogItself) {
                                    var $button = this; // 'this' here is a
                                                        // jQuery object that
                                                        // wrapping the <button>
                                                        // DOM element.
                                    $button.spin();
                                    dialogItself.enableButtons(false);
                                    dialogItself.setClosable(false);
                                    // send our update
                                    $.post("/api/add-album.php", {
                                        name : $('#new-album-name').val(),
                                        description : $('#new-album-description').val(),
                                        date : $('#new-album-date').val()
                                    }).done(function(data) {
                                        if (Math.round(data) == data && data !== '0') {
                                            var table = $('#albums').DataTable();
                                            table.row.add({
                                                "id" : data,
                                                "name" : $('#new-album-name').val(),
                                                "description" : $('#new-album-description').val(),
                                                "date" : $('#new-album-date').val(),
                                                "images" : "0",
                                                "lastAccessed" : "0000-00-00 00:00:00",
                                                "location" : ""
                                            }).draw(false);
                                            dialogItself.close();
                                            editAlbum(data);
                                        } else if (data === '0') {
                                            $('#new-album-error').html("There was some error with your request. Please contact our <a target='_blank' href='mailto:webmaster@saperstonestudios.com'>webmaster</a>");
                                        } else {
                                            $('#new-album-error').html(data);
                                        }
                                        $button.stopSpin();
                                        dialogItself.enableButtons(true);
                                        dialogItself.setClosable(true);
                                    });
                                }
                            }, {
                                label : 'Close',
                                action : function(dialogItself) {
                                    dialogItself.close();
                                }
                            } ],
                        });
                    });
        });

function setupEdit() {
    $('.edit-album-btn').off().click(function() {
        var id = $(this).closest('tr').attr('album-id');
        editAlbum(id);
    });
}

function editAlbum(id) {
    $.get("/api/get-album.php", {
        id : id
    }, function(data) {
        BootstrapDialog.show({
            draggable : true,
            size : BootstrapDialog.SIZE_WIDE,
            title : 'Edit Album <b>' + data.name + '</b>',
            message : function() {
                var inputs = '<input placeholder="Album Name" id="new-album-name" type="text" class="form-control" value="' + data.name + '" />' +
                        '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control" value="' + data.description + '" />' +
                        '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control" value="' + data.date + '" />' + '<p></p>' + '<input placeholder="Album Code" id="new-album-code" type="text" class="form-control" value="' +
                        data.code + '" />' + '<p></p>' + '<div id="upload-container"></div>' + '<div id="resize-progress" class="progress">' +
                        '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">Checking files...</div>' + '</div>' +
                        '<div id="new-album-error" class="error"></div>' + '<div id="new-album-message" class="success"></div>';
                return inputs;
            },
            buttons : [ {
                id : 'album-users-btn',
                icon : 'glyphicon glyphicon-picture',
                label : ' Update Users',
                cssClass : 'btn-info',
                action : function(dialogItself) {
                    var $button = this; // 'this' here is a jQuery object that
                                        // wrapping the <button> DOM element.
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    // send our update
                    BootstrapDialog.show({
                        draggable : true,
                        title : 'Users for Album <b>' + data.name + '</b>',
                        message : function() {
                            var inputs = $('<div class="open">');

                            var searchInput = $('<input>');
                            searchInput.attr('id', 'user-search');
                            searchInput.attr('type', 'text');
                            searchInput.addClass('form-control');
                            searchInput.attr('placeholder', 'Enter User Name');
                            searchInput.on("keyup focus", function() {
                                var search_ele = $(this);
                                var keyword = search_ele.val();
                                $.get("/api/search-users.php", {
                                    keyword : keyword
                                }, function(data) {
                                    $('.search-results').remove();
                                    var results_ul = $('<ul class="dropdown-menu search-results">');
                                    $.each(data, function(key, user) {
                                        if (!($(".selected-user[user-id='" + user.id + "']").length || user.role == "admin")) {
                                            var result_li = $('<li>');
                                            var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                            result_a.click(function() {
                                                addUser(user.id);
                                                $('.search-results').remove();
                                            });
                                            results_ul.append(result_li.append(result_a));
                                        }
                                    });
                                    results_ul.hover(function() {
                                        resultsSelected = true;
                                    }, function() {
                                        resultsSelected = false;
                                    });
                                    search_ele.after(results_ul);
                                }, "json");
                            });
                            searchInput.focusout(function() {
                                if (!resultsSelected) {
                                    $('.search-results').remove();
                                }
                            });
                            inputs.append(searchInput);

                            return inputs;
                        },
                        buttons : [ {
                            icon : 'glyphicon glyphicon-save',
                            label : ' Update',
                            cssClass : 'btn-success',
                            action : function(dialogInItself) {
                                var $buttonIn = this; // 'this' here is a
                                                        // jQuery object that
                                                        // wrapping the <button>
                                                        // DOM element.
                                $buttonIn.spin();
                                dialogInItself.enableButtons(false);
                                dialogInItself.setClosable(false);
                                var users = [];
                                $('#album-users .selected-album').each(function() {
                                    users.push($(this).attr('user-id'));
                                });
                                // send our update
                                $.post("/api/update-album-users.php", {
                                    album : data.id,
                                    users : users
                                }).done(function(data) {
                                    $('#new-album-error').html(data);
                                    $buttonIn.stopSpin();
                                    $button.stopSpin();
                                    dialogInItself.close();
                                    enableDialogButtons(dialogItself);
                                });
                            }
                        }, {
                            label : 'Close',
                            action : function(dialogInItself) {
                                $button.stopSpin();
                                enableDialogButtons(dialogItself);
                                dialogInItself.close();
                            }
                        } ],
                        onshown : function(dialogInItself) {
                            var albumsDiv = $('<div>');
                            albumsDiv.attr('id', 'album-users');
                            albumsDiv.css({
                                'padding' : '0px 10px 5px 10px'
                            });
                            dialogInItself.$modalBody.after(albumsDiv);
                            $.get("/api/get-album-users.php", {
                                album : data.id
                            }, function(album_users) {
                                for (var i = 0, len = album_users.length; i < len; i++) {
                                    addUser(album_users[i].user);
                                }
                            }, "json");
                        }
                    });
                }
            }, {
                icon : 'glyphicon glyphicon-trash',
                label : ' Delete Album',
                cssClass : 'btn-danger',
                action : function(dialogItself) {
                    var $button = this; // 'this' here is a jQuery object that
                                        // wrapping the <button> DOM element.
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    // send our update
                    BootstrapDialog.show({
                        draggable : true,
                        title : 'Are You Sure?',
                        message : 'Are you sure you want to delete the album <b>' + data.name + '</b>',
                        buttons : [ {
                            icon : 'glyphicon glyphicon-trash',
                            label : ' Delete',
                            cssClass : 'btn-danger',
                            action : function(dialogInItself) {
                                var $button = this; // 'this' here is a jQuery
                                                    // object that wrapping the
                                                    // <button> DOM element.
                                $button.spin();
                                dialogInItself.enableButtons(false);
                                dialogInItself.setClosable(false);
                                // send our update
                                $.post("/api/delete-album.php", {
                                    id : id,
                                }).done(function() {
                                    album_table.ajax.reload(null, false);
                                    dialogInItself.close();
                                    dialogItself.close();
                                });
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
                icon : 'glyphicon glyphicon-refresh',
                label : ' Make Thumbnails',
                cssClass : 'btn-warning',
                action : function(dialogItself) {
                    var $button = this; // 'this' here is a jQuery object that
                                        // wrapping the <button> DOM element.
                    $button.spin();
                    disableDialogButtons(dialogItself);
                    var markup;
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
                    $.post("/api/update-album.php", {
                        id : id,
                        name : $('#new-album-name').val(),
                        description : $('#new-album-description').val(),
                        date : $('#new-album-date').val(),
                        code : $('#new-album-code').val(),
                    }).done(function() {
                        dialogItself.close();
                        album_table.ajax.reload(null, false);
                    });
                }
            }, {
                label : 'Close',
                action : function(dialogItself) {
                    dialogItself.close();
                    album_table.ajax.reload(null, false);
                }
            } ],
            onshown : function(dialogItself) {
                $('#upload-container').uploadFile({
                    url : "/api/upload-album-images.php",
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
                    acceptFiles : "image/*,.nef,.cr2",
                    uploadQueueOrder : "bottom",
                    formData : {
                        "album" : id
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
            },
            onhide : function() {
                album_table.ajax.reload(null, false);
            },
        });
    }, "json");
}

function addUser(id) {
    var albumSpan = $('<span>');
    albumSpan.addClass('selected-album');
    albumSpan.attr('user-id', id);
    albumSpan.click(function() {
        $(this).remove();
    });
    $.get("/api/get-user.php", {
        id : id
    }, function(data) {
        albumSpan.html(data.usr);
        $('#album-users').append(albumSpan);
    }, "json");

}

function makeThumbs(id, button, dialog, markup) {
    $("#resize-progress").show();
    $.post("/api/make-thumbs.php", {
        id : id,
        markup : markup
    }).done(function() {
        var myVar = setInterval(function() {
            $.get("/scripts/status.txt", function(data) {
                $('#resize-progress .progress-bar').html(data);
                if (data.indexOf("Done") === 0) {
                    clearInterval(myVar);
                    $('#resize-progress .progress-bar').removeClass('active');
                    setTimeout(function() {
                        $('#resize-progress').hide();
                    }, 5000);
                    button.stopSpin();
                    enableDialogButtons(dialog);
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