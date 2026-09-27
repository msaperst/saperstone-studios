const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');

function javascriptFiles(directory) {
    return fs.readdirSync(directory, {withFileTypes: true}).flatMap((entry) => {
        const entryPath = path.join(directory, entry.name);
        if (entry.isDirectory()) {
            return javascriptFiles(entryPath);
        }
        return entry.isFile() && entry.name.endsWith('.js') ? [entryPath] : [];
    });
}

test('application JavaScript does not use Bootstrap tooltips', () => {
    for (const relativePath of javascriptFiles('public/js')) {
        const source = fs.readFileSync(relativePath, 'utf8');
        assert.doesNotMatch(source, /data-toggle["']?\s*[:=]\s*["']tooltip["']/, relativePath);
        assert.doesNotMatch(source, /\[data-toggle=["']tooltip["']\]/, relativePath);
        assert.doesNotMatch(source, /\.tooltip\s*\(/, relativePath);
    }
});
