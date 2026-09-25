const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const root = path.resolve(__dirname, '../..');

function read(relativePath) {
    return fs.readFileSync(path.join(root, relativePath), 'utf8');
}

test('blog sharing no longer depends on AddToAny', () => {
    const files = [
        'public/js/post.js',
        'public/css/saperstone-studios.css',
        '.docker/php/security-headers.conf',
        'public/Privacy-Policy.php',
        'docs/cookies.md'
    ];

    for (const file of files) {
        assert.doesNotMatch(read(file), /addtoany|a2a_/i, file);
    }
});

test('cookie consent describes only embedded social media as optional social functionality', () => {
    const consent = read('public/js/site-consent.js');

    assert.doesNotMatch(consent, /sharing tools/i);
    assert.match(consent, /embedded social media buttons/i);
});

test('terms distinguish link sharing from redistribution rights', () => {
    const terms = read('public/Terms-of-Use.php');

    assert.match(terms, /sharing a blog link/i);
    assert.match(terms, /does not grant/i);
    assert.match(terms, /Last updated September 24, 2026/);
});

test('privacy policy describes native browser sharing without a sharing provider', () => {
    const privacy = read('public/Privacy-Policy.php');

    assert.match(privacy, /native sharing/i);
    assert.match(privacy, /browser or device/i);
    assert.match(privacy, /Effective September 24, 2026/);
});
