const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('error report wires back navigation and submits diagnostic context', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#error-report-config').attr({'data-error':'boom','data-page':'/bad','data-referrer':'/before'});
    let backCalls = 0;
    loadBrowserScript('public/js/error-report.js', {$: environment.$, document: environment.document, window: {history: {back() { backCalls += 1; }}}, screen: {width: 1920, height: 1080}});
    environment.runReady();
    let prevented = false;
    environment.element('#error-back-link').trigger('click', {preventDefault() { prevented = true; }});
    assert.equal(prevented, true);
    assert.equal(backCalls, 1);
    assert.equal(environment.calls.post.length, 1);
    assert.equal(environment.calls.post[0].data.error, 'boom');
    assert.equal(environment.calls.post[0].data.page, '/bad');
    assert.equal(environment.calls.post[0].data.referrer, '/before');
    assert.equal(environment.calls.post[0].data.resolution, '1920x1080');
});

test('error report does not submit without configuration', () => {
    const environment = createJQueryEnvironment({autoReady: false, lengths: {'#error-report-config': 0}});
    loadBrowserScript('public/js/error-report.js', {$: environment.$, document: environment.document, window: {history: {back() {}}}, screen: {width: 1, height: 1}});
    environment.runReady();
    assert.equal(environment.calls.post.length, 0);
});
