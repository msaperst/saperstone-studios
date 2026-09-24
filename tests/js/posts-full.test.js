const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createFullPostsContext(options = {}) {
    const requests = [];
    const rendered = [];
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
    $.get = function (url, data, success) {
        requests.push({url, data, success});
    };

    const context = loadBrowserScript('public/js/posts-full.js', {
        $,
        window: windowObject,
        loadPost(data, header) {
            rendered.push({data, header});
        }
    });

    return {
        context,
        footer,
        rendered,
        requests,
        resolve(index, data) {
            requests[index].success(data);
        }
    };
}

test('posts-full.js detects whether an element intersects the viewport', () => {
    const {footer} = createFullPostsContext();

    footer.rect = {top: 100, bottom: 200};
    assert.equal(footer.isOnScreen(), true);

    footer.rect = {top: 900, bottom: 1000};
    assert.equal(footer.isOnScreen(), false);

    footer.rect = {top: -200, bottom: -1};
    assert.equal(footer.isOnScreen(), false);
});

test('PostsFull requests the first post and forwards category tags', () => {
    const {context, requests} = createFullPostsContext({
        footerRect: {top: 900, bottom: 1000}
    });

    const posts = new context.PostsFull(3, [4, 7]);

    assert.equal(posts.loaded, 1);
    assert.equal(posts.totalPosts, 3);
    assert.deepEqual(plain(posts.tag), [4, 7]);
    assert.equal(requests.length, 1);
    assert.equal(requests[0].url, '/api/get-blogs-full.php');
    assert.deepEqual(plain(requests[0].data), {
        start: 0,
        tag: [4, 7]
    });
});

test('PostsFull renders a returned post using the blog list heading', () => {
    const {context, rendered, resolve} = createFullPostsContext({
        footerRect: {top: 900, bottom: 1000}
    });

    new context.PostsFull(1);
    resolve(0, {id: 11, title: 'Sample'});

    assert.deepEqual(plain(rendered), [{
        data: {id: 11, title: 'Sample'},
        header: '<h2>'
    }]);
});

test('PostsFull automatically requests the next post while the footer is visible', () => {
    const {context, requests, resolve} = createFullPostsContext();

    const posts = new context.PostsFull(2);

    assert.doesNotThrow(() => {
        resolve(0, {id: 1});
    });

    assert.equal(posts.loaded, 2);
    assert.equal(requests.length, 2);
    assert.deepEqual(plain(requests[1].data), {
        start: 1
    });
});

test('PostsFull stops automatic loading once totalPosts has been reached', () => {
    const {context, requests, resolve} = createFullPostsContext();

    const posts = new context.PostsFull(2);

    resolve(0, {id: 1});
    resolve(1, {id: 2});

    assert.equal(posts.loaded, 2);
    assert.equal(requests.length, 2);
});
