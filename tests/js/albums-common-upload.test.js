const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createUploadContext(options = {}) {
    const environment = createJQueryEnvironment({
        lengths: {
            '#albums': options.hasAlbums === false ? 0 : 1
        }
    });
    const window = {
        album: options.album ?? null
    };
    const context = loadBrowserScript('public/js/albums-common.js', {
        $: environment.$,
        jQuery: environment.$,
        window,
        document: environment.document,
        BootstrapDialog: environment.BootstrapDialog,
        setTimeout: environment.setTimeout,
        setInterval: environment.setInterval,
        clearInterval: environment.clearInterval,
        album_table: environment.dataTable.instance,
        refreshAlbumThumbnailImages() {}
    });

    return {context, environment, window};
}

function configure(options = {}) {
    const result = createUploadContext(options);
    const dialog = result.environment.createDialog();
    result.context.configureAlbumImageUpload(options.id ?? 22, dialog);
    return {
        ...result,
        dialog,
        upload: result.environment.uploadOptions
    };
}

test('albums-common.js upload submit state disables the dialog until uploads finish', () => {
    const {dialog, environment, upload} = configure();
    const uploadContainer = environment.element('.ajax-file-upload-container').hide();
    const icon = dialog.$modalFooter.find('span.glyphicon').addClass('glyphicon-upload');
    const addImagesButton = dialog.$modalFooter.find('#add-images-button');

    upload.onSubmit();

    assert.equal(uploadContainer.visible, true);
    assert.equal(icon.hasClass('glyphicon-upload'), false);
    assert.equal(icon.hasClass('glyphicon-asterisk'), true);
    assert.equal(icon.hasClass('icon-spin'), true);
    assert.equal(addImagesButton.hasClass('disabled'), true);
    assert.equal(addImagesButton.prop('disabled'), true);
    assert.equal(addImagesButton.css('cursor'), 'not-allowed');
    assert.equal(addImagesButton.css('pointer-events'), 'none');
    assert.equal(dialog.buttonsEnabled, false);
    assert.equal(dialog.closable, false);
});

test('albums-common.js upload success shows thumbnail warning and removes its status row later', () => {
    const {environment, upload} = configure();
    const warning = environment.element('#thumbnail-warning').hide();
    const statusbar = environment.element('__upload_statusbar__');

    upload.onSuccess([], {}, null, {statusbar});

    assert.equal(warning.visible, true);
    assert.equal(statusbar.removed, false);

    environment.runTimeouts();

    assert.equal(statusbar.removed, true);
});

test('albums-common.js upload completion refreshes the matching open album and restores the dialog', () => {
    const refreshCalls = [];
    const liveAlbum = {
        albumId: '22',
        refreshImages(count) {
            refreshCalls.push(count);
        }
    };
    const {dialog, environment, upload} = configure({album: liveAlbum, id: 22});
    const uploadContainer = environment.element('.ajax-file-upload-container').show();
    const icon = dialog.$modalFooter.find('span.glyphicon').addClass('glyphicon-asterisk icon-spin');

    upload.onSubmit();
    environment.queueGet('/api/get-album.php', {
        type: 'success',
        data: {
            imageCount: 7
        }
    });

    upload.afterUploadAll();

    assert.equal(environment.calls.reload.length, 1);
    assert.deepEqual(plain(environment.calls.get.at(-1)), {
        url: '/api/get-album.php',
        data: {
            id: 22
        }
    });
    assert.deepEqual(refreshCalls, [7]);
    assert.equal(icon.hasClass('glyphicon-asterisk'), false);
    assert.equal(icon.hasClass('icon-spin'), false);
    assert.equal(icon.hasClass('glyphicon-upload'), true);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
    assert.equal(dialog.$modalFooter.find('#add-images-button').prop('disabled'), false);

    environment.runTimeouts();

    assert.equal(uploadContainer.visible, false);
});

test('albums-common.js upload completion does not refresh a different open album', () => {
    const refreshCalls = [];
    const liveAlbum = {
        albumId: '99',
        refreshImages(count) {
            refreshCalls.push(count);
        }
    };
    const {environment, upload} = configure({album: liveAlbum, id: 22});

    upload.afterUploadAll();

    assert.equal(environment.calls.reload.length, 1);
    assert.equal(environment.calls.get.length, 0);
    assert.deepEqual(refreshCalls, []);
});

test('albums-common.js upload completion works without an album table or active album', () => {
    const {environment, upload} = configure({
        hasAlbums: false,
        album: null,
        id: 22
    });

    upload.afterUploadAll();

    assert.equal(environment.calls.reload.length, 0);
    assert.equal(environment.calls.get.length, 0);
});
