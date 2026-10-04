const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createConsentContext(cookieValue) {
    const timeouts = [];
    const fn = {};
    const body = Object.create(fn);

    body.off = function () {
        return this;
    };
    body.on = function () {
        return this;
    };

    function emptyElement() {
        return {
            length: 0,
            one() {
                return this;
            },
            modal() {
                return this;
            }
        };
    }

    function $(selector) {
        if (selector === 'body' || selector === body) {
            return body;
        }
        return emptyElement();
    }

    $.fn = fn;
    $.each = function (collection, callback) {
        if (Array.isArray(collection)) {
            collection.forEach((value, index) => callback(index, value));
            return;
        }
        for (const [key, value] of Object.entries(collection || {})) {
            callback(key, value);
        }
    };

    loadBrowserScript('public/js/site-consent.js', {
        $,
        jQuery: $,
        document: {},
        JSON,
        readCookie(name) {
            return name === 'CookiePreferences' ? cookieValue : null;
        },
        deleteCookie() {},
        createCookie() {},
        setTimeout(callback, delay) {
            timeouts.push({callback, delay});
            return timeouts.length;
        }
    });

    return {
        body,
        plugin: $.fn.bsgdprcookies,
        timeouts
    };
}

test('site consent parses valid saved preferences', () => {
    const {plugin} = createConsentContext('["preferences","analytics"]');

    assert.deepEqual(
        plain(plugin.GetUserPreferences()),
        ['preferences', 'analytics']
    );
    assert.equal(plugin.PreferenceExists('preferences'), true);
    assert.equal(plugin.PreferenceExists('social'), false);
});

test('site consent treats a missing preference cookie as no granted preferences', () => {
    const {plugin} = createConsentContext(null);

    assert.deepEqual(plain(plugin.GetUserPreferences()), []);
    assert.equal(plugin.PreferenceExists('preferences'), false);
});

test('site consent treats a malformed preference cookie as no granted preferences', () => {
    const {plugin} = createConsentContext('{not-json');

    assert.deepEqual(plain(plugin.GetUserPreferences()), []);
    assert.equal(plugin.PreferenceExists('analytics'), false);
});

test('site consent prompts again when the saved preference cookie is malformed', () => {
    const {body, timeouts} = createConsentContext('{not-json');

    body.bsgdprcookies();

    assert.equal(timeouts.length, 1);
    assert.equal(timeouts[0].delay, 1500);
});

test('site consent does not prompt again after a valid explicit rejection', () => {
    const {body, timeouts} = createConsentContext('[]');

    body.bsgdprcookies();

    assert.equal(timeouts.length, 0);
});

test('site consent reinit opens customization immediately even with saved preferences', () => {
    const {body, timeouts} = createConsentContext('["preferences"]');

    body.bsgdprcookies('reinit');

    assert.equal(timeouts.length, 1);
    assert.equal(timeouts[0].delay, 0);
});
