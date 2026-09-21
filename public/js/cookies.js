function cookieAttributes() {
    var attributes = '; path=/; SameSite=Lax';
    if (window.location.protocol === 'https:') {
        attributes += '; Secure';
    }
    return attributes;
}

function createCookie(name, value, days) {
    var expires = '';
    if (typeof days === 'number') {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + encodeURIComponent(value) + expires + cookieAttributes();
}

function readCookie(name) {
    var namePrefix = name + '=';
    var cookies = document.cookie.split(';');
    for (var cookieValue of cookies) {
        var cookie = cookieValue.trim();
        if (cookie.startsWith(namePrefix)) {
            return decodeURIComponent(cookie.substring(namePrefix.length));
        }
    }
    return null;
}

function expireCookie(name, domain) {
    var domainAttribute = domain ? '; domain=' + domain : '';
    document.cookie = name + '=; Max-Age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT'
        + cookieAttributes() + domainAttribute;
}

function deleteCookie(name) {
    expireCookie(name, '');
}
