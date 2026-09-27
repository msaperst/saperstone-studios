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


test('makeSortable allows text and image sections to be reordered by their handles', () => {
    const {context, environment} = createContext();

    context.makeSortable();

    const options = environment.element('#post-content').sortableOptions;
    assert.equal(options.items, '> .blog-editable-text, > .blog-editable-images');
    assert.equal(options.handle, '.blog-section-drag-handle');
    options.start();
    assert.equal(context.isDragged, true);
    options.stop();
    assert.equal(context.isDragged, false);
});

test('addTextArea creates a draggable text section and initializes Summernote', () => {
    const {context, environment} = createContext();

    context.addTextArea('<p>Hello</p>');

    const holder = environment.element('#post-content').appended[0];
    assert.equal(holder.hasClass('blog-editable-text'), true);
    assert.equal(holder.appended.length, 2);
    const handle = holder.appended[0];
    assert.equal(handle.hasClass('blog-section-drag-handle'), true);
    assert.match(handle.attr('title'), /Drag text section/);
    const content = holder.appended[1];
    assert.equal(content.hasClass('blog-text-content'), true);
    assert.equal(content.html(), '<p>Hello</p>');
});

test('addImageArea gives empty image sections a reorder handle', () => {
    const {context, environment} = createContext();

    context.addImageArea([]);

    const holder = environment.element('#post-content').appended[0];
    assert.equal(holder.hasClass('blog-editable-images'), true);
    assert.equal(holder.hasClass('blog-empty-image-section'), true);
    assert.equal(holder.appended[0].hasClass('blog-section-drag-handle'), true);
    assert.equal(environment.element('#post-content').sortableOptions.handle, '.blog-section-drag-handle');
});


test('addImageArea gives populated image sections a reorder handle', () => {
    const {context, environment} = createContext();

    context.addImageArea([{location:'one.jpg', top:0, left:0, width:100, height:100}]);

    const holder = environment.element('#post-content').appended[0];
    assert.equal(holder.hasClass('blog-editable-images'), true);
    assert.equal(holder.hasClass('blog-empty-image-section'), false);
    assert.equal(holder.appended[0].hasClass('blog-section-drag-handle'), true);
    assert.match(holder.appended[0].attr('title'), /Drag image section/);
    assert.equal(environment.element('#post-content').sortableOptions.items,
        '> .blog-editable-text, > .blog-editable-images');
});

test('double-clicking a populated image section handle returns its images to the media library', () => {
    const {context, environment} = createContext();

    context.addImageArea([{location:'one.jpg', top:0, left:0, width:100, height:100}]);

    const holder = environment.element('#post-content').appended[0];
    const handle = holder.appended[0];
    const imageBuilder = holder.appended[1];
    const image = imageBuilder.appended[0];
    image.parentResult = imageBuilder;
    context.resizeImages = () => {};

    handle.handlers.get('dblclick')();

    assert.equal(image.appendTarget, '#holder');
    assert.equal(holder.removed, true);
});
test('section drag handle exposes accessible reorder instructions', () => {
    const {context} = createContext();

    const handle = context.createSectionDragHandle('Drag this section');

    assert.equal(handle.attr('title'), 'Drag this section');
    assert.equal(handle.attr('aria-label'), 'Drag this section');
    assert.match(handle.html(), /fa-arrows-v/);
});
