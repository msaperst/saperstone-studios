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


test('blog init loads full posts at the footer using configured tags', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#blog-page-config').attr({'data-loader':'posts-full','data-total':'6','data-tags':'[2,4]'});
    environment.element('footer').isOnScreen = () => true;
    let loader;
    function PostsFull(total, tags) {
        assert.equal(total, 6);
        assert.equal(tags.length, 2);
        loader = this;
        this.loadPosts = () => { this.calls = (this.calls || 0) + 1; };
    }
    const window = {};
    loadBrowserScript('public/js/blog-init.js', {$: environment.$, document: environment.document, window, PostsFull});
    environment.runReady();
    environment.element('[object Object]').trigger('scroll resize');
    assert.equal(loader.calls, 1);
});

test('blog init loads preview images only when footer is visible', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#blog-page-config').attr({'data-loader':'posts','data-total':'2'});
    let visible = false;
    environment.element('footer').isOnScreen = () => visible;
    let loader;
    function Posts() { loader = this; this.loadImages = () => { this.calls = (this.calls || 0) + 1; }; }
    const window = {};
    loadBrowserScript('public/js/blog-init.js', {$: environment.$, document: environment.document, window, Posts});
    environment.runReady();
    environment.element('[object Object]').trigger('scroll resize');
    assert.equal(loader.calls || 0, 0);
    visible = true;
    environment.element('[object Object]').trigger('scroll resize');
    assert.equal(loader.calls, 1);
});

test('blog init ignores unsupported or absent loader configuration', () => {
    for (const missing of [false, true]) {
        const environment = createJQueryEnvironment({autoReady: false, lengths: missing ? {'#blog-page-config': 0} : {}});
        if (!missing) environment.element('#blog-page-config').attr('data-loader', 'unexpected');
        let calls = 0;
        loadBrowserScript('public/js/blog-init.js', {$: environment.$, document: environment.document, window: {}, Posts: function () { calls += 1; }, PostsFull: function () { calls += 1; }, PostFull: function () { calls += 1; }});
        environment.runReady();
        assert.equal(calls, 0);
    }
});
