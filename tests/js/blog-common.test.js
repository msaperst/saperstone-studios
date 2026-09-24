const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext() {
    const environment = createJQueryEnvironment({autoReady: false});
    const context = loadBrowserScript('public/js/blog-common.js', {
        $: environment.$,
        window: {innerHeight: 800},
        loadPostPreview() {}
    });
    return {context, environment};
}

test('appendBlogRequestError prefers a response body when available', () => {
    const {context, environment} = createContext();
    const container = environment.element('__errors__');

    context.appendBlogRequestError(
        container,
        {responseText: 'Server explanation'},
        'Server Error',
        'Fallback'
    );

    assert.match(container.appended.join(''), /Server explanation/);
    assert.doesNotMatch(container.appended.join(''), /Fallback/);
});

test('appendBlogRequestError reports an expired session for unauthorized failures', () => {
    const {context, environment} = createContext();
    const container = environment.element('__errors__');

    context.appendBlogRequestError(
        container,
        {responseText: ''},
        'Unauthorized',
        'Fallback'
    );

    assert.match(container.appended.join(''), /session has timed out/);
    assert.doesNotMatch(container.appended.join(''), /Fallback/);
});

test('appendBlogRequestError uses the caller fallback when no detail is available', () => {
    const {context, environment} = createContext();
    const container = environment.element('__errors__');

    context.appendBlogRequestError(
        container,
        {responseText: ''},
        'Server Error',
        'Something specific failed'
    );

    assert.match(container.appended.join(''), /Something specific failed/);
});

test('setBlogControlsDisabled supports one selector or several selectors', () => {
    const {context, environment} = createContext();

    context.setBlogControlsDisabled('.btn', true);
    assert.equal(environment.element('.btn').prop('disabled'), true);

    context.setBlogControlsDisabled(
        ['#save', '#delete', '#close'],
        true
    );
    assert.equal(environment.element('#save').prop('disabled'), true);
    assert.equal(environment.element('#delete').prop('disabled'), true);
    assert.equal(environment.element('#close').prop('disabled'), true);

    context.setBlogControlsDisabled(['#save', '#delete'], false);
    assert.equal(environment.element('#save').prop('disabled'), false);
    assert.equal(environment.element('#delete').prop('disabled'), false);
});

test('blog common viewport helper reports visible and off-screen elements', () => {
    const {context, environment} = createContext();
    const element = environment.element('__viewport__');

    element.rect = {top: 100, bottom: 200};
    assert.equal(element.isOnScreen(), true);

    element.rect = {top: 900, bottom: 1000};
    assert.equal(element.isOnScreen(), false);

    element.rect = {top: -200, bottom: -1};
    assert.equal(element.isOnScreen(), false);
});
