const assert = require('node:assert/strict');
const {test} = require('node:test');
const fs = require('node:fs');
const path = require('node:path');

test('gallery admin breadcrumb controls do not use overlapping Bootstrap tooltips', () => {
    const source = fs.readFileSync(path.join(process.cwd(), 'public/portrait/galleries.php'), 'utf8');
    for (const id of ['edit-gallery-btn', 'save-gallery-btn', 'sort-gallery-btn']) {
        const match = source.match(new RegExp('<button[^>]*id="' + id + '"[^>]*>', 's'));
        assert.ok(match, id + ' should exist');
        assert.doesNotMatch(match[0], /data-toggle="tooltip"/);
        assert.match(match[0], /title="[^"]+"/);
    }
});
