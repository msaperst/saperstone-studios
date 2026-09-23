const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumDetailContext} = require('./helpers/album-detail-test-utils');

function createInitializedLazyAlbum(options = {}) {
    const cardSelector = options.cardSelector || '__lazy_edge_card__';
    const lengths = {
        '#album-grid .album-card img[data-src]': 1,
        ...(options.lengths || {})
    };
    const {context, environment} = createAlbumDetailContext({
        lengths,
        innerHeight: options.innerHeight ?? 800
    });
    const image = environment.element('#album-grid .album-card img[data-src]');
    const card = environment.element(cardSelector);
    const media = environment.element('__lazy_edge_media__');

    card.rect = options.rect || {top: 100, bottom: 350, width: 300, height: 250};
    card.attr('data-location', '/albums/sample/thumbs/lazy-edge.jpg');
    card.attr('data-loaded', options.loaded ?? '0');
    card.attr('data-loading', options.loading ?? '0');
    card.find = (selector) => selector === '.album-card-media'
        ? media
        : environment.element('__lazy_edge_find__');

    image.closestResult = card;
    image.attr('src', 'placeholder');
    image.attr('data-src', '/img/image.png');
    image.nativeElement.complete = options.complete ?? false;
    image.nativeElement.naturalWidth = options.naturalWidth ?? 0;

    const album = Object.create(context.Album.prototype);
    Object.assign(album, {
        initialized: true,
        loaded: options.albumLoaded ?? 0,
        loading: false,
        images: [],
        totalImages: 1
    });

    return {album, card, context, environment, image, media};
}

test('album.js refreshImages keeps current metadata when the cache already matches the total', () => {
    const {context} = createAlbumDetailContext();
    let loadCalls = 0;
    const album = Object.create(context.Album.prototype);
    Object.assign(album, {
        images: [{sequence: '10'}, {sequence: '11'}],
        totalImages: 2,
        loaded: 2,
        initialized: true,
        loading: false,
        loadImages() {
            loadCalls += 1;
            return this.loaded;
        }
    });

    const count = album.refreshImages(2);

    assert.equal(count, 2);
    assert.equal(album.totalImages, 2);
    assert.equal(album.loaded, 2);
    assert.equal(album.initialized, true);
    assert.equal(album.loading, false);
    assert.equal(loadCalls, 1);
});

test('album.js skips cards whose real thumbnail is already loaded', () => {
    const {album, card, image, media} = createInitializedLazyAlbum({
        loaded: '1',
        albumLoaded: 3
    });

    album.loadImages();

    assert.equal(album.loaded, 3);
    assert.equal(image.attr('src'), 'placeholder');
    assert.equal(media.css('background-image'), undefined);
    assert.equal(card.attr('data-loaded'), '1');
});

test('album.js ignores lazy image nodes that are not inside an album card', () => {
    const {album, environment, image} = createInitializedLazyAlbum();
    const missingCard = environment.element('__missing_lazy_card__');
    missingCard.length = 0;
    image.closestResult = missingCard;

    album.loadImages();

    assert.equal(album.loaded, 0);
    assert.equal(image.attr('src'), 'placeholder');
});

test('album.js marks a lazy card loaded when the placeholder load event fires', () => {
    const {album, card, image, media} = createInitializedLazyAlbum();

    album.loadImages();

    assert.equal(card.attr('data-loading'), '1');
    assert.equal(card.attr('data-loaded'), '0');
    assert.equal(media.css('background-image'), 'url("/albums/sample/thumbs/lazy-edge.jpg")');

    image.trigger('load');

    assert.equal(card.attr('data-loading'), '0');
    assert.equal(card.attr('data-loaded'), '1');
    assert.equal(card.hasClass('is-loaded'), true);
});

test('album.js immediately marks cached placeholder images loaded', () => {
    const {album, card, image} = createInitializedLazyAlbum({
        complete: true,
        naturalWidth: 32
    });

    album.loadImages();

    assert.equal(image.attr('src'), '/img/image.png');
    assert.equal(card.attr('data-loading'), '0');
    assert.equal(card.attr('data-loaded'), '1');
    assert.equal(card.hasClass('is-loaded'), true);
    assert.equal(album.loaded, 1);
});
