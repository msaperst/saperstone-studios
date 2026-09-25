const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

test('post editor initializes a blank post with an image area', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#post-editor-config').attr({'data-tags':'[]','data-groups':'[]'});
    let imageAreas = 0;
    loadBrowserScript('public/js/post-editor-init.js', {
        $: environment.$,
        document: environment.document,
        addTag() {},
        addTextArea() {},
        addImageArea() { imageAreas += 1; }
    });
    environment.runReady();
    assert.equal(imageAreas, 1);
});

test('post editor restores tags and mixed content groups', () => {
    const environment = createJQueryEnvironment({autoReady: false});
    environment.element('#post-editor-config').attr({
        'data-tags':'[2,4]',
        'data-groups': JSON.stringify([
            {type:'text', text:'hello'},
            {type:'images', images:['one.jpg','two.jpg']}
        ])
    });
    const tags = [];
    const texts = [];
    const images = [];
    loadBrowserScript('public/js/post-editor-init.js', {
        $: environment.$,
        document: environment.document,
        addTag(element) { tags.push(element.val()); },
        addTextArea(text) { texts.push(text); },
        addImageArea(group) { images.push(group); }
    });
    environment.runReady();
    assert.deepEqual(tags, [2, 4]);
    assert.deepEqual(texts, ['hello']);
    assert.deepEqual(images, [['one.jpg','two.jpg']]);
});
