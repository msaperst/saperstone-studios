const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext() {
    const environment = createJQueryEnvironment({autoReady: false});
    const context = loadBrowserScript('public/js/contract-admin.js', {
        $: environment.$,
        document: environment.document,
        window: {location: {href: ''}, open() {}},
        BootstrapDialog: environment.BootstrapDialog
    });
    environment.runReady();
    return {context, environment};
}

test('contract admin action buttons use native titles', () => {
    const {environment} = createContext();
    const render = environment.dataTable.config.columnDefs[0].data;

    const unsigned = render({name: 'Client', session: 'Wedding', signature: '', link: ''});
    assert.match(unsigned, /title="Edit Client Wedding Details"/);
    assert.match(unsigned, /title="View Client Wedding Details"/);
    assert.doesNotMatch(unsigned, /data-toggle|data-placement/);

    const signed = render({name: 'Client', session: 'Wedding', signature: 'signed', link: '/contract'});
    assert.match(signed, /title="Download Client Wedding Contract"/);
    assert.match(signed, /title="View Embedded Client Wedding Contract"/);
    assert.doesNotMatch(signed, /data-toggle|data-placement/);
});

test('dynamically added contract line item uses a native title', () => {
    const {context, environment} = createContext();
    context.setupAddLineItem();

    environment.element('#add-contract-line-item-btn').trigger('click');

    const span = environment.element('#add-contract-line-item-btn').beforeValues[0];
    const removeButton = span.appended.find((item) =>
        item && item.classes && item.classes.has('remove-contract-line-item-btn')
    );
    assert.ok(removeButton);
    assert.equal(removeButton.attr('title'), 'Remove Line Item');
    assert.equal(removeButton.attr('data-toggle'), undefined);
    assert.equal(removeButton.attr('data-placement'), undefined);
});


test('contract admin requires a client name before saving', () => {
    const {context, environment} = createContext();
    const button = environment.createButton('__contract_save_button__');
    const modal = environment.element('__contract_modal__');
    button.closestResult = modal;

    environment.element('#contract-type').val('commercial');
    environment.element('#contract-name').val('');
    environment.element('#contract-session').val('Session');

    assert.equal(context.requiredInfo(button), false);

    const body = modal.find('.bootstrap-dialog-body');
    assert.equal(body.appended.length, 1);
    assert.match(String(body.appended[0]), /Please enter a Client Name/);
});

test('successful contract submit closes the dialog and reloads the contract table', () => {
    const {context, environment} = createContext();
    const button = environment.createButton('__contract_submit_button__');
    const modal = environment.element('__contract_submit_modal__');
    button.closestResult = modal;
    const dialog = environment.createDialog();

    environment.queuePost('/api/update-contract.php', {type: 'success', data: '98980'});

    context.submitContract(
        {id: 98980, type: 'commercial', name: 'Client', session: 'Session', content: 'Content'},
        '/api/update-contract.php',
        dialog,
        button
    );

    assert.equal(environment.calls.post.length, 1);
    assert.equal(environment.calls.post[0].url, '/api/update-contract.php');
    assert.equal(environment.calls.post[0].data.name, 'Client');
    assert.equal(dialog.closed, true);
    assert.equal(environment.calls.reload.length, 1);
    assert.equal(button.stopSpinCount, 1);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});

test('failed contract submit displays the server error and restores dialog controls', () => {
    const {context, environment} = createContext();
    const button = environment.createButton('__contract_failed_submit_button__');
    const modal = environment.element('__contract_failed_submit_modal__');
    button.closestResult = modal;
    const dialog = environment.createDialog();

    environment.queuePost('/api/update-contract.php', {
        type: 'failure',
        xhr: {responseText: 'Contract email is not valid'},
        error: 'Bad Request'
    });

    context.submitContract(
        {id: 98980, type: 'commercial', name: 'Client', session: 'Session', content: 'Content'},
        '/api/update-contract.php',
        dialog,
        button
    );

    const body = modal.find('.bootstrap-dialog-body');
    assert.equal(body.appended.length, 1);
    assert.match(String(body.appended[0]), /Contract email is not valid/);
    assert.equal(dialog.closed, false);
    assert.equal(environment.calls.reload.length, 0);
    assert.equal(button.stopSpinCount, 1);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});
