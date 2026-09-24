const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const projectRoot = path.resolve(__dirname, '../..');

function read(relativePath) {
    return fs.readFileSync(path.join(projectRoot, relativePath), 'utf8');
}

test('blog viewport detection has one shared implementation', () => {
    const scripts = [
        'public/js/blog-common.js',
        'public/js/posts.js',
        'public/js/posts-search.js',
        'public/js/posts-full.js'
    ];

    const definitions = scripts.filter((script) =>
        read(script).includes('$.fn.isOnScreen = function')
    );

    assert.deepEqual(definitions, ['public/js/blog-common.js']);
});

test('blog preview list variants use the shared loader implementation', () => {
    for (const script of ['public/js/posts.js', 'public/js/posts-search.js']) {
        const source = read(script);

        assert.match(source, /createPostPreviewLoader\s*\(/);
        assert.doesNotMatch(source, /prototype\.loadImages\s*=\s*function/);
        assert.doesNotMatch(source, /\$\.get\s*\(/);
    }
});

test('shared blog request helpers stay centralized in blog-common.js', () => {
    const common = read('public/js/blog-common.js');
    assert.match(common, /function appendBlogRequestError\s*\(/);
    assert.match(common, /function setBlogControlsDisabled\s*\(/);

    for (const script of [
        'public/js/post.js',
        'public/js/post-admin.js',
        'public/js/posts-manage.js'
    ]) {
        const source = read(script);
        assert.doesNotMatch(source, /function appendBlogRequestError\s*\(/);
        assert.doesNotMatch(source, /function setBlogControlsDisabled\s*\(/);
    }
});
