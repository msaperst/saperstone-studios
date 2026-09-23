const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./album-test-utils');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function registerAddAlbumTests(label, scriptPath) {
    test(`${label}: addAlbum reloads the table and restores the button on success`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('ABC123');
        environment.queueGet('/api/find-album.php', {
            type: 'success',
            data: '42'
        });

        context.addAlbum();

        assert.deepEqual(plain(environment.calls.get[0]), {
            url: '/api/find-album.php',
            data: {
                code: 'ABC123',
                albumAdd: 1
            }
        });
        assert.equal(environment.calls.reload.length, 1);
        assert.match(environment.element('#add-album-div').appended[0], /Added album to your list/);
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
        assert.equal(environment.element('#album-code-add em').hasClass('fa'), true);
        assert.equal(environment.element('#album-code-add em').hasClass('icon-spin'), false);

        environment.runTimeouts();
        assert.equal(environment.element('#album-code-add-message').removed, true);
    });

    test(`${label}: addAlbum displays an API error and restores the button`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('BAD');
        environment.queueGet('/api/find-album.php', {
            type: 'failure',
            xhr: {
                responseText: 'That code does not match any albums'
            }
        });

        context.addAlbum();

        assert.match(
            environment.element('#add-album-div').appended[0],
            /That code does not match any albums/
        );
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
        assert.equal(environment.element('#album-code-add em').hasClass('icon-spin'), false);
    });
}

function registerSharedAlbumManagementTests(label, scriptPath) {
    test(`${label}: thumbnailStatus distinguishes N/A, ready, and missing thumbnails`, () => {
        const {context} = createAlbumContext(scriptPath);

        assert.match(context.thumbnailStatus({images: '0', thumbsCreated: '0'}), /N\/A/);
        assert.match(context.thumbnailStatus({images: '2', thumbsCreated: '1'}), /Ready/);
        assert.match(context.thumbnailStatus({images: '2', thumbsCreated: '0'}), /Missing/);
    });

    test(`${label}: disableDialogButtons disables the upload and dialog controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const dialog = environment.createDialog();

        context.disableDialogButtons(dialog);

        const uploadButton = dialog.$modalFooter.find('#add-images-button');
        assert.equal(uploadButton.prop('disabled'), true);
        assert.equal(uploadButton.hasClass('disabled'), true);
        assert.equal(uploadButton.css('pointer-events'), 'none');
        assert.equal(dialog.buttonsEnabled, false);
        assert.equal(dialog.closable, false);
    });

    test(`${label}: enableDialogButtons restores the upload and dialog controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const dialog = environment.createDialog();
        context.disableDialogButtons(dialog);

        context.enableDialogButtons(dialog);

        const uploadButton = dialog.$modalFooter.find('#add-images-button');
        assert.equal(uploadButton.prop('disabled'), false);
        assert.equal(uploadButton.hasClass('disabled'), false);
        assert.equal(uploadButton.css('pointer-events'), 'inherit');
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    });

    test(`${label}: empty albums report that there are no thumbnails to create`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();
        parentDialog.enableButtons(false);
        parentDialog.setClosable(false);

        context.chooseThumbnailScope(10, 0, false, button, parentDialog);

        const config = environment.dialogs.at(-1);
        assert.equal(config.title, 'Create Thumbnails');
        assert.match(config.message, /does not have any images/);
        assert.deepEqual(Array.from(config.buttons, (item) => item.label), ['Close']);

        const scopeDialog = environment.createDialog();
        config.buttons[0].action(scopeDialog);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(parentDialog.buttonsEnabled, true);
        assert.equal(parentDialog.closable, true);
        assert.equal(scopeDialog.closed, true);
    });

    test(`${label}: incomplete albums offer missing-only and recreate-all thumbnail choices`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const calls = [];
        context.chooseThumbnailMarkup = (...args) => calls.push(args);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailScope(11, 3, true, button, parentDialog);

        const config = environment.dialogs.at(-1);
        assert.match(config.message, /Some thumbnails are missing/);
        assert.deepEqual(
            Array.from(config.buttons, (item) => item.label.trim()),
            ['Missing Only', 'Recreate All', 'Close']
        );

        const scopeDialog = environment.createDialog();
        config.buttons[0].action(scopeDialog);
        assert.equal(scopeDialog.closed, true);
        assert.deepEqual(calls[0], [11, button, parentDialog, 'missing']);
    });

    test(`${label}: complete albums offer recreate-all without missing-only`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailScope(12, 3, false, button, parentDialog);

        const config = environment.dialogs.at(-1);
        assert.match(config.message, /All thumbnails already exist/);
        assert.deepEqual(
            Array.from(config.buttons, (item) => item.label.trim()),
            ['Recreate All', 'Close']
        );
    });

    test(`${label}: thumbnail markup choices pass the selected treatment and mode`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const calls = [];
        context.makeThumbs = (...args) => calls.push(args);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailMarkup(13, button, parentDialog, 'all');

        const config = environment.dialogs.at(-1);
        assert.deepEqual(
            Array.from(config.buttons, (item) => item.label.trim()),
            ['Proof', 'Watermark', 'Nothing', 'Close']
        );

        for (let i = 0; i < 3; i += 1) {
            const markupDialog = environment.createDialog();
            config.buttons[i].action(markupDialog);
            assert.equal(markupDialog.closed, true);
        }

        assert.deepEqual(calls, [
            [13, button, parentDialog, 'proof', 'all'],
            [13, button, parentDialog, 'watermark', 'all'],
            [13, button, parentDialog, 'none', 'all']
        ]);
    });

    test(`${label}: makeThumbs handles successful completion and refreshes album state`, () => {
        let refreshed = 0;
        const {context, environment} = createAlbumContext(scriptPath, {
            refreshAlbumThumbnailImages: () => {
                refreshed += 1;
            }
        });
        environment.queuePost('/api/make-thumbs.php', {
            type: 'success',
            data: ''
        });
        environment.queueGet('/tmp/status.txt', {
            type: 'success',
            data: 'Done'
        });

        const button = environment.createButton('__thumb_button__');
        const dialog = environment.createDialog();
        dialog.enableButtons(false);
        dialog.setClosable(false);

        context.makeThumbs(14, button, dialog, 'watermark', 'all');
        environment.runIntervalsOnce();

        assert.deepEqual(plain(environment.calls.post[0]), {
            url: '/api/make-thumbs.php',
            data: {
                id: 14,
                markup: 'watermark',
                mode: 'all'
            }
        });
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
        assert.equal(refreshed, 1);
        assert.equal(environment.calls.reload.length, 1);
        assert.equal(environment.element('#thumbnail-warning').visible, false);
        assert.equal(environment.element('#resize-progress .progress-bar').hasClass('active'), false);

        environment.runTimeouts();
        assert.equal(environment.element('#resize-progress').visible, false);
    });

    test(`${label}: makeThumbs restores controls when background processing reports an error`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queuePost('/api/make-thumbs.php', {
            type: 'success',
            data: ''
        });
        environment.queueGet('/tmp/status.txt', {
            type: 'success',
            data: 'Error: Thumbnail generation incomplete'
        });

        const button = environment.createButton('__thumb_button__');
        const dialog = environment.createDialog();
        context.makeThumbs(15, button, dialog, 'proof', 'missing');
        environment.runIntervalsOnce();

        const progress = environment.element('#resize-progress .progress-bar');
        assert.equal(progress.hasClass('progress-bar-danger'), true);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    });

    test(`${label}: makeThumbs displays request failures and restores controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queuePost('/api/make-thumbs.php', {
            type: 'failure',
            xhr: {
                responseText: 'Unable to create thumbnails'
            }
        });

        const button = environment.createButton('__thumb_button__');
        const dialog = environment.createDialog();
        context.makeThumbs(16, button, dialog, 'none', 'all');

        const progress = environment.element('#resize-progress .progress-bar');
        assert.equal(progress.html(), 'Error: Unable to create thumbnails');
        assert.equal(progress.hasClass('progress-bar-danger'), true);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    });

    test(`${label}: addUser loads and renders the selected user`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-user.php', {
            type: 'success',
            data: {
                usr: 'photographer'
            }
        });

        context.addUser(23);

        assert.deepEqual(plain(environment.calls.get[0]), {
            url: '/api/get-user.php',
            data: {
                id: 23
            }
        });
        const appended = environment.element('#album-users').appended[0];
        assert.equal(appended.attr('user-id'), '23');
        assert.equal(appended.html(), 'photographer');

        appended.trigger('click');
        assert.equal(appended.removed, true);
    });
}

module.exports = {
    registerAddAlbumTests,
    registerSharedAlbumManagementTests
};
