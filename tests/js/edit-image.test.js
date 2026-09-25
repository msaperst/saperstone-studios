const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext(pathname = '/portrait/gallery.php') {
    const environment = createJQueryEnvironment({autoReady: false});
    const context = loadBrowserScript('public/js/edit-image.js', {
        $: environment.$, document: environment.document,
        window: {location: {pathname}}, Math,
        BootstrapDialog: environment.BootstrapDialog
    });
    return {context, environment};
}

test('edit image derives the section folder from nested URLs', () => {
    assert.equal(createContext('/portrait/gallery.php').context.folder, '/portrait');
    assert.equal(createContext('/index.php').context.folder, '');
});

test('random image number stays within cache-busting range', () => {
    const {context} = createContext();
    for (let i = 0; i < 20; i++) {
        const value = context.randomImgNumber();
        assert.ok(value >= 0 && value < 100000000);
    }
});

test('arrangeImage cleans image then forces aspect-ratio height', () => {
    const {context, environment} = createContext();
    const img = environment.element('__img__');
    const parent = environment.element('__parent__');
    img.parentResult = parent;
    parent.widthValue = 300;
    let cleaned = false;
    context.cleanImage = () => { cleaned = true; };

    context.arrangeImage(img, 2 / 3);

    assert.equal(cleaned, true);
    assert.equal(parent.styles.height, '200px');
});

test('cropImage enables south-handle resizing after cleanup', () => {
    const {context, environment} = createContext();
    const img = environment.element('__img__');
    const parent = environment.element('__parent__');
    img.parentResult = parent;
    context.cleanImage = () => {};

    context.cropImage(img);

    assert.deepEqual(parent.resizableOptions, {handles: 's'});
});
