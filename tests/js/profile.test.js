const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createProfileContext(href = 'https://saperstonestudios.com/user/profile.php', lengths = {}) {
    const environment = createJQueryEnvironment({autoReady: false, lengths});
    const location = {href, reloadCalled: false, reload() { this.reloadCalled = true; }};
    const context = loadBrowserScript('public/js/profile.js', {
        $: environment.$,
        document: environment.document,
        window: {location},
        location
    });
    return {context, environment, location};
}

function validFields(environment) {
    environment.element('#profile-username').val('valid_user');
    environment.element('#profile-firstname').val('Max');
    environment.element('#profile-lastname').val('User');
    environment.element('#profile-email').val('max@example.com');
    environment.element('#profile-password').val('');
    environment.element('#profile-confirm-password').val('');
    environment.element('#profile-current-password').val('');
}

test('profile validation accepts valid profile fields with blank password', () => {
    const {context, environment} = createProfileContext();
    validFields(environment);

    assert.equal(context.validateInput(), true);
    assert.equal(environment.element('#update-profile').prop('disabled'), false);
});

test('profile validation rejects invalid identity fields', () => {
    const {context, environment} = createProfileContext();
    validFields(environment);
    environment.element('#profile-username').val('bad!');
    environment.element('#profile-firstname').val('');
    environment.element('#profile-lastname').val('');
    environment.element('#profile-email').val('not-email');

    assert.equal(context.validateInput(), false);
    assert.equal(environment.element('#update-profile').prop('disabled'), true);
    assert.match(String(environment.element('#update-profile-email-message').appended[0]), /valid email/);
});

test('registration requires a password and matching confirmation', () => {
    const {context, environment} = createProfileContext('https://saperstonestudios.com/register.php', {'#profile-current-password': 0});
    validFields(environment);

    assert.equal(context.validateInput(), false);
    assert.match(String(environment.element('#update-profile-password-message').appended[0]), /password is required/);

    environment.element('#profile-password').val('secret123');
    environment.element('#profile-confirm-password').val('different');
    assert.equal(context.validateInput(), false);
    assert.match(String(environment.element('#update-profile-confirm-password-message').appended[0]), /do not match/);
});

test('profile update posts normalized fields to update endpoint', () => {
    const {context, environment} = createProfileContext();
    validFields(environment);
    environment.element('#profile-password').val('new-password');
    environment.element('#profile-confirm-password').val('new-password');
    environment.element('#profile-current-password').val('old-password');
    environment.element('#profile-remember').prop('checked', true);
    environment.queuePost('/api/update-profile.php', {type: 'success', data: ''});

    context.updateProfile();

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-profile.php',
        data: {
            username: 'valid_user', firstName: 'Max', lastName: 'User',
            curPass: 'old-password', password: 'new-password',
            passwordConfirm: 'new-password', email: 'max@example.com', rememberMe: 1
        }
    });
    assert.equal(environment.element('#update-profile').prop('disabled'), false);
    assert.match(String(environment.element('#update-profile-message').appended[0]), /successfully updated/);
});

test('registration posts to register endpoint and reloads after numeric success', () => {
    const {context, environment, location} = createProfileContext('https://saperstonestudios.com/register.php', {'#profile-current-password': 0});
    validFields(environment);
    environment.element('#profile-password').val('secret123');
    environment.element('#profile-confirm-password').val('secret123');
    environment.queuePost('/api/register-user.php', {type: 'success', data: '42'});

    context.updateProfile();

    assert.equal(environment.calls.post[0].url, '/api/register-user.php');
    assert.equal(location.reloadCalled, true);
});
