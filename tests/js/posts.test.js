const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScripts} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createPostsContext(options = {}) {
    const requests = [];
    const previews = [];
    const windowObject = {innerHeight: options.innerHeight ?? 800};
    const elementPrototype = {
        get(index) {
            return index === 0 ? this.nativeElement : undefined;
        }
    };
    const footer = Object.create(elementPrototype);
    footer.rect = options.footerRect || {top: 100, bottom: 200};
    footer.nativeElement = {
        getBoundingClientRect() {
            return footer.rect;
        }
    };

    function $(selector) {
        if (selector === 'footer') {
            return footer;
        }
        return Object.create(elementPrototype);
    }
    $.fn = elementPrototype;
    $.each = (collection, callback) => collection.forEach((value, index) => callback(index, value));
    $.get = (url, data, success) => {
        const request = {
            url,
            data,
            success,
            failure: null,
            fail(callback) {
                request.failure = callback;
                return request;
            }
        };
        requests.push(request);
        return request;
    };

    const context = loadBrowserScripts(['public/js/blog-common.js', 'public/js/posts.js'], {
        $,
        window: windowObject,
        loadPostPreview(index, post) {
            previews.push({index, post});
        }
    });

    return {
        context,
        footer,
        previews,
        requests,
        resolve(index, data) {
            requests[index].success({data});
        }
    };
}

test('posts.js detects whether an element intersects the viewport', () => {
    const {footer} = createPostsContext();

    footer.rect = {top: 100, bottom: 200};
    assert.equal(footer.isOnScreen(), true);

    footer.rect = {top: 900, bottom: 1000};
    assert.equal(footer.isOnScreen(), false);

    footer.rect = {top: -200, bottom: -1};
    assert.equal(footer.isOnScreen(), false);
});

test('Posts requests the first preview page and advances its offset', () => {
    const {context, requests} = createPostsContext({footerRect: {top: 900, bottom: 1000}});

    const posts = new context.Posts(3, 8);

    assert.equal(posts.columns, 3);
    assert.equal(posts.totalImages, 8);
    assert.equal(requests.length, 1);
    assert.equal(requests[0].url, '/api/get-blogs-details.php');
    assert.deepEqual(plain(requests[0].data), {start: 0, howMany: 3});
});

test('Posts renders each preview returned by the API', () => {
    const {context, previews, resolve} = createPostsContext({footerRect: {top: 900, bottom: 1000}});

    const posts = new context.Posts(3, 8);
    resolve(0, [{id: 1}, {id: 2}, {id: 3}]);

    assert.equal(posts.loaded, 3);
    assert.deepEqual(plain(previews), [
        {index: 0, post: {id: 1}},
        {index: 1, post: {id: 2}},
        {index: 2, post: {id: 3}}
    ]);
});

test('Posts automatically requests another page while the footer is visible', () => {
    const {context, requests, resolve} = createPostsContext();

    const posts = new context.Posts(3, 7);
    resolve(0, [{id: 1}, {id: 2}, {id: 3}]);

    assert.equal(requests.length, 2);
    assert.deepEqual(plain(requests[1].data), {start: 3, howMany: 3});
});

test('Posts stops automatic pagination when its loaded offset reaches the total', () => {
    const {context, requests, resolve} = createPostsContext();

    const posts = new context.Posts(3, 3);
    resolve(0, [{id: 1}, {id: 2}, {id: 3}]);

    assert.equal(posts.loaded, 3);
    assert.equal(requests.length, 1);
});

test('Posts does not issue overlapping preview requests while loading', () => {
    const {context, requests} = createPostsContext({
        footerRect: {top: 900, bottom: 1000}
    });
    const posts = new context.Posts(3, 6);

    posts.loadImages();
    posts.loadImages();

    assert.equal(requests.length, 1);
});

test('Posts advances by the actual number of previews returned and stops on a short page', () => {
    const {context, requests, resolve} = createPostsContext({
        footerRect: {top: 900, bottom: 1000}
    });
    const posts = new context.Posts(3, 6);

    resolve(0, [{id: 1}, {id: 2}]);
    posts.loadImages();

    assert.equal(posts.loaded, 2);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 1);
});

test('Posts with no results avoids an unnecessary preview request', () => {
    const {context, requests} = createPostsContext();

    const posts = new context.Posts(3, 0);

    assert.equal(posts.loaded, 0);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 0);
});
