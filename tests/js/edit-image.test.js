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

    assert.equal(parent.resizableOptions.handles, 's');
});

test('failed image upload unblocks the page and displays the API response', () => {
    const {environment} = createContext('/index.php');
    const editControl = environment.element('.editme');
    const holder = environment.element('__editable_holder__').addClass('horizontal');
    const column = environment.element('__editable_column__').attr('class', 'col-md-6');
    editControl.parentResult = holder;
    holder.parentResult = column;
    holder.find('img').attr('src', '/img/main/portrait.jpg');

    let unblocked = 0;
    environment.$.blockUI = () => {};
    environment.$.unblockUI = () => { unblocked += 1; };
    environment.runReady();

    const options = editControl.uploadOptions;
    assert.equal(typeof options.onError, 'function');
    options.onError([], 'error', 'Bad Request', {}, {responseText: 'Image does not meet minimum width'});

    assert.equal(unblocked, 1);
    assert.equal(environment.dialogs.length, 1);
    assert.equal(environment.dialogs[0].message, 'Image does not meet minimum width');
});
