!function (f, b, e, v, n, t, s) {
    if (f.fbq) return;
    n = f.fbq = function () {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
    };
    if (!f._fbq) f._fbq = n;
    n.push = n;
    n.loaded = true;
    n.version = '2.0';
    n.queue = [];
    t = b.createElement(e);
    t.async = true;
    t.src = v;
    s = b.head || b.documentElement;
    s.appendChild(t);
}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

window.fbq('init', '269624791467010');
window.fbq('track', 'PageView');
