const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {test} = require('node:test');
const {projectRoot} = require('./helpers/load-browser-script');

for (const configPath of [
    '.docker/php/000-default.conf',
    '.docker/php/default-ssl.conf'
]) {
    test(`${configPath} blocks direct external image requests`, () => {
        const config = fs.readFileSync(path.join(projectRoot, configPath), 'utf8');

        assert.match(
            config,
            /#all other images are dead to the outside world/
        );
        assert.match(
            config,
            /RewriteRule \(\?i\)\\\.\(png\|gif\|jpg\|jpeg\)\$\s+- \[F\]/
        );
        assert.match(
            config,
            /RewriteCond %\{HTTP_REFERER\} !\^https?:\/\//
        );
    });
}
