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

test('blog sharing no longer loads the legacy Facebook or Twitter widgets', () => {
    const post = read('public/js/post.js');

    assert.doesNotMatch(post, /facebook-jssdk|connect\.facebook\.net|fb-like/i);
    assert.doesNotMatch(post, /twitter\.com\/intent\/like/i);
    assert.doesNotMatch(post, /addSocialMedias|loadSM|socialTrackingAllowed/);
});

test('cookie consent and privacy docs do not advertise an unused social cookie category', () => {
    const consent = read('public/js/site-consent.js');
    const privacy = read('public/Privacy-Policy.php');
    const cookies = read('docs/cookies.md');

    assert.doesNotMatch(consent, /name:\s*['"]social['"]/i);
    assert.doesNotMatch(consent, /embedded social media buttons/i);
    assert.doesNotMatch(privacy, /Facebook SDK|Social Media cookies/i);
    assert.doesNotMatch(cookies, /Facebook SDK|social cookies/i);
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


test('blog sharing footer is compact and visually separated from post content', () => {
    const css = read('public/css/saperstone-studios.css');

    assert.match(css, /\.blog-share-footer\s*\{/);
    assert.match(css, /border-top:\s*1px solid/);
    assert.match(css, /text-align:\s*right/);
    assert.match(css, /\.blog-share-button\s*\{/);
});
