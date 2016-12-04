$(document).ready(function() {
    $('#update-profile').click(function(){
        updateProfile();
    });
});

function updateProfile() {
    $("#update-profile").prop("disabled", true);
    $("#update-profile em").removeClass('fa fa-floppy-o').addClass('glyphicon glyphicon-asterisk icon-spin');

    $.post("/api/updateProfile.php", {
        firstName : $('#profile-firstname'),
        lastName : $('#profile-lastname'),
        email : $('#profile-email')
    }).done(function(data) {
        $("#update-profile").prop("disabled", false);
        $("#update-profile em").addClass('fa fa-floppy-o').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}