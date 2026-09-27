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
