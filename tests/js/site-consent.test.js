const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createConsentContext(cookieValue, cookieShow = null) {
    const environment = createJQueryEnvironment({autoReady: false});
    const cookies = new Map();
    if (cookieValue !== null) {
        cookies.set('CookiePreferences', cookieValue);
    }
    if (cookieShow !== null) {
        cookies.set('CookieShow', cookieShow);
    }
    const calls = {
        created: [],
        deleted: []
    };

    loadBrowserScript('public/js/site-consent.js', {
        $: environment.$,
        jQuery: environment.$,
        document: environment.document,
        JSON,
        readCookie(name) {
            return cookies.has(name) ? cookies.get(name) : null;
        },
        deleteCookie(name) {
            calls.deleted.push(name);
            cookies.delete(name);
        },
        createCookie(name, value, days) {
            calls.created.push({name, value, days});
            cookies.set(name, value);
        },
        setTimeout: environment.setTimeout
    });

    return {
        body: environment.element('body'),
        calls,
        cookies,
        environment,
        plugin: environment.$.fn.bsgdprcookies
    };
}

function delegated(body, selector) {
    return body.delegatedHandlers.get('click.bsgdprcookies ' + selector);
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
    const {body, environment} = createConsentContext('{not-json');

    body.bsgdprcookies();

    assert.equal(environment.timeouts?.length ?? 0, 0);
    environment.runTimeouts();
    assert.equal(body.appended.length, 1);
    assert.match(String(body.appended[0]), /Cookies & Privacy Policy/);
    assert.match(String(body.appended[0]), /Site Preferences/);
    assert.match(String(body.appended[0]), /Analytics/);
});

test('site consent does not prompt again after a valid explicit rejection', () => {
    const {body, environment} = createConsentContext('[]');

    body.bsgdprcookies();
    environment.runTimeouts();

    assert.equal(body.appended.length, 0);
    assert.deepEqual(environment.element('#bs-gdpr-cookies-modal').modalCalls, ['hide']);
});

test('site consent reinit opens customization immediately even with saved preferences', () => {
    const {body, environment} = createConsentContext('["preferences"]');

    body.bsgdprcookies('reinit');

    environment.runTimeouts();
    assert.equal(body.appended.length, 1);
    assert.match(String(body.appended[0]), /Customize/);
});

test('site consent removes the legacy CookieShow marker when initializing', () => {
    const {body, calls} = createConsentContext('[]', '1');

    body.bsgdprcookies();

    assert.deepEqual(calls.deleted, ['CookieShow']);
});

test('site consent accept persists selected preferences and immediately enables remember-me controls', () => {
    const {body, calls, environment} = createConsentContext(null);
    let preferenceEvent = null;
    environment.element('__document__').on('cookiePreferencesChanged', (eventData) => {
        preferenceEvent = eventData[0];
    });

    body.bsgdprcookies();

    const choices = environment.element('input[name="bsgdpr[]"]');
    choices.serializedValues = [
        {name: 'bsgdpr[]', value: 'necessary'},
        {name: 'bsgdpr[]', value: 'preferences'},
        {name: 'bsgdpr[]', value: 'analytics'}
    ];

    const accept = delegated(body, '#bs-gdpr-cookies-modal-accept-btn');
    assert.equal(typeof accept, 'function');
    accept.call(body);

    assert.deepEqual(calls.deleted, ['CookiePreferences']);
    assert.deepEqual(calls.created, [{
        name: 'CookiePreferences',
        value: '["necessary","preferences","analytics"]',
        days: 365
    }]);
    assert.equal(environment.element('#profile-remember-span').visible, true);
    assert.equal(environment.element('#login-remember-span').visible, true);
    assert.equal(environment.element('#forgot-password-remember-span').visible, true);
    assert.deepEqual(plain(preferenceEvent), ['necessary', 'preferences', 'analytics']);
    assert.deepEqual(environment.element('#bs-gdpr-cookies-modal').modalCalls, ['hide']);
});

test('site consent accept without preferences disables persistent-login controls', () => {
    const {body, environment} = createConsentContext(null);
    body.bsgdprcookies();

    environment.element('#profile-remember').prop('checked', true);
    environment.element('#login-remember').prop('checked', true);
    environment.element('#forgot-password-remember').prop('checked', true);
    environment.element('input[name="bsgdpr[]"]').serializedValues = [
        {name: 'bsgdpr[]', value: 'necessary'}
    ];

    delegated(body, '#bs-gdpr-cookies-modal-accept-btn').call(body);

    const spans = environment.element(
        '#profile-remember-span, #login-remember-span, #forgot-password-remember-span'
    );
    const checks = environment.element(
        '#profile-remember, #login-remember, #forgot-password-remember'
    );
    assert.equal(spans.visible, false);
    assert.equal(checks.prop('checked'), false);
});

test('site consent customize clears optional selections and reveals advanced choices', () => {
    const {body, environment} = createConsentContext(null);
    body.bsgdprcookies();

    const optional = environment.element('input[name="bsgdpr[]"]:not(:disabled)')
        .attr('data-auto', 'on')
        .prop('checked', true);

    const customize = delegated(body, '#bs-gdpr-cookies-modal-advanced-btn');
    assert.equal(typeof customize, 'function');
    customize.call(body);

    assert.equal(optional.attr('data-auto'), 'off');
    assert.equal(optional.prop('checked'), false);
    assert.equal(environment.element('#bs-gdpr-cookies-modal-advanced-types').visible, true);
    assert.equal(environment.element('#bs-gdpr-cookies-modal-advanced-btn').prop('disabled'), true);
});

test('site consent modal is disposed after Bootstrap finishes hiding it', () => {
    const {body, environment} = createConsentContext('[]');
    const modal = environment.element('#bs-gdpr-cookies-modal');

    body.bsgdprcookies();
    modal.trigger('hidden.bs.modal');

    assert.equal(modal.removed, true);
});
