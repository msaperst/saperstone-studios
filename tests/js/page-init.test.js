const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('page init wires data-href navigation without inline handlers', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const link = environment.element('[data-href]').attr('data-href', '/portrait/galleries.php?w=1');
    const location = {href: ''};
    loadBrowserScript('public/js/page-init.js', {$: environment.$, document: environment.document, window: {location}});
    environment.runReady();
    link.trigger('click');
    assert.equal(location.href, '/portrait/galleries.php?w=1');
});

test('page init wires data-hash controls and carousel defaults', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const hash = environment.element('[data-hash]').attr('data-hash', '3');
    const location = {hash: ''};
    const carousel = environment.element('.carousel');
    carousel.carousel = function (options) { this.carouselOptions = options; return this; };
    loadBrowserScript('public/js/page-init.js', {$: environment.$, document: environment.document, window: {location}});
    environment.runReady();
    hash.trigger('click');
    assert.equal(location.hash, '3');
    assert.equal(carousel.carouselOptions.interval, 4000);
});
