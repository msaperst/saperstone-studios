const assert = require('node:assert/strict');
const {beforeEach, test} = require('node:test');
const {loadBrowserScript} = require('./helpers/load-browser-script');

class CookieDocument {
    constructor() {
        this.cookies = new Map();
        this.lastSet = '';
    }

    get cookie() {
        return Array.from(this.cookies.entries())
            .map(([name, value]) => `${name}=${value}`)
            .join('; ');
    }

    set cookie(value) {
        this.lastSet = value;

        const parts = value.split(';').map((part) => part.trim());
        const pair = parts.shift();
        const separator = pair.indexOf('=');
        const name = pair.substring(0, separator);
        const cookieValue = pair.substring(separator + 1);
        const maxAge = parts.find((part) => part.toLowerCase().startsWith('max-age='));

        if (maxAge && Number(maxAge.split('=')[1]) <= 0) {
            this.cookies.delete(name);
            return;
        }

        this.cookies.set(name, cookieValue);
    }
}

let cookieScript;
let document;
let window;

beforeEach(() => {
    document = new CookieDocument();
    window = {
        location: {
            protocol: 'http:'
        }
    };
    cookieScript = loadBrowserScript('public/js/cookies.js', {
        document,
        window
    });
});

test('cookie attributes are appropriate for HTTP', () => {
    assert.equal(cookieScript.cookieAttributes(), '; path=/; SameSite=Lax');
});

test('cookie attributes add Secure for HTTPS', () => {
    window.location.protocol = 'https:';

    assert.equal(cookieScript.cookieAttributes(), '; path=/; SameSite=Lax; Secure');
});

test('createCookie encodes values and can be read back', () => {
    cookieScript.createCookie('preference', 'hello world & photos');

    assert.equal(cookieScript.readCookie('preference'), 'hello world & photos');
    assert.equal(
        document.lastSet,
        'preference=hello%20world%20%26%20photos; path=/; SameSite=Lax'
    );
});

test('createCookie adds an expiration when days are provided', () => {
    const before = Date.now();
    cookieScript.createCookie('remember', 'yes', 2);
    const after = Date.now();

    const match = document.lastSet.match(/; expires=([^;]+)/);
    assert.ok(match, 'Expected an expires attribute');

    const expiration = Date.parse(match[1]);
    const twoDays = 2 * 24 * 60 * 60 * 1000;
    assert.ok(expiration >= before + twoDays - 1000);
    assert.ok(expiration <= after + twoDays + 1000);
});

test('readCookie finds and decodes the requested cookie among multiple cookies', () => {
    cookieScript.createCookie('first', 'one');
    cookieScript.createCookie('second', 'two words');

    assert.equal(cookieScript.readCookie('second'), 'two words');
});

test('readCookie returns null when the cookie is absent', () => {
    cookieScript.createCookie('present', 'yes');

    assert.equal(cookieScript.readCookie('missing'), null);
});

test('expireCookie removes the cookie and includes an explicit domain', () => {
    cookieScript.createCookie('session', 'active');
    cookieScript.expireCookie('session', '.example.com');

    assert.equal(cookieScript.readCookie('session'), null);
    assert.match(document.lastSet, /; domain=\.example\.com$/);
    assert.match(document.lastSet, /Max-Age=0/);
});

test('deleteCookie expires the cookie without adding a domain', () => {
    cookieScript.createCookie('session', 'active');
    cookieScript.deleteCookie('session');

    assert.equal(cookieScript.readCookie('session'), null);
    assert.doesNotMatch(document.lastSet, /; domain=/);
});
