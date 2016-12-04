var my_role;
var my_id;

$(function() {
    my_role = $('#my-user-role').val();
    my_id = $('#my-user-id').val();
    
    $.ajax({ 
        url: '/api/save-stats.php', 
        data: { 
            resolution: screen.width + "x" + screen.height
        } 
    });

    $('#nav-search-input').width($('#nav-search-input').parent().parent().width() - 55);
    
    $('#nav-search-icon').click(function(){
        searchBlog();
    });
    
    $('#nav-search-input').keypress(function (e) {
        if (e.which === 13) {
            searchBlog();
        }
    });

    $('#login-submit').click(function() {
        $.post("/api/login.php", {
            username : $('#login-user').val(),
            password : md5( $('#login-pass').val() ),
            rememberMe : $('#login-remember').is(':checked') ? 1 : 0,
            submit : "Login"
        }).done(function(data) {
            if (data === "") {
                $('#login-modal .loginmodal-container').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Successfully Logged In. Please wait as you are redirected.</div>");
                location.reload();
            } else {
                $('#login-modal .loginmodal-container').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
            }
        }).fail(function(){
            $('#login-modal .loginmodal-container').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        });
    });

    $('.loginmodal-container').keypress(function(e) {
        if (e.which === 13) {
            $("#login-submit").trigger("click");
        }
    });

    $('#logout-button').click(function() {
        $.post("/api/login.php", {
            submit : "Logout"
        }).done(function() {
            location.reload();
        });
    });

    $("#login-forgot-password").click(function() {
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

    $("#forgot-password-submit").click(function() {
        $.post("/api/send-reset-code.php", {
            email : $('#forgot-password-email').val(),
        }).done(function(data) {
            $('#forgot-password-error').html(data);
            if (data === "") {
                $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Reset code has been sent, please enter it below, along with a new password</div>");
                resetPasswordForm();
            } else {
                $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
            }
        }).fail(function(){
            $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        });
    });
    $("#forgot-password-prev-code").click(function() {
        $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Enter your email address above, with your previous reset code and a new password below</div>");
        resetPasswordForm();
    });
    $("#forgot-password-reset-password").click(function() {
        $.post("/api/reset-password.php", {
            email : $('#forgot-password-email').val(),
            code : $('#forgot-password-code').val(),
            password : $('#forgot-password-new-password').val(),
            passwordConfirm : $('#forgot-password-new-password-confirm').val(),
        }).done(function(data) {
            if (data === "") {
                $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your password has been successfully reset. You can now login with your new credentials.</div>");
                setTimeout(function() {
                    $('#forgot-password-modal').modal('hide');
                }, 10000);
            } else {
                $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
            }
        }).fail(function(){
            $('#forgot-password-modal .loginmodal-container').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        });
    });

    if (window.location.hash && window.location.hash === "#album") {
        findAlbum();
    }
});

window.onhashchange = function() {
    if (window.location.hash === "#album") {
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
        draggable : true,
        title : 'Find An Album',
        message : function() {
            var inputs = '<input placeholder="Album Code" id="find-album-code" type="text" class="form-control"/>';
            if (my_role === 'downloader' || my_role === 'uploader') {
                inputs += '<div class="checkbox">' + '<label><input id="find-album-add" type="checkbox" value="" checked>Add to my albums</label>' + '</div>';
            }
            return inputs;
        },
        buttons : [ {
            icon : 'glyphicon glyphicon-search',
            label : ' Find Album',
            hotkey : 13,
            cssClass : 'btn-success',
            action : function(dialogItself) {
                var $button = this; // 'this' here is a jQuery object that
                                    // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogItself.enableButtons(false);
                dialogItself.setClosable(false);
                // send our update
                $.get("/api/find-album.php", {
                    code : $('#find-album-code').val(),
                    albumAdd : $('#find-album-add').is(':checked') ? 1 : 0,
                }).done(function(data) {
                    $button.stopSpin();
                    dialogItself.enableButtons(true);
                    dialogItself.setClosable(true);
                    // goto album url if it exists
                    if ($.isNumeric(data) && data !== '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Navigating to album</div>");                        
                        window.location.href = '/albums/album.php?album=' + data;
                    } else if (data === '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function(){
                    modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                });
            }
        }, {
            label : 'Close',
            action : function(dialogItself) {
                window.location.hash = "";
                dialogItself.close();
            }
        } ],
    });
}

function searchBlog() {
    window.location = "/blog/search.php?s=" + $('#nav-search-input').val();
}

function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] === variable) {
            return pair[1];
        }
    }
    return (false);
}

if (typeof String.prototype.endsWith !== 'function') {
    String.prototype.endsWith = function(suffix) {
        return this.indexOf(suffix, this.length - suffix.length) !== -1;
    };
}