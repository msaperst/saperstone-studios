const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createRetouchContext(hash = '') {
    const environment = createJQueryEnvironment();
    const windowObject = {location: {hash, origin: 'https://saperstonestudios.com'}};
    const documentObject = {
        createTextNode(value) { return {nodeType: 3, textContent: value}; }
    };
    const context = loadBrowserScript('public/js/retouch.js', {
        $: environment.$, window: windowObject, document: documentObject, setInterval: environment.setInterval
    });
    return {context, environment, windowObject};
}

test('retouch slider clips the edited image to the selected percentage', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__retouch__');
    retouch.slider = environment.element('__slider__').val('37');
    const edit = environment.element('__edit__');
    retouch.ele.find = (selector) => selector === '#edit' ? edit : environment.element(selector);

    retouch.slide();

    assert.equal(edit.widthValue, '37%');
});

test('setSelect sizes tall images to the max height and resets slider', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__retouch__');
    retouch.ele.parentResult = environment.element('__parent__');
    retouch.ele.parentResult.widthValue = 1000;
    retouch.slider = environment.element('__slider__').val('50');
    retouch.selector = environment.element('__selector__');
    retouch.images = [{width: 400, height: 800, orig: '/orig.jpg', edit: '/edit.jpg', text: 'Before and after'}];
    const heighter = environment.element('__heighter__');
    const original = environment.element('__original_img__');
    const edit = environment.element('__edit_img__');
    retouch.ele.find = (selector) => ({'#heighter': heighter, '#original img': original, '#edit img': edit, '#edit': environment.element('__edit__')}[selector] || environment.element(selector));
    const img = environment.element('__selected__').attr('hash', '0');

    retouch.setSelect(img);

    assert.equal(retouch.ele.widthValue, 275);
    assert.equal(retouch.slider.widthValue, 275);
    assert.equal(retouch.slider.val(), 0);
    assert.equal(original.attr('src'), '/orig.jpg');
    assert.equal(edit.attr('src'), '/edit.jpg');
});

test('selector creates one thumbnail per image with comparison metadata', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__retouch__');
    retouch.ele.closestResult = environment.element('__column__');
    retouch.images = [{orig:'/a.jpg', edit:'/b.jpg', width:100, height:80, text:'Example', thumb:'/t.jpg'}];

    const selector = retouch.addSelector();

    assert.equal(selector.hasClass('col-md-12'), true);
    assert.equal(retouch.ele.closestResult.afterValues.length, 1);
});


test('sanitizeImageUrl accepts image paths and rejects executable schemes', () => {
    const {context} = createRetouchContext();
    assert.equal(context.sanitizeImageUrl('/images/example.jpg'), '/images/example.jpg');
    assert.equal(context.sanitizeImageUrl('https://cdn.example.com/example.jpg'), 'https://cdn.example.com/example.jpg');
    assert.equal(context.sanitizeImageUrl('images/example.jpg'), '/images/example.jpg');
    assert.equal(context.sanitizeImageUrl('javascript:alert(1)'), '');
    assert.equal(context.sanitizeImageUrl('data:text/html,<script>alert(1)</script>'), '');
    assert.equal(context.sanitizeImageUrl(null), '');
});

test('selector does not copy retouch metadata into DOM attributes', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__retouch_metadata__');
    retouch.ele.closestResult = environment.element('__column_metadata__');
    retouch.images = [{orig:'/a.jpg', edit:'/b.jpg', width:100, height:80, text:'Example', thumb:'javascript:alert(1)'}];

    retouch.addSelector();

    const thumb = environment.element('<img>');
    assert.equal(thumb.attr('imgOrig'), undefined);
    assert.equal(thumb.attr('imgEdit'), undefined);
    assert.equal(thumb.attr('text'), undefined);
});
