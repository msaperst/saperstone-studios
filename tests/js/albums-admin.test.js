const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./helpers/album-test-utils');
const {
    registerCreateAlbumTests,
    registerSharedAlbumManagementTests
} = require('./helpers/shared-album-tests');

const scriptPath = 'public/js/albums-admin.js';

registerCreateAlbumTests('albums-admin.js', scriptPath);
registerSharedAlbumManagementTests('albums-admin.js', scriptPath);

test('albums-admin.js opens the shared create album dialog', () => {
    const {environment} = createAlbumContext(scriptPath);

    environment.element('#add-album-btn').trigger('click');

    assert.equal(environment.dialogs.at(-1).title, 'Add A New Album');
});

test('albums-admin.js configures admin columns and thumbnail filtering', () => {
    const {environment} = createAlbumContext(scriptPath);
    const config = environment.dataTable.config;

    assert.equal(config.ajax, '/api/get-albums.php');
    assert.equal(JSON.stringify(config.order), JSON.stringify([[1, 'asc']]));
    assert.equal(config.columnDefs.length, 8);
    assert.equal(config.columnDefs[5].className, 'album-thumbnails');

    environment.element('#thumbnail-status-filter').val('Missing').trigger('change');
    assert.deepEqual(environment.calls.columnSearch[0], {
        index: 5,
        value: 'Missing'
    });
});

test('albums-admin.js wires edit and activity buttons to their album id', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    const editCalls = [];
    const logCalls = [];
    context.editAlbum = (id) => editCalls.push(id);
    context.viewLogs = (id) => logCalls.push(id);

    const editRow = environment.element('__edit_row__').attr('album-id', '17');
    const logRow = environment.element('__log_row__').attr('album-id', '18');
    environment.element('.edit-album-btn').closestResult = editRow;
    environment.element('.view-album-log-btn').closestResult = logRow;

    context.setupEdit();
    environment.element('.edit-album-btn').trigger('click');
    environment.element('.view-album-log-btn').trigger('click');

    assert.deepEqual(editCalls, ['17']);
    assert.deepEqual(logCalls, ['18']);
});

test('albums-admin.js edit dialog includes admin-only access and code controls', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    environment.queueGet('/api/get-album.php', {
        type: 'success',
        data: {
            name: 'Admin Album',
            description: 'Description',
            date: '2026-09-22',
            code: 'CODE123',
            imageCount: 2,
            needsThumbnails: false
        }
    });

    context.editAlbum(21);

    const config = environment.dialogs.at(-1);
    assert.equal(config.title, 'Edit Album <b>Admin Album</b>');
    assert.match(config.message(), /Album Code/);
    assert.match(config.message(), /CODE123/);
    assert.deepEqual(
        Array.from(config.buttons, (button) => button.label.trim()),
        ['Set Access', 'Delete Album', 'Make Thumbnails', 'Save Details', 'Close']
    );
});

test('albums-admin.js configures album image uploads for the edited album', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    environment.queueGet('/api/get-album.php', {
        type: 'success',
        data: {
            name: 'Admin Album',
            description: '',
            date: '2026-09-22',
            code: '',
            imageCount: 1,
            needsThumbnails: true
        }
    });

    context.editAlbum(22);
    const config = environment.dialogs.at(-1);
    const dialog = environment.createDialog();
    config.onshown(dialog);

    assert.equal(environment.uploadOptions.url, '/api/upload-album-images.php');
    assert.equal(environment.uploadOptions.formData.album, 22);
    assert.equal(environment.uploadOptions.multiple, true);
    assert.equal(environment.uploadOptions.sequential, true);
});
