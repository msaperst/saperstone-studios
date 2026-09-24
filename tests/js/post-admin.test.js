const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function equalStructure(actual, expected) {
    assert.deepEqual(
        JSON.parse(JSON.stringify(actual)),
        JSON.parse(JSON.stringify(expected))
    );
}

function createAdminContext(options = {}) {
    const environment = createJQueryEnvironment({
        autoReady: false,
        lengths: {
            '#post-image-holder': 0,
            ...(options.lengths || {})
        }
    });
    const alerts = [];
    const confirmations = [];
    const BootstrapDialog = environment.BootstrapDialog;
    BootstrapDialog.alert = (message) => alerts.push(message);
    BootstrapDialog.confirm = (message, callback) => confirmations.push({message, callback});

    const windowObject = {location: {href: ''}};
    environment.element(String(windowObject)).heightValue = options.windowHeight ?? 900;

    const context = loadBrowserScript('public/js/post-admin.js', {
        $: environment.$,
        document: environment.document,
        window: windowObject,
        BootstrapDialog,
        setTimeout: environment.setTimeout,
        addTextArea() {},
        addImageArea() {},
        removeImage() {}
    });

    return {alerts, confirmations, context, environment, windowObject};
}

test('post-admin uploadForPost configures image uploads and editor height', () => {
    const {context, environment} = createAdminContext({
        lengths: {'#post-image-holder': 1}
    });
    const holder = environment.element('#post-image-holder');
    holder.offsetValue = {top: 100, left: 0};

    context.uploadForPost();

    assert.equal(environment.uploadOptions.url, '/api/upload-blog-images.php');
    assert.equal(environment.uploadOptions.multiple, true);
    assert.equal(environment.uploadOptions.sequential, true);
    assert.equal(environment.uploadOptions.acceptFiles, 'image/*');
    assert.equal(holder.heightValue, 730);
});

test('post-admin successful uploads create draggable images and preview options', () => {
    const {context, environment} = createAdminContext({
        lengths: {'#post-image-holder': 1}
    });

    context.uploadForPost();
    const statusbar = environment.element('__statusbar__');
    environment.uploadOptions.onSuccess(
        ['one.jpg', 'two.jpg'],
        JSON.stringify(['one.jpg', 'two.jpg']),
        {},
        {
            statusbar,
            progressDiv: environment.element('__progress__'),
            filename: environment.element('__filename__')
        }
    );

    const images = environment.element('#post-image-holder').appended;
    const options = environment.element('#post-preview-image').appended;
    assert.equal(images.length, 2);
    assert.equal(images[0].attr('src'), '../tmp/one.jpg');
    assert.equal(images[0].hasClass('draggable'), true);
    equalStructure(images[0].draggableOptions, {});
    assert.equal(options.length, 2);
    assert.equal(options[0].text(), 'one.jpg');
});

test('post-admin addTag moves the selected option into the selected tag list', () => {
    const {context, environment} = createAdminContext();
    const select = environment.element('#post-tags-select');
    const parent = environment.element('__tag_parent__');
    select.parentResult = parent;
    select.val('4');
    environment.element('option:selected').text('Portrait');

    context.addTag(select);

    assert.equal(parent.appended.length, 1);
    assert.equal(parent.appended[0].attr('tag-id'), '4');
    assert.equal(parent.appended[0].html(), 'Portrait');
    assert.equal(environment.element('option:selected').removed, true);
});

test('post-admin addTag delegates category creation for the special zero option', () => {
    const {context, environment} = createAdminContext();
    const select = environment.element('#post-tags-select');
    select.val('0');
    let passed;
    context.newTag = (element) => {
        passed = element;
    };

    context.addTag(select);

    assert.equal(passed, select);
});

test('post-admin removeTag restores an option and re-sorts choices', () => {
    const {context, environment} = createAdminContext();
    const tag = environment.element('__selected_tag__');
    tag.attr('tag-id', '8');
    tag.text('Events');
    let sorted = 0;
    context.sortOptions = () => {
        sorted += 1;
    };

    context.removeTag(tag);

    const option = environment.element('#post-tags-select').appended[0];
    assert.equal(option.val(), '8');
    assert.equal(option.html(), 'Events');
    assert.equal(tag.removed, true);
    assert.equal(sorted, 1);
});

test('post-admin previewPost and editPost toggle editor/preview state', () => {
    const {context, environment} = createAdminContext();
    environment.element('#post-title-input').val('Preview Title');
    environment.element('#post-date-input').val('2026-09-24');
    environment.element('.blog-editable-text').summernoteCode = '<p>Body</p>';

    context.previewPost();

    assert.equal(environment.element('#preview-post').visible, false);
    assert.equal(environment.element('#edit-post').visible, true);
    assert.equal(environment.element('#post-preview-image').visible, false);
    assert.equal(environment.element('#post-preview-holder').hasClass('post'), true);
    assert.equal(environment.element('#post-title-input').visible, false);
    assert.equal(environment.element('.note-editor').visible, false);

    context.editPost();

    assert.equal(environment.element('#preview-post').visible, true);
    assert.equal(environment.element('#edit-post').visible, false);
    assert.equal(environment.element('#post-preview-image').visible, true);
    assert.equal(environment.element('#post-preview-holder').hasClass('post'), false);
    assert.equal(environment.element('#post-title-input').visible, true);
    assert.equal(environment.element('.note-editor').visible, true);
});

test('post-admin collectPost rejects an empty title before invoking persistence', () => {
    const {alerts, context, environment} = createAdminContext();
    environment.element('#post-title-input').val('');
    let called = 0;

    context.collectPost(() => {
        called += 1;
    });

    equalStructure(alerts, ['Please enter a title for your post']);
    assert.equal(called, 0);
    assert.equal(environment.element('#post-information-message').removed, true);
});

test('post-admin collectPost assembles tags, preview and text content', () => {
    const {context, environment} = createAdminContext();
    environment.element('#post-title-input').val('Title');
    environment.element('#post-tags span').attr('tag-id', '3');
    environment.element('#post-preview-holder img').attr('src', '/tmp/preview.jpg');
    environment.element('#post-preview-holder img').css('top', '-10px');
    environment.element('#post-content>li').addClass('blog-editable-text');
    environment.element('#post-content>li').summernoteCode = '<p>Text</p>';

    let captured;
    context.collectPost((tags, preview, content) => {
        captured = {tags, preview, content};
    });

    equalStructure(captured.tags, ['3']);
    equalStructure(captured.preview, {
        img: '/tmp/preview.jpg',
        offset: '-10px'
    });
    equalStructure(captured.content, {
        1: {
            group: 1,
            type: 'text',
            text: '<p>Text</p>'
        }
    });
});

test('post-admin savePost sends create payload and invokes a follow-up callback', () => {
    const {context, environment} = createAdminContext();
    environment.element('#post-title-input').val('New Post');
    environment.element('#post-date-input').val('2026-09-24');
    environment.queuePost('/api/create-blog-post.php', {type: 'success', data: '77'});
    let callbackPost;

    context.savePost(['2'], {img: '/a.jpg', offset: '0px'}, {1: {type: 'text'}}, (post) => {
        callbackPost = post;
    });

    equalStructure(environment.calls.post[0], {
        url: '/api/create-blog-post.php',
        data: {
            title: 'New Post',
            date: '2026-09-24',
            tags: ['2'],
            preview: {img: '/a.jpg', offset: '0px'},
            content: {1: {type: 'text'}}
        }
    });
    assert.equal(callbackPost, '77');
});

test('post-admin updatePost sends the current post id and redirects after success', () => {
    const {context, environment, windowObject} = createAdminContext();
    environment.element('#post').attr('post-id', '88');
    environment.element('#post-title-input').val('Updated');
    environment.element('#post-date-input').val('2026-09-25');
    environment.queuePost('/api/update-blog-post.php', {type: 'success', data: ''});

    context.updatePost(['5'], {img: '/b.jpg', offset: '1px'}, {});

    equalStructure(environment.calls.post[0], {
        url: '/api/update-blog-post.php',
        data: {
            post: '88',
            title: 'Updated',
            date: '2026-09-25',
            tags: ['5'],
            preview: {img: '/b.jpg', offset: '1px'},
            content: {}
        }
    });
    assert.equal(windowObject.location.href, '/blog/post.php?p=88');
});

test('post-admin publishPost publishes the supplied id and redirects on success', () => {
    const {context, environment, windowObject} = createAdminContext();
    environment.queuePost('/api/publish-blog-post.php', {type: 'success', data: ''});

    context.publishPost(91);

    equalStructure(environment.calls.post[0], {
        url: '/api/publish-blog-post.php',
        data: {post: 91}
    });
    assert.equal(windowObject.location.href, '/blog/post.php?p=91');
});

test('post-admin schedulePost posts selected date/time from its dialog', () => {
    const {context, environment, windowObject} = createAdminContext();
    environment.element('#post-publish-date').val('2026-10-01');
    environment.element('#post-publish-time').val('14:30');
    environment.queuePost('/api/schedule-blog-post.php', {type: 'success', data: ''});

    context.schedulePost(93);
    const config = environment.dialogs[0];
    const dialog = environment.createDialog();
    const button = environment.createButton('__schedule_button__');
    button.closestResult = environment.element('__schedule_modal__');
    config.buttons[0].action.call(button, dialog);

    equalStructure(environment.calls.post[0], {
        url: '/api/schedule-blog-post.php',
        data: {
            post: 93,
            date: '2026-10-01',
            time: '14:30'
        }
    });
    assert.equal(dialog.closed, true);
    assert.equal(windowObject.location.href, '/blog/post.php?p=93');
});

test('post-admin setPreview replaces the preview with a draggable selected image', () => {
    const {context, environment} = createAdminContext();
    environment.element('#post').attr('post-location', '../tmp');
    environment.element('#post-preview-image').val('photo.jpg');

    context.setPreview();

    const image = environment.element('#post-preview-holder').appended[0];
    assert.equal(image.attr('src'), '../tmp/photo.jpg');
    assert.equal(image.css('width'), '300px');
    equalStructure(image.draggableOptions, {axis: 'y'});
});
