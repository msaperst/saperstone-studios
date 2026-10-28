const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./helpers/album-test-utils');

const scriptPath = 'public/js/album-cover-picker.js';

test('cover picker loads existing images through a manager-only API', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.openAlbumCoverPicker(27);
    const config = environment.dialogs.at(-1);
    assert.equal(config.title, 'Choose Album Cover');
    environment.queueGet('/api/get-cover-candidates.php', {
        type: 'success',
        data: {candidates: [], nextStart: null}
    });
    config.onshown(environment.createDialog());
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.get.at(-1))), {
        url: '/api/get-cover-candidates.php',
        data: {album: 27, start: 0, count: 36}
    });
});

test('cover picker previews only authorized derivative endpoints and saves photo identity', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.openAlbumCoverPicker(27);
    const config = environment.dialogs.at(-1);
    const dialog = environment.createDialog();
    environment.queueGet('/api/get-cover-candidates.php', {
        type: 'success',
        data: {
            candidates: [{
                id: 211,
                album: 27,
                galleryName: 'Bimah',
                preview: '/api/get-album-cover.php?album=27&candidate=211'
            }],
            nextStart: null
        }
    });
    config.onshown(dialog);
    const buttons = environment.element('#album-cover-options').appended;
    assert.equal(buttons.length, 1);
    assert.match(buttons[0].css('background-image'), /api\\/get-album-cover.php/);
    assert.doesNotMatch(buttons[0].css('background-image'), /\\/albums\\/.*\\.jpg/);
    environment.queuePost('/api/set-album-cover.php', {type: 'success', data: ''});
    buttons[0].trigger('click');
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post.at(-1))), {
        url: '/api/set-album-cover.php',
        data: {album: 27, image: 211}
    });
    assert.equal(dialog.closed, true);
});

test('cover picker can restore automatic cover selection without deleting any image', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.openAlbumCoverPicker(27);
    const config = environment.dialogs.at(-1);
    environment.queuePost('/api/set-album-cover.php', {type: 'success', data: ''});
    config.buttons[0].action(environment.createDialog());
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post.at(-1))), {
        url: '/api/set-album-cover.php',
        data: {album: 27, image: ''}
    });
});
