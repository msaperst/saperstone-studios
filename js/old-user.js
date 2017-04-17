var userId;

$(function() {
    $('#login-button').click(function() {
        $.post("/api/old-user-login.php", {
            username : $('#login-username').val(),
            password : md5($('#login-password').val()),
        }).done(function(data) {
            if (data === "") {
                $('#login-message').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Successfully Logged In. Please wait as you are redirected.</div>");
                window.location = "index.php";
            } else if ($.isNumeric(data)) {
                userId = data;
                $('#login-message').append("<div class='alert alert-info lead'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>We have recently upgraded our login accounts along with our website. None of your data is lost, but you will need to update your credentials in order to access your images.</div>");
                $('#login-button').hide();
                $('#login').hide();

                $('#update').removeClass('hidden');
                $('#create-profile-button').removeClass('hidden');
            } else {
                $('#login-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
            }
        }).fail(function(xhr, status, error) {
            if (xhr.responseText !== "") {
                $('#login-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
            } else if (error === "Unauthorized") {
                $('#login-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
            } else {
                $('#login-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
            }
        });
    });

    $('#create-profile-button').click(function() {
        createNewProfile();
    });
});

function createNewProfile() {
    // setup our button
    $("#create-profile-button").prop("disabled", true);
    $("#create-profile-button em").removeClass('fa fa-floppy-o').addClass('glyphicon glyphicon-asterisk icon-spin');

    var url = "/api/update-old-user.php";

    $.post(url, {
        id : userId,
        username : $('#profile-username').val(),
        firstName : $('#profile-firstname').val(),
        lastName : $('#profile-lastname').val(),
        password : $('#profile-password').val().length ? md5($('#profile-password').val()) : "",
        email : $('#profile-email').val()
    }).done(function(data) {
        if (data !== "") {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        } else {
            $('#update-profile-message').append("<div class='alert alert-success'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your profile information was successfully updated.</div>");
            if (!$('#profile-current-password').length) {
                window.location = "index.php";
            }
        }
    }).fail(function(xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your album users.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function() {
        $("#create-profile-button").prop("disabled", false);
        $("#create-profile-button em").addClass('fa fa-floppy-o').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}