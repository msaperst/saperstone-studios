$.fn.isOnScreen = function () {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function getCurrentAlbumCard() {
    if (window.album && typeof window.album.getCurrentCard === "function") {
        return window.album.getCurrentCard();
    }
    return $('#album-grid .album-card.is-active');
}

function getCurrentAlbumImage() {
    return getCurrentAlbumCard();
}

function getCurrentAlbumImageId() {
    return getCurrentAlbumImage().attr('image-id');
}

function getAlbumImageCard(imageId) {
    return $('#album-grid .album-card[data-image-id="' + imageId + '"]');
}

function toggleFavoriteForImage(imageId) {
    var card = getAlbumImageCard(imageId);
    if (!card.length) {
        return;
    }
    var isFavorite = card.attr('data-favorite') === '1';
    var request = isFavorite ? $.post("/api/unset-favorite.php", {
        album: card.attr('album-id'),
        image: card.attr('image-id')
    }) : $.post("/api/set-favorite.php", {
        album: card.attr('album-id'),
        image: card.attr('image-id')
    });

    request.done(function (count) {
        updateFavoriteCount(count);
        card.attr('data-favorite', isFavorite ? '0' : '1');
        card.toggleClass('is-favorite', !isFavorite);
        card.find('.album-card-action[data-action="favorite"] em').removeClass('fa-heart error').addClass(!isFavorite ? 'fa-heart error' : 'fa-heart');
        if (String(getCurrentAlbumImageId()) === String(imageId)) {
            if (!isFavorite) {
                setFavorite();
            } else {
                unsetFavorite();
            }
        }

        if (window.album && window.album.showFavoritesOnly) {
            window.album.applyFilter();
        }
    });
}

function downloadImageFor(imageId) {
    var card = getAlbumImageCard(imageId);
    if (!card.length) {
        return;
    }
    $.get("/api/is-downloadable.php", {
        album: card.attr('album-id'),
        image: card.attr('image-id')
    }).done(function (data) {
        if (data === '1') {
            downloadImages(card.attr('album-id'), card.attr('image-id'));
        }
    });
}

function submitImageFor(imageId) {
    var card = getAlbumImageCard(imageId);
    if (!card.length) {
        return;
    }
    $('#submit').attr('what', card.attr('image-id')).modal();
}

function createIconButton(action, icon, title, handler, extraClass) {
    var button = $('<button>');
    button.attr('type', 'button');
    button.attr('data-action', action);
    button.attr('title', title);
    button.attr('aria-label', title);
    button.addClass('btn btn-default album-card-action');
    if (extraClass) {
        button.addClass(extraClass);
    }
    button.append($('<em>').addClass('fa ' + icon));
    button.click(function (event) {
        event.preventDefault();
        event.stopPropagation();
        handler.call(this, event);
    });
    return button;
}

function updateViewerMeta(image) {
    if (!image || image.length === 0) {
        $('#album-viewer-image').attr('src', '/img/image.png').attr('alt', '');
        $('#album-viewer-title').text('');
        $('#album-viewer-caption').text('');
        $('#album-viewer-overlay').removeAttr('image-id');
        return;
    }
    $('#album-viewer-overlay').attr('album-id', image.attr('album-id'));
    $('#album-viewer-overlay').attr('image-id', image.attr('image-id'));
    $('#album-viewer-image').attr('style', 'background-image: url("' + image.attr('data-location') || image.attr('data-src') + '")');
    $('#album-viewer-image').attr('src', '/img/image.png');
    if (window.showImageTitle) {
        $('#album-viewer-title').text(image.attr('data-title') || image.attr('title') || '');
    }
    $('#album-viewer-caption').text(image.attr('data-caption') || '');
}

function updateFavoriteCount(count) {
    var total = parseInt(count, 10);
    if (isNaN(total) || total < 0) {
        total = 0;
    }
    if (total > 0) {
        $('#favorite-count').html(total).css({
            'padding-left': '10px'
        });
    } else {
        $('#favorite-count').html("").css({
            'padding-left': ''
        });
    }
}

function updateAlbumCardSpan(card) {
    if (!card || !card.length) {
        return;
    }
    var element = card.get(0);
    if (!element || typeof element.getBoundingClientRect !== "function") {
        return;
    }
    var rowHeight = parseFloat($('#album-grid').css('grid-auto-rows')) || 10;
    var rowGap = parseFloat($('#album-grid').css('gap')) || parseFloat($('#album-grid').css('grid-row-gap')) || 12;
    var height = parseFloat(card.attr('data-height')) || 1;
    var width = parseFloat(card.attr('data-width')) || 1;
    var cardWidth = element.getBoundingClientRect().width;
    if (!cardWidth) {
        return;
    }
    var renderedHeight = cardWidth * (height / width);
    var span = Math.round((renderedHeight + rowGap) / (rowHeight + rowGap));
    card.css({
        'height': renderedHeight + 'px',
        'grid-row-end': 'span ' + span
    });
}

function updateAllAlbumCardSpans() {
    $('#album-grid .album-card').each(function () {
        updateAlbumCardSpan($(this));
    });
}

function markAlbumCardLoaded(card) {
    if (!card || !card.length) {
        return;
    }
    card.attr('data-loading', '0');
    card.attr('data-loaded', '1');
    card.addClass('is-loaded');
}

function openViewer() {
    $('#album-viewer-overlay').removeClass('hidden').attr('aria-hidden', 'false');
    $('body').addClass('album-viewer-open');
}

function closeViewer() {
    $('#album-viewer-overlay').addClass('hidden').attr('aria-hidden', 'true');
    $('body').removeClass('album-viewer-open');
}

function Album(albumId, columns, totalImages) {
    var Album = this;

    Album.loaded = 0;
    Album.loading = false;
    Album.albumId = albumId;
    Album.columns = columns;
    Album.totalImages = totalImages;
    Album.images = [];
    Album.currentImageId = null;
    Album.pendingImageId = null;
    Album.initialized = false;
    Album.showFavoritesOnly = false;

    Album.loadImages();

    if (window.location.hash && window.location.hash.length > 1) {
        Album.setImage(window.location.hash.substr(1), {
            scroll: false,
            updateHash: false,
            force: true
        });
    }

    window.onhashchange = function () {
        if (window.location.hash.length > 1) {
            Album.setImage(window.location.hash.substr(1), {
                scroll: false,
                updateHash: false,
                force: true
            });
        } else {
            Album.currentImageId = null;
            updateViewerMeta($());
            closeViewer();
        }
    };
}

Album.prototype.getCurrentCard = function () {
    if (this.currentImageId === null) {
        return $();
    }
    return $('#album-grid .album-card[data-image-id="' + this.currentImageId + '"]');
};

Album.prototype.getImageCard = function (img) {
    return $('#album-grid .album-card[data-image-id="' + img + '"]');
};

Album.prototype.selectImage = function (img, options) {
    var Album = this;
    options = options || {};
    var card = Album.getImageCard(img);
    if (!card.length) {
        Album.pendingImageId = img;
        return false;
    }
    if (!options.force && String(Album.currentImageId) === String(img)) {
        updateViewerMeta(card);
        if (card.attr('data-favorite') === '1') {
            setFavorite();
        } else {
            unsetFavorite();
        }
        openViewer();
        return true;
    }
    Album.currentImageId = img;
    Album.pendingImageId = null;
    $('#album-grid .album-card').removeClass('is-active');
    card.addClass('is-active');
    updateViewerMeta(card);
    if (card.attr('data-favorite') === '1') {
        setFavorite();
    } else {
        unsetFavorite();
    }
    openViewer();
    if (options.updateHash !== false) {
        var currentHash = window.location.hash.replace('#', '');
        if (String(currentHash) !== String(img)) {
            history.replaceState("", document.title, window.location.pathname + window.location.search + '#' + img);
        }
    }
    return true;
};

Album.prototype.setImage = function (img, options) {
    return this.selectImage(img, options);
};

Album.prototype.prev = function () {
    var Album = this;
    if (Album.currentImageId === null) {
        return;
    }
    var index = -1;
    $.each(Album.images, function (i, image) {
        if (String(image.sequence) === String(Album.currentImageId)) {
            index = i;
            return false;
        }
    });
    if (index === -1) {
        return;
    }
    index--;
    if (index < 0) {
        index = Album.images.length - 1;
    }
    if (Album.images[index]) {
        Album.selectImage(Album.images[index].sequence, {
            scroll: true,
            updateHash: true
        });
    }
};

Album.prototype.next = function () {
    var Album = this;
    if (Album.currentImageId === null) {
        return;
    }
    var index = -1;
    $.each(Album.images, function (i, image) {
        if (String(image.sequence) === String(Album.currentImageId)) {
            index = i;
            return false;
        }
    });
    if (index === -1) {
        return;
    }
    index++;
    if (index >= Album.images.length) {
        index = 0;
    }
    if (Album.images[index]) {
        Album.selectImage(Album.images[index].sequence, {
            scroll: true,
            updateHash: true
        });
    }
};

Album.prototype.removeImage = function (img) {
    var Album = this;
    var index = -1;
    $.each(Album.images, function (i, image) {
        if (String(image.sequence) === String(img)) {
            index = i;
            return false;
        }
    });
    $('#album-grid .album-card[data-image-id="' + img + '"]').remove();
    if (index !== -1) {
        Album.images.splice(index, 1);
    }
    Album.totalImages = Math.max(0, Album.totalImages - 1);
    if (String(Album.currentImageId) === String(img)) {
        Album.currentImageId = null;
        if (Album.images.length > 0) {
            var next = Album.images[index] || Album.images[index - 1] || Album.images[0];
            if (next) {
                Album.selectImage(next.sequence, {
                    scroll: false,
                    updateHash: true,
                    force: true
                });
                return;
            }
        }
        updateViewerMeta($());
        closeViewer();
    }
};

Album.prototype.syncPendingImage = function () {
    var Album = this;
    if (Album.pendingImageId !== null && Album.getImageCard(Album.pendingImageId).length) {
        Album.selectImage(Album.pendingImageId, {
            scroll: false,
            updateHash: false,
            force: true
        });
        return true;
    }
    return false;
};

Album.prototype.applyFilter = function () {
    var Album = this;

    $('#album-grid .album-card').each(function () {
        var card = $(this);

        if (!Album.showFavoritesOnly) {
            card.show();
        } else {
            card.toggle(card.attr('data-favorite') === '1');
        }
    });

    var favoriteButton = $('#favorite-btn');
    if (!Album.showFavoritesOnly) {
        // Resetting the buttons
        favoriteButton
            .attr('title', 'View favorite images from this album')
            .attr('data-original-title', 'View favorite images from this album');
        $('#favorite-btn em').addClass('error');
        $('#favorite-count').show();
        $('#downloadable-favorites-btn').hide();
        $('#downloadable-all-btn').show();
        $('#submit-favorites-btn').hide();

        // --- RESTORE ORIGINAL FAVORITES ---
        if (Album.originalFavoritesBackup) {
            $('#album-grid .album-card').each(function () {
                var card = $(this);
                var origFav = Album.originalFavoritesBackup[card.attr('data-image-id')] || '0';
                card.attr('data-favorite', origFav);
                card.toggleClass('is-favorite', origFav === '1');

                // Restore card favorite heart icons to correct state
                var isFav = (origFav === '1');
                card.find('.album-card-action[data-action="favorite"] em')
                    .removeClass('fa-heart error')
                    .addClass(isFav ? 'fa-heart error' : 'fa-heart');
            });
            // Wipe backup and state
            Album.originalFavoritesBackup = null;
            Album.viewingAdminUserFavorites = null;
        }
        // ----------------------------------

        // Resetting the menu breadcrumbs
        $('.breadcrumb>li').last().remove();
        var lastLink = $('.breadcrumb>li').last();
        var text = lastLink.text();
        lastLink.addClass('active').html(text).off("click").css('cursor', 'unset');
    } else {
        // Setting the buttons
        favoriteButton
            .attr('title', 'View all images from this album')
            .attr('data-original-title', 'View all images from this album');
        $('#favorite-btn em').removeClass('error');
        $('#favorite-count').hide();
        $('#downloadable-all-btn').hide();
        $('#downloadable-favorites-btn').show();
        $('#submit-favorites-btn').show();

        // Adding favorite label to breadcrumbs
        var lastLink = $('.breadcrumb>li').last();
        lastLink.removeClass('active');
        var text = lastLink.text();
        var anchor = $('<a>');
        anchor.text(text);
        lastLink.html(anchor).css('cursor', 'pointer').off("click").click(function () {
            toggleFavorites();
        });

        // Determine custom breadcrumb label based on active admin target user
        var breadcrumbLabel = 'Favorites';
        if (Album.viewingAdminUserFavorites) {
            breadcrumbLabel = 'Favorites (' + Album.viewingAdminUserFavorites + ')';
        }

        var listItem = $('<li>');
        listItem.addClass('active').text(breadcrumbLabel);
        $('#actions').before(listItem);
    }
    var tooltip = favoriteButton.attr('aria-describedby');
    $('#' + tooltip).remove();
    favoriteButton.remove('aria-describedby');

    updateAllAlbumCardSpans();
    Album.loadImages();     // lazy-load any newly visible images
};

Album.prototype.loadImages = function () {
    var Album = this;
    if (Album.initialized) {
        var loadedNow = 0;
        $('#album-grid .album-card img[data-src]').each(function () {
            var img = $(this);
            if (img.attr('data-loaded') === '1' || img.attr('data-loading') === '1') {
                return;
            }
            var card = img.closest('.album-card');
            if (!card.length) {
                return;
            }
            var rect = card.get(0).getBoundingClientRect();
            var threshold = window.innerHeight * 1.5;
            if (rect.top < threshold && rect.bottom > -threshold * 0.25) {
                card.attr('data-loading', '1');
                img.one('load', function () {
                    markAlbumCardLoaded(card);
                });
                img.attr('src', img.attr('data-src'));
                if (img.get(0).complete && img.get(0).naturalWidth > 0) {
                    markAlbumCardLoaded(card);
                }
                loadedNow++;
            }
        });
        Album.loaded += loadedNow;
        return Album.loaded;
    }
    if (Album.loading) {
        return Album.loaded;
    }
    Album.loading = true;
    $.get("/api/get-album-images.php", {
        albumId: Album.albumId,
        start: Album.loaded,
        howMany: Album.totalImages
    }, function (data) {
        if (typeof data.favoriteCount !== "undefined") {
            updateFavoriteCount(data.favoriteCount);
        }
        $.each(data.images, function (k, v) {
            var isFavorite = parseInt(v.favorite, 10) === 1;
            var card = $('<article>');
            card.addClass('album-card');
            card.attr('album-id', Album.albumId);
            card.attr('image-id', v.sequence);
            card.attr('data-image-id', v.sequence);
            card.attr('data-favorite', isFavorite ? '1' : '0');
            card.toggleClass('is-favorite', isFavorite);
            card.attr('data-loading', '0');
            card.attr('data-loaded', '0');
            card.attr('data-title', v.title);
            card.attr('data-caption', v.caption || '');
            card.attr('data-location', v.location);
            card.attr('data-height', v.height || 1);
            card.attr('data-width', v.width || 1);
            card.attr('data-index', Album.images.length);

            var media = $('<button>');
            media.attr('type', 'button');
            media.addClass('album-card-media');
            media.attr('aria-label', 'Open image ' + (v.title || v.sequence));
            media.attr('style', 'background-image: url("' + v.location + '")');

            var img = $('<img>');
            img.addClass('album-card-image');
            img.attr('src', 'data:image/gif;base64,R0lGODlhAQABAAAAACw=');
            img.attr('data-src', '/img/image.png');
            img.one('load', function () {
                markAlbumCardLoaded(card);
            });

            var overlay = $('<div>');
            overlay.addClass('album-card-overlay');
            overlay.click(function (event) {
                event.preventDefault();
                event.stopPropagation();
                Album.selectImage(v.sequence, {
                    scroll: true,
                    updateHash: true,
                    force: true
                });
            });

            var meta = $('<div>');
            meta.addClass('album-card-meta');
            if (window.showImageTitle) {
                meta.append($('<strong>').text(v.title || ('Image ' + v.sequence)));
            }
            if (v.caption) {
                meta.append($('<span>').text(v.caption));
            }
            overlay.append(meta);

            var actions = $('<div>');
            actions.addClass('album-card-actions');

            if (window.albumCanDownload) {
                actions.append(createIconButton('download', 'fa-download', 'Download image', function () {
                    downloadImageFor(v.sequence);
                }));
            }

            actions.append(createIconButton('submit', 'fa-paper-plane', 'Submit image', function () {
                submitImageFor(v.sequence);
            }));

            actions.append(createIconButton('favorite', isFavorite ? 'fa-heart error' : 'fa-heart', 'Toggle favorite', function () {
                toggleFavoriteForImage(v.sequence);
            }));

            overlay.append(actions);
            media.append(img);
            card.append(media);
            card.append(overlay);
            $('#album-grid').append(card);
            updateAlbumCardSpan(card);
            Album.images.push({
                sequence: v.sequence,
                location: v.location,
                title: v.title,
                caption: v.caption || '',
                favorite: isFavorite
            });
        });
        Album.initialized = true;
        Album.loading = false;
        updateAllAlbumCardSpans();
        Album.loadImages();
        Album.syncPendingImage();
    }, "json").fail(function () {
        Album.loading = false;
    });
    return Album.loaded;
};

$(document).ready(function () {
    var albumResizeTimer = null;
    const urlParams = new URLSearchParams(window.location.search);
    const albumId = urlParams.get('album');

    $('#album-grid').on('click', '.album-card-media', function (event) {
        event.preventDefault();
        var card = $(this).closest('.album-card');
        var imageId = card.attr('data-image-id');
        if (window.album && imageId) {
            window.album.selectImage(imageId, {
                scroll: true,
                updateHash: true,
                force: true
            });
        }
    });

    $('#album-grid').on('click', '.album-card-action', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var card = $(this).closest('.album-card');
        var imageId = card.attr('data-image-id');
        var action = $(this).attr('data-action');
        if (!imageId) {
            return;
        }
        if (action === 'view') {
            if (window.album) {
                window.album.selectImage(imageId, {
                    scroll: true,
                    updateHash: true,
                    force: true
                });
            }
        } else if (action === 'download') {
            downloadImageFor(imageId);
        } else if (action === 'submit') {
            submitImageFor(imageId);
        } else if (action === 'favorite') {
            toggleFavoriteForImage(imageId);
        }
    });

    $('#album-viewer-close').click(function () {
        history.replaceState("", document.title, window.location.pathname + window.location.search);
        closeViewer();
        $('#album-grid .album-card').removeClass('is-active');
        if (window.album) {
            window.album.currentImageId = null;
        }
    });

    $('#album-viewer-overlay').click(function (event) {
        if ($(event.target).is('#album-viewer-overlay')) {
            $('#album-viewer-close').click();
        }
    });

    $(document).on('keydown', function (event) {
        if ($('#album-viewer-overlay').hasClass('hidden')) {
            return;
        }
        if (event.key === 'Escape') {
            $('#album-viewer-close').click();
        } else if (event.key === 'ArrowLeft') {
            if (window.album) {
                window.album.prev();
            }
        } else if (event.key === 'ArrowRight') {
            if (window.album) {
                window.album.next();
            }
        }
    });

    $('#album-prev-btn').click(function () {
        if (window.album) {
            window.album.prev();
        }
    });
    $('#album-next-btn').click(function () {
        if (window.album) {
            window.album.next();
        }
    });

    $('#downloadable-image-btn').click(function () {
        downloadSelectedImage();
    });
    $('#submit-image-btn').click(function () {
        submitSelectedImage();
    });

    $('#downloadable-favorites-btn').click(function () {
        downloadImages(albumId, 'favorites');
    });
    $('#submit-favorites-btn').click(function () {
        $('#submit').attr('what', 'favorites').modal();
    });

    $('#downloadable-all-btn').click(function () {
        downloadImages(albumId, 'all');
    });

    $('#submit-send').click(function () {
        submitImages();
    });

    $('#set-favorite-image-btn').click(function () {
        setFavoriteImage();
    });
    $('#unset-favorite-image-btn').click(function () {
        unsetFavoriteImage();
    });

    $('#favorite-btn').click(function () {
        toggleFavorites();
    });

    $(".nav-tabs a").click(function () {
        $(this).tab('show');
    });

    $('#notify-submit').click(function () {
        submitNotifyEmail();
    });

    $(window).on('resize', function () {
        clearTimeout(albumResizeTimer);
        albumResizeTimer = setTimeout(function () {
            updateAllAlbumCardSpans();
            if (window.album) {
                window.album.loadImages();
            }
        }, 50);
    });
});

function setFavoriteImage() {
    var img = getCurrentAlbumImage();
    if (img.length === 0) return;
    var imageId = img.attr('image-id');

    $.post("/api/set-favorite.php", {album: img.attr('album-id'), image: imageId}).done(function (data) {
        updateFavoriteCount(data);
        setFavorite();
        syncCardFavoriteState(imageId, true);
    });
}

function unsetFavoriteImage() {
    var img = getCurrentAlbumImage();
    if (img.length === 0) return;
    var imageId = img.attr('image-id');

    $.post("/api/unset-favorite.php", {album: img.attr('album-id'), image: imageId}).done(function (data) {
        updateFavoriteCount(data);
        unsetFavorite();
        syncCardFavoriteState(imageId, false);
    });
}

function syncCardFavoriteState(imageId, isFavorite) {
    var card = getAlbumImageCard(imageId);
    if (!card.length) return;

    var favStr = isFavorite ? '1' : '0';
    card.attr('data-favorite', favStr);
    card.toggleClass('is-favorite', isFavorite);

    // Sync the small grid action heart icon inside the thumbnail overlay
    card.find('.album-card-action[data-action="favorite"] em')
        .removeClass('fa-heart error')
        .addClass(isFavorite ? 'fa-heart error' : 'fa-heart');
}

function toggleFavorites() {
    if (!window.album) {
        return;
    }

    window.album.showFavoritesOnly = !window.album.showFavoritesOnly;
    $(this).toggleClass('active', window.album.showFavoritesOnly);
    window.album.applyFilter();
}

function downloadSelectedImage() {
    var img = getCurrentAlbumImage();
    if (img.length === 0) {
        return;
    }
    $.get("/api/is-downloadable.php", {
        album: img.attr('album-id'),
        image: img.attr('image-id')
    }).done(function (data) {
        if (data === '1') {
            downloadImages(img.attr('album-id'), img.attr('image-id'));
        }
    });
}

function submitSelectedImage() {
    var img = getCurrentAlbumImage();
    if (img.length === 0) {
        return;
    }
    $('#submit').attr('what', img.attr('image-id')).modal();
}

function showFavorites() {
    $('#favorites-list').empty();
    $('#favorites').modal();
    var albumId = $('#album-viewer-overlay').attr('album-id');
    if (!albumId && window.album) {
        albumId = window.album.albumId;
    }
    $.get("/api/get-favorites.php", {
        album: albumId,
    }, function (data) {
        $.each(data, function (i) {
            var li = $('<li image-id="' + data[i].sequence + '" class="img-favorite">');
            li.css('background-image', 'url("' + data[i].location + '")');
            li.click(function () {
                $.post("/api/unset-favorite.php", {
                    album: albumId,
                    image: $(this).attr('image-id')
                }).done(function (data) {
                    updateFavoriteCount(data);
                    if (parseInt(data, 10) <= 0) {
                        $("#downloadable-favorites-btn").prop("disabled", true);
                        $("#submit-favorites-btn").prop("disabled", true);
                    }
                });
                $(this).remove();
            });
            $('#favorites-list').append(li);
        });
        $("#downloadable-favorites-btn").prop("disabled", false);
        $("#submit-favorites-btn").prop("disabled", false);
        if (!$("#favorites-list").has("li").length) {
            $("#downloadable-favorites-btn").prop("disabled", true);
            $("#submit-favorites-btn").prop("disabled", true);
        }
    }, "json");
}

function setFavorite() {
    $('#set-favorite-image-btn').addClass('hidden');
    $('#unset-favorite-image-btn').removeClass('hidden');
    $('#album-grid .album-card.is-active .album-card-action[data-action="favorite"] em').removeClass('fa-heart').addClass('fa-heart error');
}

function unsetFavorite() {
    $('#set-favorite-image-btn').removeClass('hidden');
    $('#unset-favorite-image-btn').addClass('hidden');
    $('#album-grid .album-card.is-active .album-card-action[data-action="favorite"] em').removeClass('fa-heart error').addClass('fa-heart');
}

function downloadImages(album, what) {
    BootstrapDialog.show({
        draggable: true,
        title: 'Terms Of Service',
        message: '<em class="fa fa-exclamation-triangle"></em> By downloading the selected files, you are agreeing to the right to copy, display, reproduce, enlarge and distribute said photographs taken by the Photographer in connection with the Services and in connection with the publication known as Saperstone Studios for personal use, and any reprints or reproductions, or excerpts thereof; all other rights are expressly reserved by and to Photographer.<br/><br/>While usage in accordance with above policies of selected files on public social media sites and personal websites for non-profit purposes is acceptable, any use of selected files in any publication, display, exhibit or paid medium are not permitted without express consent from Photographer.<br/><br/>Please note that only images you have expressly purchased rights to will be downloaded, even if additional images were selected for this download.',
        buttons: [{
            icon: 'glyphicon glyphicon-download-alt',
            label: ' Download',
            cssClass: 'btn-success',
            action: function (dialogInItself) {
                let $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                let modal = $button.closest('.modal-content');
                modal.find('.bootstrap-dialog-body').append('<div id="compressing-download" class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>We are compressing your images for download. They should automatically start downloading shortly.</div>');
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/download-selected-images.php", {
                    album: album,
                    what: what
                }, "json").done(function (data) {
                    data = jQuery.parseJSON(data);
                    if (data.hasOwnProperty('message')) {
                        modal.find('.bootstrap-dialog-body').append('<div id="download-email-address-alert" class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data.message + '<br/><br/><input style="width:430px; display:inline; margin-right:20px;" placeholder="Email" type="email" class="form-control" id="download-email-address"/><button onClick="submitDownloadEmail(\'' + data.file.replace(/'/g, "\\'") + '\')" class="btn btn-info">Submit</button></div>');
                        $button.remove();
                    } else if (data.hasOwnProperty('file')) {
                        window.location = data.file;
                        dialogInItself.close();
                    } else if (data.hasOwnProperty('error')) {
                        modal.find('.bootstrap-dialog-body').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data.error + '</div>');
                    } else {
                        modal.find('.bootstrap-dialog-body').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
                    }
                }).always(function () {
                    $('#compressing-download').remove();
                    $button.stopSpin();
                    dialogInItself.enableButtons(true);
                    dialogInItself.setClosable(true);
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

function submitDownloadEmail(file) {
    $.post("/api/download-send-email.php", {
        email: $('#download-email-address').val(),
        file: file,
    }).done(function (data) {
        if (data !== "") {
            $('.bootstrap-dialog-body').append('<div class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data + '</div>');
        } else {
            $('.modal-backdrop').remove();
            $('.modal').remove();
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function () {
        $('#download-email-address-alert').remove();
    });
}

function submitImages() {
    $("#submit-send").prop("disabled", true);
    $("#submit-send").next().prop("disabled", true);
    $("#submit-send em").removeClass('fa fa-paper-plane').addClass('glyphicon glyphicon-asterisk icon-spin');
    $.post("/api/send-selected-images.php", {
        album: $('#submit').attr('album-id'),
        what: $('#submit').attr('what'),
        name: $('#submit-name').val(),
        email: $('#submit-email').val(),
        comment: $('#submit-comment').val()
    }).done(function (data) {
        if (data !== "") {
            $("#submit .modal-body").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data + '</div>');
        } else {
            $("#submit").modal('hide')
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#submit .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#submit .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $("#submit .modal-body").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
        }
    }).always(function () {
        $('#submit-send').prop("disabled", false);
        $('#submit-send').next().prop("disabled", false);
        $('#submit-send em').addClass('fa fa-paper-plane').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}

function submitNotifyEmail() {
    $("#notify-submit").prop("disabled", true);
    $("#notify-submit").next().prop("disabled", true);
    $("#notify-submit em").removeClass('fa fa-paper-plane').addClass('glyphicon glyphicon-asterisk icon-spin');
    $.post("/api/add-notification-email.php", {
        album: $('#submit').attr('album-id'),
        email: $('#notify-email').val()
    }).done(function (data) {
        if (data !== "") {
            $("#album-thumbs").after('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data + '</div>');
        } else {
            $("#album-thumbs").empty().after('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Your email address was successfully recorded. You will be notified once the images have been uploaded.</div>');
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#album-thumbs').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#album-thumbs').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $("#album-thumbs").after('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
        }
    }).always(function () {
        $('#notify-submit').prop("disabled", false);
        $('#notify-submit').next().prop("disabled", false);
        $('#notify-submit em').addClass('fa fa-paper-plane').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}
