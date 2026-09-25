const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('dynamic content toggles the child and switches plus to minus when visible', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const header = environment.element('.collapse-header');
    const icon = environment.element('__icon__');
    const parent = environment.element('__parent__');
    const child = environment.element('__child__');
    header.find = () => icon;
    header.parent = () => parent;
    parent.next = () => child;
    child.slideToggle = (duration, callback) => { assert.equal(duration, 500); child.visible = true; callback(); return child; };
    child.is = (query) => query === ':visible' && child.visible;

    const context = loadBrowserScript('public/js/dynamic-content.js', {$: environment.$, document: environment.document});
    environment.runReady();
    header.trigger('click');

    assert.equal(icon.hasClass('fa-minus-square'), true);
    assert.equal(icon.hasClass('fa-plus-square'), false);
});

test('dynamic content switches minus back to plus when collapsed', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    const header = environment.element('.collapse-header');
    const icon = environment.element('__icon__').addClass('fa-minus-square');
    const parent = environment.element('__parent__');
    const child = environment.element('__child__');
    header.find = () => icon;
    header.parent = () => parent;
    parent.next = () => child;
    child.slideToggle = (duration, callback) => { child.visible = false; callback(); return child; };
    child.is = () => false;

    loadBrowserScript('public/js/dynamic-content.js', {$: environment.$, document: environment.document});
    environment.runReady();
    header.trigger('click');

    assert.equal(icon.hasClass('fa-plus-square'), true);
    assert.equal(icon.hasClass('fa-minus-square'), false);
});
