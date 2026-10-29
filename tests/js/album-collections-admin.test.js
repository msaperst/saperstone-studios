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

test('collection editor has no collection-level cover picker for either management role', () => {
    const {context, environment} = createAlbumContext(scriptPath, {myId: 4});
    const data = {
        id: 27, name: 'Family Event', description: '', date: '2026-10-28',
        children: [], owner: 4, shared: false
    };
    for (const role of ['admin', 'uploader']) {
        context.showEditCollectionDialog(data, role);
        const actions = environment.dialogs.at(-1).buttons.map(item => item.label.trim());
        assert.equal(actions.includes('Choose Cover'), false);
        assert.deepEqual(actions, ['Add New Child', 'Attach Existing Album', 'Delete Collection', 'Save Details', 'Close']);
    }
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
    assert.equal(environment.dialogs.at(-1).cssClass, 'album-management-dialog');
    const message = environment.dialogs.at(-1).message();
    assert.doesNotMatch(message, /collection-code|Access Code/);
});

test('collection page edit control invokes the same editor as the albums table', () => {
    const {environment} = createAlbumContext(scriptPath, {lengths: {'#albums': 0}});
    environment.element('#edit-collection-btn').attr('data-collection-id', '27');
    environment.queueGet('/api/get-collection.php', {
        type: 'success',
        data: {id: 27, name: 'Family Event', description: 'Before',
            date: '', code: 'EVENT', owner: 1, shared: true, children: []}
    });
    environment.element('#edit-collection-btn').trigger('click');
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.get.at(-1))), {
        url: '/api/get-collection.php',
        data: {id: 27}
    });
    const dialog = environment.dialogs.at(-1);
    assert.equal(dialog.title, 'Edit Collection');
    assert.equal(dialog.message().includes('collection-code'), true);
});

test('saving collection from its page refreshes the page without requiring DataTables', () => {
    const {context, environment, window} = createAlbumContext(scriptPath, {lengths: {'#albums': 0}});
    let refreshes = 0;
    window.location = {reload: () => { refreshes++; }};
    context.showEditCollectionDialog({
        id: 27, name: 'Family Event', description: 'Before',
        date: '', code: 'EVENT', owner: 1, shared: true, children: []
    }, 'admin');
    environment.element('#edit-collection-name').val('Family Event');
    environment.element('#edit-collection-description').val('After');
    environment.element('#edit-collection-date').val('');
    environment.element('#collection-code').val('EVENT');
    environment.queuePost('/api/update-collection.php', {type: 'success', data: ''});
    const config = environment.dialogs.at(-1);
    const save = config.buttons.find(button => button.label === 'Save Details');
    const button = environment.createButton('__save_collection_page__');
    button.closestResult = environment.element('__collection_page_modal__');
    save.action.call(button, environment.createDialog());
    assert.equal(environment.calls.post.at(-1).url, '/api/update-collection.php');
    assert.equal(environment.calls.post.at(-1).data.description, 'After');
    assert.equal(environment.calls.reload.length, 0);
    assert.equal(refreshes, 1);
});

test('deleting a collection from its page navigates back to albums, never to a deleted URL', () => {
    const {context, environment, window} = createAlbumContext(scriptPath, {lengths: {'#albums': 0}});
    const visited = [];
    window.location = {assign: path => visited.push(path)};
    context.confirmDeleteCollection({id: 27, name: 'Family Event', children: []});
    environment.queuePost('/api/delete-collection.php', {type: 'success', data: ''});
    const button = environment.createButton('__delete_collection__');
    button.closestResult = environment.element('__collection_delete_modal__');
    environment.dialogs.at(-1).buttons[0].action.call(button, environment.createDialog());
    assert.deepEqual(visited, ['/user/index.php']);
});

test('attaching an existing album on the collection page reuses authorized album listing API', () => {
    const {context, environment} = createAlbumContext(scriptPath, {lengths: {'#albums': 0}});
    environment.queueGet('/api/get-albums.php', {
        type: 'success',
        data: {data: [
            {id: 30, name: 'Standalone album'},
            {id: 31, name: 'Another collection', isCollection: true}
        ]}
    });
    context.openAttachAlbumDialog = context.openAttachAlbumDialog || undefined;
    context.openAttachAlbumDialog({id: 27, name: 'Family Event'});
    assert.equal(environment.calls.get.at(-1).url, '/api/get-albums.php');
    assert.equal(environment.dialogs.at(-1).title, 'Attach Existing Album');
});
