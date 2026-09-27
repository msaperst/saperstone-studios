const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const applicationFiles = [
    'public/user/users.php',
    'public/js/contract.js',
    'public/js/albums-admin.js',
    'public/user/contracts.php',
    'public/user/album.php',
    'public/js/contract-admin.js',
    'public/blog/post.php',
    'public/user/index.php',
    'public/js/user.js',
    'public/user/contract/photobooth.php',
    'public/user/contract/contractor.php',
    'public/js/site-consent.js',
    'public/js/gallery-admin.js',
    'public/user/contract/commercial.php',
    'public/js/posts-manage.js'
];

test('application controls use native titles instead of Bootstrap tooltips', () => {
    for (const relativePath of applicationFiles) {
        const source = fs.readFileSync(path.resolve(relativePath), 'utf8');
        assert.doesNotMatch(source, /data-toggle=(["'])tooltip\1/, relativePath);
        assert.doesNotMatch(source, /data-placement=(["'])[^"']+\1/, relativePath);
        assert.doesNotMatch(source, /\[data-toggle=["']tooltip["']\][^;]*\.tooltip\(/, relativePath);
    }
});
