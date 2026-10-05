const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext(token = 'csrf-token-value') {
    function $(selector) {
        if (typeof selector === 'function') {
            return {};
        }
        if (selector === '#csrf-token') {
            return {
                val() {
                    return token;
                }
            };
        }
        return {};
    }

    return loadBrowserScript('public/js/nav.js', {
        $,
        window: {
            location: {
                hash: '',
                pathname: '/',
                search: ''
            }
        }
    });
}

test('nav adds CSRF token header to state-changing AJAX requests', () => {
    const context = createContext('abc123');
    const headers = {};
    const xhr = {
        setRequestHeader(name, value) {
            headers[name] = value;
        }
    };

    context.addCsrfHeader(xhr, {type: 'POST'});

    assert.deepEqual(headers, {
        'X-CSRF-Token': 'abc123'
    });
});

test('nav does not add CSRF token header to safe AJAX requests', () => {
    const context = createContext('abc123');

    for (const method of ['GET', 'HEAD', 'OPTIONS']) {
        let called = false;
        context.addCsrfHeader({
            setRequestHeader() {
                called = true;
            }
        }, {type: method});
        assert.equal(called, false, method);
    }
});

test('nav does not send an empty CSRF token', () => {
    const context = createContext('');
    let called = false;

    context.addCsrfHeader({
        setRequestHeader() {
            called = true;
        }
    }, {type: 'POST'});

    assert.equal(called, false);
});
