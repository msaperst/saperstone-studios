const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./album-test-utils');

function plain(value) {
    return JSON.parse(JSON.stringify(value));
}

function registerAddAlbumTests(label, scriptPath) {
    test(`${label}: addAlbum reloads the table and restores the button on a new add`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('ABC123');
        environment.queuePost('/api/add-album.php', {
            type: 'success',
            data: {
                id: '42',
                added: true
            }
        });

        context.addAlbum();

        assert.deepEqual(plain(environment.calls.post[0]), {
            url: '/api/add-album.php',
            data: {
                code: 'ABC123'
            }
        });
        assert.equal(environment.calls.reload.length, 1);
        assert.match(environment.element('#add-album-div').appended[0], /Added album to your list/);
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
        assert.equal(environment.element('#album-code-add em').hasClass('fa'), true);
        assert.equal(environment.element('#album-code-add em').hasClass('icon-spin'), false);
    });

    test(`${label}: addAlbum reports when the album is already in the list without reloading`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('ABC123');
        environment.queuePost('/api/add-album.php', {
            type: 'success',
            data: {
                id: '42',
                added: false
            }
        });

        context.addAlbum();

        assert.equal(environment.calls.reload.length, 0);
        assert.match(environment.element('#add-album-div').appended[0], /already in your list/);
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
    });

    test(`${label}: addAlbum clears the previous message before sending another request`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const previousMessage = environment.element('#album-code-add-message');
        previousMessage.removed = false;
        environment.element('#album-code').val('BAD');
        environment.queuePost('/api/add-album.php', {
            type: 'failure',
            xhr: {
                responseText: 'That code does not match any albums'
            }
        });

        context.addAlbum();

        assert.equal(previousMessage.removed, true);
        assert.match(
            environment.element('#add-album-div').appended[0],
            /That code does not match any albums/
        );
    });

    test(`${label}: addAlbum handles a zero id as an unexpected API error`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('ZERO');
        environment.queuePost('/api/add-album.php', {
            type: 'success',
            data: {
                id: '0',
                added: true
            }
        });

        context.addAlbum();

        assert.match(
            environment.element('#add-album-div').appended[0],
            /Some unexpected error occurred while searching for your album/
        );
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
    });

    test(`${label}: addAlbum reports an expired session for unauthorized failures without a response body`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('EXPIRED');
        environment.queuePost('/api/add-album.php', {
            type: 'failure',
            xhr: {
                responseText: ''
            },
            error: 'Unauthorized'
        });

        context.addAlbum();

        assert.match(
            environment.element('#add-album-div').appended[0],
            /session has timed out/
        );
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
    });

    test(`${label}: addAlbum displays a generic error for a malformed success response`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('CUSTOM');
        environment.queuePost('/api/add-album.php', {
            type: 'success',
            data: {
                id: 'not-a-number',
                added: true
            }
        });

        context.addAlbum();

        assert.match(
            environment.element('#add-album-div').appended[0],
            /Some unexpected error occurred while searching for your album/
        );
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
    });

    test(`${label}: addAlbum displays a generic error for failures without details`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('UNKNOWN');
        environment.queuePost('/api/add-album.php', {
            type: 'failure',
            xhr: {
                responseText: ''
            },
            error: 'Server Error'
        });

        context.addAlbum();

        assert.match(
            environment.element('#add-album-div').appended[0],
            /Some unexpected error occurred while searching for your album/
        );
        assert.equal(environment.element('#album-code-add').prop('disabled'), false);
    });

    test(`${label}: addAlbum displays an API error and restores the button`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#album-code').val('BAD');
        environment.queuePost('/api/add-album.php', {
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

function registerCreateAlbumTests(label, scriptPath) {
    function runCreate(response) {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.element('#new-album-name').val('Created Album');
        environment.element('#new-album-description').val('Description');
        environment.element('#new-album-date').val('2026-09-22');
        environment.queuePost('/api/create-album.php', response);

        const editCalls = [];
        context.editAlbum = (id) => editCalls.push(id);
        context.openCreateAlbumDialog();

        const config = environment.dialogs.at(-1);
        const dialog = environment.createDialog();
        const button = environment.createButton('__create_album_button__');
        button.closestResult = environment.element('__create_album_modal__');
        config.buttons[0].action.call(button, dialog);

        return {
            context,
            environment,
            config,
            dialog,
            button,
            editCalls,
            modalBody: environment.element('__create_album_modal__ .bootstrap-dialog-body')
        };
    }

    test(`${label}: create album adds the new row and opens the album editor`, () => {
        const result = runCreate({
            type: 'success',
            data: '31'
        });

        assert.deepEqual(plain(result.environment.calls.post[0]), {
            url: '/api/create-album.php',
            data: {
                name: 'Created Album',
                description: 'Description',
                date: '2026-09-22'
            }
        });
        assert.equal(result.environment.calls.rowAdd.length, 1);
        assert.equal(result.environment.calls.rowAdd[0].id, '31');
        assert.equal(result.environment.calls.rowAdd[0].name, 'Created Album');
        assert.equal(result.dialog.closed, true);
        assert.deepEqual(result.editCalls, ['31']);
        assert.equal(result.button.stopSpinCount, 1);
        assert.equal(result.dialog.buttonsEnabled, true);
        assert.equal(result.dialog.closable, true);
    });

    test(`${label}: create album treats a zero response as an unexpected error`, () => {
        const result = runCreate({
            type: 'success',
            data: '0'
        });

        assert.match(
            result.modalBody.appended[0],
            /Some unexpected error occurred while creating your album/
        );
        assert.equal(result.button.stopSpinCount, 1);
    });

    test(`${label}: create album displays a custom API response`, () => {
        const result = runCreate({
            type: 'success',
            data: 'Custom create error'
        });

        assert.match(result.modalBody.appended[0], /Custom create error/);
        assert.equal(result.button.stopSpinCount, 1);
    });

    test(`${label}: create album displays an HTTP response error`, () => {
        const result = runCreate({
            type: 'failure',
            xhr: {
                responseText: 'Album creation failed'
            }
        });

        assert.match(result.modalBody.appended[0], /Album creation failed/);
        assert.equal(result.button.stopSpinCount, 1);
    });

    test(`${label}: create album reports an expired session`, () => {
        const result = runCreate({
            type: 'failure',
            xhr: {
                responseText: ''
            },
            error: 'Unauthorized'
        });

        assert.match(result.modalBody.appended[0], /session has timed out/);
        assert.equal(result.button.stopSpinCount, 1);
    });

    test(`${label}: create album displays a generic request error`, () => {
        const result = runCreate({
            type: 'failure',
            xhr: {
                responseText: ''
            },
            error: 'Server Error'
        });

        assert.match(
            result.modalBody.appended[0],
            /Some unexpected error occurred while creating your album/
        );
        assert.equal(result.button.stopSpinCount, 1);
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

        context.chooseThumbnailScope(10, 0, false, false, button, parentDialog);

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

    test(`${label}: albums with no existing thumbnails skip the redundant scope choice`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const calls = [];
        context.chooseThumbnailMarkup = (...args) => calls.push(args);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailScope(11, 3, true, false, button, parentDialog);

        assert.equal(environment.dialogs.length, 0);
        assert.deepEqual(calls[0], [11, button, parentDialog, 'missing']);
    });

    test(`${label}: partially thumbed albums offer missing-only and recreate-all thumbnail choices`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const calls = [];
        context.chooseThumbnailMarkup = (...args) => calls.push(args);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailScope(12, 3, true, true, button, parentDialog);

        const config = environment.dialogs.at(-1);
        assert.match(config.message, /Some thumbnails are missing/);
        assert.deepEqual(
            Array.from(config.buttons, (item) => item.label.trim()),
            ['Missing Only', 'Recreate All', 'Close']
        );

        const scopeDialog = environment.createDialog();
        config.buttons[0].action(scopeDialog);
        assert.equal(scopeDialog.closed, true);
        assert.deepEqual(calls[0], [12, button, parentDialog, 'missing']);
    });

    test(`${label}: complete albums offer recreate-all without missing-only`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailScope(13, 3, false, true, button, parentDialog);

        const config = environment.dialogs.at(-1);
        assert.match(config.message, /All thumbnails already exist/);
        assert.deepEqual(
            Array.from(config.buttons, (item) => item.label.trim()),
            ['Recreate All', 'Close']
        );
    });

    test(`${label}: make thumbnails forwards whether any thumbnails already exist`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                name: 'Album',
                description: '',
                date: '2026-09-23',
                code: '',
                imageCount: 3,
                needsThumbnails: true,
                hasAnyThumbnails: false
            }
        });
        context.editAlbum(15);

        const config = environment.dialogs.at(-1);
        const makeThumbnails = config.buttons.find((buttonConfig) =>
            buttonConfig.label && buttonConfig.label.trim() === 'Make Thumbnails'
        );
        const scopeCalls = [];
        context.chooseThumbnailScope = (...args) => scopeCalls.push(args);

        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                imageCount: 3,
                needsThumbnails: true,
                hasAnyThumbnails: false
            }
        });

        const button = environment.createButton('__make_thumbnails_button__');
        const parentDialog = environment.createDialog();
        makeThumbnails.action.call(button, parentDialog);

        assert.equal(scopeCalls.length, 1);
        assert.equal(scopeCalls[0][0], 15);
        assert.equal(scopeCalls[0][1], 3);
        assert.equal(scopeCalls[0][2], true);
        assert.equal(scopeCalls[0][3], false);
        assert.equal(scopeCalls[0][4], button);
        assert.equal(scopeCalls[0][5], parentDialog);
    });

    test(`${label}: saving album details posts edits and restores dialog controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                name: 'Original',
                description: 'Old description',
                date: '2026-09-23',
                code: 'OLD',
                imageCount: 2,
                needsThumbnails: false,
                hasAnyThumbnails: true
            }
        });
        environment.queuePost('/api/update-album.php', {type: 'success', data: ''});

        context.editAlbum(41);
        environment.element('#new-album-name').val('Updated');
        environment.element('#new-album-description').val('New description');
        environment.element('#new-album-date').val('2026-10-04');
        environment.element('#new-album-code').val('NEW');

        const config = environment.dialogs.at(-1);
        const save = config.buttons.find((item) => item.label && item.label.trim() === 'Save Details');
        const dialog = environment.createDialog();
        const button = environment.createButton('__save_album_button__');
        button.closestResult = environment.element('__save_album_modal__');

        save.action.call(button, dialog);

        assert.deepEqual(plain(environment.calls.post[0]), {
            url: '/api/update-album.php',
            data: {
                id: 41,
                name: 'Updated',
                description: 'New description',
                date: '2026-10-04',
                code: 'NEW'
            }
        });
        assert.equal(dialog.closed, true);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    });

    test(`${label}: album save failures show the server response and restore controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                name: 'Original',
                description: '',
                date: '2026-09-23',
                code: '',
                imageCount: 1,
                needsThumbnails: false,
                hasAnyThumbnails: true
            }
        });
        environment.queuePost('/api/update-album.php', {
            type: 'failure',
            xhr: {responseText: 'Album name is required'},
            error: 'Bad Request'
        });

        context.editAlbum(42);
        const config = environment.dialogs.at(-1);
        const save = config.buttons.find((item) => item.label && item.label.trim() === 'Save Details');
        const dialog = environment.createDialog();
        const button = environment.createButton('__save_album_failure_button__');
        const modal = environment.element('__save_album_failure_modal__');
        button.closestResult = modal;

        save.action.call(button, dialog);

        assert.match(
            modal.find('.bootstrap-dialog-body').appended.join(''),
            /Album name is required/
        );
        assert.equal(dialog.closed, false);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    });

    test(`${label}: deleting an album closes both dialogs and reloads the table`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                name: 'Delete Me',
                description: '',
                date: '2026-09-23',
                code: '',
                imageCount: 1,
                needsThumbnails: false,
                hasAnyThumbnails: true
            }
        });
        environment.queuePost('/api/delete-album.php', {type: 'success', data: ''});

        context.editAlbum(43);
        const editConfig = environment.dialogs.at(-1);
        const remove = editConfig.buttons.find((item) => item.label && item.label.trim() === 'Delete Album');
        const editDialog = environment.createDialog();
        remove.action.call(environment.createButton('__delete_album_parent_button__'), editDialog);

        const confirmConfig = environment.dialogs.at(-1);
        const confirmDialog = environment.createDialog();
        const confirmButton = environment.createButton('__delete_album_confirm_button__');
        confirmButton.closestResult = environment.element('__delete_album_confirm_modal__');
        confirmConfig.buttons[0].action.call(confirmButton, confirmDialog);

        assert.deepEqual(plain(environment.calls.post[0]), {
            url: '/api/delete-album.php',
            data: {id: 43}
        });
        assert.equal(confirmDialog.closed, true);
        assert.equal(editDialog.closed, true);
        assert.ok(environment.calls.reload.length >= 1);
    });

    test(`${label}: album delete failures remain visible and restore confirmation controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                name: 'Protected Album',
                description: '',
                date: '2026-09-23',
                code: '',
                imageCount: 1,
                needsThumbnails: false,
                hasAnyThumbnails: true
            }
        });
        environment.queuePost('/api/delete-album.php', {
            type: 'failure',
            xhr: {responseText: 'Album cannot be deleted'},
            error: 'Bad Request'
        });

        context.editAlbum(44);
        const editConfig = environment.dialogs.at(-1);
        const remove = editConfig.buttons.find((item) => item.label && item.label.trim() === 'Delete Album');
        const editDialog = environment.createDialog();
        remove.action.call(environment.createButton('__delete_album_parent_failure_button__'), editDialog);

        const confirmConfig = environment.dialogs.at(-1);
        const confirmDialog = environment.createDialog();
        const confirmButton = environment.createButton('__delete_album_confirm_failure_button__');
        const modal = environment.element('__delete_album_confirm_failure_modal__');
        confirmButton.closestResult = modal;
        confirmConfig.buttons[0].action.call(confirmButton, confirmDialog);

        assert.match(
            modal.find('.bootstrap-dialog-body').appended.join(''),
            /Album cannot be deleted/
        );
        assert.equal(confirmDialog.closed, false);
        assert.equal(editDialog.closed, false);
        assert.equal(confirmButton.stopSpinCount, 1);
        assert.equal(confirmDialog.buttonsEnabled, true);
        assert.equal(confirmDialog.closable, true);
    });

    test(`${label}: thumbnail refresh failures show progress errors and restore controls`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queueGet('/api/get-album.php', {
            type: 'success',
            data: {
                name: 'Album',
                description: '',
                date: '2026-09-23',
                code: '',
                imageCount: 3,
                needsThumbnails: true,
                hasAnyThumbnails: true
            }
        });
        context.editAlbum(45);

        environment.queueGet('/api/get-album.php', {
            type: 'failure',
            xhr: {responseText: 'Unable to load album'}
        });

        const config = environment.dialogs.at(-1);
        const makeThumbnails = config.buttons.find((item) =>
            item.label && item.label.trim() === 'Make Thumbnails'
        );
        const dialog = environment.createDialog();
        const button = environment.createButton('__thumbnail_refresh_failure_button__');

        makeThumbnails.action.call(button, dialog);

        const progress = environment.element('#resize-progress .progress-bar');
        assert.equal(progress.html(), 'Error: Unable to load album');
        assert.equal(progress.hasClass('progress-bar-danger'), true);
        assert.equal(environment.element('#resize-progress').visible, true);
        assert.equal(button.stopSpinCount, 1);
        assert.equal(dialog.buttonsEnabled, true);
        assert.equal(dialog.closable, true);
    });

    test(`${label}: thumbnail markup choices pass the selected treatment and mode`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        const calls = [];
        context.makeThumbs = (...args) => calls.push(args);
        const button = environment.createButton('__thumb_button__');
        const parentDialog = environment.createDialog();

        context.chooseThumbnailMarkup(14, button, parentDialog, 'all');

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
            [14, button, parentDialog, 'proof', 'all'],
            [14, button, parentDialog, 'watermark', 'all'],
            [14, button, parentDialog, 'none', 'all']
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
        environment.queueGet('/api/get-thumbnail-status.php', {
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
        environment.queueGet('/api/get-thumbnail-status.php', {
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

    test(`${label}: makeThumbs uses the fallback message when the request fails without a response body`, () => {
        const {context, environment} = createAlbumContext(scriptPath);
        environment.queuePost('/api/make-thumbs.php', {
            type: 'failure',
            xhr: {
                responseText: ''
            }
        });

        const button = environment.createButton('__thumb_button__');
        const dialog = environment.createDialog();
        context.makeThumbs(16, button, dialog, 'none', 'all');

        const progress = environment.element('#resize-progress .progress-bar');
        assert.equal(progress.html(), 'Error: Unable to start thumbnail generation');
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
    registerCreateAlbumTests,
    registerSharedAlbumManagementTests
};
