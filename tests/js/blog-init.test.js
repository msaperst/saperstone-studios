const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('blog init constructs the full-post loader from inert page configuration', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#blog-page-config').attr({'data-loader':'post-full','data-post':'17'});
    let postId;
    loadBrowserScript('public/js/blog-init.js', {
        $: environment.$, document: environment.document, window: {},
        PostFull: function (id) { postId = id; }
    });
    environment.runReady();
    assert.equal(postId, 17);
});

test('blog init constructs preview loader with search configuration', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#blog-page-config').attr({
        'data-loader':'posts','data-columns':'3','data-total':'8','data-search':'family'
    });
    let args;
    loadBrowserScript('public/js/blog-init.js', {
        $: environment.$, document: environment.document, window: {},
        Posts: function (...values) { args = values; }
    });
    environment.runReady();
    assert.deepEqual(args, [3, 8, 'family']);
});
