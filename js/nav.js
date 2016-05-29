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
});

function resetPasswordForm() {
    $('#forgot-password-instructions').hide();
    $('#forgot-password-submit').hide();
    $('#forgot-password-code').show();
    $('#forgot-password-new-password').show();
    $('#forgot-password-new-password-confirm').show();
    $('#forgot-password-reset-password').show();
}