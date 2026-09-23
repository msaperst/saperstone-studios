const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createRequest(calls, type, url, data) {
    calls[type].push({url, data});
    const request = {
        done() {
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

function createContext(addChecked) {
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
    $.get = (url, data) => createRequest(calls, 'get', url, data);
    $.post = (url, data) => createRequest(calls, 'post', url, data);
    $.isNumeric = (value) => !Number.isNaN(Number(value));

    let dialogConfig;
    const context = loadBrowserScript('public/js/nav.js', {
        $,
        BootstrapDialog: {
            show(config) {
                dialogConfig = config;
            }
        },
        window: {
            location: {
                hash: '',
                pathname: '/'
            }
        },
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

    return {calls};
}

test('nav findAlbum posts the code when adding the album to the user', () => {
    const {calls} = createContext(true);

    assert.deepEqual(calls.post, [{
        url: '/api/add-album.php',
        data: {
            code: 'ABC123'
        }
    }]);
    assert.deepEqual(calls.get, []);
});

test('nav findAlbum uses safe GET for lookup-only requests', () => {
    const {calls} = createContext(false);

    assert.deepEqual(calls.get, [{
        url: '/api/find-album.php',
        data: {
            code: 'ABC123'
        }
    }]);
    assert.deepEqual(calls.post, []);
});
