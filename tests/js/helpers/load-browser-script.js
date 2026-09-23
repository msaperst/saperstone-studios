const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function loadBrowserScript(relativePath, globals = {}) {
    const projectRoot = path.resolve(__dirname, '../../..');
    const absolutePath = path.join(projectRoot, relativePath);
    const context = vm.createContext({
        console,
        ...globals
    });

    vm.runInContext(fs.readFileSync(absolutePath, 'utf8'), context, {
        filename: absolutePath
    });

    return context;
}

module.exports = {
    loadBrowserScript
};
