const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const projectRoot = path.resolve(__dirname, '../../..');

function loadBrowserScripts(relativePaths, globals = {}) {
    const context = vm.createContext({
        console,
        ...globals
    });

    for (const relativePath of relativePaths) {
        const absolutePath = path.join(projectRoot, relativePath);
        vm.runInContext(fs.readFileSync(absolutePath, 'utf8'), context, {
            filename: absolutePath
        });
    }

    return context;
}

function loadBrowserScript(relativePath, globals = {}) {
    return loadBrowserScripts([relativePath], globals);
}

module.exports = {
    loadBrowserScript,
    loadBrowserScripts,
    projectRoot
};
