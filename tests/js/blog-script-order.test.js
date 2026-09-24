const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const projectRoot = path.resolve(__dirname, '../..');

const pages = [
    ['public/blog/post.php', ['/js/post.js', '/js/post-full.js']],
    ['public/blog/new.php', ['/js/post-admin.js']],
    ['public/blog/manage.php', ['/js/post-admin.js', '/js/posts-manage.js']],
    ['public/blog/posts.php', ['/js/post.js', '/js/posts.js']],
    ['public/blog/search.php', ['/js/post.js', '/js/posts-search.js']],
    ['public/blog/index.php', ['/js/post.js', '/js/posts-full.js']],
    ['public/blog/category.php', ['/js/post.js', '/js/posts-full.js']]
];

test('blog pages load shared helpers before every dependent blog script', () => {
    for (const [page, dependentScripts] of pages) {
        const source = fs.readFileSync(path.join(projectRoot, page), 'utf8');
        const commonIndex = source.indexOf('/js/blog-common.js');

        assert.notEqual(commonIndex, -1, `${page} should load blog-common.js`);

        for (const dependentScript of dependentScripts) {
            const dependentIndex = source.indexOf(dependentScript);
            assert.notEqual(dependentIndex, -1, `${page} should load ${dependentScript}`);
            assert.ok(
                commonIndex < dependentIndex,
                `${page} should load blog-common.js before ${dependentScript}`
            );
        }
    }
});
