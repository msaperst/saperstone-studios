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
    assert.match(config.columnDefs[0].data({usr: 'sample'}), /edit-user-btn/);
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
