const assert = require('node:assert/strict');
const {test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createReadyContext(options = {}) {
    const elements = new Map();
    const calls = {
        consent: [],
        expiredCookies: [],
        createdCookies: [],
        reloads: 0,
        timeouts: []
    };

    const documentObject = {
        cookie: options.documentCookie || ''
    };

    function element(selector) {
        if (!elements.has(selector)) {
            const handlers = new Map();
            elements.set(selector, {
                selector,
                handlers,
                value: '',
                checked: false,
                visible: true,
                attributes: new Map(),
                styles: {},
                removed: false,
                val(value) {
                    if (arguments.length) {
                        this.value = value;
                        return this;
                    }
                    return this.value;
                },
                prop(name, value) {
                    if (arguments.length === 1) {
                        return name === 'checked' ? this.checked : this[name];
                    }
                    if (name === 'checked') {
                        this.checked = value;
                    } else {
                        this[name] = value;
                    }
                    return this;
                },
                attr(name, value) {
                    if (arguments.length === 1) {
                        return this.attributes.get(name);
                    }
                    this.attributes.set(name, String(value));
                    return this;
                },
                css(name, value) {
                    if (arguments.length === 1) {
                        return this.styles[name];
                    }
                    this.styles[name] = value;
                    return this;
                },
                hide() {
                    this.visible = false;
                    return this;
                },
                show() {
                    this.visible = true;
                    return this;
                },
                remove() {
                    this.removed = true;
                    return this;
                },
                on(event, selectorOrHandler, handler) {
                    handlers.set(event, typeof selectorOrHandler === 'function' ? selectorOrHandler : handler);
                    return this;
                },
                ajaxSend(handler) {
                    handlers.set('ajaxSend', handler);
                    return this;
                },
                click(handler) {
                    handlers.set('click', handler);
                    return this;
                },
                keypress(handler) {
                    handlers.set('keypress', handler);
                    return this;
                },
                submit(handler) {
                    handlers.set('submit', handler);
                    return this;
                },
                trigger(event, ...args) {
                    const handler = handlers.get(event);
                    if (handler) {
                        handler.call(this, ...args);
                    }
                    return this;
                },
                bsgdprcookies(event) {
                    calls.consent.push(event);
                    return this;
                }
            });
        }
        return elements.get(selector);
    }

    function $(selector) {
        if (typeof selector === 'function') {
            selector();
            return element('__ready__');
        }
        if (selector === documentObject) {
            return element('__document__');
        }
        if (selector && selector.selector && selector.handlers) {
            return selector;
        }
        return element(String(selector));
    }

    $.isNumeric = (value) => value !== null && value !== '' && !Number.isNaN(Number(value));

    const location = {
        hash: options.hash || '',
        pathname: options.pathname || '/',
        search: '',
        hostname: 'example.test',
        reload() {
            calls.reloads += 1;
        }
    };
    const windowObject = {location};

    const cookies = new Map(Object.entries(options.cookies || {}));
    if (options.borderTopWidth !== undefined) {
        element('.navbar-inverse').attr('data-border-top-width', String(options.borderTopWidth));
    }
    if (options.role !== undefined) {
        element('#my-user-role').val(options.role);
    }
    if (options.userId !== undefined) {
        element('#my-user-id').val(options.userId);
    }

    const context = loadBrowserScript('public/js/nav.js', {
        $,
        window: windowObject,
        location,
        top: {location: {pathname: options.topPath || '/'}},
        document: documentObject,
        readCookie(name) {
            return cookies.has(name) ? cookies.get(name) : null;
        },
        expireCookie(name, domain) {
            calls.expiredCookies.push({name, domain});
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
        runTimeouts() {
            for (const timeout of calls.timeouts.splice(0)) {
                timeout.callback();
            }
        }
    };
}

test('nav initialization applies consent and remember-me state', () => {
    const {calls, context, element} = createReadyContext({
        cookies: {CookiePreferences: '[]'},
        borderTopWidth: 24,
        role: 'viewer',
        userId: '42'
    });

    assert.deepEqual(calls.consent, [undefined]);
    assert.equal(context.my_role, 'viewer');
    assert.equal(context.my_id, '42');
    assert.equal(element('.navbar-inverse').css('border-top-width'), '24px');
    assert.equal(element('#profile-remember-span').visible, false);
    assert.equal(element('#forgot-password-remember-span').visible, false);
    assert.equal(element('#login-remember-span').visible, false);
    assert.equal(element('#profile-remember').checked, false);
    assert.equal(element('#forgot-password-remember').checked, false);
    assert.equal(element('#login-remember').checked, false);
});

test('nav does not open consent automatically on the privacy policy page', () => {
    const {calls} = createReadyContext({
        topPath: '/Privacy-Policy.php'
    });

    assert.deepEqual(calls.consent, []);
});

test('nav cookie preference changes clear revoked cookies and reload integrations only when needed', () => {
    const {calls, element, runTimeouts} = createReadyContext({
        cookies: {CookiePreferences: '["preferences","analytics","social"]'},
        documentCookie: '_ga=1; __atuvc=2; essential=3'
    });

    const handler = element('__document__').handlers.get('cookiePreferencesChanged');
    assert.equal(typeof handler, 'function');

    handler({}, ['preferences']);

    assert.ok(calls.expiredCookies.some(({name}) => name === '_ga'));
    assert.ok(calls.expiredCookies.some(({name}) => name === '__atuvc'));
    assert.equal(calls.reloads, 0);
    assert.equal(calls.timeouts.length, 1);

    runTimeouts();
    assert.equal(calls.reloads, 1);
});

test('nav cookie preference changes do not reload when integration consent is unchanged', () => {
    const {calls, element} = createReadyContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });

    const handler = element('__document__').handlers.get('cookiePreferencesChanged');
    handler({}, ['preferences']);

    assert.equal(calls.timeouts.length, 0);
    assert.equal(calls.reloads, 0);
});

test('nav edit-cookie action reopens the consent dialog', () => {
    const {calls, element} = createReadyContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });
    const event = {
        prevented: false,
        preventDefault() {
            this.prevented = true;
        }
    };

    element('#edit-cookies').trigger('click', event);

    assert.equal(event.prevented, true);
    assert.deepEqual(calls.consent, [undefined, 'reinit']);
});

test('nav announcement dismissal stores a cookie and adjusts the navbar offset', () => {
    const {calls, element} = createReadyContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });
    element('.navbar-inverse').css('border-top-width', '100px');
    element('#displayed-alerts .close').attr('id', 'announcement-9');

    element('#displayed-alerts .close').trigger('click');

    assert.deepEqual(calls.createdCookies, [{
        name: 'announcement-9',
        value: 'dismissed'
    }]);
    assert.equal(element('.navbar-inverse').css('border-top-width'), '40px');
});

test('nav AJAX send hook removes stale errors and adds CSRF on writes', () => {
    const {element} = createReadyContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });
    element('#csrf-token').val('csrf-token');
    const danger = element('.alert-danger');
    const headers = {};
    const xhr = {
        setRequestHeader(name, value) {
            headers[name] = value;
        }
    };

    element('__document__').trigger('ajaxSend', {}, xhr, {type: 'POST'});

    assert.deepEqual(headers, {'X-CSRF-Token': 'csrf-token'});
    assert.equal(danger.removed, true);
});

test('nav form submit hook clears stale validation errors', () => {
    const {element} = createReadyContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });
    const danger = element('.alert-danger');

    const handler = element('__document__').handlers.get('submit');
    handler();

    assert.equal(danger.removed, true);
});

test('nav hash change opens the album finder only for album hashes', () => {
    let dialogCount = 0;
    const {context} = createReadyContext({
        cookies: {CookiePreferences: '["preferences"]'}
    });
    context.BootstrapDialog = {
        show() {
            dialogCount += 1;
        }
    };

    context.window.location.hash = '#not-album';
    context.window.onhashchange();
    assert.equal(dialogCount, 0);
});
