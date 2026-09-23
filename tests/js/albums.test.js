const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./helpers/album-test-utils');
const {registerAddAlbumTests} = require('./helpers/shared-album-tests');

const scriptPath = 'public/js/albums.js';

registerAddAlbumTests('albums.js', scriptPath);

test('albums.js configures the album table and album links', () => {
    const {environment} = createAlbumContext(scriptPath);
    const config = environment.dataTable.config;

    assert.equal(config.ajax, '/api/get-albums.php');
    assert.equal(JSON.stringify(config.order), JSON.stringify([[0, 'asc']]));
    assert.equal(config.columnDefs.length, 4);
    assert.equal(
        config.columnDefs[0].data({id: 7, name: 'Sample'}),
        "<a href='album.php?album=7'>Sample</a>"
    );

    const row = environment.element('__album_row__');
    config.fnCreatedRow(row, {id: 7});
    assert.equal(row.attr('album-id'), '7');
});

test('albums.js submits an album code when Enter is pressed', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    let calls = 0;
    context.addAlbum = () => {
        calls += 1;
    };

    environment.element('#add-album-div').trigger('keypress', {which: 13});
    environment.element('#add-album-div').trigger('keypress', {which: 12});

    assert.equal(calls, 1);
});
