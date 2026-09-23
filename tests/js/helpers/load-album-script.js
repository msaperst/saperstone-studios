const fs = require('node:fs');
const path = require('node:path');
const {loadBrowserScripts, projectRoot} = require('./load-browser-script');

function loadAlbumScript(relativePath, globals = {}) {
    const commonPath = 'public/js/albums-common.js';
    const scripts = [];

    if (fs.existsSync(path.join(projectRoot, commonPath))) {
        scripts.push(commonPath);
    }

    scripts.push(relativePath);
    return loadBrowserScripts(scripts, globals);
}

module.exports = {
    loadAlbumScript
};
