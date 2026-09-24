const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const projectRoot = path.resolve(__dirname, '../..');

for (const page of ['public/blog/index.php', 'public/blog/category.php']) {
    test(`${page} delegates full-post loading state to PostsFull`, () => {
        const source = fs.readFileSync(path.join(projectRoot, page), 'utf8');

        assert.doesNotMatch(source, /var\s+loaded\s*=\s*0/);
        assert.match(source, /postsFull\.loadPosts\(\)/);
    });
}
