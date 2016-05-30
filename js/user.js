var album_table;

$(document).ready(function() {
    user_table = $('#users').DataTable({
        "ajax" : "/api/get-users.php",
        "order": [[ 2, "asc" ]],
        "columnDefs": [
            {
                "orderable" : false,
                "searchable": false,
                "data" : function(row, type, val, meta) {
                    var buttons = '<button type="button" class="btn btn-xs btn-warning edit-user-btn">' +
                        '<i class="fa fa-pencil-square-o"></i></button>';
                    return buttons;
                },
                "targets" : 0
            }, {
                "data" : "id",
                "className" : "user-id",
                "visible": false,
                "searchable": false,
                "targets" : 1
            }, {
                "data" : "usr",
                "className" : "user-username",
                "targets" : 2
            }, {
                "data" : function(row, type, val, meta) {
                    return row.firstName + " " + row.lastName;
                },
                "className" : "user-name",
                "targets" : 3
            }, {
                "data" : "email",
                "className" : "user-email",
                "targets" : 4
            }, {
                "data" : "role",
                "className" : "user-role",
                "targets" : 5
            }, {
                "data" : "active",
                "className" : "user-active",
                "targets" : 6
            }, {
                "data" : "lastLogin",
                "className" : "user-last-login",
                "targets" : 7
            }
        ],
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('user-id',aData.id);
        }
    });
    $('#users').on( 'draw.dt search.dt', function () {
        setupEdit();
    });

    $('#add-user-btn').click(function(){
        editUser(null);
    });
});

function setupEdit() {
    $('.edit-user-btn').off().click(function(){
        var id = $(this).closest('tr').attr('user-id');
        $.get( 
            "/api/get-user.php",
            { id: id },
            function( data ) {
                editUser( data );
            },
            "json"
        );
    });
}

function editUser(data) {
    BootstrapDialog.show({
        draggable: true,
        size: BootstrapDialog.SIZE_WIDE,
        title: function(dialogItself){
            if( data !== null ) {
                return 'Edit User <b>' + data.usr + '</b>';
            } else {
                return 'Add new user';
            }
        },
        message: function(dialogItself){
            var inputs = $('<div>');
            inputs.addClass('form-horizontal');

            var usernameDiv = $('<div>');
            usernameDiv.addClass('form-group');
            var usernameLabel = $('<label>');
            usernameLabel.addClass('control-label col-sm-2');
            usernameLabel.attr('for','user-username');
            usernameLabel.html('Username: ');
            var usernameInputDiv = $('<div>');
            usernameInputDiv.addClass('col-sm-10');
            var usernameInput = $('<input>');
            usernameInput.attr('id','user-username');
            usernameInput.attr('type','text');
            usernameInput.addClass('form-control');
            usernameInput.attr('placeholder','Username');
            if ( data !== null ) {
                usernameInput.val( data.usr );
            }
            inputs.append(usernameDiv.append(usernameLabel).append(usernameInputDiv.append(usernameInput)));
            
            var firstNameDiv = $('<div>');
            firstNameDiv.addClass('form-group');
            var firstNameLabel = $('<label>');
            firstNameLabel.addClass('control-label col-sm-2');
            firstNameLabel.attr('for','user-first-name');
            firstNameLabel.html('First Name: ');
            var firstNameInputDiv = $('<div>');
            firstNameInputDiv.addClass('col-sm-10');
            var firstNameInput = $('<input>');
            firstNameInput.attr('id','user-first-name');
            firstNameInput.attr('type','text');
            firstNameInput.addClass('form-control');
            firstNameInput.attr('placeholder','First Name');
            if ( data !== null ) {
                firstNameInput.val( data.firstName );
            }
            inputs.append(firstNameDiv.append(firstNameLabel).append(firstNameInputDiv.append(firstNameInput)));

            var lastNameDiv = $('<div>');
            lastNameDiv.addClass('form-group');
            var lastNameLabel = $('<label>');
            lastNameLabel.addClass('control-label col-sm-2');
            lastNameLabel.attr('for','user-last-name');
            lastNameLabel.html('Last Name: ');
            var lastNameInputDiv = $('<div>');
            lastNameInputDiv.addClass('col-sm-10');
            var lastNameInput = $('<input>');
            lastNameInput.attr('id','user-last-name');
            lastNameInput.attr('type','text');
            lastNameInput.addClass('form-control');
            lastNameInput.attr('placeholder','Last Name');
            if ( data !== null ) {
                lastNameInput.val( data.lastName );
            }
            inputs.append(lastNameDiv.append(lastNameLabel).append(lastNameInputDiv.append(lastNameInput)));
            
            var emailDiv = $('<div>');
            emailDiv.addClass('form-group');
            var emailLabel = $('<label>');
            emailLabel.addClass('control-label col-sm-2');
            emailLabel.attr('for','user-email');
            emailLabel.html('Email: ');
            var emailInputDiv = $('<div>');
            emailInputDiv.addClass('col-sm-10');
            var emailInput = $('<input>');
            emailInput.attr('id','user-email');
            emailInput.attr('type','text');
            emailInput.addClass('form-control');
            emailInput.attr('placeholder','Email Address');
            if ( data !== null ) {
                emailInput.val( data.email );
            }
            inputs.append(emailDiv.append(emailLabel).append(emailInputDiv.append(emailInput)));
            
            var roleDiv = $('<div>');
            roleDiv.addClass('form-group');
            var roleLabel = $('<label>');
            roleLabel.addClass('control-label col-sm-2');
            roleLabel.attr('for','user-role');
            roleLabel.html('Role: ');
            var roleSelectDiv = $('<div>');
            roleSelectDiv.addClass('col-sm-10');
            var roleSelect = $('<select>');
            roleSelect.attr('id','user-role');
            roleSelect.addClass('form-control');
            inputs.append(roleDiv.append(roleLabel).append(roleSelectDiv.append(roleSelect)));
            
            var resetDiv = $('<div>');
            resetDiv.addClass('form-group');
            var resetLabel = $('<label>');
            resetLabel.addClass('control-label col-sm-2');
            resetLabel.attr('for','user-reset');
            resetLabel.html('Reset Key: ');
            var resetInputDiv = $('<div>');
            resetInputDiv.addClass('col-sm-10');
            var resetInput = $('<input>');
            resetInput.attr('type','text');
            resetInput.addClass('form-control');
            resetInput.prop('disabled',true);
            if ( data !== null ) {
                resetInput.val( data.resetKey );
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
            activeInput.attr('id','user-active');
            activeInput.attr('type','checkbox');
            activeInput.prop('checked', true);
            if ( data !== null ) {
                if( data.active != "1" ) {
                    activeInput.prop('checked', false);
                }
            }
            inputs.append(activeDiv.append(activeInputDiv.append(activeInputDivDiv.append(activeLabel.append(activeInput).append( " Active User" )))));
            inputs.append( '<div id="user-error" class="error"></div>' +
                   '<div id="user-message" class="success"></div>');
            
            return inputs;
        }, 
        buttons: [{
            id: 'user-delete-btn',
            icon: 'glyphicon glyphicon-trash',
            label: ' Delete User',
            cssClass: 'btn-danger',
            action: function(dialogItself){
                var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                //send our update
                BootstrapDialog.show({
                    draggable: true,
                    title: 'Are You Sure?',
                    message: function(dialogInItself){
                        if( data !== null ) {
                            return 'Are you sure you want to delete the user <b>' + data.usr + '</b>';
                        } else {
                            return '';
                        }
                    },
                    buttons: [{
                        icon: 'glyphicon glyphicon-trash',
                        label: ' Delete',
                        cssClass: 'btn-danger',
                        action: function(dialogInItself){
                            var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                            $button.spin();
                            dialogInItself.enableButtons(false);
                            dialogInItself.setClosable(false);
                            //send our update
                            $.post("/api/delete-user.php", {
                                id : data.id,
                            }).done(function(data) {
                                $('#user-error').html(data);
                                if(data === "") {
                                    user_table.ajax.reload( null, false );
                                    dialogItself.close();
                                }
                                $button.stopSpin();
                                dialogInItself.close();
                                enableDialogButtons(dialogItself);
                            });                    
                        }
                    }, {
                        label: 'Close',
                        action: function(dialogInItself){
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
            action: function(dialogItself){
                var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                //send our update
                BootstrapDialog.show({
                    draggable: true,
                    title: 'Change User Password',
                    message: function(dialogInItself){
                        var inputs = $('<div>');

                        var passwordInput = $('<input>');
                        passwordInput.attr('id','user-password');
                        passwordInput.attr('type','password');
                        passwordInput.addClass('form-control');
                        passwordInput.attr('placeholder','Password');
                        inputs.append(passwordInput);

                        var passwordConfirmInput = $('<input>');
                        passwordConfirmInput.attr('id','user-password-confirm');
                        passwordConfirmInput.attr('type','password');
                        passwordConfirmInput.addClass('form-control');
                        passwordConfirmInput.attr('placeholder','Re-type Password');
                        inputs.append(passwordConfirmInput);
                        
                        return inputs;
                    },
                    buttons: [{
                        icon: 'glyphicon glyphicon-save',
                        label: ' Update',
                        cssClass: 'btn-success',
                        action: function(dialogInItself){
                            var $buttonIn = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                            $buttonIn.spin();
                            dialogInItself.enableButtons(false);
                            dialogInItself.setClosable(false);
                            //send our update
                            $.post("/api/update-user-password.php", {
                                id : data.id,
                                password : $('#user-password').val(),
                                passwordConfirm : $('#user-password-confirm').val(),
                            }).done(function(data) {
                                $('#user-error').html(data);
                                $buttonIn.stopSpin();
                                $button.stopSpin();
                                dialogInItself.close();
                                enableDialogButtons(dialogItself);
                            });                    
                        }
                    }, {
                        label: 'Close',
                        action: function(dialogInItself){
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
            action: function(dialogItself){
                var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                $.post("/api/update-user.php", {
                    id : data.id,
                    username : $('#user-username').val(),
                    firstName : $('#user-first-name').val(),
                    lastName : $('#user-last-name').val(),
                    email : $('#user-email').val(),
                    role : $('#user-role').val(),
                    active : $('#user-active').is(':checked') ? 1 : 0,
                }).done(function(data) {
                    $('#user-error').html(data);
                    if(data === "") {
                        dialogItself.close();
                        user_table.ajax.reload( null, false );
                    }
                    $button.stopSpin();
                    enableDialogButtons(dialogItself);
                });
            }
        }, {
            id: 'user-save-btn',
            icon: 'glyphicon glyphicon-save',
            label: ' Add User',
            cssClass: 'btn-success',
            action: function(dialogItself){
                var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                $button.spin();
                disableDialogButtons(dialogItself);
                $.post("/api/add-user.php", {
                    username : $('#user-username').val(),
                    firstName : $('#user-first-name').val(),
                    lastName : $('#user-last-name').val(),
                    email : $('#user-email').val(),
                    role : $('#user-role').val(),
                    active : $('#user-active').is(':checked') ? 1 : 0,
                }).done(function(data) {
                    $('#user-error').html(data);
                    if(data === "") {
                        dialogItself.close();
                        user_table.ajax.reload( null, false );
                    }
                    $button.stopSpin();
                    enableDialogButtons(dialogItself);
                });
            }
        }, {
            label: 'Close',
            action: function(dialogItself) {
                dialogItself.close();
                user_table.ajax.reload( null, false );
            }
        }],
        onshow: function(dialogItself) {
            if( data !== null ) {
                dialogItself.$modalFooter.find('#user-save-btn').remove();
            } else {
                dialogItself.$modalFooter.find('#user-delete-btn').remove();
                dialogItself.$modalFooter.find('#user-update-btn').remove();
            }
        },
        onshown: function(dialogItself) {
            $.get( 
                "/api/get-roles.php",
                function( roles ) {
                    for (var i = 0, len = roles.length; i < len; i++) {
                        var option = $('<option>');
                        option.val(roles[i]);
                        option.html(roles[i]);
                        $('#user-role').append(option);
                    }
                    if( data !== null ) {
                        $("#user-role option[value='" + data.role + "']").attr("selected",true);
                    } else {
                        $("#user-role option[value='downloader']").attr("selected",true);
                    }
                },
                "json"
            );
        },
        onhide: function(dialogRef){
            user_table.ajax.reload( null, false );
        },
    });
}

function disableDialogButtons(dialog) {
    dialog.enableButtons(false);
    dialog.setClosable(false);    
}
function enableDialogButtons(dialog) {
    dialog.enableButtons(true);
    dialog.setClosable(true);    
}