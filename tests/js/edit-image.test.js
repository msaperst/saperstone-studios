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


test('successful upload prepares a narrow site image for arrangement', () => {
    const {context, environment} = createContext('/index.php');
    const editControl = environment.element('.editme');
    const holder = environment.element('__editable_holder__').addClass('horizontal');
    const column = environment.element('__editable_column__').attr('class', 'col-md-6');
    const img = holder.find('img').attr('src', '/img/main/portraits.jpg');

    editControl.parentResult = holder;
    holder.parentResult = column;
    environment.$.blockUI = () => {};
    environment.$.unblockUI = () => {};

    let arranged = null;
    context.arrangeImage = (image, scale) => {
        arranged = {image, scale};
    };

    environment.runReady();
    editControl.uploadOptions.onSuccess(['flower.jpeg'], '');

    assert.equal(arranged.image, img);
    assert.equal(arranged.scale, 2 / 3);
});

test('successful upload prepares a full-width site image for cropping', () => {
    const {context, environment} = createContext('/index.php');
    const editControl = environment.element('.editme');
    const holder = environment.element('__editable_holder__').addClass('horizontal');
    const column = environment.element('__editable_column__').attr('class', 'col-md-12');
    const img = holder.find('img').attr('src', '/img/main/portraits.jpg');

    editControl.parentResult = holder;
    holder.parentResult = column;
    environment.$.blockUI = () => {};
    environment.$.unblockUI = () => {};

    let cropped = null;
    context.cropImage = (image) => {
        cropped = image;
    };

    environment.runReady();
    editControl.uploadOptions.onSuccess(['flower.jpeg'], '');

    assert.equal(cropped, img);
});

test('saving an edited image posts crop coordinates and restores the normal image state', () => {
    const {context, environment} = createContext('/index.php');
    const img = environment.element('__img__')
        .attr('src', '/img/main/tmp_portraits.jpg?42')
        .css('top', '-10px');
    img.widthValue = 600;

    const parent = environment.element('__parent__')
        .attr('section', 'Portraits')
        .attr('link', '/portrait/index.php')
        .css('height', '400px');
    img.parentResult = parent;

    const save = parent.find('.saveme');
    const saveButton = parent.find('.saveme button');
    const watermark = parent.find('.watermark');
    environment.queuePost('/api/crop-image.php', {type: 'success', data: ''});
    context.randomImgNumber = () => 12345;

    context.saveImg(img);

    assert.deepEqual(environment.calls.post[0], {
        url: '/api/crop-image.php',
        data: {
            image: '..//img/main/tmp_portraits.jpg',
            top: 10,
            bottom: 410,
            'max-width': 600
        }
    });
    assert.equal(saveButton.properties.get('disabled'), true);
    assert.equal(save.removed, true);
    assert.equal(watermark.removed, true);
    assert.equal(img.attr('src'), '/img/main/portraits.jpg?12345');
    assert.equal(img.draggableOptions, 'destroy');
    assert.equal(parent.hasClass('hovereffect'), true);
    assert.equal(parent.styles.height, '');
    assert.equal(parent.styles.cursor, '');
});


test('failed image save shows the crop error and allows the user to retry', () => {
    const {context, environment} = createContext('/index.php');
    const img = environment.element('__failed_img__')
        .attr('src', '/img/main/tmp_portraits.jpg?42')
        .css('top', '-10px');
    img.widthValue = 600;

    const parent = environment.element('__failed_parent__').css('height', '400px');
    img.parentResult = parent;
    const saveButton = parent.find('.saveme button');

    environment.queuePost('/api/crop-image.php', {
        type: 'failure',
        xhr: {responseText: 'Cropped image is smaller than the required image'},
        error: 'Bad Request'
    });

    context.saveImg(img);

    assert.equal(saveButton.properties.get('disabled'), false);
    assert.equal(environment.dialogs.length, 1);
    assert.equal(environment.dialogs[0].title, 'Whoops, Something Went Wrong');
    assert.equal(environment.dialogs[0].message, 'Cropped image is smaller than the required image');
    assert.equal(img.attr('src'), '/img/main/tmp_portraits.jpg?42');
});
