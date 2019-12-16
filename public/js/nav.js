var my_role;
var my_id;

$(function() {
    //setup our cookie consent policy, unless we're on the privacy policy page
    if (top.location.pathname !== '/Privacy-Policy.php') {
        $('body').bsgdprcookies({
            // Cookie Consent message
            message:"This site uses cookies in order to provide you with the best experience possible, provide social media " +
            "features, analyze our traffic, and personalize album and photo data.<br/>\n<br/>\n" +
            "Please click 'Accept' to accept this use of your data. Alternatively, you may click 'Customize' to accept (or " +
            "reject) specific categories of data processing.<br/>\n<br/>\n" +
            "For more information on how we process your personal data - or to update your preferences at any time - please " +
            "visit our ",

            // set expiration
            expireDays: 10 * 52 * 7,

            // options for read more link
            moreLinkActive:true,
            moreLinkLabel:'Privacy Policy',
            moreLinkNewTab:true,
            moreLink:'privacy-policy.php',

            //customize options
            allowAdvancedOptions:true,
            advancedTitle:'Select which cookies you want to accept',
            advancedAutoOpenDelay: 1000,
            advancedButtonLabel:'Customize',
            advancedCookiesToSelect: [
                {
                    name: 'necessary',
                    title: 'Necessary',
                    description: 'Required for the site to work properly',
                    isFixed: true
                },
                {
                    name: 'preferences',
                    title: 'Site Preferences',
                    description: 'Required for saving your site preferences, e.g. remembering your username etc.',
                },
                {
                    name: 'analytics',
                    title: 'Analytics',
                    description: 'Required to collect site visits, browser types, etc.',
                },
            ],
            OnAccept: function() {
                // show remember me option if user accepts to use preferences cookies
                var cookies = jQuery.parseJSON(readCookie('CookiePreferences'));
                if( cookies !== null && cookies.includes("preferences") ) {
                    // hide all of the labels containing this
                    $('#profile-remember-span').show();
                    $('#login-remember-span').show();
                    $('#forgot-password-remember-span').show();
                }
            }
        });
    }

    var cookies = jQuery.parseJSON(readCookie('CookiePreferences'));

    my_role = $('#my-user-role').val();
    my_id = $('#my-user-id').val();

    //only save if analytics
    var resolution = "";
    if( cookies !== null && cookies.includes("analytics") ) {
        resolution = { resolution : screen.width + "x" + screen.height };
    }
    $.ajax({
        url : '/api/save-stats.php',
        data : resolution
    });

    $('#nav-search-icon').click(function() {
        searchBlog();
    });

    $('#nav-search-input').keypress(function(e) {
        if (e.which === 13) {
            searchBlog();
        }
    });

    $('#login-submit').click(function() {
        submitLogin();
    });

    $('.modal-body').keypress(function(e) {
        if (e.which === 13) {
            $("#login-submit").trigger("click");
        }
    });

    $('#logout-button').click(function() {
        logout();
    });

    $("#login-forgot-password").click(function() {
        forgotPassword();
    });

    $("#forgot-password-submit").click(function() {
        forgotPasswordSubmit();
    });

    $("#forgot-password-prev-code").click(function() {
        $('#forgot-password-modal .modal-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Enter your email address above, with your previous reset code and a new password below</div>");
        resetPasswordForm();
    });
    $("#forgot-password-reset-password").click(function() {
        forgotPasswordReset();
    });

    if (window.location.hash && window.location.hash === "#album") {
        findAlbum();
    }

    // hide remember me option if user declines to use preferences cookies
    if( cookies === null || !cookies.includes("preferences") ) {
        // hide all of the labels containing this
        $('#profile-remember-span').hide();
        $('#login-remember-span').hide();
        $('#forgot-password-remember-span').hide();
        // ensure everything is unchecked
        $("#profile-remember").prop("checked", false);
        $('#login-remember').prop("checked", false);
        $('#forgot-password-remember').prop("checked", false);
    }
});

window.onhashchange = function() {
    if (window.location.hash === "#album") {
        findAlbum();
    }
};

// Cookies
function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else var expires = "";

    document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}

function submitLogin() {
    $.post("/api/login.php", {
        username : $('#login-user').val(),
        password : md5($('#login-pass').val()),
        rememberMe : $('#login-remember').is(':checked') ? 1 : 0,
        submit : "Login"
    }).done(function(data) {
        if (data === "") {
            $('#login-modal .modal-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Successfully Logged In. Please wait as you are redirected.</div>");
            location.reload();
        } else {
            $('#login-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function(xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#login-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#login-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#login-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    });
}

function logout() {
    $.post("/api/login.php", {
        submit : "Logout"
    }).done(function() {
        if (window.location.pathname.lastIndexOf('/user/', 0) === 0) {
            window.location = "/";
        } else {
            location.reload();
        }
    });
}

function forgotPassword() {
    $('#login-modal').modal('hide');
    $('#forgot-password-instructions').show();
    $('#forgot-password-error').empty();
    $('#forgot-password-message').empty();
    $('#forgot-password-instructions').show();
    $('#forgot-password-submit').show();
    $('#forgot-password-code').hide();
    $('#forgot-password-new-password').hide();
    $('#forgot-password-new-password-confirm').hide();
    $('#forgot-password-remember-span').hide();
    $('#forgot-password-reset-password').hide();
    $('#forgot-password-modal').modal('show');
}

function forgotPasswordSubmit() {
    var button = $(this);
    button.prop("disabled", true);
    $.post("/api/send-reset-code.php", {
        email : $('#forgot-password-email').val(),
    }).done(function(data) {
        $('#forgot-password-error').html(data);
        if (data === "") {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Reset code has been sent, please enter it below, along with a new password</div>");
            resetPasswordForm();
            button.prop("disabled", false);
        } else {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function(xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    });
}

function forgotPasswordReset() {
    var button = $(this);
    button.prop("disabled", true);
    $.post("/api/reset-password.php", {
        email : $('#forgot-password-email').val(),
        code : $('#forgot-password-code').val(),
        password : $('#forgot-password-new-password').val(),
        passwordConfirm : $('#forgot-password-new-password-confirm').val(),
        rememberMe : $('#forgot-password-remember').is(':checked') ? 1 : 0,
    }).done(function(data) {
        button.prop("disabled", false);
        if (data === "") {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your password has been successfully reset. Logging you in now.</div>");
            setTimeout(function() {
                location.reload();
            }, 5000);
        } else {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function(xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#forgot-password-modal .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    });
}

function resetPasswordForm() {
    $('#forgot-password-instructions').hide();
    $('#forgot-password-submit').hide();
    $('#forgot-password-code').show();
    $('#forgot-password-new-password').show();
    $('#forgot-password-new-password-confirm').show();
    var cookies = jQuery.parseJSON(readCookie('CookiePreferences'));
    if( cookies !== null && cookies.includes("preferences") ) {
        $('#forgot-password-remember-span').show();
    }
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
                        window.location.href = '/user/album.php?album=' + data;
                    } else if (data === '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function(xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
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