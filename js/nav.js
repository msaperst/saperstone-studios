var my_role;

$(function() {
    $('#nav-search-input').width(
            $('#nav-search-input').parent().parent().width() - 50);

    $('#login-submit').click(function(){
        $.post("/api/login.php", {
            username : $('#login-user').val(),
            password : $('#login-pass').val(),
            rememberMe : $('#login-remember').is(':checked') ? 1 : 0,
            submit : "Login"
        }).done(function(data) {
            $('#login-error').html(data);
            if(data === "") {
                $('#login-message').html("Successfully Logged In");
                location.reload();
            }
        });
    });
    
    $('.loginmodal-container').keypress(function(e) {
        if(e.which == 13) {
            $( "#login-submit" ).trigger( "click" );
        }
    });
    
    $('#logout-button').click(function() {
        $.post("/api/login.php", {
            submit : "Logout"
        }).done(function(data) {
            location.reload();
        });
    });
    
    $("#login-forgot-password").click(function(){
        $('#login-modal').modal('hide');
        $('#forgot-password-instructions').show();
        $('#forgot-password-error').empty();
        $('#forgot-password-message').empty();
        $('#forgot-password-instructions').show();
        $('#forgot-password-submit').show();
        $('#forgot-password-code').hide();
        $('#forgot-password-new-password').hide();
        $('#forgot-password-new-password-confirm').hide();
        $('#forgot-password-reset-password').hide();
        $('#forgot-password-modal').modal('show');
    });
    
    $("#forgot-password-submit").click(function(){
        $.post("/api/send-reset-code.php", {
            email : $('#forgot-password-email').val(),
        }).done(function(data) {
            $('#forgot-password-error').html(data);
            if(data === "") {
                $('#forgot-password-message').html("Reset code has been sent, please enter it below, along with a new password");
                resetPasswordForm();
            }
        });
    });
    $("#forgot-password-prev-code").click(function(){
        $('#forgot-password-message').html("Enter your email address above, with your previous reset code and a new password below");
        resetPasswordForm();
    });
    $("#forgot-password-reset-password").click(function(){
        $.post("/api/reset-password.php", {
            email : $('#forgot-password-email').val(),
            code : $('#forgot-password-code').val(),
            password : $('#forgot-password-new-password').val(),
            passwordConfirm : $('#forgot-password-new-password-confirm').val(),
        }).done(function(data) {
            $('#forgot-password-error').html(data);
            if(data === "") {
                $('#forgot-password-message').html("Your password has been successfully reset. You can now login with your new credentials.");
                setTimeout(function() {$('#forgot-password-modal').modal('hide');}, 10000);
            }
        });
    });
    
    if (window.location.hash) {
        if (window.location.hash == "#album") {
            $.get("/api/get-my-role.php", {
            }).done(function(data) {
                my_role = data;
                findAlbum();
            });
        }
    }
    
    $.get("/api/get-my-role.php", {
    }).done(function(data) {
        my_role = data;
    });
});
window.onhashchange=function(){
    if (window.location.hash == "#album") {
        findAlbum();
    }
};

function resetPasswordForm() {
    $('#forgot-password-instructions').hide();
    $('#forgot-password-submit').hide();
    $('#forgot-password-code').show();
    $('#forgot-password-new-password').show();
    $('#forgot-password-new-password-confirm').show();
    $('#forgot-password-reset-password').show();
}

function findAlbum() {
    BootstrapDialog.show({
        draggable: true,
        title: 'Find An Album',
        message: function(dialogItself){
            var inputs =  '<input placeholder="Album Code" id="find-album-code" type="text" class="form-control"/>';
            if( my_role == 'downloader' || my_role == 'uploader' ) {
                inputs += '<div class="checkbox">' +
                        '<label><input id="find-album-add" type="checkbox" value="" checked>Add to my albums</label>' + 
                        '</div>';
            }
            inputs += '<div id="find-album-error" class="error"></div>' +
               '<div id="find-album-message" class="success"></div>';
            return inputs;
        }, 
        buttons: [{
            icon: 'glyphicon glyphicon-search',
            label: ' Find Album',
            hotkey: 13,
            cssClass: 'btn-success',
            action: function(dialogItself){
                var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                $button.spin();
                dialogItself.enableButtons(false);
                dialogItself.setClosable(false);
                //send our update
                $.get("/api/find-album.php", {
                    code : $('#find-album-code').val(),
                    albumAdd : $('#find-album-add').is(':checked') ? 1 : 0,
                }).done(function(data) {
                    $button.stopSpin();
                    dialogItself.enableButtons(true);
                    dialogItself.setClosable(true);
                    //goto album url if it exists
                    if( Math.round(data) == data && data !== '0' ) {
                        $('#find-album-message').html("Navigating to album");
                        window.location.href = '/albums/album.php?album=' + data;
                    } else if ( data === '0' ) {
                        $('#find-album-error').html("There was some error with your request. Please contact our <a target='_blank' href='mailto:webmaster@saperstonestudios.com'>webmaster</a>");
                    } else {
                        $('#find-album-error').html(data);
                    }
                });                    
            }
        }, {
            label: 'Close',
            action: function(dialogItself){
                window.location.hash = "";
                dialogItself.close();
            }
        }],
    });
}