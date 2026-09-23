const {createJQueryEnvironment} = require('./jquery-mock');
const {loadBrowserScript} = require('./load-browser-script');

function createAlbumDetailContext(options = {}) {
    const environment = createJQueryEnvironment({
        lengths: {
            '#album-grid .album-card': 0,
            '#album-grid .album-card img[data-src]': 0,
            ...(options.lengths || {})
        }
    });

    const windowObject = {
        innerHeight: options.innerHeight ?? 800,
        location: {
            hash: options.hash ?? '',
            search: options.search ?? '',
            pathname: '/user/album.php'
        }
    };
    const documentObject = environment.document;
    documentObject.title = 'Album';

    const historyCalls = [];
    const history = {
        replaceState(...args) {
            historyCalls.push(args);
        }
    };

    const globals = {
        $: environment.$,
        jQuery: environment.$,
        window: windowObject,
        document: documentObject,
        history,
        URLSearchParams,
        BootstrapDialog: environment.BootstrapDialog,
        setTimeout: environment.setTimeout,
        clearTimeout() {},
        showImageTitle: options.showImageTitle ?? false,
        setupImageAccess() {},
        deleteImage() {}
    };

    const context = loadBrowserScript('public/js/album.js', globals);
    windowObject.album = null;

    return {
        context,
        environment,
        window: windowObject,
        document: documentObject,
        historyCalls
    };
}

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

module.exports = {
    createAlbumDetailContext,
    plain
};
