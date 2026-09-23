const assert = require('node:assert/strict');
const {test} = require('node:test');
const {
    createAlbumDetailContext,
    plain
} = require('./helpers/album-detail-test-utils');

function bareAlbum(context, values = {}) {
    return Object.assign(Object.create(context.Album.prototype), {
        currentImageId: null,
        pendingImageId: null,
        images: [],
        totalImages: 0,
        showFavoritesOnly: false
    }, values);
}

test('album.js selectImage opens the protected viewer and updates the URL hash', () => {
    const {context, environment, historyCalls, window} = createAlbumDetailContext();
    const album = bareAlbum(context);
    window.album = album;

    const card = environment.element('#album-grid .album-card[data-image-id="10"]');
    card.attr('album-id', '7');
    card.attr('image-id', '10');
    card.attr('data-image-id', '10');
    card.attr('data-location', '/albums/sample/thumbs/10.jpg');
    card.attr('data-caption', 'Caption');
    card.attr('data-downloadable', '1');
    card.attr('data-favorite', '1');

    const selected = album.selectImage('10', {
        updateHash: true,
        force: true
    });

    assert.equal(selected, true);
    assert.equal(album.currentImageId, '10');
    assert.equal(album.pendingImageId, null);
    assert.equal(card.hasClass('is-active'), true);
    assert.equal(
        environment.element('#album-viewer-image').attr('style'),
        'background-image: url("/albums/sample/thumbs/10.jpg")'
    );
    assert.equal(environment.element('#album-viewer-image').attr('src'), '/img/image.png');
    assert.equal(environment.element('#album-viewer-caption').text(), 'Caption');
    assert.equal(environment.element('#downloadable-image-btn').visible, true);
    assert.equal(environment.element('#album-viewer-overlay').attr('aria-hidden'), 'false');
    assert.equal(environment.element('body').hasClass('album-viewer-open'), true);
    assert.equal(historyCalls.length, 1);
    assert.equal(historyCalls[0][2], '/user/album.php#10');
});

test('album.js selectImage defers selection when the card has not been loaded yet', () => {
    const selector = '#album-grid .album-card[data-image-id="44"]';
    const {context} = createAlbumDetailContext({
        lengths: {
            [selector]: 0
        }
    });
    const album = bareAlbum(context);

    const selected = album.selectImage('44', {
        updateHash: true
    });

    assert.equal(selected, false);
    assert.equal(album.currentImageId, null);
    assert.equal(album.pendingImageId, '44');
});

test('album.js next and previous follow the complete server image order', () => {
    const {context} = createAlbumDetailContext();
    const calls = [];
    const album = bareAlbum(context, {
        currentImageId: '11',
        images: [
            {sequence: '10'},
            {sequence: '11'},
            {sequence: '12'}
        ]
    });
    album.selectImage = (imageId, options) => {
        calls.push({imageId, options});
        return true;
    };

    album.next();
    album.prev();

    assert.deepEqual(plain(calls), [{
        imageId: '12',
        options: {
            scroll: true,
            updateHash: true
        }
    }, {
        imageId: '10',
        options: {
            scroll: true,
            updateHash: true
        }
    }]);
});

test('album.js favorites-only navigation skips non-favorites and wraps', () => {
    const {context, environment} = createAlbumDetailContext();
    const calls = [];
    const album = bareAlbum(context, {
        currentImageId: '10',
        showFavoritesOnly: true,
        images: [
            {sequence: '10'},
            {sequence: '11'},
            {sequence: '12'}
        ]
    });

    environment.element('#album-grid .album-card[data-image-id="11"]').attr('data-favorite', '0');
    environment.element('#album-grid .album-card[data-image-id="12"]').attr('data-favorite', '1');

    album.selectImage = (imageId) => {
        calls.push(imageId);
        return true;
    };

    album.next();
    album.prev();

    assert.deepEqual(calls, ['12', '12']);
});

test('album.js removing the selected image advances to the next cached image', () => {
    const {context, environment} = createAlbumDetailContext();
    const calls = [];
    const album = bareAlbum(context, {
        currentImageId: '11',
        totalImages: 3,
        images: [
            {sequence: '10'},
            {sequence: '11'},
            {sequence: '12'}
        ]
    });
    album.selectImage = (imageId, options) => {
        calls.push({imageId, options});
        return true;
    };

    const removedCard = environment.element('#album-grid .album-card[data-image-id="11"]');

    album.removeImage('11');

    assert.equal(removedCard.removed, true);
    assert.deepEqual(album.images.map((image) => image.sequence), ['10', '12']);
    assert.equal(album.totalImages, 2);
    assert.deepEqual(plain(calls), [{
        imageId: '12',
        options: {
            scroll: false,
            updateHash: true,
            force: true
        }
    }]);
});

test('album.js syncPendingImage selects a requested image once its card exists', () => {
    const {context} = createAlbumDetailContext();
    const calls = [];
    const album = bareAlbum(context, {
        pendingImageId: '12'
    });
    album.selectImage = (imageId, options) => {
        calls.push({imageId, options});
        return true;
    };

    const synced = album.syncPendingImage();

    assert.equal(synced, true);
    assert.deepEqual(plain(calls), [{
        imageId: '12',
        options: {
            scroll: false,
            updateHash: false,
            force: true
        }
    }]);
});
