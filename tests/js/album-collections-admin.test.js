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
        assert.deepEqual(actions, ['Add New Album', 'Attach Existing Album', 'Delete Collection', 'Save Details', 'Close']);
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



test('new child album from collection page attaches and refreshes without a DataTable', () => {
    const {context, environment, window} = createAlbumContext(scriptPath, {lengths: {'#albums': 0}});
    let reloads = 0;
    window.location = {reload: () => { reloads++; }};
    environment.queuePost('/api/create-album.php', {type: 'success', data: '31'});
    environment.queuePost('/api/set-album-parent.php', {type: 'success', data: ''});
    context.openCreateAlbumDialog(27);
    const config = environment.dialogs.at(-1);
    const button = environment.createButton('__create_child_on_collection_page__');
    button.closestResult = environment.element('__new_child_modal__');
    config.buttons[0].action.call(button, environment.createDialog());
    assert.deepEqual(environment.calls.post.map(call => call.url),
        ['/api/create-album.php', '/api/set-album-parent.php']);
    assert.equal(environment.calls.reload.length, 0);
    assert.equal(reloads, 1);
});

test('album attachment list API failure is shown without opening an empty picker', () => {
    const {context, environment} = createAlbumContext(scriptPath, {
        lengths: {'#albums': 0}
    });
    environment.queueGet('/api/get-albums.php', {
        type: 'failure', xhr: {responseText: 'Could not load albums'}
    });
    context.openAttachAlbumDialog({id: 27});
    assert.equal(environment.alerts.at(-1), 'Could not load albums');
    assert.equal(environment.dialogs.length, 0);
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
    context.openAttachAlbumDialog({id: 27, name: 'Family Event'});
    assert.equal(environment.calls.get.at(-1).url, '/api/get-albums.php');
    assert.equal(environment.dialogs.at(-1).title, 'Attach Existing Album');
});


test('collection editor lists children and shows a clear non-destructive remove action', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.showEditCollectionDialog({
        id: 27, name: 'Family Event', description: '', date: '',
        owner: 1, shared: true, children: [
            {id: 41, name: 'Bimah <Session>', imageCount: 7},
            {id: 42, name: 'Celebration', imageCount: 18}
        ]
    }, 'admin');
    const markup = environment.dialogs.at(-1).message();
    assert.match(markup, /Albums in this collection/);
    assert.match(markup, /Bimah &lt;Session&gt;/);
    assert.doesNotMatch(markup, /Bimah <Session>/);
    assert.match(markup, /data-child-id="41"/);
    assert.match(markup, /data-child-id="42"/);
    assert.match(markup, /Remove from collection/);
    assert.doesNotMatch(markup, /Delete Album/);
});

test('shared collection shows its children but no remove controls to an uploader', () => {
    const {context, environment} = createAlbumContext(scriptPath, {myId: 4});
    context.showEditCollectionDialog({
        id: 27, name: 'Shared Event', description: '', date: '',
        owner: 4, shared: true, children: [{id: 41, name: 'Bimah', imageCount: 7}]
    }, 'uploader');
    const markup = environment.dialogs.at(-1).message();
    assert.match(markup, /Bimah/);
    assert.doesNotMatch(markup, /collection-member-remove/);
});

test('collection removal confirmation explains lost inherited access and preserves children', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.confirmRemoveFromCollection({id: 27, name: 'Family Event'}, {
        id: 41, name: 'Bimah'
    }, () => {});
    const dialog = environment.dialogs.at(-1);
    assert.equal(dialog.title, 'Remove Album from Collection?');
    assert.match(dialog.message, /Bimah/);
    assert.match(dialog.message, /remain available as a standalone album/);
    assert.match(dialog.message, /inherited access/);
    assert.deepEqual(dialog.buttons.map(b => b.label), ['Remove from Collection', 'Cancel']);
});

test('confirmed collection removal reuses the membership API and reloads children', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    let refreshes = 0;
    context.confirmRemoveFromCollection({id: 27, name: 'Family Event'}, {
        id: 41, name: 'Bimah'
    }, () => { refreshes++; });
    environment.queuePost('/api/set-album-parent.php', {type: 'success', data: ''});
    const dialog = environment.createDialog();
    const button = environment.createButton('__confirm_detach__');
    button.closestResult = environment.element('__detach_dialog__');
    environment.dialogs.at(-1).buttons[0].action.call(button, dialog);
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post.at(-1))), {
        url: '/api/set-album-parent.php',
        data: {album: 41, parent: ''}
    });
    assert.equal(refreshes, 1);
});

test('failed collection removal leaves the child in place and shows an error', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    let refreshes = 0;
    context.confirmRemoveFromCollection({id: 27, name: 'Family Event'}, {
        id: 41, name: 'Bimah'
    }, () => { refreshes++; });
    environment.queuePost('/api/set-album-parent.php', {
        type: 'failure', xhr: {responseText: 'You cannot modify a shared collection'}
    });
    const button = environment.createButton('__failed_detach__');
    button.closestResult = environment.element('__detach_dialog__');
    environment.dialogs.at(-1).buttons[0].action.call(button, environment.createDialog());
    assert.equal(refreshes, 0);
    assert.equal(environment.calls.post.length, 1);
});

test('removing a member is wired from the shared editor dialog, not just a standalone helper', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    const calls = [];
    context.confirmRemoveFromCollection = (collection, child, callback) => {
        calls.push({collection: collection.id, child: child.id, hasCallback: typeof callback === 'function'});
    };
    context.showEditCollectionDialog({
        id: 27, name: 'Family Event', description: '', date: '',
        owner: 1, shared: false, children: [{id: 41, name: 'Bimah', imageCount: 7}]
    }, 'admin');
    const config = environment.dialogs.at(-1);
    const dialog = environment.createDialog();
    config.onshown(dialog);
    const handler = dialog.getModal().delegatedHandlers.get('click .collection-member-remove');
    assert.equal(typeof handler, 'function');
    const button = environment.createButton('__member_remove__');
    button.attr('data-child-id', '41');
    handler.call(button);
    assert.deepEqual(calls, [{collection: 27, child: 41, hasCallback: true}]);
});

test('collection editor names its creation action Add New Album', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    context.showEditCollectionDialog({
        id: 27, name: 'Mitzvah', description: '', date: '', owner: 1,
        shared: false, children: []
    }, 'admin');
    const labels = environment.dialogs.at(-1).buttons.map(b => b.label);
    assert.ok(labels.includes('Add New Album'));
    assert.ok(!labels.includes('Add New Child'));
});

test('collection save updates visible collection details without reloading page', () => {
    const {context, environment, window} = createAlbumContext(scriptPath, {lengths: {'#albums': 0, '#collection-page-name': 1}});
    let reloads = 0;
    window.location = {reload: () => { reloads++; }};
    environment.element('#collection-page-name').text('Before');
    context.showEditCollectionDialog({
        id: 27, name: 'Before', description: 'Old', date: '2026-01-01',
        owner: 1, shared: false, code: '', children: []
    }, 'admin');
    environment.element('#edit-collection-name').val('After');
    environment.element('#edit-collection-description').val('Updated description');
    environment.element('#edit-collection-date').val('2026-10-30');
    environment.element('#collection-code').val('');
    environment.queuePost('/api/update-collection.php', {type: 'success', data: ''});
    const config = environment.dialogs.at(-1);
    const button = environment.createButton('__save__');
    button.closestResult = environment.element('__save-modal__');
    config.buttons.find(b => b.label === 'Save Details').action.call(button, environment.createDialog());
    assert.equal(environment.element('#collection-page-name').text(), 'After');
    assert.equal(environment.element('#collection-page-description').text(), 'Updated description');
    assert.equal(environment.element('#collection-breadcrumb-name').text(), 'After');
    assert.equal(environment.element('#collection-page-date').text(), '2026-10-30');
    assert.equal(reloads, 0);
    assert.equal(environment.calls.reload.length, 0);
});

test('collection editor renders drag and keyboard controls only when user may organize it', () => {
    const {context, environment} = createAlbumContext(scriptPath, {myId: 4});
    const data = {id: 27, name: 'Mitzvah', owner: 4, shared: false,
        children: [{id: 41, name: 'Ceremony', imageCount: 1}, {id: 42, name: 'Party', imageCount: 1}]};
    context.showEditCollectionDialog(data, 'uploader');
    let markup = environment.dialogs.at(-1).message();
    assert.match(markup, /collection-member-drag-handle/);
    assert.match(markup, /collection-member-move-up/);
    assert.match(markup, /collection-member-move-down/);
    data.shared = true;
    context.showEditCollectionDialog(data, 'uploader');
    markup = environment.dialogs.at(-1).message();
    assert.doesNotMatch(markup, /collection-member-drag-handle/);
    assert.doesNotMatch(markup, /collection-member-move-up/);
});

test('reordering albums from edit collection uses existing API and reports failures', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    environment.queuePost('/api/reorder-collection.php', {type: 'success', data: ''});
    let completed = 0;
    context.saveCollectionMemberOrder(27, [42, 41], () => { completed++; }, () => {});
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post.at(-1))), {
        url: '/api/reorder-collection.php',
        data: {collection: 27, order: '[42,41]'}
    });
    assert.equal(completed, 1);
    environment.queuePost('/api/reorder-collection.php', {
        type: 'failure', xhr: {responseText: 'Reordering forbidden'}
    });
    let errors = [];
    context.saveCollectionMemberOrder(27, [41, 42], () => {}, (message) => { errors.push(message); });
    assert.deepEqual(errors, ['Reordering forbidden']);
});

test('collection page edit album action delegates to the existing admin editor', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    const calls = [];
    context.editAlbum = id => calls.push(id);
    const button = environment.element('.collection-edit-album-btn').attr('data-album-id', '41');
    environment.element('__document__').delegatedHandlers
        .get('click .collection-edit-album-btn').call(button);
    assert.deepEqual(calls, [41]);
});
