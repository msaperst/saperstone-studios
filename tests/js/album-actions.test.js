const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumDetailContext} = require('./helpers/album-detail-test-utils');

test('album.js toggles a card favorite on and updates the returned count', () => {
    const {context, environment, window} = createAlbumDetailContext();
    const card = environment.element('#album-grid .album-card[data-image-id="10"]');
    card.attr('album-id', '7');
    card.attr('image-id', '10');
    card.attr('data-favorite', '0');

    window.album = {
        showFavoritesOnly: false,
        getCurrentCard() {
            return environment.element('__different_current_card__').attr('image-id', '99');
        }
    };
    environment.queuePost('/api/set-favorite.php', {
        type: 'success',
        data: '2'
    });

    context.toggleFavoriteForImage('10');

    assert.deepEqual(environment.calls.post[0], {
        url: '/api/set-favorite.php',
        data: {
            album: '7',
            image: '10'
        }
    });
    assert.equal(card.attr('data-favorite'), '1');
    assert.equal(card.hasClass('is-favorite'), true);
    assert.equal(environment.element('#favorite-count').html(), 2);
});

test('album.js toggles the current favorite off and reapplies an active favorites filter', () => {
    const {context, environment, window} = createAlbumDetailContext();
    const card = environment.element('#album-grid .album-card[data-image-id="10"]');
    card.attr('album-id', '7');
    card.attr('image-id', '10');
    card.attr('data-favorite', '1');

    let filterCalls = 0;
    window.album = {
        showFavoritesOnly: true,
        getCurrentCard() {
            return card;
        },
        applyFilter() {
            filterCalls += 1;
        }
    };
    environment.queuePost('/api/unset-favorite.php', {
        type: 'success',
        data: '0'
    });

    context.toggleFavoriteForImage('10');

    assert.deepEqual(environment.calls.post[0], {
        url: '/api/unset-favorite.php',
        data: {
            album: '7',
            image: '10'
        }
    });
    assert.equal(card.attr('data-favorite'), '0');
    assert.equal(card.hasClass('is-favorite'), false);
    assert.equal(environment.element('#favorite-count').html(), '');
    assert.equal(environment.element('#set-favorite-image-btn').hasClass('hidden'), false);
    assert.equal(environment.element('#unset-favorite-image-btn').hasClass('hidden'), true);
    assert.equal(filterCalls, 1);
});

test('album.js downloads a card only when the API confirms image rights', () => {
    const {context, environment} = createAlbumDetailContext();
    const card = environment.element('#album-grid .album-card[data-image-id="10"]');
    card.attr('album-id', '7');
    card.attr('image-id', '10');

    const downloads = [];
    context.downloadImages = (albumId, imageId) => {
        downloads.push([albumId, imageId]);
    };
    environment.queueGet('/api/is-downloadable.php', {
        type: 'success',
        data: '1'
    });

    context.downloadImageFor('10');

    assert.deepEqual(downloads, [['7', '10']]);
    assert.deepEqual(environment.calls.get[0], {
        url: '/api/is-downloadable.php',
        data: {
            album: '7',
            image: '10'
        }
    });
});

test('album.js does not download a card when the API denies image rights', () => {
    const {context, environment} = createAlbumDetailContext();
    const card = environment.element('#album-grid .album-card[data-image-id="10"]');
    card.attr('album-id', '7');
    card.attr('image-id', '10');

    const downloads = [];
    context.downloadImages = (...args) => downloads.push(args);
    environment.queueGet('/api/is-downloadable.php', {
        type: 'success',
        data: '0'
    });

    context.downloadImageFor('10');

    assert.deepEqual(downloads, []);
});

test('album.js submits the selected card id without exposing its image URL', () => {
    const {context, environment} = createAlbumDetailContext();
    const card = environment.element('#album-grid .album-card[data-image-id="10"]');
    card.attr('image-id', '10');
    const submit = environment.element('#submit');
    let modalCalls = 0;
    submit.modal = () => {
        modalCalls += 1;
        return submit;
    };

    context.submitImageFor('10');

    assert.equal(submit.attr('what'), '10');
    assert.equal(modalCalls, 1);
});

test('album.js refreshes thumbnail cache-busters in both cards and cached metadata', () => {
    const {context, environment, window} = createAlbumDetailContext({
        lengths: {
            '#album-grid .album-card.is-active': 0
        }
    });
    const card = environment.element('#album-grid .album-card');
    const media = card.find('.album-card-media');
    card.attr('data-location', '/albums/sample/thumbs/10.jpg?old=1');

    window.album = {
        images: [{
            sequence: '10',
            location: '/albums/sample/thumbs/10.jpg?old=1'
        }]
    };

    context.refreshAlbumThumbnailImages();

    assert.match(card.attr('data-location'), /^\/albums\/sample\/thumbs\/10\.jpg\?v=\d+$/);
    assert.equal(
        media.css('background-image'),
        'url("' + card.attr('data-location') + '")'
    );
    assert.equal(window.album.images[0].location, card.attr('data-location'));
});

test('album.js downloadSelectedImage uses the active card and still checks rights first', () => {
    const {context, environment, window} = createAlbumDetailContext();
    const card = environment.element('__selected_download_card__');
    card.attr('album-id', '7');
    card.attr('image-id', '12');
    window.album = {
        getCurrentCard() {
            return card;
        }
    };

    const downloads = [];
    context.downloadImages = (...args) => downloads.push(args);
    environment.queueGet('/api/is-downloadable.php', {
        type: 'success',
        data: '1'
    });

    context.downloadSelectedImage();

    assert.deepEqual(downloads, [['7', '12']]);
});

test('album.js toggleFavorites flips state, button styling, and reapplies the filter', () => {
    const {context, environment, window} = createAlbumDetailContext();
    let filterCalls = 0;
    window.album = {
        showFavoritesOnly: false,
        applyFilter() {
            filterCalls += 1;
        }
    };
    const button = environment.element('__favorite_toggle_button__');

    context.toggleFavorites.call(button);

    assert.equal(window.album.showFavoritesOnly, true);
    assert.equal(button.hasClass('active'), true);
    assert.equal(filterCalls, 1);

    context.toggleFavorites.call(button);

    assert.equal(window.album.showFavoritesOnly, false);
    assert.equal(button.hasClass('active'), false);
    assert.equal(filterCalls, 2);
});
