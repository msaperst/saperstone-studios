const assert = require('node:assert/strict');
const {test} = require('node:test');
const {
    createAlbumDetailContext,
    plain
} = require('./helpers/album-detail-test-utils');

function sampleImages() {
    return [{
        sequence: '10',
        location: '/albums/sample/10.jpg',
        thumbnail400: '/albums/sample/thumbs/400/10.jpg',
        thumbnail800: '/albums/sample/thumbs/800/10.jpg',
        thumbnail1200: '/albums/sample/thumbs/1200/10.jpg',
        thumbnail1600: '/albums/sample/10.jpg',
        title: 'First',
        caption: 'First caption',
        height: '800',
        width: '1200',
        favorite: '1',
        downloadable: '1'
    }, {
        sequence: '11',
        location: '/albums/sample/11.jpg',
        thumbnail400: '/albums/sample/thumbs/400/11.jpg',
        thumbnail800: '/albums/sample/thumbs/800/11.jpg',
        thumbnail1200: '/albums/sample/thumbs/1200/11.jpg',
        thumbnail1600: '/albums/sample/11.jpg',
        title: 'Second',
        caption: '',
        height: '1200',
        width: '800',
        favorite: '0',
        downloadable: '0'
    }];
}

test('album.js loads the initial page of image metadata', () => {
    const {context, environment, window} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: sampleImages(),
            favoriteCount: 1
        }
    });

    const album = new context.Album('7', 4, 2);
    window.album = album;

    assert.deepEqual(plain(environment.calls.get[0]), {
        url: '/api/get-album-images.php',
        data: {
            albumId: '7',
            start: 0,
            howMany: 2
        }
    });
    assert.equal(album.initialized, true);
    assert.equal(album.loading, false);
    assert.equal(album.images.length, 2);
});

function imageBatch(start, count) {
    return Array.from({length: count}, (_, index) => ({
        ...sampleImages()[0],
        sequence: String(start + index),
        title: 'Image ' + (start + index)
    }));
}

test('album.js requests four metadata rows first even for a very large album', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 0}
    });

    const album = new context.Album('7', 4, 2500);

    assert.deepEqual(plain(environment.calls.get), [{
        url: '/api/get-album-images.php',
        data: {albumId: '7', start: 0, howMany: 4}
    }]);
    assert.equal(album.images.length, 4);
    assert.equal(environment.element('#album-grid').appended.length, 4);
});

test('album.js loads the next page on scroll and stops at the album total', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 0}
    });
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(4, 2), favoriteCount: 0}
    });

    const album = new context.Album('7', 4, 6);
    assert.equal(environment.calls.get.length, 1, 'offscreen footer must not preload');

    environment.element('#album-load-trigger').rect.top = 200;
    album.loadImages();

    assert.equal(environment.calls.get.length, 2);
    assert.deepEqual(plain(environment.calls.get[1].data), {
        albumId: '7', start: 4, howMany: 2
    });
    assert.deepEqual(plain(album.images.map(image => image.sequence)), ['0','1','2','3','4','5']);
    album.loadImages();
    assert.equal(environment.calls.get.length, 2, 'album must not fetch beyond its total');
});

test('album.js avoids overlapping metadata requests during rapid scroll events', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 0}
    });
    environment.queueGet('/api/get-album-images.php', {type: 'pending'});

    const album = new context.Album('7', 4, 8);
    environment.element('#album-load-trigger').rect.top = 200;
    album.loadImages();
    album.loadImages();
    album.loadImages();

    assert.equal(environment.calls.get.length, 2);
    assert.equal(album.loading, true);
});

test('album.js can retry a failed subsequent page without losing prior cards', () => {
    const {context, environment, window} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 0}
    });
    environment.queueGet('/api/get-album-images.php', {type: 'failure'});

    const album = new context.Album('7', 4, 6);
    window.album = album;
    environment.element('#album-load-trigger').rect.top = 200;
    album.loadImages();
    assert.equal(album.loadFailed, true);
    assert.equal(album.images.length, 4);
    assert.equal(environment.element('#album-retry-btn').hasClass('hidden'), false);

    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(4, 2), favoriteCount: 0}
    });
    environment.element('#album-load-trigger').rect.top = 2000;
    environment.element('#album-retry-btn').click();

    assert.equal(environment.calls.get.length, 3);
    assert.deepEqual(plain(environment.calls.get[2].data), {
        albumId: '7', start: 4, howMany: 2
    });
    assert.equal(album.images.length, 6);
    assert.equal(album.loadFailed, false);
});

test('album.js favorite filtering includes photos in unloaded pages', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 1}
    });
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(4, 2), favoriteCount: 1}
    });

    const album = new context.Album('7', 4, 6);
    const breadcrumbs = environment.element('.breadcrumb > li');
    breadcrumbs.last = () => breadcrumbs;
    album.showFavoritesOnly = true;
    album.applyFilter();

    assert.equal(environment.calls.get.length, 2);
    assert.deepEqual(plain(environment.calls.get[1].data), {
        albumId: '7', start: 4, howMany: 2
    });
    assert.equal(album.images.length, 6);
});

test('album.js updates favorites controls immediately while later metadata is loading', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 1}
    });
    environment.queueGet('/api/get-album-images.php', {type: 'pending'});

    const album = new context.Album('7', 4, 6);
    const breadcrumbs = environment.element('.breadcrumb > li');
    breadcrumbs.last = () => breadcrumbs;
    album.showFavoritesOnly = true;
    album.applyFilter();

    assert.equal(album.loading, true);
    assert.equal(environment.calls.get.length, 2);
    assert.equal(environment.element('#favorite-count').visible, false);
    assert.equal(environment.element('#downloadable-all-btn').visible, false);
    assert.equal(environment.element('#downloadable-favorites-btn').visible, true);
    assert.equal(environment.element('#submit-favorites-btn').visible, true);
    assert.equal(environment.element('#downloadable-favorites-btn').prop('disabled'), true);
    assert.equal(environment.element('#submit-favorites-btn').prop('disabled'), true);
});

test('album.js opens a deep link into a later metadata page', () => {
    const {context, environment, window} = createAlbumDetailContext({hash: '#5'});
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(0, 4), favoriteCount: 0}
    });
    environment.queueGet('/api/get-album-images.php', {
        type: 'success', data: {images: imageBatch(4, 2), favoriteCount: 0}
    });

    const originalGetImageCard = context.Album.prototype.getImageCard;
    context.Album.prototype.getImageCard = function (imageId) {
        const card = originalGetImageCard.call(this, imageId);
        card.length = this.images.some(image => String(image.sequence) === String(imageId)) ? 1 : 0;
        return card;
    };

    const album = new context.Album('7', 4, 6);
    window.album = album;

    assert.equal(environment.calls.get.length, 2);
    assert.equal(album.currentImageId, '5');
    assert.equal(album.pendingImageId, null);
});

test('album.js preserves server image order and metadata in its local cache', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: sampleImages(),
            favoriteCount: 1
        }
    });

    const album = new context.Album('7', 4, 2);

    assert.deepEqual(plain(album.images), [{
        sequence: '10',
        location: '/albums/sample/10.jpg',
        thumbnail400: '/albums/sample/thumbs/400/10.jpg',
        thumbnail800: '/albums/sample/thumbs/800/10.jpg',
        thumbnail1200: '/albums/sample/thumbs/1200/10.jpg',
        thumbnail1600: '/albums/sample/10.jpg',
        title: 'First',
        caption: 'First caption',
        favorite: true,
        downloadable: true
    }, {
        sequence: '11',
        location: '/albums/sample/11.jpg',
        thumbnail400: '/albums/sample/thumbs/400/11.jpg',
        thumbnail800: '/albums/sample/thumbs/800/11.jpg',
        thumbnail1200: '/albums/sample/thumbs/1200/11.jpg',
        thumbnail1600: '/albums/sample/11.jpg',
        title: 'Second',
        caption: '',
        favorite: false,
        downloadable: false
    }]);
});

test('album.js appends one card per returned image with favorite and download state', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: sampleImages(),
            favoriteCount: 1
        }
    });

    new context.Album('7', 4, 2);

    const cards = environment.element('#album-grid').appended;
    assert.equal(cards.length, 2);

    assert.equal(cards[0].attr('data-image-id'), '10');
    assert.equal(cards[0].attr('data-favorite'), '1');
    assert.equal(cards[0].attr('data-downloadable'), '1');
    assert.equal(cards[0].attr('data-height'), '800');
    assert.equal(cards[0].attr('data-width'), '1200');
    assert.equal(cards[0].attr('data-location'), '/albums/sample/10.jpg');
    assert.equal(cards[0].attr('data-thumbnail-400'), '/albums/sample/thumbs/400/10.jpg');
    assert.equal(cards[0].attr('data-thumbnail-800'), '/albums/sample/thumbs/800/10.jpg');
    assert.equal(cards[0].attr('data-thumbnail-1200'), '/albums/sample/thumbs/1200/10.jpg');
    assert.equal(cards[0].attr('data-thumbnail-1600'), '/albums/sample/10.jpg');

    assert.equal(cards[1].attr('data-image-id'), '11');
    assert.equal(cards[1].attr('data-favorite'), '0');
    assert.equal(cards[1].attr('data-downloadable'), '0');
});

test('album.js updates the favorite count returned with album metadata', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: sampleImages(),
            favoriteCount: 3
        }
    });

    new context.Album('7', 4, 2);

    assert.equal(environment.element('#favorite-count').text(), 3);
    assert.equal(environment.element('#favorite-count').hasClass('album-favorite-count-present'), true);
});

test('album.js shows loading feedback while album metadata is pending', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {type: 'pending'});

    const album = new context.Album('7', 4, 2000);
    const status = environment.element('#album-load-status');

    assert.equal(album.loading, true);
    assert.equal(status.hasClass('hidden'), false);
    assert.equal(status.hasClass('is-error'), false);
    assert.equal(environment.element('#album-load-message').text(), 'Loading album photos…');
    assert.equal(environment.element('#album-load-spinner').hasClass('hidden'), false);
    assert.equal(environment.element('#album-retry-btn').hasClass('hidden'), true);
});

test('album.js hides loading feedback when images are ready', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {images: sampleImages(), favoriteCount: 1}
    });

    const album = new context.Album('7', 4, 2);

    assert.equal(album.initialized, true);
    assert.equal(environment.element('#album-load-status').hasClass('hidden'), true);
    assert.equal(environment.element('#album-grid').appended.length, 2);
});

test('album.js leaves the empty album without a loading indicator', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {images: [], favoriteCount: 0}
    });

    const album = new context.Album('7', 4, 0);

    assert.equal(album.initialized, true);
    assert.equal(environment.element('#album-load-status').hasClass('hidden'), true);
    assert.equal(environment.element('#album-grid').appended.length, 0);
});

test('album.js offers a retry after a metadata error without auto-retrying on scroll', () => {
    const {context, environment, window} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {type: 'failure'});

    const album = new context.Album('7', 4, 2);
    window.album = album;

    const status = environment.element('#album-load-status');
    assert.equal(album.loadFailed, true);
    assert.equal(status.hasClass('hidden'), false);
    assert.equal(status.hasClass('is-error'), true);
    assert.equal(environment.element('#album-load-spinner').hasClass('hidden'), true);
    assert.equal(environment.element('#album-retry-btn').hasClass('hidden'), false);

    album.loadImages();
    assert.equal(environment.calls.get.length, 1, 'scrolling should not silently retry');

    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {images: sampleImages(), favoriteCount: 1}
    });
    environment.element('#album-retry-btn').click();

    assert.equal(environment.calls.get.length, 2);
    assert.equal(album.loadFailed, false);
    assert.equal(album.initialized, true);
    assert.equal(status.hasClass('hidden'), true);
    assert.equal(environment.element('#album-grid').appended.length, 2);
});

test('album.js stops the loading state when the metadata request fails', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'failure',
        xhr: {
            responseText: 'Unable to load album'
        }
    });

    const album = new context.Album('7', 4, 2);

    assert.equal(album.loading, false);
    assert.equal(album.initialized, false);
    assert.equal(album.images.length, 0);
});

test('album.js removeImage keeps the local cache and total count synchronized', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: sampleImages(),
            favoriteCount: 1
        }
    });

    const album = new context.Album('7', 4, 2);
    album.removeImage('10');

    assert.deepEqual(plain(album.images.map((image) => image.sequence)), ['11']);
    assert.equal(album.totalImages, 1);
});


test('album.js defers the real thumbnail URL when building cards', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: [sampleImages()[0]],
            favoriteCount: 0
        }
    });

    new context.Album('7', 4, 1);

    const card = environment.element('#album-grid').appended[0];
    const media = card.appended[0];
    const image = media.appended[0];

    assert.equal(media.attr('style'), undefined);
    assert.equal(image.attr('src'), 'data:image/gif;base64,R0lGODlhAQABAAAAACw=');
    assert.equal(image.attr('data-src'), '/img/image.png');
    assert.notEqual(image.attr('src'), '/albums/sample/thumbs/10.jpg');
    assert.notEqual(image.attr('data-src'), '/albums/sample/thumbs/10.jpg');
});

test('album.js loads the real thumbnail only when its card is near the viewport', () => {
    const {context, environment} = createAlbumDetailContext({
        lengths: {
            '#album-grid .album-card img[data-src]': 1
        },
        innerHeight: 800
    });
    const image = environment.element('#album-grid .album-card img[data-src]');
    const card = environment.element('__lazy_card__');
    const media = environment.element('__lazy_media__');
    card.rect = {top: 100, bottom: 350, width: 300, height: 250};
    card.attr('data-location', '/albums/sample/thumbs/lazy.jpg');
    card.find = (selector) => selector === '.album-card-media' ? media : environment.element('__lazy_find__');
    image.closestResult = card;
    image.attr('src', 'placeholder');
    // A manipulated DOM data-src must never become a directly loadable image source.
    image.attr('data-src', 'https://example.org/unprotected-photo.jpg');

    const album = Object.create(context.Album.prototype);
    Object.assign(album, {
        initialized: true,
        loaded: 0,
        loading: false,
        images: [],
        totalImages: 1
    });

    album.loadImages();

    assert.equal(image.attr('src'), '/img/image.png');
    assert.equal(image.attr('data-src'), 'https://example.org/unprotected-photo.jpg');
    assert.equal(media.css('background-image'), 'url("/albums/sample/thumbs/lazy.jpg")');
    assert.equal(card.attr('data-loading'), '1');
    assert.equal(album.loaded, 1);
});

test('album.js leaves offscreen thumbnail URLs untouched', () => {
    const {context, environment} = createAlbumDetailContext({
        lengths: {
            '#album-grid .album-card img[data-src]': 1
        },
        innerHeight: 800
    });
    const image = environment.element('#album-grid .album-card img[data-src]');
    const card = environment.element('__offscreen_card__');
    const media = environment.element('__offscreen_media__');
    card.rect = {top: 2000, bottom: 2250, width: 300, height: 250};
    card.attr('data-location', '/albums/sample/thumbs/offscreen.jpg');
    card.find = (selector) => selector === '.album-card-media' ? media : environment.element('__offscreen_find__');
    image.closestResult = card;
    image.attr('src', 'placeholder');
    image.attr('data-src', '/img/image.png');

    const album = Object.create(context.Album.prototype);
    Object.assign(album, {
        initialized: true,
        loaded: 0,
        loading: false,
        images: [],
        totalImages: 1
    });

    album.loadImages();

    assert.equal(image.attr('src'), 'placeholder');
    assert.equal(media.css('background-image'), undefined);
    assert.equal(card.attr('data-loading'), undefined);
    assert.equal(album.loaded, 0);
});

test('album.js does not process a card again while its thumbnail is already loading', () => {
    const {context, environment} = createAlbumDetailContext({
        lengths: {
            '#album-grid .album-card img[data-src]': 1
        }
    });
    const image = environment.element('#album-grid .album-card img[data-src]');
    const card = environment.element('__loading_card__');
    card.attr('data-loading', '1');
    image.closestResult = card;
    image.attr('src', '/img/image.png');
    image.attr('data-src', '/img/image.png');

    const album = Object.create(context.Album.prototype);
    Object.assign(album, {
        initialized: true,
        loaded: 4,
        loading: false,
        images: [],
        totalImages: 4
    });

    album.loadImages();

    assert.equal(album.loaded, 4);
});

test('album.js refreshImages appends metadata added after the page initialized', () => {
    const {context, environment} = createAlbumDetailContext();
    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: sampleImages(),
            favoriteCount: 1
        }
    });

    const album = new context.Album('7', 4, 2);

    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: [{
                sequence: '12',
                location: '/albums/sample/thumbs/12.jpg',
                title: 'Uploaded',
                caption: '',
                height: '900',
                width: '900',
                favorite: '0',
                downloadable: '1'
            }],
            favoriteCount: 1
        }
    });

    album.refreshImages(3);

    assert.deepEqual(plain(environment.calls.get[1]), {
        url: '/api/get-album-images.php',
        data: {
            albumId: '7',
            start: 2,
            howMany: 1
        }
    });
    assert.deepEqual(plain(album.images.map((image) => image.sequence)), ['10', '11', '12']);
    assert.equal(album.totalImages, 3);
    assert.equal(environment.element('#album-grid').appended.length, 3);
});


test('album.js shows the first uploaded image without requiring an empty-gallery page refresh', () => {
    const {context, environment, window} = createAlbumDetailContext();
    const emptyState = environment.element('#album-empty-state');

    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: [],
            favoriteCount: 0
        }
    });

    const album = new context.Album('7', 4, 0);
    window.album = album;

    environment.queueGet('/api/get-album-images.php', {
        type: 'success',
        data: {
            images: [{
                sequence: '1',
                location: '/albums/sample/thumbs/1.jpg',
                title: 'First upload',
                caption: '',
                height: '800',
                width: '1200',
                favorite: '0',
                downloadable: '1'
            }],
            favoriteCount: 0
        }
    });

    album.refreshImages(1);

    assert.deepEqual(plain(album.images.map((image) => image.sequence)), ['1']);
    assert.equal(environment.element('#album-grid').appended.length, 1);
    assert.equal(emptyState.removed, true);
});


test('album.js chooses responsive protected derivatives for grid cards', () => {
    const {context, environment, window} = createAlbumDetailContext();
    const card = environment.element('__responsive_card__');
    card.rect = {top: 0, bottom: 200, width: 180, height: 200};
    card.attr('data-location', '/albums/sample/10.jpg');
    card.attr('data-thumbnail-400', '/albums/sample/thumbs/400/10.jpg');
    card.attr('data-thumbnail-800', '/albums/sample/thumbs/800/10.jpg');
    card.attr('data-thumbnail-1200', '/albums/sample/thumbs/1200/10.jpg');
    card.attr('data-thumbnail-1600', '/albums/sample/10.jpg');

    window.devicePixelRatio = 2;
    assert.equal(context.getResponsiveThumbnailLocation(card), '/albums/sample/thumbs/400/10.jpg');

    card.rect.width = 300;
    assert.equal(context.getResponsiveThumbnailLocation(card), '/albums/sample/thumbs/800/10.jpg');

    card.rect.width = 500;
    assert.equal(context.getResponsiveThumbnailLocation(card), '/albums/sample/thumbs/1200/10.jpg');

    card.rect.width = 700;
    assert.equal(context.getResponsiveThumbnailLocation(card), '/albums/sample/10.jpg');
});

test('album.js viewer uses the 1600px protected derivative', () => {
    const {context, environment} = createAlbumDetailContext();
    const card = environment.element('__viewer_card__');
    card.attr('album-id', '7');
    card.attr('image-id', '10');
    card.attr('data-location', '/albums/sample/thumbs/400/10.jpg');
    card.attr('data-thumbnail-1600', '/albums/sample/10.jpg');
    card.attr('data-downloadable', '0');

    context.updateViewerMeta(card);

    assert.equal(
        environment.element('#album-viewer-image').css('background-image'),
        'url("/albums/sample/10.jpg")'
    );
    assert.equal(environment.element('#album-viewer-image').attr('src'), '/img/image.png');
});
