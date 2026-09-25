const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createContext() {
    const environment = createJQueryEnvironment();
    environment.element('body').mousemove = function (handler) { this.handlers.set('mousemove', handler); return this; };
    const context = loadBrowserScript('public/js/dragndrop.js', {
        $: environment.$, document: environment.document,
        confirm: () => true
    });
    return {context, environment};
}

test('DragNDrop updates builder configuration', () => {
    const {context} = createContext();
    context.DragNDrop('#builder', '.images', '#holder', true);
    assert.equal(context.elementBuilder, '#builder');
    assert.equal(context.imageBuilder, '.images');
    assert.equal(context.imagePlace, '#holder');
    assert.equal(context.showGhost, true);
});

test('image comparison helpers sort left and top positions', () => {
    const {context} = createContext();
    assert.equal(context.compareLeft({left: 1}, {left: 2}), -1);
    assert.equal(context.compareLeft({left: 2}, {left: 1}), 1);
    assert.equal(context.compareLeft({left: 1}, {left: 1}), 0);
    assert.equal(context.compareTop({top: 1}, {top: 2}), -1);
    assert.equal(context.compareTop({top: 2}, {top: 1}), 1);
    assert.equal(context.compareTop({top: 1}, {top: 1}), 0);
});

test('resizeImages removes leading, trailing, and duplicate breaks', () => {
    const {context, environment} = createContext();
    const img = environment.element('__img__');
    img.widthValue = 200;
    img.heightValue = 100;
    environment.element('div#area').widthValue = 400;
    context.imageOrder.area = ['BREAK', 'BREAK', img, 'BREAK', 'BREAK'];

    context.resizeImages('area');

    assert.equal(context.imageOrder.area.length, 1);
    assert.equal(context.imageOrder.area[0], img);
    assert.equal(environment.element('div#area').heightValue, 200);
});

test('removeImage returns an image to the image holder and clears layout styles', () => {
    const {context, environment} = createContext();
    const img = environment.element('__img__').attr('id', 'image-1');
    const parent = environment.element('__area__').attr('id', 'area');
    img.parentResult = parent;
    context.imageOrder.area = [img];
    context.resizeImages = () => {};

    context.removeImage(img);

    assert.equal(context.imageOrder.area.length, 0);
    assert.equal(img.styles.position, 'relative');
    assert.equal(img.styles.left, '');
});
