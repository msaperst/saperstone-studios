var resultsSelected = false;
var allUser = {};
allUser.id = "0";
allUser.usr = "<i>All Users</i>";

function getAlbumId() {
    var albumId = $('#album-viewer').attr('album-id');
    if (!albumId && window.album) {
        albumId = window.album.albumId;
    }
    return albumId;
}

$(document).ready(function () {

    $('#edit-album-btn').click(function () {
        editAlbum(getAlbumId());
    });

    $('#delete-image-btn').click(function () {
        deleteImage()
    });
    $('#access-image-btn').click(function () {
        setupImageAccess();
    });

    $('#access-btn').click(function () {
        setupAlbumAccess();
    });

    $('#view-all-favorites-btn').click(function () {
        viewAllFavorites();
    });

    $('#favorites').on('hidden.bs.modal', function (e) {
        $('#view-all-favorites-btn').removeClass('disabled').prop("disabled", false);
    });

    $('#email-users').click(function () {
        $('#notifications').modal();
    });

    $('#notifications-send-btn').click(function () {
        sendNotifications();
    })
});

function deleteImage() {
    var activeCard = $('#album-grid .album-card.is-active');
    var img = activeCard.length ? activeCard : ((window.album && typeof window.album.getCurrentCard === "function") ? window.album.getCurrentCard() : $());

    BootstrapDialog.show({
        draggable: true,
        title: 'Are You Sure?',
        message: 'Are you sure you want to delete the image <b>' + img.attr('data-title') + '</b>',
        buttons: [{
            icon: 'glyphicon glyphicon-trash',
            label: ' Delete',
            cssClass: 'btn-danger',
            action: function (dialogInItself) {
                var $button = this;
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/delete-album-image.php", {
                    album: img.attr('album-id'),
                    image: img.attr('image-id')
                }).done(function () {
                    dialogInItself.close();

                    var imageId = img.attr('image-id');
                    var wasFavorite = img.attr('data-favorite') === '1';

                    // Sync active local caches before DOM extraction
                    if (window.album && window.album.images) {
                        // Remove from the internal lazy loader array tracking cache
                        var cacheIndex = -1;
                        $.each(window.album.images, function (i, item) {
                            if (String(item.sequence) === String(imageId)) {
                                cacheIndex = i;
                                return false;
                            }
                        });
                        if (cacheIndex !== -1) {
                            window.album.images.splice(cacheIndex, 1);
                        }
                    }

                    // Revert DOM elements cleanly
                    if (window.album && typeof window.album.removeImage === "function") {
                        window.album.removeImage(imageId);
                    } else {
                        img.remove();
                    }

                    // If the deleted image was a favorite, recalibrate the total counters
                    if (wasFavorite) {
                        var countElement = $('#favorite-count');
                        var currentCount = parseInt(countElement.text(), 10) || 0;
                        var newCount = Math.max(0, currentCount - 1);

                        // Use existing method to gracefully adjust strings/spacing
                        updateFavoriteCount(newCount);

                        // Disable favorite buttons if the user just cleared out their last favorite item
                        if (newCount <= 0) {
                            $("#downloadable-favorites-btn").prop("disabled", true);
                            $("#submit-favorites-btn").prop("disabled", true).hide();
                        }
                    }

                    // Recalibrate grid spans and trigger a quick evaluation check on the download rules
                    updateAllAlbumCardSpans();
                    if (window.album) {
                        // If they are looking at a filtered view, hide the empty card space immediately
                        if (window.album.showFavoritesOnly) { //[cite: 3]
                            window.album.applyFilter(); //[cite: 3]
                        } else {
                            window.album.loadImages(); //[cite: 3]
                        }
                    }

                    //check if ANY downloadable items remain on the screen.
                    // If zero match, disable the overall folder download action utility.
                    var hasDownloadableImages = $('#album-grid .album-card').filter(function () {
                        return $(this).find('.album-card-action[data-action="download"]').length > 0;
                    }).length > 0;

                    if (!hasDownloadableImages) {
                        $('#downloadable-all-btn').prop('disabled', true).addClass('disabled'); //[cite: 3]
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while deleting your image.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                });
            }
        }, {
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }]
    });
}

function setupImageAccess() {
    var activeCard = $('#album-grid .album-card.is-active');
    var img = activeCard.length ? activeCard : ((window.album && typeof window.album.getCurrentCard === "function") ? window.album.getCurrentCard() : $());

    BootstrapDialog.show({
        draggable: true,
        title: 'Who Do You Want To Give Access To For Image <b>' + img.attr('data-title') + '</b>?',
        message: function () {
            var inputs = $('<div>');
            var albumDiv = $('<div id="albumDiv">');
            albumDiv.append('<h4>Overall Album Access</h4>');
            var albumInput = $('<div class="open">');

            var searchAlbumInput = $('<input>');
            searchAlbumInput.attr('id', 'user-search');
            searchAlbumInput.attr('type', 'text');
            searchAlbumInput.addClass('form-control');
            searchAlbumInput.attr('placeholder', 'Enter User Name');
            searchAlbumInput.on("keyup focus", function () {
                var search_ele = $(this);
                var keyword = search_ele.val();
                $.get("/api/search-users.php", {keyword: keyword}, function (data) {
                    $('.search-results').remove();
                    var results_ul = $('<ul class="dropdown-menu search-results">');
                    $.each(data, function (key, user) {
                        results_ul.append(createUserBullet("album-users", user));
                    });
                    results_ul.hover(function () {
                        resultsSelected = true;
                    }, function () {
                        resultsSelected = false;
                    });
                    search_ele.after(results_ul);
                }, "json");
            });
            searchAlbumInput.focusout(function () {
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
            searchDownloadInput.on("keyup focus", function () {
                var search_ele = $(this);
                var keyword = search_ele.val();
                $.get("/api/search-album-users.php", {album: img.attr('album-id'), keyword: keyword}, function (data) {
                    $('.search-results').remove();
                    var results_ul = $('<ul class="dropdown-menu search-results">');
                    results_ul.append(createUserBullet("download-users", allUser));
                    $.each(data, function (key, user) {
                        results_ul.append(createUserBullet("download-users", user));
                    });
                    results_ul.hover(function () {
                        resultsSelected = true;
                    }, function () {
                        resultsSelected = false;
                    });
                    search_ele.after(results_ul);
                }, "json");
            });
            searchDownloadInput.focusout(function () {
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
            searchUploadInput.on("keyup focus", function () {
                var search_ele = $(this);
                var keyword = search_ele.val();
                $.get("/api/search-album-users.php", {album: img.attr('album-id'), keyword: keyword}, function (data) {
                    $('.search-results').remove();
                    var results_ul = $('<ul class="dropdown-menu search-results">');
                    results_ul.append(createUserBullet("share-users", allUser));
                    $.each(data, function (key, user) {
                        results_ul.append(createUserBullet("share-users", user));
                    });
                    results_ul.hover(function () {
                        resultsSelected = true;
                    }, function () {
                        resultsSelected = false;
                    });
                    search_ele.after(results_ul);
                }, "json");
            });
            searchUploadInput.focusout(function () {
                if (!resultsSelected) {
                    $('.search-results').remove();
                }
            });
            shareInput.append(searchUploadInput);
            shareDiv.append(shareInput);
            inputs.append(shareDiv);

            return inputs;
        },
        buttons: [{
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }],
        onshown: function () {
            var albumsDiv = $('<div>');
            albumsDiv.attr('id', 'album-users');
            albumsDiv.attr('url', 'update-album-users.php');
            albumsDiv.attr('image-id', img.attr('image-id'));
            albumsDiv.attr('album-id', img.attr('album-id'));
            albumsDiv.css({
                'padding': '10px 0 10px 0',
                'margin': '0 -5px 0 -5px'
            });
            $('#albumDiv').after(albumsDiv);
            $.get("/api/get-album-users.php", {
                album: getAlbumId(),
            }, function (album_users) {
                for (var i = 0, len = album_users.length; i < len; i++) {
                    addAlbumUser($('#album-users'), album_users[i].user, false);
                }
            }, "json");
            var downloadsDiv = $('<div>');
            downloadsDiv.attr('id', 'download-users');
            downloadsDiv.attr('url', 'update-image-downloaders.php');
            downloadsDiv.attr('image-id', img.attr('image-id'));
            downloadsDiv.attr('album-id', img.attr('album-id'));
            downloadsDiv.css({
                'padding': '10px 0 10px 0',
                'margin': '0 -5px 0 -5px'
            });
            $('#downloadDiv').after(downloadsDiv);
            $.get("/api/get-image-downloaders.php", {
                album: img.attr('album-id'),
                image: img.attr('image-id')
            }, function (album_users) {
                for (var i = 0, len = album_users.length; i < len; i++) {
                    addAlbumUser($('#download-users'), album_users[i].user, false);
                }
            }, "json");
            var sharesDiv = $('<div>');
            sharesDiv.attr('id', 'share-users');
            sharesDiv.attr('url', 'update-image-sharers.php');
            sharesDiv.attr('image-id', img.attr('image-id'));
            sharesDiv.attr('album-id', img.attr('album-id'));
            sharesDiv.css({
                'padding': '10px 0 10px 0',
                'margin': '0 -5px 0 -5px'
            });
            $('#shareDiv').after(sharesDiv);
            $.get("/api/get-image-sharers.php", {
                album: img.attr('album-id'),
                image: img.attr('image-id')
            }, function (album_users) {
                for (var i = 0, len = album_users.length; i < len; i++) {
                    addAlbumUser($('#share-users'), album_users[i].user, false);
                }
            }, "json");
        }
    });
}

function setupAlbumAccess() {
    BootstrapDialog.show({
        draggable: true,
        title: 'Who Do You Want To Give Access To For Album <b>' + $('#album-title').html() + '</b>?',
        message: function () {
            var inputs = $('<div>');
            var albumDiv = $('<div id="albumDiv">');
            albumDiv.append('<h4>Album Access</h4>');
            var albumInput = $('<div class="open">');

            var searchAlbumInput = $('<input>');
            searchAlbumInput.attr('id', 'user-search');
            searchAlbumInput.attr('type', 'text');
            searchAlbumInput.addClass('form-control');
            searchAlbumInput.attr('placeholder', 'Enter User Name');
            searchAlbumInput.on("keyup focus", function () {
                var search_ele = $(this);
                var keyword = search_ele.val();
                $.get("/api/search-users.php", {keyword: keyword}, function (data) {
                    $('.search-results').remove();
                    var results_ul = $('<ul class="dropdown-menu search-results">');
                    $.each(data, function (key, user) {
                        results_ul.append(createUserBullet("album-users", user));
                    });
                    results_ul.hover(function () {
                        resultsSelected = true;
                    }, function () {
                        resultsSelected = false;
                    });
                    search_ele.after(results_ul);
                }, "json");
            });
            searchAlbumInput.focusout(function () {
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
            searchDownloadInput.on("keyup focus", function () {
                var search_ele = $(this);
                var keyword = search_ele.val();
                $.get("/api/search-album-users.php", {album: getAlbumId(), keyword: keyword}, function (data) {
                    $('.search-results').remove();
                    var results_ul = $('<ul class="dropdown-menu search-results">');
                    results_ul.append(createUserBullet("download-users", allUser));
                    $.each(data, function (key, user) {
                        results_ul.append(createUserBullet("download-users", user));
                    });
                    results_ul.hover(function () {
                        resultsSelected = true;
                    }, function () {
                        resultsSelected = false;
                    });
                    search_ele.after(results_ul);
                }, "json");
            });
            searchDownloadInput.focusout(function () {
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
            searchUploadInput.on("keyup focus", function () {
                var search_ele = $(this);
                var keyword = search_ele.val();
                $.get("/api/search-album-users.php", {album: getAlbumId(), keyword: keyword}, function (data) {
                    $('.search-results').remove();
                    var results_ul = $('<ul class="dropdown-menu search-results">');
                    results_ul.append(createUserBullet("share-users", allUser));
                    $.each(data, function (key, user) {
                        results_ul.append(createUserBullet("share-users", user));
                    });
                    results_ul.hover(function () {
                        resultsSelected = true;
                    }, function () {
                        resultsSelected = false;
                    });
                    search_ele.after(results_ul);
                }, "json");
            });
            searchUploadInput.focusout(function () {
                if (!resultsSelected) {
                    $('.search-results').remove();
                }
            });
            shareInput.append(searchUploadInput);
            shareDiv.append(shareInput);
            inputs.append(shareDiv);

            return inputs;
        },
        buttons: [{
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }],
        onshown: function () {
            var albumsDiv = $('<div>');
            albumsDiv.attr('id', 'album-users');
            albumsDiv.attr('url', 'update-album-users.php');
            albumsDiv.attr('image-id', "*");
            albumsDiv.attr('album-id', getAlbumId());
            albumsDiv.css({
                'padding': '10px 0 10px 0',
                'margin': '0 -5px 0 -5px'
            });
            $('#albumDiv').after(albumsDiv);
            $.get("/api/get-album-users.php", {
                album: getAlbumId(),
            }, function (album_users) {
                for (var i = 0, len = album_users.length; i < len; i++) {
                    addAlbumUser($('#album-users'), album_users[i].user, false);
                }
            }, "json");
            var downloadsDiv = $('<div>');
            downloadsDiv.attr('id', 'download-users');
            downloadsDiv.attr('url', 'update-image-downloaders.php');
            downloadsDiv.attr('image-id', "*");
            downloadsDiv.attr('album-id', getAlbumId());
            downloadsDiv.css({
                'padding': '10px 0 10px 0',
                'margin': '0 -5px 0 -5px'
            });
            $('#downloadDiv').after(downloadsDiv);
            $.get("/api/get-image-downloaders.php", {album: getAlbumId(), image: "*"}, function (album_users) {
                for (var i = 0, len = album_users.length; i < len; i++) {
                    addAlbumUser($('#download-users'), album_users[i].user, false);
                }
            }, "json");
            var sharesDiv = $('<div>');
            sharesDiv.attr('id', 'share-users');
            sharesDiv.attr('url', 'update-image-sharers.php');
            sharesDiv.attr('image-id', "*");
            sharesDiv.attr('album-id', getAlbumId());
            sharesDiv.css({
                'padding': '10px 0 10px 0',
                'margin': '0 -5px 0 -5px'
            });
            $('#shareDiv').after(sharesDiv);
            $.get("/api/get-image-sharers.php", {album: getAlbumId(), image: "*"}, function (album_users) {
                for (var i = 0, len = album_users.length; i < len; i++) {
                    addAlbumUser($('#share-users'), album_users[i].user, false);
                }
            }, "json");
        }
    });
}

function viewAllFavorites() {
    $('#favorites').modal();
    $('#view-all-favorites-btn').addClass('disabled').prop("disabled", true);
    $('#view-all-favorites-btn em').addClass('fa-spinner fa-spin').removeClass('fa-search');

    $.get("/api/get-all-favorites.php", {
        album: getAlbumId()
    }, function (favorites) {
        $('#view-all-favorites-btn em').removeClass('fa-spinner fa-spin').addClass('fa-search');

        // Target the modal-body and clean it out
        var modalBody = $('#favorites .modal-body.all').empty();

        // Create our modern grid container
        var grid = $('<div class="favorites-grid">');

        $.each(favorites, function (user, favs) {
            // Skip empty favorites lists to keep it clean
            if (!favs || favs.length === 0) return;

            // Determine if the key looks like an IP address
            var isIP = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/.test(user);

            // Format Display Name
            var displayName = user;
            if (favs[0].usr !== null) {
                displayName = favs[0].usr;
                isIP = false; // Mark as registered if an actual username is attached
            }

            // Build the card container
            var card = $('<div class="fav-user-card">');

            // Meta Section (Icon + Name)
            var metaSection = $('<div class="user-meta">');
            var iconClass = isIP ? 'fa-laptop' : 'fa-user';
            var iconWrapper = $('<div class="user-icon-wrapper">').append($('<em>').addClass('fa ' + iconClass));
            var nameSpan = $('<div class="user-name">').text(displayName);

            metaSection.append(iconWrapper).append(nameSpan);

            // Badge Section (Favorite Count Indicator)
            var badgeText = favs.length + ' Item' + (favs.length === 1 ? '' : 's');
            var favoriteBadge = $('<div class="favorite-badge">')
                .append($('<em>').addClass('fa fa-heart'))
                .append($('<span>').text(badgeText));

            card.append(metaSection).append(favoriteBadge);

            // Re-apply our slick filter logic on click
            card.click(function (e) {
                e.preventDefault();
                $('#favorites').modal('hide');

                if (window.album) {
                    // Back up original user favorites if not already backed up
                    if (!window.album.originalFavoritesBackup) {
                        window.album.originalFavoritesBackup = {};
                        $('#album-grid .album-card').each(function () {
                            var cardElement = $(this);
                            window.album.originalFavoritesBackup[cardElement.attr('data-image-id')] = cardElement.attr('data-favorite');
                        });
                    }

                    window.album.viewingAdminUserFavorites = displayName;

                    var favIds = favs.map(function (f) {
                        return String(f.sequence);
                    });

                    // Overwrite main layout card favorite flags temporarily
                    $('#album-grid .album-card').each(function () {
                        var cardElement = $(this);
                        var imgId = String(cardElement.attr('data-image-id'));
                        if (favIds.indexOf(imgId) !== -1) {
                            cardElement.attr('data-favorite', '1');
                            cardElement.addClass('is-favorite');
                        } else {
                            cardElement.attr('data-favorite', '0');
                            cardElement.removeClass('is-favorite');
                        }
                    });

                    window.album.showFavoritesOnly = true;
                    window.album.applyFilter();
                }
            });

            grid.append(card);
        });

        // If no user favorites exist in this album, show an empty state
        if (grid.children().length === 0) {
            grid = $('<div class="text-center text-muted" style="padding: 40px 0;">')
                .append($('<em class="fa fa-heart-o" style="font-size: 40px; margin-bottom: 15px; display: block;"></em>'))
                .append($('<p>').text('No users have added favorites to this album yet.'));
        }

        modalBody.append(grid);
    }, "json");
}

function addAlbumUser(ele, user_id, update) {
    var userSpan = $('<span>');
    userSpan.addClass('selected-user');
    userSpan.attr('user-id', user_id);
    userSpan.click(function () {
        $(this).remove();
        var users = [];
        $('.selected-user', $(ele)).each(function () {
            users.push($(this).attr('user-id'));
        });
        // send our update
        $.post("/api/" + ele.attr('url'), {
            album: ele.attr('album-id'),
            image: ele.attr('image-id'),
            users: users
        });
    });
    $.get("/api/get-user.php", {
        id: user_id
    }, function (data) {
        userSpan.html(data.usr);
        $(ele).append(userSpan);

        if (update) {
            var users = [];
            $('.selected-user', $(ele)).each(function () {
                users.push($(this).attr('user-id'));
            });
            // send our update
            $.post("/api/" + ele.attr('url'), {
                album: ele.attr('album-id'),
                image: ele.attr('image-id'),
                users: users
            });
        }
    }, "json");
}

function createUserBullet(element, user) {
    if (!($("#" + element + " .selected-user[user-id='" + user.id + "']").length || user.role === "admin")) {
        var result_li = $('<li>');
        var result_a = $('<a user-id="' + user.id + '" >' + user.usr + '</a>');
        result_a.click(function () {
            addAlbumUser($('#' + element), user.id, true);
            $('.search-results').remove();
        });
        return result_li.append(result_a);
    }
    return null;
}

function sendNotifications() {
    $("#notifications-send-btn").prop("disabled", true);
    $("#notifications-send-btn").next().prop("disabled", true);
    $("#notifications-send-btn em").removeClass('fa fa-paper-plane').addClass('glyphicon glyphicon-asterisk icon-spin');
    $.post("/api/send-notification-email.php", {
        album: $('#notifications').attr('album-id'),
        message: $('#notifications-message').val()
    }).done(function (data) {
        if (data !== "") {
            $("#notifications .modal-body").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data + '</div>');
        } else {
            $("#notifications").modal('hide');
            $('#email-list').prev().remove();
            $('#email-list').next().remove();
            $('#email-list').next().remove();
            $('#email-list').remove();
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#notifications .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#notifications .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $("#notifications .modal-body").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
        }
    }).always(function () {
        $('#notifications-send-btn').prop("disabled", false);
        $('#notifications-send-btn').next().prop("disabled", false);
        $('#notifications-send-btn em').addClass('fa fa-paper-plane').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}
