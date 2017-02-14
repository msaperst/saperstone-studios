var resultsSelected = false;

$(document).ready(function() {

    $('#edit-album-btn').click(function() {
        editAlbum($('#album').attr('album-id'));
    });

    $('#delete-image-btn').click(function() {
        var img = $('#album-carousel div.active div');
        BootstrapDialog.show({
            draggable : true,
            title : 'Are You Sure?',
            message : 'Are you sure you want to delete the image <b>' + img.attr('alt') + '</b>',
            buttons : [ {
                icon : 'glyphicon glyphicon-trash',
                label : ' Delete',
                cssClass : 'btn-danger',
                action : function(dialogInItself) {
                    var $button = this; // 'this' here is a jQuery object that
                                        // wrapping the <button> DOM element.
                    var modal = $button.closest('.modal-content');
                    $button.spin();
                    dialogInItself.enableButtons(false);
                    dialogInItself.setClosable(false);
                    // send our update
                    $.post("/api/delete-album-image.php", {
                        album : img.attr('album-id'),
                        image : img.attr('image-id')
                    }).done(function() {
                        dialogInItself.close();
                        // go to the next image
                        $('#album-carousel').carousel("next");
                        // cleanup the dom
                        $('.gallery img[alt="' + img.attr('alt') + '"]').parent().remove();
                        img.parent().remove();
                    }).fail(function(){
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while deleting your image.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    });
                }
            }, {
                label : 'Close',
                action : function(dialogInItself) {
                    dialogInItself.close();
                }
            } ]
        });
    });
    $('#access-image-btn').click(function() {
        var img = $('#album-carousel div.active div');
        BootstrapDialog.show({
            draggable : true,
            title : 'Who Do You Want To Give Access To For Image <b>' + img.attr('alt') + '</b>?',
            message : function() {
                var inputs = $('<div>');
                var albumDiv = $('<div id="albumDiv">');
                albumDiv.append('<h4>Overall Album Access</h4>');
                var albumInput = $('<div class="open">');

                var searchAlbumInput = $('<input>');
                searchAlbumInput.attr('id', 'user-search');
                searchAlbumInput.attr('type', 'text');
                searchAlbumInput.addClass('form-control');
                searchAlbumInput.attr('placeholder', 'Enter User Name');
                searchAlbumInput.on("keyup focus", function() {
                    var search_ele = $(this);
                    var keyword = search_ele.val();
                    $.get("/api/search-users.php", {
                        keyword : keyword
                    }, function(data) {
                        $('.search-results').remove();
                        var results_ul = $('<ul class="dropdown-menu search-results">');
                        $.each(data, function(key, user) {
                            if (!($("#album-users .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
                                var result_li = $('<li>');
                                var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                result_a.click(function() {
                                    addUser($('#album-users'), user.id);
                                    $('.search-results').remove();
                                });
                                results_ul.append(result_li.append(result_a));
                            }
                        });
                        results_ul.hover(function() {
                            resultsSelected = true;
                        }, function() {
                            resultsSelected = false;
                        });
                        search_ele.after(results_ul);
                    }, "json");
                });
                searchAlbumInput.focusout(function() {
                    if (!resultsSelected) {
                        $('.search-results').remove();
                    }
                });
                albumInput.append(searchAlbumInput);
                albumDiv.append(albumInput);
                inputs.append(albumDiv);

                var downloadDiv = $('<div id="downloadDiv">');
                downloadDiv.append('<h4>Download Access</h4>');
                var downloadInput = $('<div class="open">');

                var searchDownloadInput = $('<input>');
                searchDownloadInput.attr('id', 'user-search');
                searchDownloadInput.attr('type', 'text');
                searchDownloadInput.addClass('form-control');
                searchDownloadInput.attr('placeholder', 'Enter User Name');
                searchDownloadInput.on("keyup focus", function() {
                    var search_ele = $(this);
                    var keyword = search_ele.val();
                    $.get("/api/search-album-users.php", {
                        album : img.attr('album-id'),
                        keyword : keyword
                    }, function(data) {
                        $('.search-results').remove();
                        var results_ul = $('<ul class="dropdown-menu search-results">');
                        $.each(data, function(key, user) {
                            if (!($("#download-users .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
                                var result_li = $('<li>');
                                var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                result_a.click(function() {
                                    addUser($('#download-users'), user.id);
                                    $('.search-results').remove();
                                });
                                results_ul.append(result_li.append(result_a));
                            }
                        });
                        results_ul.hover(function() {
                            resultsSelected = true;
                        }, function() {
                            resultsSelected = false;
                        });
                        search_ele.after(results_ul);
                    }, "json");
                });
                searchDownloadInput.focusout(function() {
                    if (!resultsSelected) {
                        $('.search-results').remove();
                    }
                });
                downloadInput.append(searchDownloadInput);
                downloadDiv.append(downloadInput);
                inputs.append(downloadDiv);

                var shareDiv = $('<div id="shareDiv">');
                shareDiv.append('<h4>Share Access</h4>');
                var shareInput = $('<div class="open">');

                var searchUploadInput = $('<input>');
                searchUploadInput.attr('id', 'user-search');
                searchUploadInput.attr('type', 'text');
                searchUploadInput.addClass('form-control');
                searchUploadInput.attr('placeholder', 'Enter User Name');
                searchUploadInput.on("keyup focus", function() {
                    var search_ele = $(this);
                    var keyword = search_ele.val();
                    $.get("/api/search-album-users.php", {
                        album : img.attr('album-id'),
                        keyword : keyword
                    }, function(data) {
                        $('.search-results').remove();
                        var results_ul = $('<ul class="dropdown-menu search-results">');
                        $.each(data, function(key, user) {
                            if (!($("#share-users .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
                                var result_li = $('<li>');
                                var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                result_a.click(function() {
                                    addUser($('#share-users'), user.id);
                                    $('.search-results').remove();
                                });
                                results_ul.append(result_li.append(result_a));
                            }
                        });
                        results_ul.hover(function() {
                            resultsSelected = true;
                        }, function() {
                            resultsSelected = false;
                        });
                        search_ele.after(results_ul);
                    }, "json");
                });
                searchUploadInput.focusout(function() {
                    if (!resultsSelected) {
                        $('.search-results').remove();
                    }
                });
                shareInput.append(searchUploadInput);
                shareDiv.append(shareInput);
                inputs.append(shareDiv);

                return inputs;
            },
            buttons : [ {
                label : 'Close',
                action : function(dialogInItself) {
                    dialogInItself.close();
                }
            } ],
            onshown : function() {
                var albumsDiv = $('<div>');
                albumsDiv.attr('id', 'album-users');
                albumsDiv.attr('url', 'update-album-users.php');
                albumsDiv.attr('image-id', img.attr('image-id'));
                albumsDiv.attr('album-id', img.attr('album-id'));
                albumsDiv.css({
                    'padding' : '10px 0 10px 0',
                    'margin' : '0 -5px 0 -5px'
                });
                $('#albumDiv').after(albumsDiv);
                $.get("/api/get-album-users.php", {
                    album : $('#album').attr('album-id'),
                }, function(album_users) {
                    for (var i = 0, len = album_users.length; i < len; i++) {
                        addUser($('#album-users'), album_users[i].user);
                    }
                }, "json");
                var downloadsDiv = $('<div>');
                downloadsDiv.attr('id', 'download-users');
                downloadsDiv.attr('url', 'update-image-downloaders.php');
                downloadsDiv.attr('image-id', img.attr('image-id'));
                downloadsDiv.attr('album-id', img.attr('album-id'));
                downloadsDiv.css({
                    'padding' : '10px 0 10px 0',
                    'margin' : '0 -5px 0 -5px'
                });
                $('#downloadDiv').after(downloadsDiv);
                $.get("/api/get-image-downloaders.php", {
                    album : img.attr('album-id'),
                    image : img.attr('image-id')
                }, function(album_users) {
                    for (var i = 0, len = album_users.length; i < len; i++) {
                        addUser($('#download-users'), album_users[i].user);
                    }
                }, "json");
                var sharesDiv = $('<div>');
                sharesDiv.attr('id', 'share-users');
                sharesDiv.attr('url', 'update-image-sharers.php');
                sharesDiv.attr('image-id', img.attr('image-id'));
                sharesDiv.attr('album-id', img.attr('album-id'));
                sharesDiv.css({
                    'padding' : '10px 0 10px 0',
                    'margin' : '0 -5px 0 -5px'
                });
                $('#shareDiv').after(sharesDiv);
                $.get("/api/get-image-sharers.php", {
                    album : img.attr('album-id'),
                    image : img.attr('image-id')
                }, function(album_users) {
                    for (var i = 0, len = album_users.length; i < len; i++) {
                        addUser($('#share-users'), album_users[i].user);
                    }
                }, "json");
            }
        });
    });

    $('#access-btn').click(function() {
        BootstrapDialog.show({
            draggable : true,
            title : 'Who Do You Want To Give Access To For Album <b>' + $('#album-title').html() + '</b>?',
            message : function() {
                var inputs = $('<div>');
                var albumDiv = $('<div id="albumDiv">');
                albumDiv.append('<h4>Album Access</h4>');
                var albumInput = $('<div class="open">');

                var searchAlbumInput = $('<input>');
                searchAlbumInput.attr('id', 'user-search');
                searchAlbumInput.attr('type', 'text');
                searchAlbumInput.addClass('form-control');
                searchAlbumInput.attr('placeholder', 'Enter User Name');
                searchAlbumInput.on("keyup focus", function() {
                    var search_ele = $(this);
                    var keyword = search_ele.val();
                    $.get("/api/search-users.php", {
                        keyword : keyword
                    }, function(data) {
                        $('.search-results').remove();
                        var results_ul = $('<ul class="dropdown-menu search-results">');
                        $.each(data, function(key, user) {
                            if (!($("#album-users .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
                                var result_li = $('<li>');
                                var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                result_a.click(function() {
                                    addUser($('#album-users'), user.id);
                                    $('.search-results').remove();
                                });
                                results_ul.append(result_li.append(result_a));
                            }
                        });
                        results_ul.hover(function() {
                            resultsSelected = true;
                        }, function() {
                            resultsSelected = false;
                        });
                        search_ele.after(results_ul);
                    }, "json");
                });
                searchAlbumInput.focusout(function() {
                    if (!resultsSelected) {
                        $('.search-results').remove();
                    }
                });
                albumInput.append(searchAlbumInput);
                albumDiv.append(albumInput);
                inputs.append(albumDiv);

                var downloadDiv = $('<div id="downloadDiv">');
                downloadDiv.append('<h4>Download Access</h4>');
                var downloadInput = $('<div class="open">');

                var searchDownloadInput = $('<input>');
                searchDownloadInput.attr('id', 'user-search');
                searchDownloadInput.attr('type', 'text');
                searchDownloadInput.addClass('form-control');
                searchDownloadInput.attr('placeholder', 'Enter User Name');
                searchDownloadInput.on("keyup focus", function() {
                    var search_ele = $(this);
                    var keyword = search_ele.val();
                    $.get("/api/search-album-users.php", {
                        album : $('#album').attr('album-id'),
                        keyword : keyword
                    }, function(data) {
                        $('.search-results').remove();
                        var results_ul = $('<ul class="dropdown-menu search-results">');
                        $.each(data, function(key, user) {
                            if (!($("#download-users .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
                                var result_li = $('<li>');
                                var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                result_a.click(function() {
                                    addUser($('#download-users'), user.id);
                                    $('.search-results').remove();
                                });
                                results_ul.append(result_li.append(result_a));
                            }
                        });
                        results_ul.hover(function() {
                            resultsSelected = true;
                        }, function() {
                            resultsSelected = false;
                        });
                        search_ele.after(results_ul);
                    }, "json");
                });
                searchDownloadInput.focusout(function() {
                    if (!resultsSelected) {
                        $('.search-results').remove();
                    }
                });
                downloadInput.append(searchDownloadInput);
                downloadDiv.append(downloadInput);
                inputs.append(downloadDiv);

                var shareDiv = $('<div id="shareDiv">');
                shareDiv.append('<h4>Share Access</h4>');
                var shareInput = $('<div class="open">');

                var searchUploadInput = $('<input>');
                searchUploadInput.attr('id', 'user-search');
                searchUploadInput.attr('type', 'text');
                searchUploadInput.addClass('form-control');
                searchUploadInput.attr('placeholder', 'Enter User Name');
                searchUploadInput.on("keyup focus", function() {
                    var search_ele = $(this);
                    var keyword = search_ele.val();
                    $.get("/api/search-album-users.php", {
                        album : $('#album').attr('album-id'),
                        keyword : keyword
                    }, function(data) {
                        $('.search-results').remove();
                        var results_ul = $('<ul class="dropdown-menu search-results">');
                        $.each(data, function(key, user) {
                            if (!($("#share-users .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
                                var result_li = $('<li>');
                                var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
                                result_a.click(function() {
                                    addUser($('#share-users'), user.id);
                                    $('.search-results').remove();
                                });
                                results_ul.append(result_li.append(result_a));
                            }
                        });
                        results_ul.hover(function() {
                            resultsSelected = true;
                        }, function() {
                            resultsSelected = false;
                        });
                        search_ele.after(results_ul);
                    }, "json");
                });
                searchUploadInput.focusout(function() {
                    if (!resultsSelected) {
                        $('.search-results').remove();
                    }
                });
                shareInput.append(searchUploadInput);
                shareDiv.append(shareInput);
                inputs.append(shareDiv);

                return inputs;
            },
            buttons : [ {
                label : 'Close',
                action : function(dialogInItself) {
                    dialogInItself.close();
                }
            } ],
            onshown : function() {
                var albumsDiv = $('<div>');
                albumsDiv.attr('id', 'album-users');
                albumsDiv.attr('url', 'update-album-users.php');
                albumsDiv.attr('image-id', "*");
                albumsDiv.attr('album-id', $('#album').attr('album-id'));
                albumsDiv.css({
                    'padding' : '10px 0 10px 0',
                    'margin' : '0 -5px 0 -5px'
                });
                $('#albumDiv').after(albumsDiv);
                $.get("/api/get-album-users.php", {
                    album : $('#album').attr('album-id'),
                }, function(album_users) {
                    for (var i = 0, len = album_users.length; i < len; i++) {
                        addUser($('#album-users'), album_users[i].user);
                    }
                }, "json");
                var downloadsDiv = $('<div>');
                downloadsDiv.attr('id', 'download-users');
                downloadsDiv.attr('url', 'update-image-downloaders.php');
                downloadsDiv.attr('image-id', "*");
                downloadsDiv.attr('album-id', $('#album').attr('album-id'));
                downloadsDiv.css({
                    'padding' : '10px 0 10px 0',
                    'margin' : '0 -5px 0 -5px'
                });
                $('#downloadDiv').after(downloadsDiv);
                $.get("/api/get-image-downloaders.php", {
                    album : $('#album').attr('album-id'),
                    image : "*"
                }, function(album_users) {
                    for (var i = 0, len = album_users.length; i < len; i++) {
                        addUser($('#download-users'), album_users[i].user);
                    }
                }, "json");
                var sharesDiv = $('<div>');
                sharesDiv.attr('id', 'share-users');
                sharesDiv.attr('url', 'update-image-sharers.php');
                sharesDiv.attr('image-id', "*");
                sharesDiv.attr('album-id', $('#album').attr('album-id'));
                sharesDiv.css({
                    'padding' : '10px 0 10px 0',
                    'margin' : '0 -5px 0 -5px'
                });
                $('#shareDiv').after(sharesDiv);
                $.get("/api/get-image-sharers.php", {
                    album : $('#album').attr('album-id'),
                    image : "*"
                }, function(album_users) {
                    for (var i = 0, len = album_users.length; i < len; i++) {
                        addUser($('#share-users'), album_users[i].user);
                    }
                }, "json");
            }
        });
    });
});

function addUser(ele, user_id) {
    var userSpan = $('<span>');
    userSpan.addClass('selected-user');
    userSpan.attr('user-id', user_id);
    userSpan.click(function() {
        $(this).remove();
        var users = [];
        $('.selected-user', $(ele)).each(function() {
            users.push($(this).attr('user-id'));
        });
        // send our update
        $.post("/api/" + ele.attr('url'), {
            album : ele.attr('album-id'),
            image : ele.attr('image-id'),
            users : users
        });
    });
    $.get("/api/get-user.php", {
        id : user_id
    }, function(data) {
        userSpan.html(data.usr);
        $(ele).append(userSpan);

        var users = [];
        $('.selected-user', $(ele)).each(function() {
            users.push($(this).attr('user-id'));
        });
        // send our update
        $.post("/api/" + ele.attr('url'), {
            album : ele.attr('album-id'),
            image : ele.attr('image-id'),
            users : users
        });
    }, "json");

}
