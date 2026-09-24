const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

const projectRoot = path.resolve(__dirname, '../..');

test('every post-prefixed blog JavaScript file has direct unit coverage', () => {
    const scripts = fs.readdirSync(path.join(projectRoot, 'public/js'))
        .filter((file) => /^post.*\.js$/.test(file))
        .sort();

    assert.ok(scripts.length > 0);

    for (const script of scripts) {
        const expectedTest = path.join(
            projectRoot,
            'tests/js',
            script.replace(/\.js$/, '.test.js')
        );

        assert.equal(
            fs.existsSync(expectedTest),
            true,
            `${script} should have direct JS unit coverage`
        );
    }
});
