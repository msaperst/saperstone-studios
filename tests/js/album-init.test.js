const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('album init restores flags, creates album, and updates sticky breadcrumb', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#album-page-config').attr({'data-can-download':'true','data-show-title':'false','data-album-id':'abc','data-total':'5'});
    const breadcrumb = environment.element('.breadcrumb'); breadcrumb.offsetValue = {top: 120, left: 0};
    const navbar = environment.element('.navbar-fixed-top'); navbar.outerHeight = () => 60;
    environment.element('.page-header').widthValue = 900;
    const window = {};
    const windowElement = environment.element('[object Object]'); windowElement.scrollTop = () => 100;
    let album;
    function Album(id, columns, total) { assert.equal(id, 'abc'); assert.equal(columns, 4); assert.equal(total, 5); album = this; this.loadImages = () => { this.loads = (this.loads || 0) + 1; }; }
    loadBrowserScript('public/js/album-init.js', {$: environment.$, document: environment.document, window, Album, Math});
    environment.runReady();
    assert.equal(window.albumCanDownload, true); assert.equal(window.showImageTitle, false); assert.equal(window.album, album);
    windowElement.trigger('scroll resize');
    assert.equal(album.loads, 1); assert.equal(breadcrumb.hasClass('breadcrumb-fixed'), true); assert.equal(breadcrumb.styles.top, '80px');
    windowElement.scrollTop = () => 0; windowElement.trigger('scroll resize');
    assert.equal(breadcrumb.hasClass('breadcrumb-fixed'), false);
});

test('album init exits without page configuration', () => {
    const environment = createJQueryEnvironment({autoReady: false, lengths: {'#album-page-config': 0}});
    let calls = 0;
    loadBrowserScript('public/js/album-init.js', {$: environment.$, document: environment.document, window: {}, Album: function () { calls += 1; }, Math});
    environment.runReady();
    assert.equal(calls, 0);
});
