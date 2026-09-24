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

function createManageContext(options = {}) {
    const environment = createJQueryEnvironment({
        autoReady: false,
        lengths: {
            '#post-image-holder': 0,
            '.selected-tag': 0,
            '#post-preview-image option': 0,
            ...(options.lengths || {})
        }
    });
    const confirmations = [];
    const BootstrapDialog = environment.BootstrapDialog;
    BootstrapDialog.alert = () => {};
    BootstrapDialog.confirm = (message, callback) => {
        confirmations.push(message);
        callback(options.confirmDelete !== false);
    };
    const windowObject = {location: {href: ''}};

    const context = loadBrowserScripts([
        'public/js/post-admin.js',
        'public/js/posts-manage.js'
    ], {
        $: environment.$,
        document: environment.document,
        window: windowObject,
        BootstrapDialog,
        setTimeout: environment.setTimeout,
        addTextArea() {},
        addImageArea() {},
        removeImage() {}
    });

    return {confirmations, context, environment, windowObject};
}

test('posts-manage initializes the DataTable with blog management columns', () => {
    const {context, environment} = createManageContext({lengths: {'#posts': 1}});
    context.sortOptions = () => {};

    environment.runReady();

    const config = environment.dataTable.config;
    assert.equal(config.ajax, '/api/get-blogs-details.php?a=1');
    equalStructure(config.order, [[2, 'desc']]);
    assert.equal(config.columnDefs.length, 4);
    assert.match(config.columnDefs[0].data({id: 7}), /quick-edit-post-btn/);
    assert.equal(config.columnDefs[1].data({id: 7, title: 'Title'}), "<a href='/blog/post.php?p=7'>Title</a>");
    assert.equal(config.columnDefs[3].data({active: '1'}), 'true');

    const row = environment.element('__post_row__');
    config.fnCreatedRow(row, {id: 7});
    assert.equal(row.attr('post-id'), '7');
});

test('posts-manage setupEdit sends the clicked row data to quick edit', () => {
    const {context, environment} = createManageContext();
    const expected = {id: 15, title: 'Quick'};
    context.post_table = {
        row() {
            return {
                data() {
                    return expected;
                }
            };
        }
    };
    let edited;
    context.editPost = (post) => {
        edited = post;
    };

    context.setupEdit();
    environment.element('.quick-edit-post-btn').trigger('click');

    equalStructure(edited, expected);
});

test('posts-manage editPost populates fields and loads full post metadata', () => {
    const {context, environment} = createManageContext();
    environment.queueGet('/api/get-blog-full.php', {
        type: 'success',
        data: {
            content: [[{location: '/blog/images/photo.jpg'}]],
            tags: []
        }
    });

    context.editPost({
        id: 21,
        title: 'Edit Me',
        date: '2026-09-24',
        active: '1',
        preview: '/preview.jpg',
        offset: '-4'
    });

    assert.equal(environment.element('#post').attr('post-id'), '21');
    assert.equal(environment.element('#post-title-input').val(), 'Edit Me');
    assert.equal(environment.element('#post-date-input').val(), '2026-09-24');
    assert.equal(environment.element('#post-active-input').prop('checked'), true);
    const preview = environment.element('#post-preview-holder').appended[0];
    assert.equal(preview.attr('src'), '/preview.jpg');
    assert.equal(preview.css('top'), '-4px');
    equalStructure(preview.draggableOptions, {axis: 'y'});
    equalStructure(environment.calls.get[0], {
        url: '/api/get-blog-full.php',
        data: {post: 21}
    });
    assert.equal(environment.element('#post').attr('post-location'), '/blog/images');
});

test('posts-manage deletePost removes the DataTable row after confirmation', () => {
    const {context, environment} = createManageContext();
    let removed = false;
    let drawn = false;
    context.post_table = {
        row() {
            return {
                remove() {
                    removed = true;
                    return {
                        draw() {
                            drawn = true;
                        }
                    };
                }
            };
        }
    };
    environment.queuePost('/api/delete-blog.php', {type: 'success', data: ''});

    context.deletePost(33);

    equalStructure(environment.calls.post[0], {
        url: '/api/delete-blog.php',
        data: {post: 33}
    });
    assert.equal(removed, true);
    assert.equal(drawn, true);
    assert.equal(environment.element('#post-update-button').prop('disabled'), false);
    assert.equal(environment.element('#post-delete-button').prop('disabled'), false);
});

test('posts-manage deletePost leaves controls enabled when deletion is cancelled', () => {
    const {context, environment} = createManageContext({confirmDelete: false});

    context.deletePost(34);

    assert.equal(environment.calls.post.length, 0);
    assert.equal(environment.element('#post-update-button').prop('disabled'), false);
    assert.equal(environment.element('#post-delete-button').prop('disabled'), false);
    assert.equal(environment.element('#post-update-close-button').prop('disabled'), false);
});

test('posts-manage updatePost sends quick-edit fields and reloads the table', () => {
    const {context, environment} = createManageContext({
        lengths: {'#post-tags span': 1}
    });
    environment.element('#post-tags span').attr('tag-id', '4');
    environment.element('#post-preview-image').val('preview.jpg');
    environment.element('#post-preview-holder img').css('top', '-8px');
    environment.element('#post-title-input').val('Changed');
    environment.element('#post-date-input').val('2026-09-25');
    environment.element('#post-active-input').prop('checked', true);
    let reloads = 0;
    context.post_table = {
        ajax: {
            reload() {
                reloads += 1;
            }
        }
    };
    environment.queuePost('/api/update-blog-post.php', {type: 'success', data: ''});

    context.updatePost(51);

    equalStructure(environment.calls.post[0], {
        url: '/api/update-blog-post.php',
        data: {
            post: 51,
            title: 'Changed',
            date: '2026-09-25',
            tags: ['4'],
            preview: {
                img: 'preview.jpg',
                offset: '-8px'
            },
            active: 1
        }
    });
    assert.equal(reloads, 1);
    assert.equal(environment.element('#post-update-button').prop('disabled'), false);
});

test('posts-manage updatePost publishes when the update endpoint returns published', () => {
    const {context, environment} = createManageContext();
    let reloads = 0;
    context.post_table = {
        ajax: {
            reload() {
                reloads += 1;
            }
        }
    };
    environment.queuePost('/api/update-blog-post.php', {type: 'success', data: 'published'});
    environment.queuePost('/api/publish-blog-post.php', {type: 'success', data: ''});

    context.updatePost(52);

    assert.equal(environment.calls.post.length, 2);
    equalStructure(environment.calls.post[1], {
        url: '/api/publish-blog-post.php',
        data: {post: 52}
    });
    assert.equal(reloads, 1);
});

test('posts-manage draw/search refreshes edit handlers and tooltips', () => {
    const {context, environment} = createManageContext({lengths: {'#posts': 1}});
    context.sortOptions = () => {};
    let setups = 0;
    context.setupEdit = () => {
        setups += 1;
    };

    environment.runReady();
    environment.element('#posts').trigger('draw.dt search.dt');

    assert.equal(setups, 1);
});

test('posts-manage editPost adds returned tags to the quick editor', () => {
    const {context, environment} = createManageContext();
    let added = 0;
    context.addTag = () => {
        added += 1;
    };
    environment.queueGet('/api/get-blog-full.php', {
        type: 'success',
        data: {
            content: [],
            tags: [{id: 2}, {id: 4}]
        }
    });

    context.editPost({
        id: 22,
        title: 'Tags',
        date: '2026-09-24',
        active: '0',
        preview: '/preview.jpg',
        offset: '0'
    });

    assert.equal(added, 2);
    assert.equal(environment.element('#post-tags-select').val(), 4);
});

test('posts-manage deletePost displays API errors without removing the row', () => {
    const {context, environment} = createManageContext();
    let removed = false;
    context.post_table = {
        row() {
            return {
                remove() {
                    removed = true;
                    return {draw() {}};
                }
            };
        }
    };
    environment.queuePost('/api/delete-blog.php', {
        type: 'success',
        data: 'Delete refused'
    });

    context.deletePost(61);

    assert.equal(removed, false);
    assert.match(environment.element('#post .modal-body').appended.join(''), /Delete refused/);
    assert.equal(environment.element('#post-delete-button').prop('disabled'), false);
});

test('posts-manage updatePost displays server validation without reloading', () => {
    const {context, environment} = createManageContext();
    let reloads = 0;
    context.post_table = {
        ajax: {
            reload() {
                reloads += 1;
            }
        }
    };
    environment.queuePost('/api/update-blog-post.php', {
        type: 'success',
        data: 'Validation failed'
    });

    context.updatePost(62);

    assert.equal(reloads, 0);
    assert.match(environment.element('#post .modal-body').appended.join(''), /Validation failed/);
    assert.equal(environment.element('#post-update-button').prop('disabled'), false);
});
