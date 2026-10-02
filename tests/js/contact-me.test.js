const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScripts} = require('./helpers/load-browser-script');

function createContactContext(response = {type: 'success'}) {
    const environment = createJQueryEnvironment();
    const ajaxCalls = [];
    const windowObject = {};
    environment.element(String(windowObject)).widthValue = 1280;
    environment.element(String(windowObject)).heightValue = 720;

    environment.$.fn.jqBootstrapValidation = function (options) {
        this.jqBootstrapValidationOptions = options;
        return this;
    };
    environment.$.fn.focus = function (handler) {
        if (typeof handler === 'function') {
            this.handlers.set('focus', handler);
            return this;
        }
        return this.trigger('focus');
    };
    environment.$.fn.tab = function (action) {
        this.tabCalls = this.tabCalls || [];
        this.tabCalls.push(action);
        return this;
    };

    let buttonDisabledDuringAjax = false;
    environment.$.ajax = function (options) {
        ajaxCalls.push({
            url: options.url,
            type: options.type,
            data: options.data,
            cache: options.cache
        });
        buttonDisabledDuringAjax = environment.element('#contactForm button').prop('disabled') === true;

        if (response.type === 'failure') {
            options.error(
                response.xhr || {responseText: ''},
                response.status || 'error',
                response.error || 'Error'
            );
        } else {
            options.success(response.data);
        }
    };

    loadBrowserScripts(['public/js/contact_me.js'], {
        $: environment.$,
        window: windowObject
    });

    return {
        environment,
        ajaxCalls,
        getButtonDisabledDuringAjax: () => buttonDisabledDuringAjax
    };
}

function submitContact(environment, values) {
    environment.element('input#loadtime').val(values.loadtime || 'abc123');
    environment.element('input#company').val(values.company || '');
    environment.element('input#name').val(values.name || 'Max Saperstone');
    environment.element('input#phone').val(values.phone || '5712660004');
    environment.element('input#email').val(values.email || 'max@example.com');
    environment.element('textarea#message').val(values.message || 'Hello from the contact form');

    let prevented = false;
    let resetCount = 0;
    environment.element('#contactForm').on('reset', () => {
        resetCount += 1;
    });

    const options = environment
        .element('#contactForm input,#contactForm textarea')
        .jqBootstrapValidationOptions;
    assert.ok(options);

    options.submitSuccess(environment.element('#contactForm'), {
        preventDefault() {
            prevented = true;
        }
    });

    return {prevented, resetCount, options};
}

test('contact form initializes Bootstrap validation for visible fields', () => {
    const {environment} = createContactContext();
    const controls = environment.element('#contactForm input,#contactForm textarea');
    const options = controls.jqBootstrapValidationOptions;

    assert.equal(options.preventSubmit, true);
    controls.visible = true;
    assert.equal(options.filter.call(controls), true);
    controls.visible = false;
    assert.equal(options.filter.call(controls), false);
});

test('contact form submits all fields and displays success state', () => {
    const {environment, ajaxCalls, getButtonDisabledDuringAjax} = createContactContext();
    const result = submitContact(environment, {
        loadtime: 'load-token',
        company: '',
        name: 'Max Saperstone',
        phone: '571-266-0004',
        email: 'max@example.com',
        message: 'I would like more information.'
    });

    assert.equal(result.prevented, true);
    assert.equal(getButtonDisabledDuringAjax(), true);
    assert.deepEqual(JSON.parse(JSON.stringify(ajaxCalls)), [{
        url: 'api/contact-me.php',
        type: 'POST',
        data: {
            loadtime: 'load-token',
            company: '',
            name: 'Max Saperstone',
            phone: '571-266-0004',
            email: 'max@example.com',
            resolution: '1280x720',
            message: 'I would like more information.'
        },
        cache: false
    }]);
    assert.match(
        environment.element('#success > .alert-success').appended.join(''),
        /Your message has been sent/
    );
    assert.equal(result.resetCount, 1);
    assert.equal(environment.element('#contactForm button').prop('disabled'), false);
});

test('contact form displays the mail-server failure message and restores the form', () => {
    const {environment, getButtonDisabledDuringAjax} = createContactContext({
        type: 'failure',
        error: 'Server Error'
    });
    const result = submitContact(environment, {
        name: 'Max Saperstone',
        email: 'max@example.com'
    });

    assert.equal(result.prevented, true);
    assert.equal(getButtonDisabledDuringAjax(), true);
    assert.match(
        environment.element('#success > .alert-danger').appended.join(''),
        /Sorry Max it seems that my mail server is not responding/
    );
    assert.match(
        environment.element('#success > .alert-danger').appended.join(''),
        /mailto:la@saperstonestudios\.com/
    );
    assert.equal(result.resetCount, 1);
    assert.equal(environment.element('#contactForm button').prop('disabled'), false);
});

test('contact form name focus clears prior success or failure messages', () => {
    const {environment} = createContactContext();
    environment.element('#success').html('old message');

    environment.element('#name').trigger('focus');

    assert.equal(environment.element('#success').html(), '');
});

test('contact form tab links prevent navigation and show the selected tab', () => {
    const {environment} = createContactContext();
    const tab = environment.element('a[data-toggle="tab"]');
    let prevented = false;

    tab.trigger('click', {
        preventDefault() {
            prevented = true;
        }
    });

    assert.equal(prevented, true);
    assert.deepEqual(tab.tabCalls, ['show']);
});
