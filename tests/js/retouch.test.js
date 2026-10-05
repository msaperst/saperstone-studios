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
        $: environment.$, window: windowObject, document: documentObject, URL, setInterval: environment.setInterval
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
    assert.equal(original[0].src, '/orig.jpg');
    assert.equal(edit[0].src, '/edit.jpg');
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


test('retouch constructor builds controls, starts slider updates, and restores hash selection', () => {
    const {context, environment, windowObject} = createRetouchContext('#1');
    const slider = environment.element('__constructor_slider__');
    const selector = environment.element('__constructor_selector__');
    const calls = [];

    context.Retouch.prototype.createSlider = function () {
        calls.push('create-slider');
        return slider;
    };
    context.Retouch.prototype.addSelector = function () {
        calls.push('add-selector');
        return selector;
    };
    context.Retouch.prototype.slide = function () {
        calls.push('slide');
    };
    context.Retouch.prototype.setSelect = function (img) {
        calls.push('select:' + img.selector);
    };

    const holder = environment.element('__constructor_holder__');
    const retouch = new context.Retouch(holder, [{}, {}], true);

    assert.equal(retouch.ele, holder);
    assert.equal(retouch.images.length, 2);
    assert.equal(retouch.instruct, true);
    assert.equal(retouch.slider, slider);
    assert.equal(retouch.selector, selector);
    assert.deepEqual(calls.slice(0, 3), [
        'create-slider',
        'add-selector',
        'select:__constructor_selector__ img[hash=1]'
    ]);

    environment.runIntervalsOnce();
    assert.equal(calls.at(-1), 'slide');
    assert.equal(windowObject.location.hash, '#1');
});

test('createSlider builds protected comparison layers, instructions, range control, and comment area', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__slider_holder__');
    retouch.instruct = true;

    const slider = retouch.createSlider();

    assert.equal(retouch.ele.beforeValues.length, 1);
    const instructions = retouch.ele.beforeValues[0];
    assert.equal(instructions.attr('id'), 'instructions');
    assert.equal(instructions.html(), 'Select an Image then drag slider');

    assert.equal(retouch.ele.appended.length, 4);
    const protector = retouch.ele.appended[0];
    assert.equal(protector.hasClass('protect'), true);
    assert.equal(protector.attr('src'), '/img/image.png');
    assert.equal(protector.attr('alt'), 'placeholder');

    const heighter = retouch.ele.appended[1];
    assert.equal(heighter.attr('id'), 'heighter');
    assert.equal(heighter.css('margin-top'), '0%');

    const original = retouch.ele.appended[2];
    const edited = retouch.ele.appended[3];
    assert.equal(original.attr('id'), 'original');
    assert.equal(original.hasClass('original'), true);
    assert.equal(original.appended[0].tagName, 'img');
    assert.equal(edited.attr('id'), 'edit');
    assert.equal(edited.hasClass('edit'), true);
    assert.equal(edited.appended[0].tagName, 'img');

    assert.equal(retouch.ele.afterValues.length, 1);
    const controls = retouch.ele.afterValues[0];
    assert.equal(controls.appended[0], slider);
    assert.equal(slider.hasClass('slider'), true);
    assert.equal(slider.attr('type'), 'range');
    assert.equal(slider.attr('min'), '0');
    assert.equal(slider.attr('max'), '100');
    assert.equal(controls.appended[1].hasClass('comment'), true);
});

test('createSlider omits instructions when disabled', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__slider_no_instructions__');
    retouch.instruct = false;

    retouch.createSlider();

    assert.equal(retouch.ele.beforeValues.length, 0);
});

test('setSelect ignores missing image indexes without changing the current view', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__missing_selection_holder__');
    retouch.slider = environment.element('__missing_selection_slider__').val('42');
    retouch.selector = environment.element('__missing_selection_selector__');
    retouch.images = [];

    retouch.setSelect(environment.element('__missing_selection__').attr('hash', '99'));

    assert.equal(retouch.slider.val(), '42');
    assert.equal(retouch.ele.widthValue, 300);
});

test('setSelect renders normal-height images, safe text, and selection state', () => {
    const {context, environment} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__normal_retouch__');
    const parent = environment.element('__normal_parent__');
    parent.widthValue = 600;
    retouch.ele.parentResult = parent;
    retouch.slider = environment.element('__normal_slider__').val('75');
    retouch.selector = environment.element('__normal_selector__');
    retouch.images = [{
        width: 1200,
        height: 600,
        orig: ' /original.jpg ',
        edit: 'https://cdn.example.com/edited.jpg',
        text: '<strong>Keep this as text</strong>'
    }];

    const heighter = environment.element('__normal_heighter__');
    const original = environment.element('__normal_original__');
    const edited = environment.element('__normal_edited__');
    const editLayer = environment.element('__normal_edit_layer__');
    retouch.ele.find = (selector) => ({
        '#heighter': heighter,
        '#original img': original,
        '#edit img': edited,
        '#edit': editLayer
    }[selector] || environment.element(selector));

    const thumbnails = environment.element('__normal_thumbnails__');
    retouch.selector.find = (selector) =>
        selector === 'img.thumb' ? thumbnails : environment.element(selector);

    const comment = environment.element('__normal_comment__');
    parent.find = (selector) =>
        selector === '.comment' ? comment : environment.element(selector);

    const selected = environment.element('__normal_selected__').attr('hash', '0');
    retouch.setSelect(selected);

    assert.equal(heighter.css('margin-top'), '50%');
    assert.equal(retouch.ele.widthValue, 600);
    assert.equal(retouch.slider.widthValue, 600);
    assert.equal(original[0].src, '/original.jpg');
    assert.equal(edited[0].src, 'https://cdn.example.com/edited.jpg');
    assert.equal(retouch.slider.val(), 0);
    assert.equal(comment.appended.length, 1);
    assert.equal(comment.appended[0].nodeType, 3);
    assert.equal(comment.appended[0].textContent, '<strong>Keep this as text</strong>');
    assert.equal(thumbnails.css('border'), '2px transparent solid');
    assert.equal(selected.css('border'), '2px #9dcb3b solid');
    assert.equal(editLayer.widthValue, '0%');
});

test('selector renders safe thumbnails and clicking one updates hash and selection', () => {
    const {context, environment, windowObject} = createRetouchContext();
    const retouch = Object.create(context.Retouch.prototype);
    retouch.ele = environment.element('__selector_holder__');
    retouch.ele.closestResult = environment.element('__selector_column__');
    retouch.images = [
        {thumb: '/thumb-one.jpg'},
        {thumb: 'javascript:alert(1)'}
    ];

    const selections = [];
    retouch.setSelect = (img) => selections.push(img);

    const selector = retouch.addSelector();
    const row = selector.appended[0];

    assert.equal(row.hasClass('row-fluid'), true);
    assert.equal(row.hasClass('text-center'), true);
    assert.equal(row.appended.length, 2);

    const first = row.appended[0].appended[0];
    const second = row.appended[1].appended[0];

    assert.equal(first.hasClass('thumb'), true);
    assert.equal(first.attr('hash'), '0');
    assert.equal(first.attr('alt'), 'Retouched image 1');
    assert.equal(first[0].src, '/thumb-one.jpg');
    assert.equal(second.attr('hash'), '1');
    assert.equal(second.attr('alt'), 'Retouched image 2');
    assert.equal(second[0].src, '');

    first.trigger('click');

    assert.equal(windowObject.location.hash, '0');
    assert.equal(selections.length, 1);
    assert.equal(selections[0], first);
});

test('sanitizeImageUrl handles blank, protocol-relative, and malformed URL values safely', () => {
    const {context} = createRetouchContext();

    assert.equal(context.sanitizeImageUrl('   '), '');
    assert.equal(
        context.sanitizeImageUrl('//cdn.example.com/example.jpg'),
        'https://cdn.example.com/example.jpg'
    );
    assert.equal(context.sanitizeImageUrl('http://cdn.example.com/example.jpg'), 'http://cdn.example.com/example.jpg');
    assert.equal(context.sanitizeImageUrl('https://[bad-host'), '');
});
