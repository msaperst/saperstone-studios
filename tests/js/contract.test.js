const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext() {
    const environment = createJQueryEnvironment({autoReady: false});
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
        location: {reload() {}}
    });
    environment.runReady();
    return {context, environment, sig, ini};
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
