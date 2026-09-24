const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createPostContext(options = {}) {
    const environment = createJQueryEnvironment({autoReady: false});
    const scripts = new Map();
    const documentObject = environment.document;
    documentObject.getElementById = (id) => scripts.get(id) || null;
    documentObject.createElement = () => {
        const listeners = new Map();
        return {
            id: '',
            async: false,
            src: '',
            dataset: {},
            onload: null,
            addEventListener(event, callback) {
                listeners.set(event, callback);
            },
            trigger(event) {
                const callback = listeners.get(event);
                if (callback) {
                    callback();
                }
            }
        };
    };
    documentObject.head = {
        appendChild(script) {
            scripts.set(script.id, script);
        }
    };

    const windowObject = {
        FB: options.FB,
        a2a: options.a2a
    };
    const windowElement = environment.element(String(windowObject));
    windowElement.widthValue = options.windowWidth ?? 1024;

    const globals = {
        $: environment.$,
        document: documentObject,
        window: windowObject,
        BootstrapDialog: environment.BootstrapDialog
    };
    if (Object.prototype.hasOwnProperty.call(options, 'socialAllowed')) {
        globals.hasCookiePreference = () => options.socialAllowed;
    }

    const context = loadBrowserScript('public/js/post.js', globals);
    return {context, environment, scripts, windowObject};
}

test('post.js builds canonical blog post links', () => {
    const {context} = createPostContext();

    assert.equal(context.getLink({id: 42}), '/blog/post.php?p=42');
});

test('post.js pads single-digit date/time values', () => {
    const {context} = createPostContext();

    assert.equal(context.appendLeadingZeroes(0), '00');
    assert.equal(context.appendLeadingZeroes(9), '09');
    assert.equal(context.appendLeadingZeroes(10), 10);
});

test('checkPost disables comment submission for empty or blocked content', () => {
    const {context, environment} = createPostContext();
    const message = environment.element('#post-comment-message');
    const submit = environment.element('#post-comment-submit');

    message.val('');
    assert.equal(context.checkPost(), false);
    assert.equal(submit.prop('disabled'), true);
    assert.equal(submit.hasClass('disabled'), true);

    message.val('This contains shit');
    assert.equal(context.checkPost(), false);
    assert.equal(submit.prop('disabled'), true);
});

test('checkPost enables comment submission for acceptable content', () => {
    const {context, environment} = createPostContext();
    environment.element('#post-comment-message').val('Really enjoyed this post');

    assert.equal(context.checkPost(), true);
    assert.equal(environment.element('#post-comment-submit').prop('disabled'), false);
    assert.equal(environment.element('#post-comment-submit').hasClass('disabled'), false);
});

test('loadPostPreview renders preview metadata into the requested desktop column', () => {
    const {context, environment} = createPostContext({windowWidth: 1200});
    environment.element('.col-gallery').widthValue = 340;

    context.loadPostPreview(2, {
        id: 7,
        preview: '/img/blog.jpg',
        offset: '-12',
        title: 'Sample Blog'
    });

    const column = environment.element('#post-2');
    assert.equal(column.appended.length, 1);
    const holder = column.appended[0];
    assert.equal(holder.hasClass('post'), true);
    assert.equal(holder.hasClass('hovereffect'), true);
    assert.equal(holder.heightValue, 200);
    assert.equal(holder.css('background-image'), 'url("/img/blog.jpg")');
    assert.equal(holder.css('background-position'), '0 -12px');
    assert.equal(holder.css('background-size'), '340px');
});

test('loadPostPreview collapses previews into the center column on mobile', () => {
    const {context, environment} = createPostContext({windowWidth: 600});
    environment.element('.col-gallery').widthValue = 340;

    context.loadPostPreview(0, {
        id: 8,
        preview: '/img/mobile.jpg',
        offset: '0',
        title: 'Mobile'
    });

    assert.equal(environment.element('#post-0').appended.length, 0);
    assert.equal(environment.element('#post-1').appended.length, 1);
});

test('socialTrackingAllowed follows the social consent preference', () => {
    assert.equal(createPostContext().context.socialTrackingAllowed(), false);
    assert.equal(createPostContext({socialAllowed: false}).context.socialTrackingAllowed(), false);
    assert.equal(createPostContext({socialAllowed: true}).context.socialTrackingAllowed(), true);
});

test('loadExternalScript creates a script once and calls its callback after load', () => {
    const {context, scripts} = createPostContext();
    let calls = 0;

    context.loadExternalScript('external-script', 'https://example.org/a.js', () => {
        calls += 1;
    });

    const script = scripts.get('external-script');
    assert.ok(script);
    assert.equal(script.async, true);
    assert.equal(script.src, 'https://example.org/a.js');
    assert.equal(calls, 0);

    script.onload();
    assert.equal(script.dataset.loaded, 'true');
    assert.equal(calls, 1);

    context.loadExternalScript('external-script', 'https://example.org/a.js', () => {
        calls += 1;
    });
    assert.equal(calls, 2);
    assert.equal(scripts.size, 1);
});

test('loadSM initializes Facebook only after the SDK is loaded', () => {
    let parses = 0;
    const {context, scripts} = createPostContext({
        FB: {
            XFBML: {
                parse() {
                    parses += 1;
                }
            }
        }
    });

    context.loadSM();
    scripts.get('facebook-jssdk').onload();

    assert.equal(parses, 1);
});

test('addComment appends a comment row and updates the comment count', () => {
    const {context, environment} = createPostContext();
    environment.element('#post-comments h2').html('1 Comment');

    context.addComment({
        id: 15,
        delete: false,
        comment: 'Nice post',
        name: 'Reader',
        date: '2026-09-24'
    });

    assert.equal(environment.element('#post-comments > div:first-child').afterValues.length, 1);
    assert.equal(environment.element('#post-comments h2').html(), '2 Comments');
});

test('loadPost renders a full post without social widgets when consent is absent', () => {
    const {context, environment, scripts} = createPostContext({socialAllowed: false});
    environment.element('#post-comments h2').html('0 Comments');

    context.loadPost({
        id: 12,
        title: 'Rendered Post',
        date: 'September 24, 2026',
        twitter: 'tweet',
        tags: [{id: 3, tag: 'Family'}],
        content: [[{text: '<p>Hello</p>'}]],
        comments: [{
            id: 1,
            delete: false,
            comment: 'Comment',
            name: 'Reader',
            date: 'Today'
        }]
    }, '<h1>');

    assert.equal(environment.element('h1').html(), 'Rendered Post');
    assert.equal(environment.element('#breadcrumb-title').html(), 'Rendered Post');
    assert.equal(environment.element('#post-content').appended.length, 1);
    assert.equal(environment.element('#post-comments h2').html(), '1 Comment');
    assert.equal(scripts.size, 0);
});

test('submitPost sends comment fields and clears the message after success', () => {
    const {context, environment} = createPostContext();
    environment.element('#post-comment-message').val('A useful comment');
    environment.element('#post-comment-name').val('Reader');
    environment.element('#post-comment-email').val('reader@example.org');
    environment.element('#post-comment-submit').attr('post-id', '55');
    environment.element('#post-comments h2').html('0 Comments');
    environment.queuePost('/api/create-blog-comment.php', {
        type: 'success',
        data: '123'
    });

    context.submitPost();

    assert.deepEqual(environment.calls.post[0], {
        url: '/api/create-blog-comment.php',
        data: {
            post: '55',
            name: 'Reader',
            email: 'reader@example.org',
            message: 'A useful comment'
        }
    });
    assert.equal(environment.element('#post-comment-message').val(), '');
    assert.equal(environment.element('#post-comments h2').html(), '1 Comment');
});

test('deletePost removes a comment after the delete API succeeds', () => {
    const {context, environment} = createPostContext();
    const target = environment.element('__comment__');
    environment.element('#post-comments h2').html('2 Comments');
    environment.queuePost('/api/delete-blog-comment.php', {
        type: 'success',
        data: ''
    });

    context.deletePost({data: 44, currentTarget: target});
    const config = environment.dialogs[0];
    const dialog = environment.createDialog();
    const button = environment.createButton('__delete_button__');
    button.closestResult = environment.element('__delete_modal__');

    config.buttons[0].action.call(button, dialog);

    assert.deepEqual(environment.calls.post[0], {
        url: '/api/delete-blog-comment.php',
        data: {comment: 44}
    });
    assert.equal(dialog.closed, true);
    assert.equal(target.removed, true);
    assert.equal(environment.element('#post-comments h2').html(), '1 Comment');
});

test('setCommentHeader preserves singular/plural grammar', () => {
    const {context, environment} = createPostContext();
    environment.element('#post-comments h2').html('2 Comments');

    context.setCommentHeader(-1);
    assert.equal(environment.element('#post-comments h2').html(), '1 Comment');

    context.setCommentHeader(-1);
    assert.equal(environment.element('#post-comments h2').html(), '0 Comments');
});
