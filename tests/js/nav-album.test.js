const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createRequest(calls, type, url, data, responseData) {
    calls[type].push({url, data});
    const request = {
        done(callback) {
            if (responseData !== undefined) {
                callback(responseData);
            }
            return request;
        },
        fail() {
            return request;
        },
        always() {
            return request;
        }
    };
    return request;
}

function createContext(addChecked, responseData) {
    const calls = {
        get: [],
        post: []
    };
    const elements = new Map();

    function element(selector) {
        if (!elements.has(selector)) {
            elements.set(selector, {
                value: '',
                checked: false,
                appended: [],
                val(value) {
                    if (arguments.length) {
                        this.value = value;
                        return this;
                    }
                    return this.value;
                },
                is(query) {
                    return query === ':checked' ? this.checked : false;
                },
                append(value) {
                    this.appended.push(value);
                    return this;
                },
                remove() {
                    return this;
                }
            });
        }
        return elements.get(selector);
    }

    const $ = function (value) {
        if (typeof value === 'function') {
            return undefined;
        }
        return element(value);
    };
    $.get = (url, data) => createRequest(calls, 'get', url, data, responseData);
    $.post = (url, data) => createRequest(calls, 'post', url, data, responseData);
    $.isNumeric = (value) => !Number.isNaN(Number(value));

    let dialogConfig;
    const windowObject = {
        location: {
            hash: '',
            pathname: '/'
        }
    };

    const context = loadBrowserScript('public/js/nav.js', {
        $,
        BootstrapDialog: {
            show(config) {
                dialogConfig = config;
            }
        },
        window: windowObject,
        top: {
            location: {
                pathname: '/'
            }
        },
        location: {
            hostname: '',
            reload() {}
        },
        document: {
            cookie: ''
        },
        setTimeout() {},
        JSON
    });

    element('#find-album-code').value = 'ABC123';
    element('#find-album-add').checked = addChecked;

    context.findAlbum();

    const modalBody = {
        append() {
            return this;
        },
        remove() {
            return this;
        }
    };
    const modal = {
        find() {
            return modalBody;
        }
    };
    const button = {
        spin() {},
        stopSpin() {},
        closest() {
            return modal;
        }
    };
    const dialog = {
        enableButtons() {},
        setClosable() {}
    };

    dialogConfig.buttons[0].action.call(button, dialog);

    return {calls, windowObject};
}

test('nav findAlbum posts the code when adding the album to the user', () => {
    const {calls} = createContext(true);

    assert.deepEqual(plain(calls.post), [{
        url: '/api/add-album.php',
        data: {
            code: 'ABC123'
        }
    }]);
    assert.deepEqual(plain(calls.get), []);
});

test('nav findAlbum uses safe GET for lookup-only requests', () => {
    const {calls} = createContext(false);

    assert.deepEqual(plain(calls.get), [{
        url: '/api/find-album.php',
        data: {
            code: 'ABC123'
        }
    }]);
    assert.deepEqual(plain(calls.post), []);
});

test('nav findAlbum navigates when an album is already in the user list', () => {
    const {windowObject} = createContext(true, {
        id: '42',
        added: false
    });

    assert.equal(windowObject.location.href, '/user/album.php?album=42');
});
