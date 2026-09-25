const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createGalleryContext(hash = '') {
    const environment = createJQueryEnvironment({autoReady: false});
    const windowObject = {innerHeight: 800, location: {hash}};
    const context = loadBrowserScript('public/js/gallery.js', {
        $: environment.$, document: environment.document, window: windowObject
    });
    return {context, environment, windowObject};
}

test('isOnScreen detects elements intersecting the viewport', () => {
    const {environment} = createGalleryContext();
    const item = environment.element('__item__');
    item.rect = {top: 10, bottom: 100, width: 100, height: 90};
    assert.equal(item.isOnScreen(), true);
    item.rect = {top: 900, bottom: 1000, width: 100, height: 100};
    assert.equal(item.isOnScreen(), false);
});

test('gallery previous and next wrap at the image boundaries', () => {
    const {context, windowObject} = createGalleryContext('#0');
    const gallery = Object.create(context.Gallery.prototype);
    gallery.totalImages = 3;

    gallery.prev();
    assert.equal(windowObject.location.hash, '#2');
    gallery.next();
    assert.equal(windowObject.location.hash, '#0');
});

test('setImage opens the modal, disables autoplay, and selects requested slide', () => {
    const {context, environment} = createGalleryContext();
    const gallery = Object.create(context.Gallery.prototype);
    gallery.gallery = 'gallery-modal';
    const modal = environment.element('#gallery-modal');
    modal.carousel = function (value) { this.carouselCalls = this.carouselCalls || []; this.carouselCalls.push(value); return this; };

    gallery.setImage('2');

    assert.deepEqual(modal.modalCalls, ['show']);
    assert.deepEqual(modal.carouselCalls, [{interval: false, pause: 'false'}, 2]);
});

test('loadImages requests the next batch and advances loaded count', () => {
    const {context, environment} = createGalleryContext();
    const gallery = Object.create(context.Gallery.prototype);
    gallery.gallery_id = 9;
    gallery.loaded = 4;
    gallery.totalImages = 20;
    environment.queueGet('/api/get-gallery-images.php', {type: 'success', data: []});
    environment.element('footer').isOnScreen = () => false;

    const loaded = gallery.loadImages(4);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.get[0])), {
        url: '/api/get-gallery-images.php',
        data: {gallery: 9, start: 4, howMany: 4}
    });
    assert.equal(loaded, 4);
});
