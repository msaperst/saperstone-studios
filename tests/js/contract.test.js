const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext() {
    const environment = createJQueryEnvironment({autoReady: false});
    const reloads = [];
    const sig = environment.element('#contract-signature');
    const ini = environment.element('#contract-initial');
    function signaturePlugin(command, format) {
        if (!command) return this;
        if (command === 'reset') { this.reset = true; return this; }
        if (command === 'getData' && format === 'native') return this.nativeData || [];
        if (command === 'getData' && format === 'svgbase64') return ['image/svg+xml;base64', 'abc'];
        if (command === 'getData') return 'signature-data';
        return this;
    }
    sig.jSignature = signaturePlugin;
    ini.jSignature = signaturePlugin;
    const context = loadBrowserScript('public/js/contract.js', {
        $: environment.$, document: environment.document,
        BootstrapDialog: environment.BootstrapDialog,
        setTimeout: environment.setTimeout,
        location: {reload(force) { reloads.push(force); }}
    });
    environment.runReady();
    return {context, environment, sig, ini, reloads};
}

test('contract validation disables submit when signatures are missing', () => {
    const {context, environment} = createContext();
    environment.element('.keep').val('filled');

    assert.equal(context.checkInputs(), false);
    assert.equal(environment.element('#contract-submit').prop('disabled'), true);
});

test('contract validation enables submit when required fields and signatures exist', () => {
    const {context, environment, sig, ini} = createContext();
    environment.element('.keep').val('filled');
    sig.nativeData = [[1, 2]];
    ini.nativeData = [[3, 4]];

    assert.equal(context.checkInputs(), true);
    assert.equal(environment.element('#contract-submit').prop('disabled'), false);
});

test('contract clear buttons reset the appropriate signature pad', () => {
    const {environment, sig, ini} = createContext();
    environment.element('#contract-clear-signature').trigger('click');
    environment.element('#contract-clear-initial').trigger('click');

    assert.equal(sig.reset, true);
    assert.equal(ini.reset, true);
});


test('contract submit stops before posting when required signing data is incomplete', () => {
    const {context, environment} = createContext();

    context.submitContract();

    assert.equal(environment.dialogs.length, 0);
    assert.equal(environment.calls.post.length, 0);
});

test('contract submit sends signed content and shows success before delayed reload', () => {
    const {context, environment, sig, ini, reloads} = createContext();
    environment.element('.keep').val('filled');
    sig.nativeData = [[1, 2]];
    ini.nativeData = [[3, 4]];
    environment.element('#contract-id').val('81');
    environment.element('#contract-name-signature').val('Client Name');
    environment.element('#contract-address').val('123 Main');
    environment.element('#contract-number').val('555-1212');
    environment.element('#contract-email').val('client@example.com');
    environment.element('#contract').html('<div>Signed contract</div>');
    environment.queuePost('/api/sign-contract.php', {type: 'success', data: ''});

    context.submitContract();

    assert.equal(environment.calls.post.length, 1);
    const request = environment.calls.post[0];
    assert.equal(request.url, '/api/sign-contract.php');
    assert.equal(request.data.id, '81');
    assert.equal(request.data.name, 'Client Name');
    assert.equal(request.data.email, 'client@example.com');
    assert.equal(request.data.signature, 'signature-data');
    assert.equal(request.data.initial, 'signature-data');
    assert.equal(request.data.content, '<div>Signed contract</div>');
    assert.equal(environment.element('#contract-submit').removed, true);
    assert.match(environment.element('#contract-messages').appended.join(''), /Thank you for signing/);
    assert.equal(reloads.length, 0);

    environment.runTimeouts();
    assert.deepEqual(reloads, [true]);
});

test('contract submit surfaces validation and HTTP failures and restores the submit control', () => {
    for (const response of [
        {type: 'success', data: 'Contract validation failed'},
        {type: 'failure', xhr: {responseText: 'Signing failed'}},
        {type: 'failure', xhr: {responseText: ''}, error: 'Unauthorized'},
        {type: 'failure', xhr: {responseText: ''}, error: 'Server Error'}
    ]) {
        const {context, environment, sig, ini} = createContext();
        environment.element('.keep').val('filled');
        sig.nativeData = [[1]];
        ini.nativeData = [[2]];
        environment.queuePost('/api/sign-contract.php', response);

        context.submitContract();

        const messages = environment.element('#contract-messages').appended.join('');
        if (response.type === 'success') {
            assert.match(messages, /Contract validation failed/);
        } else if (response.xhr.responseText) {
            assert.match(messages, /Signing failed/);
        } else if (response.error === 'Unauthorized') {
            assert.match(messages, /session has timed out/);
        } else {
            assert.match(messages, /unexpected error occurred/);
        }
        assert.equal(environment.element('#contract-submit').prop('disabled'), false);
        assert.equal(environment.element('#contract-submit em').hasClass('fa-paper-plane'), true);
    }
});

test('contract preview captures contact details and replaces signatures with renderable images', () => {
    const {context, environment, sig, ini} = createContext();
    environment.element('#contract-id').val('19');
    environment.element('#contract-name-signature').val('Signer');
    environment.element('#contract-address').val('456 Oak');
    environment.element('#contract-number').val('602-555-0100');
    environment.element('#contract-email').val('signer@example.com');
    sig.nativeData = [[1]];
    ini.nativeData = [[2]];

    const inputs = context.previewContract();

    assert.equal(inputs.id, '19');
    assert.equal(inputs.name, 'Signer');
    assert.equal(inputs.address, '456 Oak');
    assert.equal(inputs.number, '602-555-0100');
    assert.equal(inputs.email, 'signer@example.com');
    assert.equal(inputs.signature, 'signature-data');
    assert.equal(inputs.initial, 'signature-data');
    assert.match(String(environment.element('#contract-signature-holder').html()), /MockElement|object/i);
    assert.equal(environment.element('#contract-signature-holder').hasClass('signature-holder'), false);
    assert.equal(environment.element('#contract-initial-holder').hasClass('signature-holder'), false);
});
