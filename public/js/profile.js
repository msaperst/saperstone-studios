$(document).ready(function () {
    validateInput();
    $('#update-profile').click(function () {
        updateProfile();
    });
    $('input').keyup(function () {
        validateInput();
    });
});

function validateInput() {
    var allGood = true;
    var re = /^[\w]{5,}$/;
    if (!re.test($('#profile-username').val())) {
        $('#update-profile-username-message').empty().append("Your username must be at least 5 characters, and contain only letters numbers and underscores");
        setError($('#profile-username'));
        allGood = false
    } else {
        setSuccess($('#profile-username'));
        $('#update-profile-username-message').empty();
    }

    if ($('#profile-firstname').val() === "") {
        $('#update-profile-firstname-message').empty().append("A first name is required");
        setError($('#profile-firstname'));
        allGood = false
    } else {
        setSuccess($('#profile-firstname'));
        $('#update-profile-firstname-message').empty();
    }
    if ($('#profile-lastname').val() === "") {
        $('#update-profile-lastname-message').empty().append("A last name is required");
        setError($('#profile-lastname'));
        allGood = false
    } else {
        setSuccess($('#profile-lastname'));
        $('#update-profile-lastname-message').empty();
    }

    re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!re.test($('#profile-email').val())) {
        $('#update-profile-email-message').empty().append("A valid email is required");
        setError($('#profile-email'));
        allGood = false
    } else {
        setSuccess($('#profile-email'));
        $('#update-profile-email-message').empty();
    }

    if ($('#profile-password').val() === "" && window.location.href.endsWith("/register.php")) {
        $('#update-profile-password-message').empty().append("A password is required").addClass('error');
        setError($('#profile-password'));
        allGood = false
    } else if ($('#profile-password').val() === "") {
        setBlank($('#profile-password'));
        $('#update-profile-password-message').empty();
    } else {
        setSuccess($('#profile-password'));
        $('#update-profile-password-message').show();
        var length = $('#profile-password').val().length;
        if (length > 32) {
            length = 32;
        }
        var width = $('#profile-password').val().length / 32 * 100;
        var color = 'FF0000';
        if (length <= 16) {
            color = '#FF' + length.toString(16) + '000';
        }
        if (length > 16) {
            color = '#' + parseInt(32 - length).toString(16) + '0FF00';
        }
        $('#update-profile-password-message').empty().append('<div id="update-profile-password-strength"></div>').removeClass('error');
        $('#update-profile-password-strength').width(width);
        $('#update-profile-password-strength').css({
            'width': width + '%',
            'background-color': color,
            'height': '10px'
        });
    }

    if ($('#profile-password').val() !== $('#profile-confirm-password').val()) {
        $('#update-profile-confirm-password-message').empty().append("Your passwords do not match");
        setError($('#profile-confirm-password'));
        allGood = false
    } else {
        setSuccess($('#profile-confirm-password'));
        $('#update-profile-confirm-password-message').empty();
    }

    if (window.location.href.endsWith("/profile.php")) {
        if ($('#profile-password').val() === "" && $('#profile-confirm-password').val() === "") {
            setBlank($('#profile-password'));
            setBlank($('#profile-confirm-password'));
            $('#update-profile-confirm-password-message').empty();
        }
        if ($('#profile-password').val() !== "" && $('#profile-current-password').val() === "") {
            $('#update-profile-current-password-message').empty().append("Please confirm old password to set new password");
            setError($('#profile-current-password'));
            allGood = false
        } else if ($('#profile-password').val() === "" && $('#profile-confirm-password').val() === "") {
            setBlank($('#profile-current-password'));
            $('#update-profile-current-password-message').empty();
        } else {
            setSuccess($('#profile-current-password'));
            $('#update-profile-current-password-message').empty();
        }
    }

    if (!allGood) {
        $('#update-profile').prop("disabled", true);
    } else {
        $('#update-profile').prop("disabled", false);
    }
    return allGood;
}

function setSuccess(input) {
    input.next().addClass('glyphicon-ok').removeClass('glyphicon-remove');
    input.closest('.has-feedback').addClass('has-success').removeClass('has-error');
}

function setError(input) {
    input.next().removeClass('glyphicon-ok').addClass('glyphicon-remove');
    input.closest('.has-feedback').removeClass('has-success').addClass('has-error');
}

function setBlank(input) {
    input.next().removeClass('glyphicon-ok').removeClass('glyphicon-remove');
    input.closest('.has-feedback').removeClass('has-success').removeClass('has-error');
}

function updateProfile() {
    // setup our button
    $("#update-profile").prop("disabled", true);
    $("#update-profile em").removeClass('fa fa-floppy-o').addClass('glyphicon glyphicon-asterisk icon-spin');

    var url = "/api/register-user.php";
    if ($('#profile-current-password').length) {
        url = "/api/update-profile.php";
    }

    $.post(url, {
        username: $('#profile-username').val(),
        firstName: $('#profile-firstname').val(),
        lastName: $('#profile-lastname').val(),
        curPass: $('#profile-current-password').length ? $('#profile-current-password').val() : "",
        password: $('#profile-password').val().length ? $('#profile-password').val() : "",
        passwordConfirm: $('#profile-confirm-password').val().length ? $('#profile-confirm-password').val() : "",
        email: $('#profile-email').val(),
        rememberMe: $('#profile-remember').is(':checked') ? 1 : 0,
    }).done(function (data) {
        if ($.isNumeric(data) && data !== '0') {
            $('#update-profile-message').append("<div class='alert alert-success'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your user was successfully created.</div>");
            if (!$('#profile-current-password').length) {
                location.reload();
            }
        } else if (data === "") {
            $('#update-profile-message').append("<div class='alert alert-success'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your profile information was successfully updated.</div>");
        } else {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#update-profile-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your album users.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function () {
        $("#update-profile").prop("disabled", false);
        $("#update-profile em").addClass('fa fa-floppy-o').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}