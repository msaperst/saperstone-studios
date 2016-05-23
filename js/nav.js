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
            if(data == "") {
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
});