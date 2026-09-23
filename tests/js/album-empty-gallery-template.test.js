const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');
const {projectRoot} = require('./helpers/load-browser-script');

test('album.php always renders the gallery shell for an initially empty album', () => {
    const template = fs.readFileSync(
        path.join(projectRoot, 'public/user/album.php'),
        'utf8'
    );

    const gridIndex = template.indexOf('id="album-grid"');
    const emptyStateConditionIndex = template.indexOf('if (count($images) === 0)');

    assert.notEqual(gridIndex, -1);
    assert.notEqual(emptyStateConditionIndex, -1);
    assert.ok(
        gridIndex < emptyStateConditionIndex,
        'gallery grid must exist before the empty-state-only conditional'
    );
    assert.doesNotMatch(
        template,
        /if \(count\(\$images\) > 0\)[\s\S]*?id="album-grid"/
    );
});
