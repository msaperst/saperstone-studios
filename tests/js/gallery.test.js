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
    assert.equal(modal.carouselCalls.length, 2);
    assert.equal(modal.carouselCalls[0].interval, false);
    assert.equal(modal.carouselCalls[0].pause, 'false');
    assert.equal(modal.carouselCalls[1], 2);
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


test('gallery rename updates the visible page heading after save succeeds', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const windowObject = {location: {search: '?w=999'}};
    environment.queueGet('/api/get-gallery.php', {
        type: 'success',
        data: {title: 'Gallery 999'}
    });
    environment.queuePost('/api/update-gallery.php', {type: 'success', data: ''});

    const context = loadBrowserScript('public/js/gallery-admin.js', {
        $: environment.$,
        document: environment.document,
        window: windowObject,
        BootstrapDialog: environment.BootstrapDialog,
        gallery: {loadImages() {}},
        total: 4,
        loaded: 4
    });

    context.editGallery(999);
    environment.element('#new-gallery-title').val('Updated Gallery');

    const dialogConfig = environment.dialogs[0];
    const dialog = environment.createDialog();
    dialogConfig.buttons[0].action.call(environment.createButton(), dialog);

    assert.equal(environment.element('.page-header').text(), 'Updated Gallery Gallery');
    assert.equal(dialog.closed, true);
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-gallery.php',
        data: {id: 999, title: 'Updated Gallery'}
    });
});


test('successful gallery upload updates gallery count and dynamically loads the new image', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.queueGet('/api/get-gallery.php', {
        type: 'success',
        data: {title: 'Gallery 999'}
    });
    const gallery = {
        totalImages: 4,
        loadImagesCalls: [],
        loadImages(howMany) {
            this.loadImagesCalls.push(howMany);
            return 5;
        }
    };

    const context = loadBrowserScript('public/js/gallery-admin.js', {
        $: environment.$,
        document: environment.document,
        window: {location: {search: '?w=999'}},
        BootstrapDialog: environment.BootstrapDialog,
        gallery
    });

    context.editGallery(999);
    const dialogConfig = environment.dialogs[0];
    const dialog = environment.createDialog();
    dialogConfig.onshown(dialog);

    const statusbar = environment.element('__statusbar__');
    environment.uploadOptions.onSuccess(
        ['flower.jpeg'],
        JSON.stringify(['flower.jpeg']),
        {},
        {statusbar}
    );

    assert.equal(gallery.totalImages, 5);
    assert.deepEqual(gallery.loadImagesCalls, [1]);
    assert.equal(statusbar.removed, true);
});


test('gallery constructor loads images, honors initial hash, and clears hash when modal closes', () => {
    const {context, environment, windowObject} = createGalleryContext('#3');
    const calls = [];
    context.Gallery.prototype.loadImages = function () {
        calls.push('load');
    };
    context.Gallery.prototype.setImage = function (image) {
        calls.push('image:' + image);
    };

    const gallery = new context.Gallery(7, 'gallery-modal', 12);

    assert.equal(gallery.gallery_id, 7);
    assert.equal(gallery.gallery, 'gallery-modal');
    assert.equal(gallery.totalImages, 12);
    assert.deepEqual(calls, ['load', 'image:3']);

    windowObject.location.hash = '#4';
    windowObject.onhashchange();
    assert.deepEqual(calls, ['load', 'image:3', 'image:4']);

    environment.element('#gallery-modal').trigger('hide.bs.modal');
    assert.equal(windowObject.location.hash, '');
});

test('gallery loadImages builds image cards in the shortest column and corrects partial batch count', () => {
    const {context, environment} = createGalleryContext();
    const gallery = Object.create(context.Gallery.prototype);
    gallery.gallery_id = 9;
    gallery.loaded = 0;
    gallery.totalImages = 10;

    const column = environment.element('.col-gallery');
    column.heightValue = 40;
    column.rect = {top: 0, bottom: 100, left: 0, right: 400, width: 400, height: 100};
    column.css('padding-left', '10px');
    column.css('padding-right', '10px');
    environment.element('footer').isOnScreen = () => false;

    environment.queueGet('/api/get-gallery-images.php', {
        type: 'success',
        data: [{
            id: 101,
            sequence: 2,
            title: 'Portrait',
            location: '/img/portrait.jpg',
            width: 1200,
            height: 600
        }]
    });

    const loaded = gallery.loadImages(4);

    assert.equal(loaded, 1);
    assert.equal(gallery.loaded, 1);
    assert.equal(column.appended.length, 1);

    const holder = column.appended[0];
    assert.equal(holder.hasClass('gallery'), true);
    assert.equal(holder.hasClass('hovereffect'), true);
    assert.equal(holder.attr('image-id'), '101');
    assert.equal(holder.attr('sequence'), '2');
    assert.equal(holder.height(), 190);

    const image = holder.appended[0];
    const overlay = holder.appended[1];
    assert.equal(image.attr('src'), '/img/portrait.jpg');
    assert.equal(image.attr('alt'), 'Portrait');
    assert.equal(image.attr('width'), '100%');
    assert.equal(overlay.hasClass('overlay'), true);
    const link = overlay.appended[0];
    assert.equal(link.attr('href'), '#2');
    assert.equal(link.hasClass('info'), true);
    assert.equal(link.appended[0].hasClass('fa-search'), true);
});

test('gallery loadImages falls back to right-minus-left width for older browser rectangles', () => {
    const {context, environment} = createGalleryContext();
    const gallery = Object.create(context.Gallery.prototype);
    gallery.gallery_id = 5;
    gallery.loaded = 0;
    gallery.totalImages = 1;

    const column = environment.element('.col-gallery');
    column.rect = {top: 0, bottom: 100, left: 20, right: 420, width: 0, height: 100};
    column.css('padding-left', '0px');
    column.css('padding-right', '0px');
    environment.element('footer').isOnScreen = () => false;
    environment.queueGet('/api/get-gallery-images.php', {
        type: 'success',
        data: [{
            id: 1,
            sequence: 0,
            title: 'Legacy',
            location: '/img/legacy.jpg',
            width: 800,
            height: 400
        }]
    });

    gallery.loadImages(4);

    assert.equal(column.appended[0].height(), 200);
    assert.equal(gallery.loaded, 1);
});
