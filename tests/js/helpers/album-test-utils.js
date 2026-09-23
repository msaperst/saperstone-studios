const {createJQueryEnvironment} = require('./jquery-mock');
const {loadAlbumScript} = require('./load-album-script');

function createAlbumContext(scriptPath, options = {}) {
    const environment = createJQueryEnvironment({
        lengths: {
            '#albums': 1,
            ...(options.lengths || {})
        }
    });

    const windowObject = {
        album: options.album ?? null
    };

    const globals = {
        $: environment.$,
        window: windowObject,
        document: environment.document,
        BootstrapDialog: environment.BootstrapDialog,
        setTimeout: environment.setTimeout,
        setInterval: environment.setInterval,
        clearInterval: environment.clearInterval,
        my_id: options.myId ?? 5,
        refreshAlbumThumbnailImages: options.refreshAlbumThumbnailImages
    };

    const context = loadAlbumScript(scriptPath, globals);
    return {context, environment, window: windowObject};
}

module.exports = {
    createAlbumContext
};
