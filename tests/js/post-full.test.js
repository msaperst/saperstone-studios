const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function equalStructure(actual, expected) {
    assert.deepEqual(
        JSON.parse(JSON.stringify(actual)),
        JSON.parse(JSON.stringify(expected))
    );
}

function createContext(response) {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.queueGet('/api/get-blog-full.php', {
        type: 'success',
        data: response
    });
    const loaded = [];
    const windowObject = {location: {href: ''}};
    const context = loadBrowserScript('public/js/post-full.js', {
        $: environment.$,
        window: windowObject,
        loadPost(data, header) {
            loaded.push({data, header});
        }
    });
    return {context, environment, loaded, windowObject};
}

test('PostFull loads the requested blog post with the full-post heading', () => {
    const data = {id: 42, title: 'A Post'};
    const {context, environment, loaded} = createContext(data);

    const post = new context.PostFull(42);

    assert.equal(post.post, 42);
    equalStructure(environment.calls.get, [{
        url: '/api/get-blog-full.php',
        data: {post: 42}
    }]);
    equalStructure(loaded, [{data, header: '<h1>'}]);
});

test('PostFull edit button navigates to the editor for the current post', () => {
    const {context, environment, windowObject} = createContext({id: 7});

    new context.PostFull(7);
    environment.element('#edit-post-btn').trigger('click');

    assert.equal(windowObject.location.href, 'new.php?p=7');
});
