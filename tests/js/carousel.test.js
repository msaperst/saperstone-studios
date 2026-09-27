const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('carousel applies deferred background images and 3:2 sizing', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const background = environment.element('[data-background-image]');
    background.attr('data-background-image', '/img/photo.jpg');
    background.nativeElement.style = {};
    background.each = function (callback) { callback.call(background.nativeElement); return this; };
    const jquery = (selector) => selector === background.nativeElement ? background : environment.$(selector);
    Object.assign(jquery, environment.$);
    jquery.fn = environment.$.fn;
    const carousel = environment.element('.carousel-three-by-two');
    carousel.widthValue = 600;

    loadBrowserScript('public/js/carousel.js', {$: jquery, document: environment.document});
    environment.runReady();

    assert.equal(background.nativeElement.style.backgroundImage, 'url("/img/photo.jpg")');
    assert.equal(carousel.heightValue, 400);
});

test('carousel recalculates 3:2 sizing when a modal is shown', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const background = environment.element('[data-background-image]');
    background.each = () => background;
    const carousel = environment.element('.carousel-three-by-two');
    carousel.widthValue = 450;

    loadBrowserScript('public/js/carousel.js', {$: environment.$, document: environment.document});
    environment.runReady();
    carousel.widthValue = 300;
    environment.element('.modal-carousel').trigger('shown.bs.modal');

    assert.equal(carousel.heightValue, 200);
});
