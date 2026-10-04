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
        hash: '',
        pathname: options.pathname || '/',
        search: '',
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
        document: {cookie: ''},
        readCookie(name) {
            return cookies.has(name) ? cookies.get(name) : null;
        },
        expireCookie(name, domain) {
            calls.expiredCookies.push({name, domain});
            cookies.delete(name);
        },
        createCookie() {},
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
    assert.equal(location.href || String(location), '');
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
