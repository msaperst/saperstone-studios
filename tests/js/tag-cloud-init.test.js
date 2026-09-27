const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('tag cloud init passes configured tags to jQCloud', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#tag-cloud-config').attr('data-tags', '[{"text":"family","weight":2}]');
    let tags;
    environment.element('#tag-cloud').jQCloud = value => { tags = value; };
    loadBrowserScript('public/js/tag-cloud-init.js', {$: environment.$, document: environment.document});
    environment.runReady();
    assert.equal(tags[0].text, 'family');
    assert.equal(tags[0].weight, 2);
});

test('tag cloud init exits when configuration is absent', () => {
    const environment = createJQueryEnvironment({autoReady: false, lengths: {'#tag-cloud-config': 0}});
    let calls = 0;
    environment.element('#tag-cloud').jQCloud = () => { calls += 1; };
    loadBrowserScript('public/js/tag-cloud-init.js', {$: environment.$, document: environment.document});
    environment.runReady();
    assert.equal(calls, 0);
});
