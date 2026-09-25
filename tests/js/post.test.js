const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScripts} = require('./helpers/load-browser-script');

function equalStructure(actual, expected) {
    assert.deepEqual(
        JSON.parse(JSON.stringify(actual)),
        JSON.parse(JSON.stringify(expected))
    );
}

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
        location: {
            origin: options.origin || 'https://saperstonestudios.com'
        },
        prompt: options.prompt
    };
    const windowElement = environment.element(String(windowObject));
    windowElement.widthValue = options.windowWidth ?? 1024;

    const globals = {
        $: environment.$,
        document: documentObject,
        window: windowObject,
        navigator: options.navigator || {},
        BootstrapDialog: environment.BootstrapDialog
    };
    if (Object.prototype.hasOwnProperty.call(options, 'socialAllowed')) {
        globals.hasCookiePreference = () => options.socialAllowed;
    }

    const context = loadBrowserScripts(['public/js/blog-common.js', 'public/js/post.js'], globals);
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
    assert.equal(environment.element('#post-content').appended.length, 2);
    assert.equal(environment.element('#post-content').appended[1].appended[0].hasClass('blog-share-actions'), true);
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

    equalStructure(environment.calls.post[0], {
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

    equalStructure(environment.calls.post[0], {
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

test('addSocialMedias builds Facebook and Twitter controls for a post', () => {
    const {context} = createPostContext();

    const controls = context.addSocialMedias({
        id: 5,
        twitter: 'tweet-123'
    });

    assert.equal(controls.hasClass('col-md-4'), true);
    assert.equal(controls.hasClass('text-right'), true);
    assert.equal(controls.appended.length, 2);
    assert.equal(controls.appended[0].hasClass('fbook'), true);
    assert.equal(controls.appended[1].hasClass('tweet'), true);
});

test('addShares renders native share and copy-link controls without third-party markup', () => {
    const {context, environment} = createPostContext({
        navigator: {
            share() {
                return Promise.resolve();
            },
            clipboard: {
                writeText() {
                    return Promise.resolve();
                }
            }
        }
    });

    context.addShares({id: 9, title: 'Share Me'});

    const row = environment.element('#post-content').appended[0];
    const shares = row.appended[0];

    assert.equal(shares.hasClass('blog-share-actions'), true);
    assert.equal(shares.appended.length, 2);
    assert.equal(shares.appended[0].hasClass('blog-share-native'), true);
    assert.equal(shares.appended[1].hasClass('blog-share-copy'), true);

    const classes = shares.appended.map((button) => Array.from(button.classes).join(' '));
    assert.equal(classes.some((value) => value.includes('a2a_')), false);
});

test('sharePost uses the browser Web Share API with an absolute blog URL', async () => {
    let shared;
    const {context} = createPostContext({
        navigator: {
            share(data) {
                shared = data;
                return Promise.resolve();
            }
        }
    });

    await context.sharePost({id: 42, title: 'Native Share'});

    equalStructure(shared, {
        title: 'Native Share',
        url: 'https://saperstonestudios.com/blog/post.php?p=42'
    });
});

test('copyShareLink writes the absolute blog URL to the clipboard', async () => {
    let copied;
    const {context} = createPostContext({
        navigator: {
            clipboard: {
                writeText(value) {
                    copied = value;
                    return Promise.resolve();
                }
            }
        }
    });

    await context.copyShareLink({id: 11, title: 'Copy Me'});

    assert.equal(copied, 'https://saperstonestudios.com/blog/post.php?p=11');
});

test('copyShareLink falls back to a browser prompt when Clipboard API is unavailable', async () => {
    let prompted;
    const {context} = createPostContext({
        prompt(message, value) {
            prompted = {message, value};
        }
    });

    await context.copyShareLink({id: 12, title: 'Fallback'});

    equalStructure(prompted, {
        message: 'Copy this link:',
        value: 'https://saperstonestudios.com/blog/post.php?p=12'
    });
});

test('loadPost renders native sharing even when social tracking consent is absent', () => {
    const {context, environment} = createPostContext({
        socialAllowed: false,
        navigator: {
            clipboard: {
                writeText() {
                    return Promise.resolve();
                }
            }
        }
    });
    environment.element('#post-comments h2').html('0 Comments');

    context.loadPost({
        id: 14,
        title: 'Consent Independent Share',
        date: 'Today',
        tags: [],
        content: [[{text: '<p>Hello</p>'}]],
        comments: []
    }, '<h1>');

    const appended = environment.element('#post-content').appended;
    assert.equal(appended.length, 2);
    assert.equal(appended[1].appended[0].hasClass('blog-share-actions'), true);
});

test('loadPost renders protected image content with its calculated height', () => {
    const {context, environment} = createPostContext({socialAllowed: false});
    environment.element('#post-comments h2').html('0 Comments');

    context.loadPost({
        id: 13,
        title: 'Image Post',
        date: 'Today',
        tags: [],
        content: [[{
            location: '/img/blog.jpg',
            height: '100',
            width: '200',
            left: '5',
            top: '20'
        }]],
        comments: []
    }, '<h1>');

    const holder = environment.element('#post-content').appended[0];
    const contentRow = holder.appended[1];
    const protect = contentRow.appended[0];
    const images = contentRow.appended[1];

    assert.equal(protect.hasClass('post-protects'), true);
    assert.equal(protect.css('height'), '120px');
    assert.equal(protect.appended[0].attr('src'), '/img/image.png');
    assert.equal(images.hasClass('post-images'), true);
    assert.equal(images.css('height'), '120px');
    assert.equal(images.appended[0].attr('src'), '/img/blog.jpg');
});

test('submitPost surfaces an API validation response and rechecks the form', () => {
    const {context, environment} = createPostContext();
    environment.element('#post-comment-message').val('Valid comment');
    environment.element('#post-comment-name').val('Reader');
    environment.element('#post-comment-email').val('reader@example.org');
    environment.element('#post-comment-submit').attr('post-id', '22');
    environment.queuePost('/api/create-blog-comment.php', {
        type: 'success',
        data: 'Validation failed'
    });

    context.submitPost();

    const messages = environment.element('#post-comment-message-message').appended.join('');
    assert.match(messages, /Validation failed/);
    assert.equal(environment.element('#post-comment-submit').prop('disabled'), false);
});

test('deletePost surfaces request failures and restores dialog controls', () => {
    const {context, environment} = createPostContext();
    environment.queuePost('/api/delete-blog-comment.php', {
        type: 'failure',
        xhr: {responseText: 'Cannot delete'},
        error: 'Server Error'
    });

    context.deletePost({data: 45, currentTarget: environment.element('__comment_failure__')});
    const config = environment.dialogs[0];
    const dialog = environment.createDialog();
    const button = environment.createButton('__delete_failure_button__');
    const modal = environment.element('__delete_failure_modal__');
    button.closestResult = modal;

    config.buttons[0].action.call(button, dialog);

    assert.match(modal.find('.bootstrap-dialog-body').appended.join(''), /Cannot delete/);
    assert.equal(button.spinCount, 1);
    assert.equal(button.stopSpinCount, 1);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});
