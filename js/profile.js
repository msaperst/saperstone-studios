$(document).ready(function() {
    $('#update-profile').click(function() {
        updateProfile();
    });
    $('input').keyup(function() {
        validateInput();
    });
});

function validateInput() {
    var allGood = true;
    if ($('#profile-firstname').val() == "") {
        $('#update-profile-firstname-message').empty().append("A first name is required");
        setError($('#profile-firstname'));
        allGood = false
    } else {
        setSuccess($('#profile-firstname'));
        $('#update-profile-firstname-message').empty();
    }
    if ($('#profile-lastname').val() == "") {
        $('#update-profile-lastname-message').empty().append("A last name is required");
        setError($('#profile-lastname'));
        allGood = false
    } else {
        setSuccess($('#profile-lastname'));
        $('#update-profile-lastname-message').empty();
    }

    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!re.test($('#profile-email').val())) {
        $('#update-profile-email-message').empty().append("A valid email is required");
        setError($('#profile-email'));
        allGood = false
    } else {
        setSuccess($('#profile-email'));
        $('#update-profile-email-message').empty();
    }

    if ($('#profile-password').val() != "") {
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
        console.log(color);
        $('#update-profile-password-strength').width(width);
        $('#update-profile-password-strength').css({
            'width' : width + '%',
            'background-color' : color,
            'height' : '10px'
        });
    } else {
        $('#update-profile-password-message').hide();
    }
    if ($('#profile-password').val() != $('#profile-confirm-password').val()) {
        $('#update-profile-confirm-password-message').empty().append("Your passwords do not match");
        setError($('#profile-password'));
        setError($('#profile-confirm-password'));
        allGood = false
    } else {
        setSuccess($('#profile-password'));
        setSuccess($('#profile-confirm-password'));
        $('#update-profile-confirm-password-message').empty();
    }
    if ($('#profile-password').val() == "" && $('#profile-confirm-password').val() == "") {
        setBlank($('#profile-password'));
        setBlank($('#profile-confirm-password'));
        $('#update-profile-confirm-password-message').empty();
    }
    if ($('#profile-password').val() != "" && $('#profile-current-password').val() == "") {
        $('#update-profile-current-password-message').empty().append("Please confirm old password to set new password");
        setError($('#profile-current-password'));
        allGood = false
    } else {
        setBlank($('#profile-current-password'));
        $('#update-profile-current-password-message').empty();
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

    $
            .post("/api/update-profile.php", {
                firstName : $('#profile-firstname').val(),
                lastName : $('#profile-lastname').val(),
                curPass : md5($('#profile-current-password').val()),
                password : md5($('#profile-password').val()),
                email : $('#profile-email').val()
            })
            .done(
                    function(data) {
                        if (data != "") {
                            $('#update-profile-message').append(
                                    "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                        } else {
                            $('#update-profile-message')
                                    .append(
                                            "<div class='alert alert-success'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your profile information was successfully updated.</div>");
                        }
                    })
            .fail(
                    function() {
                        $('#update-profile-message')
                                .append(
                                        "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your album users.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }).always(function() {
                $("#update-profile").prop("disabled", false);
                $("#update-profile em").addClass('fa fa-floppy-o').removeClass('glyphicon glyphicon-asterisk icon-spin');
            });
}