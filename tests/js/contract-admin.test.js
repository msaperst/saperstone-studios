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


test('contract admin action handlers edit, view, and download the selected contract', () => {
    const {context, environment} = createContext();
    const editCalls = [];
    context.editContract = (data) => editCalls.push(data);

    const editRow = environment.element('__edit_contract_row__').attr('contract-id', '44');
    environment.element('.edit-contract-btn').closestResult = editRow;
    environment.queueGet('/api/get-contract.php', {
        type: 'success',
        data: {id: 44, type: 'commercial', name: 'Client', session: 'Portrait'}
    });

    const viewRow = environment.element('__view_contract_row__').attr('contract-link', 'abc123');
    environment.element('.view-contract-btn').closestResult = viewRow;

    const downloadRow = environment.element('__download_contract_row__').attr('contract-file', '/contracts/44.pdf');
    environment.element('.dl-contract-btn').closestResult = downloadRow;
    const opened = [];
    context.window.open = (url) => opened.push(url);

    context.setupEdit();
    environment.element('.edit-contract-btn').trigger('click');
    environment.element('.view-contract-btn').trigger('click');
    environment.element('.dl-contract-btn').trigger('click');

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.get[0])), {
        url: '/api/get-contract.php',
        data: {id: '44'}
    });
    assert.equal(editCalls[0].id, 44);
    assert.equal(context.window.location.href, '/contract.php?c=abc123');
    assert.deepEqual(opened, ['/contracts/44.pdf']);
});

test('contract admin validates type and session before previewing', () => {
    const {context, environment} = createContext();
    const button = environment.createButton('__contract_validation_button__');
    const modal = environment.element('__contract_validation_modal__');
    button.closestResult = modal;

    environment.element('#contract-type').val('');
    environment.element('#contract-name').val('Client');
    environment.element('#contract-session').val('Portrait');
    assert.equal(context.requiredInfo(button), false);
    assert.match(String(modal.find('.bootstrap-dialog-body').appended.at(-1)), /unexpected error occurred with the form/);

    environment.element('#contract-type').val('portrait');
    environment.element('#contract-session').val('');
    assert.equal(context.requiredInfo(button), false);
    assert.match(String(modal.find('.bootstrap-dialog-body').appended.at(-1)), /Please enter a Session/);
});

test('contract admin loads available contract types and starts the selected contract', () => {
    const {context, environment} = createContext();
    environment.queueGet('/api/get-contract-types.php', {
        type: 'success',
        data: ['commercial', 'photobooth']
    });

    const editCalls = [];
    context.editContract = (type) => editCalls.push(type);
    context.addContract();

    const config = environment.dialogs.at(-1);
    const loadingDialog = environment.createDialog();
    const message = config.message(loadingDialog);

    assert.match(message.html(), /Loading/);
    assert.equal(environment.calls.get[0].url, '/api/get-contract-types.php');
    assert.ok(loadingDialog.message);
    const select = loadingDialog.message.appended[0];
    assert.equal(select.appended.length, 2);
    assert.equal(select.appended[0].html(), 'Commercial');
    assert.equal(select.appended[1].html(), 'Photobooth');

    environment.element('#contract-type').val('photobooth');
    const createDialog = environment.createDialog();
    config.buttons[0].action(createDialog);

    assert.equal(createDialog.closed, true);
    assert.deepEqual(editCalls, ['photobooth']);
});

test('contract admin preview serializes contract fields and line items', () => {
    const {context, environment} = createContext();
    const button = environment.createButton('__preview_contract_button__');
    const dialog = environment.createDialog();

    environment.element('.contract-item').val('Coverage');
    environment.element('.contract-amount').val('250');
    environment.element('.contract-unit').val('hour');
    environment.element('#contract-type').val('portrait');
    environment.element('#contract-name').val('Client');
    environment.element('#contract-address').val('123 Main');
    environment.element('#contract-number').val('555-1212');
    environment.element('#contract-email').val('client@example.com');
    environment.element('#contract-date').val('2026-10-04');
    environment.element('#contract-location').val('Studio');
    environment.element('#contract-session').val('Portrait');
    environment.element('#contract-details').val('Details');
    environment.element('#contract-deposit').val('100');
    environment.element('#contract-invoice').val('INV-1');
    environment.element('.bootstrap-dialog-message>div>div').html('<p>Rendered contract</p>');

    dialog.getModalBody = () => ({html: () => '<div>modal</div>'});
    const inputs = context.previewContract(55, '/api/update-contract.php', dialog, button);

    assert.equal(inputs.id, 55);
    assert.equal(inputs.type, 'portrait');
    assert.equal(inputs.name, 'Client');
    assert.equal(inputs.email, 'client@example.com');
    assert.deepEqual(JSON.parse(JSON.stringify(inputs.lineItems)), [{
        item: 'Coverage',
        amount: '250',
        unit: 'hour'
    }]);
    assert.equal(inputs.content, '<p>Rendered contract</p>');
    assert.equal(button.spinCount, 1);
    assert.equal(dialog.buttonsEnabled, false);
    assert.equal(dialog.closable, false);
});

test('contract admin reports malformed success and unauthorized submit responses', () => {
    for (const response of [
        {type: 'success', data: 'Contract could not be saved'},
        {type: 'failure', xhr: {responseText: ''}, error: 'Unauthorized'}
    ]) {
        const {context, environment} = createContext();
        const button = environment.createButton('__contract_response_button__');
        const modal = environment.element('__contract_response_modal__');
        button.closestResult = modal;
        const dialog = environment.createDialog();
        environment.queuePost('/api/update-contract.php', response);

        context.submitContract(
            {id: 12, type: 'portrait', name: 'Client'},
            '/api/update-contract.php',
            dialog,
            button
        );

        const messages = modal.find('.bootstrap-dialog-body').appended.join('');
        if (response.type === 'success') {
            assert.match(messages, /Contract could not be saved/);
        } else {
            assert.match(messages, /session has timed out/);
        }
        assert.equal(dialog.closed, false);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    }
});
