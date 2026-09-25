const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('retouch init restores configured images and instructions', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#retouch-config').attr({'data-images':'[{"thumb":"one.jpg"}]','data-instructions':'true'});
    let args;
    loadBrowserScript('public/js/retouch-init.js', {$: environment.$, document: environment.document, window: {}, Retouch: function (...values) { args = values; }});
    environment.runReady();
    assert.equal(args[0], environment.element('#holder'));
    assert.equal(args[1][0].thumb, 'one.jpg');
    assert.equal(args[2], true);
});

test('retouch init honors disabled instructions and missing config', () => {
    const environment = createJQueryEnvironment({autoReady: false, lengths: {'#retouch-config': 0}});
    let calls = 0;
    loadBrowserScript('public/js/retouch-init.js', {$: environment.$, document: environment.document, window: {}, Retouch: function () { calls += 1; }});
    environment.runReady();
    assert.equal(calls, 0);
});
