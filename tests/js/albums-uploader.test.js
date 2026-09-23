const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./helpers/album-test-utils');
const {
    registerAddAlbumTests,
    registerSharedAlbumManagementTests
} = require('./helpers/shared-album-tests');

const scriptPath = 'public/js/albums-uploader.js';

registerAddAlbumTests('albums-uploader.js', scriptPath);
registerSharedAlbumManagementTests('albums-uploader.js', scriptPath);

test('albums-uploader.js opens the shared create album dialog', () => {
    const {environment} = createAlbumContext(scriptPath);

    environment.element('#add-album-btn').trigger('click');

    assert.equal(environment.dialogs.at(-1).title, 'Add A New Album');
});

test('albums-uploader.js configures uploader columns and only edits owned albums', () => {
    const {environment} = createAlbumContext(scriptPath, {myId: 5});
    const config = environment.dataTable.config;

    assert.equal(config.ajax, '/api/get-albums.php');
    assert.equal(JSON.stringify(config.order), JSON.stringify([[1, 'asc']]));
    assert.equal(config.columnDefs.length, 6);
    assert.match(config.columnDefs[0].data({owner: 5}), /edit-album-btn/);
    assert.equal(config.columnDefs[0].data({owner: 6}), '');

    environment.element('#thumbnail-status-filter').val('Ready').trigger('change');
    assert.deepEqual(environment.calls.columnSearch[0], {
        index: 5,
        value: 'Ready'
    });
});

test('albums-uploader.js wires edit buttons to their album id', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    const editCalls = [];
    context.editAlbum = (id) => editCalls.push(id);

    const row = environment.element('__edit_row__').attr('album-id', '27');
    environment.element('.edit-album-btn').closestResult = row;

    context.setupEdit();
    environment.element('.edit-album-btn').trigger('click');

    assert.deepEqual(editCalls, ['27']);
});

test('albums-uploader.js edit dialog omits admin-only access and code controls', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    environment.queueGet('/api/get-album.php', {
        type: 'success',
        data: {
            name: 'Uploader Album',
            description: 'Description',
            date: '2026-09-22',
            code: 'SECRET',
            imageCount: 2,
            needsThumbnails: false
        }
    });

    context.editAlbum(28);

    const config = environment.dialogs.at(-1);
    const message = config.message();
    assert.equal(config.title, 'Edit Album <b>Uploader Album</b>');
    assert.doesNotMatch(message, /Album Code/);
    assert.doesNotMatch(message, /SECRET/);
    assert.deepEqual(
        Array.from(config.buttons, (button) => button.label.trim()),
        ['Delete Album', 'Make Thumbnails', 'Save Details', 'Close']
    );
});

test('albums-uploader.js configures album image uploads for the edited album', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    environment.queueGet('/api/get-album.php', {
        type: 'success',
        data: {
            name: 'Uploader Album',
            description: '',
            date: '2026-09-22',
            code: '',
            imageCount: 1,
            needsThumbnails: true
        }
    });

    context.editAlbum(29);
    const config = environment.dialogs.at(-1);
    const dialog = environment.createDialog();
    config.onshown(dialog);

    assert.equal(environment.uploadOptions.url, '/api/upload-album-images.php');
    assert.equal(environment.uploadOptions.formData.album, 29);
    assert.equal(environment.uploadOptions.multiple, true);
    assert.equal(environment.uploadOptions.sequential, true);
});
