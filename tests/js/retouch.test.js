const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createRetouchContext(hash = '') {
    const environment = createJQueryEnvironment();
    const windowObject = {location: {hash}};
    const context = loadBrowserScript('public/js/retouch.js', {
        $: environment.$, window: windowObject, setInterval: environment.setInterval
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
    retouch.images = [{text: 'Before and after'}];
    const heighter = environment.element('__heighter__');
    const original = environment.element('__original_img__');
    const edit = environment.element('__edit_img__');
    retouch.ele.find = (selector) => ({'#heighter': heighter, '#original img': original, '#edit img': edit, '#edit': environment.element('__edit__')}[selector] || environment.element(selector));
    const img = environment.element('__selected__').attr({
        imgWidth: '400', imgHeight: '800', imgOrig: '/orig.jpg',
        imgEdit: '/edit.jpg', hash: '0'
    });

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
