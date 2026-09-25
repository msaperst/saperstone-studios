!function (windowObject, documentObject, elementName, sourceUrl) {
    if (windowObject.fbq) return;

    const pixelQueue = function (...args) {
        if (pixelQueue.callMethod) {
            pixelQueue.callMethod(...args);
        } else {
            pixelQueue.queue.push(args);
        }
    };
    windowObject.fbq = pixelQueue;
    if (!windowObject._fbq) windowObject._fbq = pixelQueue;
    pixelQueue.push = pixelQueue;
    pixelQueue.loaded = true;
    pixelQueue.version = '2.0';
    pixelQueue.queue = [];

    const script = documentObject.createElement(elementName);
    script.async = true;
    script.src = sourceUrl;
    const parent = documentObject.head || documentObject.documentElement;
    parent.appendChild(script);
}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

window.fbq('init', '269624791467010');
window.fbq('track', 'PageView');
