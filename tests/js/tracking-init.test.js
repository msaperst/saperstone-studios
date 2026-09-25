const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('analytics initializes the data layer and measurement id', () => {
    const window = {};
    loadBrowserScript('public/js/analytics.js', {window});
    assert.equal(window.dataLayer.length, 2);
    assert.equal(window.dataLayer[0][0], 'js');
    assert.equal(window.dataLayer[1][0], 'config');
    assert.equal(window.dataLayer[1][1], 'G-L6F5KNQDY1');
});

test('facebook pixel initializes once and appends its loader script safely', () => {
    const appended = [];
    const document = {head: {appendChild(node) { appended.push(node); }}, documentElement: {appendChild() {}}, createElement() { return {}; }};
    const window = {};
    const context = loadBrowserScript('public/js/facebook-pixel.js', {window, document});
    assert.equal(appended.length, 1);
    assert.equal(appended[0].async, true);
    assert.equal(appended[0].src, 'https://connect.facebook.net/en_US/fbevents.js');
    assert.equal(window.fbq.queue.length, 2);
    assert.equal(window.fbq.queue[0][0], 'init');
    assert.equal(window.fbq.queue[1][0], 'track');
    assert.equal(context.fbq, window.fbq);
});

test('facebook pixel reuses an existing fbq without injecting another script', () => {
    let appended = 0;
    const existing = function () {};
    const document = {head: {appendChild() { appended += 1; }}, documentElement: {}, createElement() { return {}; }};
    const window = {fbq: existing};
    loadBrowserScript('public/js/facebook-pixel.js', {window, document, fbq: existing});
    assert.equal(appended, 0);
});
