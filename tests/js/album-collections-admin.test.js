const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./helpers/album-test-utils');

const scriptPath = 'public/js/album-collections-admin.js';

test('collection management opens the shared Bootstrap create dialog', () => {
    const {environment} = createAlbumContext(scriptPath);
    environment.element('#add-collection-btn').trigger('click');
    assert.equal(environment.dialogs.at(-1).title, 'Add A New Collection');
});

test('collection creation calls the API and refreshes the existing album list', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    environment.queuePost('/api/create-collection.php', {type: 'success', data: '27'});
    environment.element('#new-collection-name').val('Family Mitzvah');
    environment.element('#new-collection-description').val('Celebration');
    environment.element('#new-collection-date').val('2026-10-28');
    context.openCreateCollectionDialog();
    const config = environment.dialogs.at(-1);
    const button = environment.createButton('__create_collection__');
    button.closestResult = environment.element('__create_collection_modal__');
    config.buttons[0].action.call(button, environment.createDialog());
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post.at(-1))), {
        url: '/api/create-collection.php',
        data: {
            name: 'Family Mitzvah',
            description: 'Celebration',
            date: '2026-10-28'
        }
    });
    assert.equal(environment.calls.reload.length, 1);
});

test('only administrators can change membership of a shared collection', () => {
    const {context} = createAlbumContext(scriptPath);
    assert.equal(context.collectionCanOrganize({owner: 4, shared: false}, 'uploader', 4), true);
    assert.equal(context.collectionCanOrganize({owner: 4, shared: true}, 'uploader', 4), false);
    assert.equal(context.collectionCanOrganize({owner: 5, shared: false}, 'uploader', 4), false);
    assert.equal(context.collectionCanOrganize({owner: 5, shared: true}, 'admin', 1), true);
    assert.equal(context.collectionCanOrganize({owner: 4, shared: false}, 'downloader', 4), false);
});

test('deletion confirmation explicitly preserves child albums and warns about access', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.confirmDeleteCollection({
        id: 27,
        name: 'Family Event',
        children: [{id: 1}, {id: 2}, {id: 3}]
    });
    const config = environment.dialogs.at(-1);
    assert.match(config.message, /3 child albums/);
    assert.match(config.message, /remain as standalone albums/);
    assert.match(config.message, /inherited access/);
    assert.deepEqual(config.buttons.map(button => button.label), ['Delete Collection', 'Cancel']);
});

test('collection editor uses the same protected cover picker', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    const calls = [];
    context.openAlbumCoverPicker = id => calls.push(id);
    context.showEditCollectionDialog({
        id: 27, name: 'Family Event', description: '', date: '2026-10-28',
        children: [], owner: 4, shared: false
    }, 'admin');
    environment.dialogs.at(-1).buttons.find(item => item.label.trim() === 'Choose Cover')
        .action(environment.createDialog());
    assert.deepEqual(calls, [27]);
});

test('an uploader collection edit cannot contain access-code controls', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.showEditCollectionDialog({
        id: 27,
        name: 'Family Event',
        description: '',
        date: '2026-10-28',
        children: [],
        owner: 4,
        shared: false
    }, 'uploader');
    const message = environment.dialogs.at(-1).message();
    assert.doesNotMatch(message, /collection-code|Access Code/);
});
