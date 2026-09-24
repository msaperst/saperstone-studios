const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const projectRoot = path.resolve(__dirname, '../..');

const pages = [
    ['public/blog/post.php', '/js/post.js'],
    ['public/blog/new.php', '/js/post-admin.js'],
    ['public/blog/manage.php', '/js/post-admin.js'],
    ['public/blog/posts.php', '/js/posts.js'],
    ['public/blog/search.php', '/js/posts-search.js'],
    ['public/blog/index.php', '/js/posts-full.js'],
    ['public/blog/category.php', '/js/posts-full.js']
];

test('blog pages load shared helpers before dependent blog scripts', () => {
    for (const [page, dependentScript] of pages) {
        const source = fs.readFileSync(path.join(projectRoot, page), 'utf8');
        const commonIndex = source.indexOf('/js/blog-common.js');
        const dependentIndex = source.indexOf(dependentScript);

        assert.notEqual(commonIndex, -1, `${page} should load blog-common.js`);
        assert.notEqual(dependentIndex, -1, `${page} should load ${dependentScript}`);
        assert.ok(
            commonIndex < dependentIndex,
            `${page} should load blog-common.js before ${dependentScript}`
        );
    }
});
