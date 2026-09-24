const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createSearchContext(options = {}) {
    const requests = [];
    const previews = [];
    const windowObject = {
        innerHeight: options.innerHeight ?? 800
    };

    const elementPrototype = {
        get(index) {
            return index === 0 ? this.nativeElement : undefined;
        }
    };

    const footer = Object.create(elementPrototype);
    footer.nativeElement = {
        getBoundingClientRect() {
            return footer.rect;
        }
    };
    footer.rect = options.footerRect || {
        top: 100,
        bottom: 200
    };

    function $(selector) {
        if (selector === 'footer') {
            return footer;
        }
        return Object.create(elementPrototype);
    }

    $.fn = elementPrototype;
    $.each = function (collection, callback) {
        collection.forEach((value, index) => callback(index, value));
    };
    $.get = function (url, data, success) {
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

    const context = loadBrowserScript('public/js/posts-search.js', {
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
            requests[index].success(data);
        },
        reject(index) {
            if (requests[index].failure) {
                requests[index].failure();
            }
        }
    };
}

test('posts-search.js detects whether an element intersects the viewport', () => {
    const {footer} = createSearchContext();

    footer.rect = {top: 100, bottom: 200};
    assert.equal(footer.isOnScreen(), true);

    footer.rect = {top: 900, bottom: 1000};
    assert.equal(footer.isOnScreen(), false);

    footer.rect = {top: -200, bottom: -1};
    assert.equal(footer.isOnScreen(), false);
});

test('Posts initializes state and requests the first search result page', () => {
    const {context, requests} = createSearchContext({
        footerRect: {top: 900, bottom: 1000}
    });

    const posts = new context.Posts(3, 5, 'wedding');

    assert.equal(posts.loaded, 0);
    assert.equal(posts.loading, true);
    assert.equal(posts.complete, false);
    assert.equal(posts.columns, 3);
    assert.equal(posts.totalImages, 5);
    assert.equal(posts.searchTerm, 'wedding');
    assert.equal(requests.length, 1);
    assert.deepEqual(plain(requests[0].data), {
        start: 0,
        howMany: 3,
        searchTerm: 'wedding'
    });
});

test('Posts renders returned previews and advances by the number actually returned', () => {
    const {context, previews, requests, resolve} = createSearchContext({
        footerRect: {top: 900, bottom: 1000}
    });
    const posts = new context.Posts(3, 5, 'portrait');

    resolve(0, [
        {id: 11, title: 'One'},
        {id: 12, title: 'Two'}
    ]);

    assert.deepEqual(plain(previews), [
        {index: 0, post: {id: 11, title: 'One'}},
        {index: 1, post: {id: 12, title: 'Two'}}
    ]);
    assert.equal(posts.loaded, 2);
    assert.equal(posts.loading, false);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 1);
});

test('Posts automatically loads another page while the footer is visible and results remain', () => {
    const {context, requests, resolve} = createSearchContext();
    const posts = new context.Posts(3, 5, 'family');

    resolve(0, [
        {id: 1},
        {id: 2},
        {id: 3}
    ]);

    assert.equal(posts.loaded, 3);
    assert.equal(posts.loading, true);
    assert.equal(posts.complete, false);
    assert.equal(requests.length, 2);
    assert.deepEqual(plain(requests[1].data), {
        start: 3,
        howMany: 3,
        searchTerm: 'family'
    });

    resolve(1, [
        {id: 4},
        {id: 5}
    ]);

    assert.equal(posts.loaded, 5);
    assert.equal(posts.loading, false);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 2);
});

test('Posts stops requesting once all known search results are loaded', () => {
    const {context, requests, resolve} = createSearchContext();
    const posts = new context.Posts(3, 2, 'studio');

    resolve(0, [{id: 1}, {id: 2}]);
    posts.loadImages();

    assert.equal(posts.loaded, 2);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 1);
});

test('Posts does not issue overlapping requests while a page is loading', () => {
    const {context, requests, resolve} = createSearchContext({
        footerRect: {top: 900, bottom: 1000}
    });
    const posts = new context.Posts(3, 6, 'event');

    posts.loadImages();
    posts.loadImages();

    assert.equal(requests.length, 1);

    resolve(0, [{id: 1}, {id: 2}, {id: 3}]);
    posts.loadImages();

    assert.equal(requests.length, 2);
    assert.equal(requests[1].data.start, 3);
});

test('Posts treats an empty response as exhausted and cannot loop forever', () => {
    const {context, requests, resolve} = createSearchContext();
    const posts = new context.Posts(3, 6, 'missing');

    resolve(0, []);
    posts.loadImages();

    assert.equal(posts.loaded, 0);
    assert.equal(posts.loading, false);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 1);
});

test('Posts unlocks loading after a failed request so it can be retried', () => {
    const {context, requests, reject} = createSearchContext({
        footerRect: {top: 900, bottom: 1000}
    });
    const posts = new context.Posts(3, 4, 'retry');

    reject(0);

    assert.equal(posts.loading, false);
    assert.equal(posts.loaded, 0);

    posts.loadImages();

    assert.equal(requests.length, 2);
    assert.equal(requests[1].data.start, 0);
});

test('Posts with zero results does not make an unnecessary request', () => {
    const {context, requests} = createSearchContext();

    const posts = new context.Posts(3, 0, 'nothing');

    assert.equal(posts.loaded, 0);
    assert.equal(posts.loading, false);
    assert.equal(posts.complete, true);
    assert.equal(requests.length, 0);
});
