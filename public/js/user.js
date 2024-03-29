var user_table;
var resultsSelected = false;

$(document).ready(function () {
    user_table = $('#users').DataTable({
        "ajax": "/api/get-users.php",
        "order": [[2, "asc"]],
        "columnDefs": [{
            "orderable": false,
            "searchable": false,
            "data": function (row) {
                return '<button type="button" class="btn btn-xs btn-warning edit-user-btn" data-toggle="tooltip" data-placement="right" title="Edit ' + row.usr + ' Details"><i class="fa fa-pencil-square-o"></i></button> <button type="button" class="btn btn-xs btn-success view-user-log-btn" data-toggle="tooltip" data-placement="right" title="View ' + row.usr + ' Activities"><i class="fa fa-bars"></i></button> <button type="button" class="btn btn-xs btn-info view-as-user-btn" data-toggle="tooltip" data-placement="right" title="View Site As ' + row.usr + '"><i class="fa fa-user-secret"></i></button>';
            },
            "targets": 0
        }, {
            "data": "id",
            "className": "user-id",
            "visible": false,
            "searchable": false,
            "targets": 1
        }, {
            "data": "usr",
            "className": "user-username",
            "targets": 2
        }, {
            "data": function (row) {
                return row.firstName + " " + row.lastName;
            },
            "className": "user-name",
            "targets": 3
        }, {
            "data": "email",
            "className": "user-email",
            "targets": 4
        }, {
            "data": "role",
            "className": "user-role",
            "targets": 5
        }, {
            "data": "active",
            "className": "user-active",
            "targets": 6
        }, {
            "data": "lastLogin",
            "className": "user-last-login",
            "targets": 7
        }],
        "fnCreatedRow": function (nRow, aData) {
            $(nRow).attr('user-id', aData.id);
        }
    });
    $('#users').on('draw.dt search.dt', function () {
        setupEdit();
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('#add-user-btn').click(function () {
        editUser(null);
    });
});

function setupEdit() {
    $('.edit-user-btn').off().click(function () {
        var id = $(this).closest('tr').attr('user-id');
        $.get("/api/get-user.php", {
            id: id
        }, function (data) {
            editUser(data);
        }, "json");
    });
    $('.view-as-user-btn').off().click(function () {
        var id = $(this).closest('tr').attr('user-id');
        $.post("/api/login-as-user.php", {
            id: id
        }).done(function () {
            window.location.href = "index.php";
        });
    });
    $('.view-user-log-btn').off().click(function () {
        viewLogs($(this).closest('tr').attr('user-id'));
    });
}

function editUser(data) {
    BootstrapDialog.show({
        draggable: true,
        size: BootstrapDialog.SIZE_WIDE,
        title: function () {
            if (data !== null) {
                return 'Edit User <b>' + data.usr + '</b>';
            } else {
                return 'Add new user';
            }
        },
        message: function () {
            var inputs = $('<div>');
            inputs.addClass('form-horizontal');

            var usernameDiv = $('<div>');
            usernameDiv.addClass('form-group');
            var usernameLabel = $('<label>');
            usernameLabel.addClass('control-label col-sm-2');
            usernameLabel.attr('for', 'user-username');
            usernameLabel.html('Username: ');
            var usernameInputDiv = $('<div>');
            usernameInputDiv.addClass('col-sm-10');
            var usernameInput = $('<input>');
            usernameInput.attr('id', 'user-username');
            usernameInput.attr('type', 'text');
            usernameInput.addClass('form-control');
            usernameInput.attr('placeholder', 'Username');
            if (data !== null) {
                usernameInput.val(data.usr);
            }
            inputs.append(usernameDiv.append(usernameLabel).append(usernameInputDiv.append(usernameInput)));

            var firstNameDiv = $('<div>');
            firstNameDiv.addClass('form-group');
            var firstNameLabel = $('<label>');
            firstNameLabel.addClass('control-label col-sm-2');
            firstNameLabel.attr('for', 'user-first-name');
            firstNameLabel.html('First Name: ');
            var firstNameInputDiv = $('<div>');
            firstNameInputDiv.addClass('col-sm-10');
            var firstNameInput = $('<input>');
            firstNameInput.attr('id', 'user-first-name');
            firstNameInput.attr('type', 'text');
            firstNameInput.addClass('form-control');
            firstNameInput.attr('placeholder', 'First Name');
            if (data !== null) {
                firstNameInput.val(data.firstName);
            }
            inputs.append(firstNameDiv.append(firstNameLabel).append(firstNameInputDiv.append(firstNameInput)));

            var lastNameDiv = $('<div>');
            lastNameDiv.addClass('form-group');
            var lastNameLabel = $('<label>');
            lastNameLabel.addClass('control-label col-sm-2');
            lastNameLabel.attr('for', 'user-last-name');
            lastNameLabel.html('Last Name: ');
            var lastNameInputDiv = $('<div>');
            lastNameInputDiv.addClass('col-sm-10');
            var lastNameInput = $('<input>');
            lastNameInput.attr('id', 'user-last-name');
            lastNameInput.attr('type', 'text');
            lastNameInput.addClass('form-control');
            lastNameInput.attr('placeholder', 'Last Name');
            if (data !== null) {
                lastNameInput.val(data.lastName);
            }
            inputs.append(lastNameDiv.append(lastNameLabel).append(lastNameInputDiv.append(lastNameInput)));

            var emailDiv = $('<div>');
            emailDiv.addClass('form-group');
            var emailLabel = $('<label>');
            emailLabel.addClass('control-label col-sm-2');
            emailLabel.attr('for', 'user-email');
            emailLabel.html('Email: ');
            var emailInputDiv = $('<div>');
            emailInputDiv.addClass('col-sm-10');
            var emailInput = $('<input>');
            emailInput.attr('id', 'user-email');
            emailInput.attr('type', 'email');
            emailInput.addClass('form-control');
            emailInput.attr('placeholder', 'Email Address');
            if (data !== null) {
                emailInput.val(data.email);
            }
            inputs.append(emailDiv.append(emailLabel).append(emailInputDiv.append(emailInput)));

            var roleDiv = $('<div>');
            roleDiv.addClass('form-group');
            var roleLabel = $('<label>');
            roleLabel.addClass('control-label col-sm-2');
            roleLabel.attr('for', 'user-role');
            roleLabel.html('Role: ');
            var roleSelectDiv = $('<div>');
            roleSelectDiv.addClass('col-sm-10');
            var roleSelect = $('<select>');
            roleSelect.attr('id', 'user-role');
            roleSelect.addClass('form-control');
            inputs.append(roleDiv.append(roleLabel).append(roleSelectDiv.append(roleSelect)));

            var resetDiv = $('<div>');
            resetDiv.addClass('form-group');
            var resetLabel = $('<label>');
            resetLabel.addClass('control-label col-sm-2');
            resetLabel.attr('for', 'user-reset');
            resetLabel.html('Reset Key: ');
            var resetInputDiv = $('<div>');
            resetInputDiv.addClass('col-sm-10');
            var resetInput = $('<input>');
            resetInput.attr('type', 'text');
            resetInput.addClass('form-control');
            resetInput.prop('disabled', true);
            if (data !== null) {
                resetInput.val(data.resetKey);
            }
            inputs.append(resetDiv.append(resetLabel).append(resetInputDiv.append(resetInput)));

            var activeDiv = $('<div>');
            activeDiv.addClass('form-group');
            var activeInputDiv = $('<div>');
            activeInputDiv.addClass('col-sm-offset-2 col-sm-10');
            var activeInputDivDiv = $('<div>');
            activeInputDivDiv.addClass('checkbox');
            var activeLabel = $('<label>');
            var activeInput = $('<input>');
            activeInput.attr('id', 'user-active');
            activeInput.attr('type', 'checkbox');
            activeInput.prop('checked', true);
            if (data !== null && data.hasOwnProperty('active') && data.active !== "1") {
                activeInput.prop('checked', false);
            }
            inputs.append(activeDiv.append(activeInputDiv.append(activeInputDivDiv.append(activeLabel.append(activeInput).append(" Active User")))));

            return inputs;
        },
        buttons: [{
            id: 'user-albums-btn',
            icon: 'glyphicon glyphicon-picture',
            label: ' Update Albums',
            cssClass: 'btn-info',
            action: function (dialogItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                // send our update
                BootstrapDialog.show({
                    draggable: true,
                    title: 'Albums for User <b>' + data.usr + '</b>',
                    message: function () {
                        var inputs = $('<div class="open">');

                        var searchInput = $('<input>');
                        searchInput.attr('id', 'album-search');
                        searchInput.attr('type', 'text');
                        searchInput.addClass('form-control');
                        searchInput.attr('placeholder', 'Enter Album Name');
                        searchInput.on("keyup focus", function () {
                            var search_ele = $(this);
                            var keyword = search_ele.val();
                            $.get("/api/search-albums.php", {
                                keyword: keyword
                            }, function (data) {
                                $('.search-results').remove();
                                var results_ul = $('<ul class="dropdown-menu search-results">');
                                $.each(data, function (key, album) {
                                    if (!$(".selected-album[album-id='" + album.id + "']").length) {
                                        var result_li = $('<li>');
                                        var result_a = $('<a album-id="' + album.id + '" >' + album.name + '</a>');
                                        result_a.click(function () {
                                            addAlbum(album.id);
                                            $('.search-results').remove();
                                        });
                                        results_ul.append(result_li.append(result_a));
                                    }
                                });
                                results_ul.hover(function () {
                                    resultsSelected = true;
                                }, function () {
                                    resultsSelected = false;
                                });
                                search_ele.after(results_ul);
                            }, "json");
                        });
                        searchInput.focusout(function () {
                            if (!resultsSelected) {
                                $('.search-results').remove();
                            }
                        });
                        inputs.append(searchInput);

                        return inputs;
                    },
                    buttons: [{
                        icon: 'glyphicon glyphicon-save',
                        label: ' Update',
                        cssClass: 'btn-success',
                        action: function (dialogInItself) {
                            var $buttonIn = this; // 'this'
                            // here
                            // is a
                            // jQuery
                            // object that
                            // wrapping the
                            // <button> DOM
                            // element.
                            var modal = $button.closest('.modal-content');
                            $buttonIn.spin();
                            dialogInItself.enableButtons(false);
                            dialogInItself.setClosable(false);
                            var albums = [];
                            $('#user-albums .selected-album').each(function () {
                                albums.push($(this).attr('album-id'));
                            });
                            // send our update
                            $.post("/api/update-user-albums.php", {
                                user: data.id,
                                albums: albums
                            }).done(function (data) {
                                if (data !== "") {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                                }
                                $buttonIn.stopSpin();
                                $button.stopSpin();
                                dialogInItself.close();
                                enableDialogButtons(dialogItself);
                            }).fail(function (xhr, status, error) {
                                if (xhr.responseText !== "") {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                                } else if (error === "Unauthorized") {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                                } else {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your user's albums.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
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
                    }],
                    onshown: function (dialogInItself) {
                        var albumsDiv = $('<div>');
                        albumsDiv.attr('id', 'user-albums');
                        albumsDiv.css({
                            'padding': '0 10px 5px 10px'
                        });
                        dialogInItself.$modalBody.after(albumsDiv);
                        $.get("/api/get-user-albums.php", {
                            user: data.id
                        }, function (user_albums) {
                            for (var i = 0, len = user_albums.length; i < len; i++) {
                                addAlbum(user_albums[i].album);
                            }
                        }, "json");
                    }
                });
            }
        }, {
            id: 'user-delete-btn',
            icon: 'glyphicon glyphicon-trash',
            label: ' Delete User',
            cssClass: 'btn-danger',
            action: function (dialogItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                // send our update
                BootstrapDialog.show({
                    draggable: true,
                    title: 'Are You Sure?',
                    message: function () {
                        if (data !== null) {
                            return 'Are you sure you want to delete the user <b>' + data.usr + '</b>';
                        } else {
                            return '';
                        }
                    },
                    buttons: [{
                        icon: 'glyphicon glyphicon-trash',
                        label: ' Delete',
                        cssClass: 'btn-danger',
                        action: function (dialogInItself) {
                            var $button = this; // 'this'
                            // here
                            // is a
                            // jQuery
                            // object that
                            // wrapping the
                            // <button> DOM
                            // element.
                            var modal = $button.closest('.modal-content');
                            $button.spin();
                            dialogInItself.enableButtons(false);
                            dialogInItself.setClosable(false);
                            // send our update
                            $.post("/api/delete-user.php", {
                                id: data.id,
                            }).done(function (data) {
                                if (data === "") {
                                    user_table.ajax.reload(null, false);
                                    dialogItself.close();
                                } else {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                                }
                            }).fail(function (xhr, status, error) {
                                if (xhr.responseText !== "") {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                                } else if (error === "Unauthorized") {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                                } else {
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while deleting your user.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                                }
                            }).always(function () {
                                $button.stopSpin();
                                dialogInItself.close();
                                enableDialogButtons(dialogItself);
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
            id: 'user-update-password-btn',
            icon: 'glyphicon glyphicon-edit',
            label: ' Update Password',
            cssClass: 'btn-warning',
            action: function (dialogItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                // send our update
                BootstrapDialog.show({
                    draggable: true,
                    title: 'Change User Password',
                    message: function () {
                        var inputs = $('<div>');

                        var passwordInput = $('<input>');
                        passwordInput.attr('id', 'user-password');
                        passwordInput.attr('type', 'password');
                        passwordInput.addClass('form-control');
                        passwordInput.attr('placeholder', 'Password');
                        inputs.append(passwordInput);

                        var passwordConfirmInput = $('<input>');
                        passwordConfirmInput.attr('id', 'user-password-confirm');
                        passwordConfirmInput.attr('type', 'password');
                        passwordConfirmInput.addClass('form-control');
                        passwordConfirmInput.attr('placeholder', 'Re-type Password');
                        inputs.append(passwordConfirmInput);

                        return inputs;
                    },
                    buttons: [{
                        icon: 'glyphicon glyphicon-save',
                        label: ' Update',
                        cssClass: 'btn-success',
                        action: function (dialogInItself) {
                            var $buttonIn = this; // 'this'
                            // here
                            // is a
                            // jQuery
                            // object that
                            // wrapping the
                            // <button> DOM
                            // element.
                            var modal = $buttonIn.closest('.modal-content');
                            $buttonIn.spin();
                            dialogInItself.enableButtons(false);
                            dialogInItself.setClosable(false);
                            // send our update
                            $.post("/api/update-user-password.php", {
                                id: data.id,
                                password: $('#user-password').val(),
                                passwordConfirm: $('#user-password-confirm').val(),
                            }).done(function (data) {
                                if (data === "") {
                                    $button.stopSpin();
                                    dialogInItself.close();
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
                                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your user's password.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                                }
                            }).always(function () {
                                $buttonIn.stopSpin();
                                enableDialogButtons(dialogInItself);
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
            id: 'user-update-btn',
            icon: 'glyphicon glyphicon-save',
            label: ' Update User',
            cssClass: 'btn-success',
            action: function (dialogItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                disableDialogButtons(dialogItself);
                $.post("/api/update-user.php", {
                    id: data.id,
                    username: $('#user-username').val(),
                    firstName: $('#user-first-name').val(),
                    lastName: $('#user-last-name').val(),
                    email: $('#user-email').val(),
                    role: $('#user-role').val(),
                    active: $('#user-active').is(':checked') ? 1 : 0,
                }).done(function (data) {
                    if (data === "") {
                        dialogItself.close();
                        user_table.ajax.reload(null, false);
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }

                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your user.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                }).always(function () {
                    $button.stopSpin();
                    enableDialogButtons(dialogItself);
                });
            }
        }, {
            id: 'user-save-btn',
            icon: 'glyphicon glyphicon-save',
            label: ' Add User',
            cssClass: 'btn-success',
            action: function (dialogItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                disableDialogButtons(dialogItself);
                $.post("/api/create-user.php", {
                    username: $('#user-username').val(),
                    firstName: $('#user-first-name').val(),
                    lastName: $('#user-last-name').val(),
                    email: $('#user-email').val(),
                    role: $('#user-role').val(),
                    active: $('#user-active').is(':checked') ? 1 : 0,
                }).done(function (data) {
                    if ($.isNumeric(data) && data !== '0') {
                        user_table.ajax.reload(null, false);
                        dialogItself.close();
                        // reload the modal
                        $.get("/api/get-user.php", {
                            id: data
                        }, function (data) {
                            editUser(data);
                        }, "json");
                    } else if (data === '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while adding your user.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your user.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
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
                user_table.ajax.reload(null, false);
            }
        }],
        onshow: function (dialogItself) {
            if (data !== null && data.role === "admin") {
                dialogItself.$modalFooter.find('#user-albums-btn').remove();
                dialogItself.$modalFooter.find('#user-save-btn').remove();
            } else if (data !== null) {
                dialogItself.$modalFooter.find('#user-save-btn').remove();
            } else {
                dialogItself.$modalFooter.find('#user-albums-btn').remove();
                dialogItself.$modalFooter.find('#user-update-password-btn').remove();
                dialogItself.$modalFooter.find('#user-delete-btn').remove();
                dialogItself.$modalFooter.find('#user-update-btn').remove();
            }
        },
        onshown: function () {
            $.get("/api/get-roles.php", function (roles) {
                for (var i = 0, len = roles.length; i < len; i++) {
                    var option = $('<option>');
                    option.val(roles[i]);
                    option.html(roles[i]);
                    $('#user-role').append(option);
                }
                if (data !== null) {
                    $("#user-role option[value='" + data.role + "']").attr("selected", true);
                } else {
                    $("#user-role option[value='downloader']").attr("selected", true);
                }
            }, "json");
        },
        onhide: function () {
            user_table.ajax.reload(null, false);
        },
    });
}

function viewLogs(id) {
    var dialog = new BootstrapDialog({
        title: 'User Activity',
        message: function () {
            var $message = $('<div>Loading...</div>');
            $.get("/api/get-user-log.php", {
                id: id
            }, function (data) {
                var message = $('<div>');
                message.addClass('row');
                for (var i = 0, len = data.length; i < len; i++) {
                    var log = data[i];

                    var time = $('<div>');
                    time.addClass('col-md-4');
                    time.html(log.time);
                    message.append(time);

                    var activity = $('<div>');
                    activity.addClass('col-md-8');
                    var action = log.action;
                    if (log.what !== null) {
                        action += " <i>" + log.what + "</i>";
                    }
                    if (log.album !== null) {
                        action += " from album <i>" + log.name + "</i>";
                    }
                    activity.html(action);
                    message.append(activity);
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

function addAlbum(id) {
    var albumSpan = $('<span>');
    albumSpan.addClass('selected-album');
    albumSpan.attr('album-id', id);
    albumSpan.click(function () {
        $(this).remove();
    });
    $.get("/api/get-album.php", {
        id: id
    }, function (data) {
        albumSpan.html(data.name);
        $('#user-albums').append(albumSpan);
    }, "json");
}

function disableDialogButtons(dialog) {
    dialog.enableButtons(false);
    dialog.setClosable(false);
}

function enableDialogButtons(dialog) {
    dialog.enableButtons(true);
    dialog.setClosable(true);
}
