const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext() {
    const environment = createJQueryEnvironment({autoReady: false});
    const windowObject = {location: {href: ''}};
    const context = loadBrowserScript('public/js/user.js', {
        $: environment.$, document: environment.document,
        window: windowObject, BootstrapDialog: environment.BootstrapDialog
    });
    environment.runReady();
    return {context, environment, windowObject};
}

test('user table is configured with expected API and display columns', () => {
    const {environment} = createContext();
    const config = environment.dataTable.config;
    assert.equal(config.ajax, '/api/get-users.php');
    assert.deepEqual(JSON.parse(JSON.stringify(config.order)), [[2, 'asc']]);
    assert.equal(config.columnDefs[2].data, 'usr');
    assert.equal(config.columnDefs[3].data({firstName: 'Max', lastName: 'User'}), 'Max User');
    const actions = config.columnDefs[0].data({usr: 'sample'});
    assert.match(actions, /edit-user-btn/);
    assert.match(actions, /title="Edit sample Details"/);
    assert.match(actions, /title="View sample Activities"/);
    assert.match(actions, /title="View Site As sample"/);
    assert.doesNotMatch(actions, /data-toggle|data-placement/);
});

test('created user rows retain user id for actions', () => {
    const {environment} = createContext();
    const row = environment.element('__row__');
    environment.dataTable.config.fnCreatedRow(row, {id: 17});
    assert.equal(row.attr('user-id'), '17');
});

test('dialog button helpers disable and restore buttons and closability', () => {
    const {context, environment} = createContext();
    const dialog = environment.createDialog();
    context.disableDialogButtons(dialog);
    assert.equal(dialog.buttonsEnabled, false);
    assert.equal(dialog.closable, false);
    context.enableDialogButtons(dialog);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});

test('addAlbum fetches album metadata and appends a removable selection', () => {
    const {context, environment} = createContext();
    environment.queueGet('/api/get-album.php', {type: 'success', data: {name: 'Wedding'}});
    context.addAlbum(12);
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.get[0])), {
        url: '/api/get-album.php', data: {id: 12}
    });
    assert.equal(environment.element('#user-albums').appended.length, 1);
});


test('edit user active checkbox handles numeric and string API values', () => {
    for (const [active, expected] of [[1, true], ['1', true], [0, false], ['0', false]]) {
        const {context, environment} = createContext();
        context.editUser({
            id: 17,
            usr: 'sample',
            firstName: 'Sample',
            lastName: 'User',
            email: 'sample@example.org',
            role: 'downloader',
            resetKey: '',
            active
        });
        const dialogConfig = environment.dialogs[0];
        const message = dialogConfig.message();
        const findById = (element, id) => {
            if (!element || typeof element !== 'object') return undefined;
            if (typeof element.attr === 'function' && element.attr('id') === id) return element;
            for (const child of element.appended || []) {
                const match = findById(child, id);
                if (match) return match;
            }
            return undefined;
        };
        const activeInput = findById(message, 'user-active');
        assert.ok(activeInput);
        assert.equal(activeInput.prop('checked'), expected);
    }
});


function sampleUser(overrides = {}) {
    return {
        id: 17,
        usr: 'sample',
        firstName: 'Sample',
        lastName: 'User',
        email: 'sample@example.org',
        role: 'downloader',
        resetKey: '',
        active: 1,
        ...overrides
    };
}

function dialogButton(config, id) {
    return config.buttons.find((button) => button.id === id);
}

test('creating a user reloads the table and opens the created user for editing', () => {
    const {context, environment} = createContext();
    const created = sampleUser({id: 23, usr: 'created-user'});

    environment.element('#user-username').val(created.usr);
    environment.element('#user-first-name').val(created.firstName);
    environment.element('#user-last-name').val(created.lastName);
    environment.element('#user-email').val(created.email);
    environment.element('#user-role').val(created.role);
    environment.element('#user-active').prop('checked', true);

    environment.queuePost('/api/create-user.php', {type: 'success', data: '23'});
    environment.queueGet('/api/get-user.php', {type: 'success', data: created});

    context.editUser(null);
    const config = environment.dialogs[0];
    const dialog = environment.createDialog();
    dialogButton(config, 'user-save-btn').action.call(environment.createButton(), dialog);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/create-user.php',
        data: {
            username: 'created-user',
            firstName: 'Sample',
            lastName: 'User',
            email: 'sample@example.org',
            role: 'downloader',
            active: 1
        }
    });
    assert.equal(environment.calls.reload.length, 1);
    assert.equal(dialog.closed, true);
    assert.equal(environment.dialogs.length, 2);
    assert.equal(environment.dialogs[1].title(), 'Edit User <b>created-user</b>');
});

test('updating a user reloads the table after a successful save', () => {
    const {context, environment} = createContext();
    const user = sampleUser();

    environment.element('#user-username').val(user.usr);
    environment.element('#user-first-name').val('Updated');
    environment.element('#user-last-name').val('Person');
    environment.element('#user-email').val('updated@example.org');
    environment.element('#user-role').val('uploader');
    environment.element('#user-active').prop('checked', false);

    environment.queuePost('/api/update-user.php', {type: 'success', data: ''});

    context.editUser(user);
    const config = environment.dialogs[0];
    const dialog = environment.createDialog();
    dialogButton(config, 'user-update-btn').action.call(environment.createButton(), dialog);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-user.php',
        data: {
            id: 17,
            username: 'sample',
            firstName: 'Updated',
            lastName: 'Person',
            email: 'updated@example.org',
            role: 'uploader',
            active: 0
        }
    });
    assert.equal(dialog.closed, true);
    assert.equal(environment.calls.reload.length, 1);
});

test('deleting a user reloads the table and closes the edit dialog', () => {
    const {context, environment} = createContext();
    const user = sampleUser();
    environment.queuePost('/api/delete-user.php', {type: 'success', data: ''});

    context.editUser(user);
    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    dialogButton(editConfig, 'user-delete-btn').action.call(environment.createButton(), editDialog);

    const confirmConfig = environment.dialogs[1];
    const confirmDialog = environment.createDialog();
    confirmConfig.buttons[0].action.call(environment.createButton(), confirmDialog);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/delete-user.php',
        data: {id: 17}
    });
    assert.equal(environment.calls.reload.length, 1);
    assert.equal(editDialog.closed, true);
    assert.equal(confirmDialog.closed, true);
});

test('view as user posts the selected user and redirects after success', () => {
    const {context, environment, windowObject} = createContext();
    const button = environment.element('.view-as-user-btn');
    const row = environment.element('__user_row__').attr('user-id', '17');
    button.closestResult = row;

    environment.queuePost('/api/login-as-user.php', {type: 'success', data: ''});
    context.setupEdit();
    button.click();

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/login-as-user.php',
        data: {id: '17'}
    });
    assert.equal(windowObject.location.href, 'index.php');
});


test('updating user albums posts selected album ids and restores both dialogs', () => {
    const {context, environment} = createContext();
    const user = sampleUser();
    context.editUser(user);

    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    const parentButton = environment.createButton('__user_albums_parent_button__');
    parentButton.closestResult = environment.element('__user_albums_parent_modal__');
    dialogButton(editConfig, 'user-albums-btn').action.call(parentButton, editDialog);

    const albumsConfig = environment.dialogs[1];
    const albumsDialog = environment.createDialog();
    environment.element('#user-albums .selected-album').attr('album-id', '12');
    environment.queuePost('/api/update-user-albums.php', {type: 'success', data: ''});

    albumsConfig.buttons[0].action.call(
        environment.createButton('__user_albums_update_button__'),
        albumsDialog
    );

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-user-albums.php',
        data: {
            user: 17,
            albums: ['12']
        }
    });
    assert.equal(albumsDialog.closed, true);
    assert.equal(parentButton.stopSpinCount, 1);
    assert.equal(editDialog.buttonsEnabled, true);
    assert.equal(editDialog.closable, true);
});

test('user album validation responses remain visible instead of closing the dialog', () => {
    const {context, environment} = createContext();
    const user = sampleUser();
    context.editUser(user);

    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    const parentButton = environment.createButton('__user_albums_validation_parent__');
    const parentModal = environment.element('__user_albums_validation_parent_modal__');
    parentButton.closestResult = parentModal;
    dialogButton(editConfig, 'user-albums-btn').action.call(parentButton, editDialog);

    const albumsConfig = environment.dialogs[1];
    const albumsDialog = environment.createDialog();
    const updateButton = environment.createButton('__user_albums_validation_update__');
    updateButton.closestResult = parentModal;
    environment.queuePost('/api/update-user-albums.php', {
        type: 'success',
        data: 'One or more albums are invalid'
    });

    albumsConfig.buttons[0].action.call(updateButton, albumsDialog);

    assert.match(
        parentModal.find('.bootstrap-dialog-body').appended.join(''),
        /One or more albums are invalid/
    );
    assert.equal(albumsDialog.closed, false);
    assert.equal(albumsDialog.buttonsEnabled, true);
    assert.equal(albumsDialog.closable, true);
});

test('user album dialog loads existing album assignments when shown', () => {
    const {context, environment} = createContext();
    const added = [];
    context.addAlbum = (id) => added.push(id);
    context.editUser(sampleUser());

    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    dialogButton(editConfig, 'user-albums-btn').action.call(
        environment.createButton('__user_albums_load_parent__'),
        editDialog
    );

    const albumsConfig = environment.dialogs[1];
    const albumsDialog = environment.createDialog();
    environment.queueGet('/api/get-user-albums.php', {
        type: 'success',
        data: [{album: 7}, {album: 8}]
    });

    albumsConfig.onshown(albumsDialog);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.get.at(-1))), {
        url: '/api/get-user-albums.php',
        data: {user: 17}
    });
    assert.deepEqual(added, [7, 8]);
    assert.equal(albumsDialog.$modalBody.afterValues.length, 1);
});

test('updating a user password posts both password fields and closes on success', () => {
    const {context, environment} = createContext();
    context.editUser(sampleUser());

    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    const parentButton = environment.createButton('__user_password_parent__');
    dialogButton(editConfig, 'user-update-password-btn').action.call(parentButton, editDialog);

    const passwordConfig = environment.dialogs[1];
    environment.element('#user-password').val('new-password');
    environment.element('#user-password-confirm').val('new-password');
    environment.queuePost('/api/update-user-password.php', {type: 'success', data: ''});

    const passwordDialog = environment.createDialog();
    passwordConfig.buttons[0].action.call(
        environment.createButton('__user_password_update__'),
        passwordDialog
    );

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-user-password.php',
        data: {
            id: 17,
            password: 'new-password',
            passwordConfirm: 'new-password'
        }
    });
    assert.equal(passwordDialog.closed, true);
    assert.equal(parentButton.stopSpinCount, 1);
    assert.equal(editDialog.buttonsEnabled, true);
    assert.equal(editDialog.closable, true);
});

test('user password failures stay visible and restore nested dialog controls', () => {
    const {context, environment} = createContext();
    context.editUser(sampleUser());

    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    const parentButton = environment.createButton('__user_password_failure_parent__');
    dialogButton(editConfig, 'user-update-password-btn').action.call(parentButton, editDialog);

    const passwordConfig = environment.dialogs[1];
    const passwordDialog = environment.createDialog();
    const updateButton = environment.createButton('__user_password_failure_update__');
    const modal = environment.element('__user_password_failure_modal__');
    updateButton.closestResult = modal;
    environment.queuePost('/api/update-user-password.php', {
        type: 'failure',
        xhr: {responseText: 'Passwords do not match'}
    });

    passwordConfig.buttons[0].action.call(updateButton, passwordDialog);

    assert.match(
        modal.find('.bootstrap-dialog-body').appended.join(''),
        /Passwords do not match/
    );
    assert.equal(passwordDialog.closed, false);
    assert.equal(updateButton.stopSpinCount, 1);
    assert.equal(passwordDialog.buttonsEnabled, true);
    assert.equal(passwordDialog.closable, true);
});

test('user deletion failures keep confirmation open so the error remains visible', () => {
    const {context, environment} = createContext();
    context.editUser(sampleUser());

    const editConfig = environment.dialogs[0];
    const editDialog = environment.createDialog();
    dialogButton(editConfig, 'user-delete-btn').action.call(
        environment.createButton('__user_delete_failure_parent__'),
        editDialog
    );

    const confirmConfig = environment.dialogs[1];
    const confirmDialog = environment.createDialog();
    const confirmButton = environment.createButton('__user_delete_failure_confirm__');
    const modal = environment.element('__user_delete_failure_modal__');
    confirmButton.closestResult = modal;
    environment.queuePost('/api/delete-user.php', {
        type: 'failure',
        xhr: {responseText: 'User still owns protected content'}
    });

    confirmConfig.buttons[0].action.call(confirmButton, confirmDialog);

    assert.match(
        modal.find('.bootstrap-dialog-body').appended.join(''),
        /User still owns protected content/
    );
    assert.equal(confirmDialog.closed, false);
    assert.equal(editDialog.closed, false);
    assert.equal(confirmButton.stopSpinCount, 1);
    assert.equal(confirmDialog.buttonsEnabled, true);
    assert.equal(confirmDialog.closable, true);
});

test('edit user dialog hides controls that do not apply to admin and new users', () => {
    for (const [user, removed] of [
        [sampleUser({role: 'admin'}), ['#user-albums-btn', '#user-save-btn']],
        [null, ['#user-albums-btn', '#user-update-password-btn', '#user-delete-btn', '#user-update-btn']]
    ]) {
        const {context, environment} = createContext();
        context.editUser(user);
        const config = environment.dialogs[0];
        const dialog = environment.createDialog();

        config.onshow(dialog);

        for (const selector of removed) {
            assert.equal(dialog.$modalFooter.find(selector).removed, true, selector);
        }
    }
});
