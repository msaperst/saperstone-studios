const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function createNavContext(options = {}) {
    const elements = new Map();
    const calls = {
        post: [],
        expiredCookies: [],
        createdCookies: [],
        reloads: 0,
        timeouts: []
    };
    const responses = new Map();

    function element(selector) {
        if (!elements.has(selector)) {
            elements.set(selector, {
                selector,
                value: '',
                checked: false,
                visible: true,
                disabled: false,
                htmlValue: '',
                appended: [],
                modalCalls: [],
                removed: false,
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
                prop(name, value) {
                    if (arguments.length === 1) {
                        return name === 'checked' ? this.checked : this[name];
                    }
                    if (name === 'checked') {
                        this.checked = value;
                    } else if (name === 'disabled') {
                        this.disabled = value;
                    } else {
                        this[name] = value;
                    }
                    return this;
                },
                show() {
                    this.visible = true;
                    return this;
                },
                hide() {
                    this.visible = false;
                    return this;
                },
                empty() {
                    this.htmlValue = '';
                    this.appended = [];
                    return this;
                },
                html(value) {
                    if (arguments.length) {
                        this.htmlValue = value;
                        return this;
                    }
                    return this.htmlValue;
                },
                modal(action) {
                    this.modalCalls.push(action);
                    return this;
                },
                remove() {
                    this.removed = true;
                    return this;
                },
                closest() {
                    return this;
                }
            });
        }
        return elements.get(selector);
    }

    function deferred(response = {type: 'success', data: ''}) {
        return {
            done(callback) {
                if (response.type === 'success') {
                    callback(response.data);
                }
                return this;
            },
            fail(callback) {
                if (response.type === 'failure') {
                    callback(
                        response.xhr || {responseText: ''},
                        response.status || 'error',
                        response.error || 'Error'
                    );
                }
                return this;
            },
            always(callback) {
                callback();
                return this;
            }
        };
    }

    function $(selector) {
        if (typeof selector === 'function') {
            return undefined;
        }
        return element(String(selector));
    }

    $.post = function (url, data) {
        calls.post.push({url, data});
        return deferred(responses.get(url));
    };

    $.isNumeric = (value) => value !== null && value !== '' && !Number.isNaN(Number(value));

    const location = {
        hash: options.hash || '',
        pathname: options.pathname || '/',
        search: options.search || '',
        hostname: 'example.test',
        href: '',
        reload() {
            calls.reloads += 1;
        }
    };
    const windowObject = {location};

    const cookies = new Map(Object.entries(options.cookies || {}));
    const context = loadBrowserScript('public/js/nav.js', {
        $,
        window: windowObject,
        location,
        top: {location: {pathname: '/'}},
        document: {cookie: options.documentCookie || ''},
        readCookie(name) {
            return cookies.has(name) ? cookies.get(name) : null;
        },
        expireCookie(name, domain) {
            calls.expiredCookies.push({name, domain});
            cookies.delete(name);
        },
        createCookie(name, value) {
            calls.createdCookies.push({name, value});
        },
        BootstrapDialog: {
            show() {}
        },
        setTimeout(callback, delay) {
            calls.timeouts.push({callback, delay});
            return calls.timeouts.length;
        },
        JSON
    });

    return {
        calls,
        context,
        element,
        location,
        queuePost(url, response) {
            responses.set(url, response);
        },
        runTimeouts() {
            for (const timeout of calls.timeouts.splice(0)) {
                timeout.callback();
            }
        }
    };
}

test('nav treats malformed cookie preferences as no consent and expires the corrupt cookie', () => {
    const {calls, context} = createNavContext({
        cookies: {CookiePreferences: '{not-json'}
    });

    assert.deepEqual(plain(context.getCookiePreferences()), []);
    assert.deepEqual(calls.expiredCookies, [{
        name: 'CookiePreferences',
        domain: ''
    }]);
});

test('nav submitLogin sends credentials, remember state, and reloads after success', () => {
    const {calls, context, element, queuePost} = createNavContext();
    element('#csrf-token').val('csrf123');
    element('#login-user').val('testUser');
    element('#login-pass').val('secret');
    element('#login-remember').checked = true;
    queuePost('/api/login.php', {type: 'success', data: ''});

    context.submitLogin();

    assert.deepEqual(plain(calls.post[0]), {
        url: '/api/login.php',
        data: {
            csrf_token: 'csrf123',
            username: 'testUser',
            password: 'secret',
            rememberMe: 1,
            submit: 'Login'
        }
    });
    assert.match(
        element('#login-modal .modal-body').appended.join(''),
        /Successfully Logged In/
    );
    assert.equal(calls.reloads, 1);
});

test('nav submitLogin shows server validation without reloading', () => {
    const {calls, context, element, queuePost} = createNavContext();
    queuePost('/api/login.php', {
        type: 'success',
        data: 'Invalid username or password'
    });

    context.submitLogin();

    assert.match(
        element('#login-modal .modal-body').appended.join(''),
        /Invalid username or password/
    );
    assert.equal(calls.reloads, 0);
});

test('nav logout redirects authenticated user pages to the home page', () => {
    const {calls, context, element, location, queuePost} = createNavContext({
        pathname: '/user/profile.php'
    });
    element('#csrf-token').val('csrf123');
    queuePost('/api/login.php', {type: 'success', data: ''});

    context.logout();

    assert.deepEqual(plain(calls.post[0]), {
        url: '/api/login.php',
        data: {
            csrf_token: 'csrf123',
            submit: 'Logout'
        }
    });
    assert.equal(context.window.location, '/');
});

test('nav forgotPasswordSubmit opens the reset form and always re-enables its button', () => {
    const {calls, context, element, queuePost} = createNavContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });
    element('#forgot-password-email').val('person@example.com');
    queuePost('/api/send-reset-code.php', {type: 'success', data: ''});

    context.forgotPasswordSubmit();

    assert.deepEqual(plain(calls.post[0]), {
        url: '/api/send-reset-code.php',
        data: {email: 'person@example.com'}
    });
    assert.equal(element('#forgot-password-submit').disabled, false);
    assert.equal(element('#forgot-password-code').visible, true);
    assert.equal(element('#forgot-password-new-password').visible, true);
    assert.equal(element('#forgot-password-new-password-confirm').visible, true);
    assert.equal(element('#forgot-password-reset-password').visible, true);
    assert.equal(element('#forgot-password-remember-span').visible, true);
});

test('nav forgotPasswordReset submits reset details and reloads after the success delay', () => {
    const {calls, context, element, queuePost, runTimeouts} = createNavContext();
    element('#forgot-password-email').val('person@example.com');
    element('#forgot-password-code').val('ABC123');
    element('#forgot-password-new-password').val('new password');
    element('#forgot-password-new-password-confirm').val('new password');
    element('#forgot-password-remember').checked = true;
    queuePost('/api/reset-password.php', {type: 'success', data: ''});

    context.forgotPasswordReset();

    assert.deepEqual(plain(calls.post[0]), {
        url: '/api/reset-password.php',
        data: {
            email: 'person@example.com',
            code: 'ABC123',
            password: 'new password',
            passwordConfirm: 'new password',
            rememberMe: 1
        }
    });
    assert.equal(element('#forgot-password-reset-password').disabled, false);
    assert.equal(calls.reloads, 0);
    assert.equal(calls.timeouts[0].delay, 5000);

    runTimeouts();
    assert.equal(calls.reloads, 1);
});


test('nav cookie preference helper recognizes granted preferences', () => {
    const {context} = createNavContext({
        cookies: {CookiePreferences: '["preferences","analytics"]'}
    });

    assert.equal(context.hasCookiePreference('preferences'), true);
    assert.equal(context.hasCookiePreference('social'), false);
});

test('nav clears analytics and social cookies across host cookie scopes', () => {
    const {calls, context} = createNavContext({
        documentCookie: '_ga=1; _ga_TEST=2; _gid=3; __atuvc=4; essential=5'
    });

    context.clearAnalyticsCookies();
    context.clearSocialCookies();

    assert.deepEqual(
        calls.expiredCookies.map(({name, domain}) => [name, domain]),
        [
            ['_ga', ''],
            ['_ga', 'example.test'],
            ['_ga', '.example.test'],
            ['_ga_TEST', ''],
            ['_ga_TEST', 'example.test'],
            ['_ga_TEST', '.example.test'],
            ['_gid', ''],
            ['_gid', 'example.test'],
            ['_gid', '.example.test'],
            ['__atuvc', ''],
            ['__atuvc', 'example.test'],
            ['__atuvc', '.example.test']
        ]
    );
});

test('nav submitLogin surfaces response-text failures', () => {
    const {context, element, queuePost} = createNavContext();
    queuePost('/api/login.php', {
        type: 'failure',
        xhr: {responseText: 'Account disabled'}
    });

    context.submitLogin();

    assert.match(
        element('#login-modal .modal-body').appended.join(''),
        /Account disabled/
    );
});

test('nav submitLogin surfaces unauthorized and generic failures', () => {
    for (const [error, expected] of [
        ['Unauthorized', /session has timed out/],
        ['Network Error', /unexpected error occurred/]
    ]) {
        const {context, element, queuePost} = createNavContext();
        queuePost('/api/login.php', {
            type: 'failure',
            xhr: {responseText: ''},
            error
        });

        context.submitLogin();

        assert.match(
            element('#login-modal .modal-body').appended.join(''),
            expected
        );
    }
});

test('nav logout reloads non-user pages', () => {
    const {calls, context, queuePost} = createNavContext({pathname: '/blog/'});
    queuePost('/api/login.php', {type: 'success', data: ''});

    context.logout();

    assert.equal(calls.reloads, 1);
});

test('nav forgotPassword resets and opens the password reset modal', () => {
    const {context, element} = createNavContext();
    element('#forgot-password-code').show();
    element('#forgot-password-new-password').show();
    element('#forgot-password-new-password-confirm').show();
    element('#forgot-password-reset-password').show();

    context.forgotPassword();

    assert.deepEqual(element('#login-modal').modalCalls, ['hide']);
    assert.equal(element('#forgot-password-instructions').visible, true);
    assert.equal(element('#forgot-password-code').visible, false);
    assert.equal(element('#forgot-password-new-password').visible, false);
    assert.equal(element('#forgot-password-new-password-confirm').visible, false);
    assert.equal(element('#forgot-password-reset-password').visible, false);
    assert.deepEqual(element('#forgot-password-modal').modalCalls, ['show']);
});

test('nav forgotPasswordSubmit shows validation and transport failures and re-enables the button', () => {
    for (const response of [
        {type: 'success', data: 'Unknown email address'},
        {type: 'failure', xhr: {responseText: 'Reset unavailable'}},
        {type: 'failure', xhr: {responseText: ''}, error: 'Unauthorized'},
        {type: 'failure', xhr: {responseText: ''}, error: 'Network Error'}
    ]) {
        const {context, element, queuePost} = createNavContext();
        queuePost('/api/send-reset-code.php', response);

        context.forgotPasswordSubmit();

        assert.equal(element('#forgot-password-submit').disabled, false);
        assert.ok(
            element('#forgot-password-modal .modal-body').appended.length > 0,
            'Expected a visible password reset message'
        );
    }
});

test('nav forgotPasswordReset shows validation and transport failures and re-enables the button', () => {
    for (const response of [
        {type: 'success', data: 'Reset code is invalid'},
        {type: 'failure', xhr: {responseText: 'Reset failed'}},
        {type: 'failure', xhr: {responseText: ''}, error: 'Unauthorized'},
        {type: 'failure', xhr: {responseText: ''}, error: 'Network Error'}
    ]) {
        const {context, element, queuePost} = createNavContext();
        queuePost('/api/reset-password.php', response);

        context.forgotPasswordReset();

        assert.equal(element('#forgot-password-reset-password').disabled, false);
        assert.ok(
            element('#forgot-password-modal .modal-body').appended.length > 0,
            'Expected a visible password reset message'
        );
    }
});

test('nav extracts URL-encoded album codes from the hash', () => {
    const {context} = createNavContext({hash: '#album=ABC%20123'});

    assert.equal(context.getAlbumCodeFromHash(), 'ABC 123');
    context.window.location.hash = '#something-else';
    assert.equal(context.getAlbumCodeFromHash(), null);
});

test('nav searchBlog navigates using the current search text', () => {
    const {context, element} = createNavContext();
    element('#nav-search-input').val('family portraits');

    context.searchBlog();

    assert.equal(context.window.location, '/blog/search.php?s=family portraits');
});

test('nav query parsing returns matching values and false for missing keys', () => {
    const {context} = createNavContext({search: '?album=42&mode=full'});

    assert.equal(context.getQueryVariable('album'), '42');
    assert.equal(context.getQueryVariable('mode'), 'full');
    assert.equal(context.getQueryVariable('missing'), false);
});
