const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const css = fs.readFileSync(
    path.resolve(__dirname, '../../public/css/saperstone-studios.css'),
    'utf8'
);

test('blog share buttons provide local CSP-safe AddToAny icon dimensions', () => {
    assert.match(css, /\.blog-share-buttons\s+\.a2a_svg\s*\{/);
    assert.match(css, /width:\s*48px\s*!important/);
    assert.match(css, /height:\s*48px\s*!important/);
});

test('blog share button labels remain accessible but visually hidden', () => {
    assert.match(css, /\.blog-share-buttons\s+\.a2a_label\s*\{/);
    assert.match(css, /clip-path:\s*inset\(50%\)/);
    assert.match(css, /white-space:\s*nowrap/);
});
