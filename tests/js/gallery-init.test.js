const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('gallery init constructs gallery, lazy loads, and wires controls', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#gallery-config').attr({'data-total':'4','data-gallery-id':'9','data-modal-id':'family-gallery'});
    environment.element('footer').isOnScreen = () => true;
    let instance;
    function Gallery(id, modalId, total) {
        assert.equal(id, 9); assert.equal(modalId, 'family-gallery'); assert.equal(total, 4);
        instance = this; this.loaded = 0; this.loadCalls = 0; this.prevCalls = 0; this.nextCalls = 0;
        this.loadImages = () => { this.loadCalls += 1; this.loaded += 1; };
        this.prev = () => { this.prevCalls += 1; };
        this.next = () => { this.nextCalls += 1; };
    }
    const window = {};
    loadBrowserScript('public/js/gallery-init.js', {$: environment.$, document: environment.document, window, Gallery});
    environment.runReady();
    assert.equal(window.gallery, instance);
    assert.equal(environment.element('#family-gallery').carouselOptions.interval, false);
    assert.equal(environment.element('#family-gallery').carouselOptions.pause, 'false');
    environment.element('[object Object]').trigger('scroll resize');
    assert.equal(instance.loadCalls, 1);
    environment.element('.gallery-prev').trigger('click');
    environment.element('.gallery-next').trigger('click');
    assert.equal(instance.prevCalls, 1); assert.equal(instance.nextCalls, 1);
});

test('gallery init exits without page configuration', () => {
    const environment = createJQueryEnvironment({autoReady: false, lengths: {'#gallery-config': 0}});
    let calls = 0;
    loadBrowserScript('public/js/gallery-init.js', {$: environment.$, document: environment.document, window: {}, Gallery: function () { calls += 1; }});
    environment.runReady();
    assert.equal(calls, 0);
});
