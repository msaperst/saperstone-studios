const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createGalleryAdminContext() {
    const environment = createJQueryEnvironment({autoReady: false});
    const gallery = {
        totalImages: 4,
        loadImagesCalls: [],
        loadImages(howMany) {
            this.loadImagesCalls.push(howMany);
            return this.totalImages;
        }
    };
    const context = loadBrowserScript('public/js/gallery-admin.js', {
        $: environment.$,
        document: environment.document,
        window: {location: {search: '?w=999'}},
        BootstrapDialog: environment.BootstrapDialog,
        gallery,
        setTimeout: environment.setTimeout
    });

    return {context, environment, gallery};
}

test('updateFilename preserves the image path and extension while sanitizing the title', () => {
    const {context, environment} = createGalleryAdminContext();
    environment.element('#gallery-filename').val('/portrait/img/original.name.jpg');
    environment.element('#gallery-title').val('Updated Title!');

    context.updateFilename();

    assert.equal(
        environment.element('#gallery-filename').val(),
        '/portrait/img/Updated Title.jpg'
    );
});

test('sortGallery switches controls and enables sortable image ordering', () => {
    const {context, environment} = createGalleryAdminContext();
    const sortParent = environment.element('__sort-parent__');
    const saveParent = environment.element('__save-parent__');
    environment.element('#sort-gallery-btn').parentResult = sortParent;
    environment.element('#save-gallery-btn').parentResult = saveParent;

    context.sortGallery();

    assert.equal(sortParent.visible, false);
    assert.equal(saveParent.visible, true);
    assert.deepEqual(environment.element('.image-grid').sortableOptions, {
        items: 'div.gallery'
    });
});

test('gallery dialog button helpers disable and restore upload controls', () => {
    const {context, environment} = createGalleryAdminContext();
    const dialog = environment.createDialog();
    const uploadButton = dialog.$modalFooter.find('#add-images-button');

    context.disableDialogButtons(dialog);

    assert.equal(uploadButton.hasClass('disabled'), true);
    assert.equal(uploadButton.prop('disabled'), true);
    assert.equal(uploadButton.css('cursor'), 'not-allowed');
    assert.equal(uploadButton.css('pointer-events'), 'none');
    assert.equal(dialog.buttonsEnabled, false);
    assert.equal(dialog.closable, false);

    context.enableDialogButtons(dialog);

    assert.equal(uploadButton.hasClass('disabled'), false);
    assert.equal(uploadButton.prop('disabled'), false);
    assert.equal(uploadButton.css('cursor'), 'pointer');
    assert.equal(uploadButton.css('pointer-events'), 'inherit');
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});

test('editImage saves metadata, updates the active image, and advances the carousel', () => {
    const {context, environment} = createGalleryAdminContext();
    const image = environment.element('.carousel div.active div:first-child');
    image
        .attr('gallery-id', '9')
        .attr('image-id', '12')
        .attr('alt', 'Original')
        .attr('data-background-image', '/portrait/img/original.jpg');

    environment.element('#gallery-title').val('Updated');
    environment.element('#gallery-caption').val('New caption');
    environment.element('#gallery-filename').val('/portrait/img/updated.jpg');
    environment.queuePost('/api/update-gallery-image.php', {type: 'success', data: ''});

    context.editImage();

    const dialogConfig = environment.dialogs[0];
    const dialog = environment.createDialog();
    const button = environment.createButton('__edit-image-button__');
    dialogConfig.buttons[0].action.call(button, dialog);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-gallery-image.php',
        data: {
            gallery: '9',
            image: '12',
            title: 'Updated',
            caption: 'New caption',
            filename: '/portrait/img/updated.jpg'
        }
    });
    assert.equal(image.attr('alt'), 'Updated');
    assert.equal(image.attr('data-background-image'), '/portrait/img/updated.jpg');
    assert.equal(image.css('background-image'), "url('/portrait/img/updated.jpg')");
    assert.equal(
        environment.element('.carousel div.active div:first-child __next__ __children__').html(),
        'New caption'
    );
    assert.equal(dialog.closed, true);
    assert.deepEqual(environment.element('.carousel').carouselCalls, ['pause', 'next']);
    assert.equal(button.spinCount, 1);
    assert.equal(button.stopSpinCount, 1);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});

test('editImage displays API failures and restores dialog controls', () => {
    const {context, environment} = createGalleryAdminContext();
    const image = environment.element('.carousel div.active div:first-child');
    image.attr('gallery-id', '9').attr('image-id', '12');
    environment.element('#gallery-title').val('Updated');
    environment.element('#gallery-caption').val('Caption');
    environment.element('#gallery-filename').val('/portrait/img/updated.jpg');
    environment.queuePost('/api/update-gallery-image.php', {
        type: 'failure',
        xhr: {responseText: 'Unable to update image'},
        error: 'Server Error'
    });

    context.editImage();

    const dialogConfig = environment.dialogs[0];
    const dialog = environment.createDialog();
    const button = environment.createButton('__edit-image-error-button__');
    dialogConfig.buttons[0].action.call(button, dialog);

    const body = dialog.getModal().find('.modal-body');
    assert.equal(body.appended.length, 1);
    assert.match(body.appended[0], /Unable to update image/);
    assert.equal(dialog.closed, false);
    assert.equal(button.stopSpinCount, 1);
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
});

test('deleteImage removes the active image and thumbnail after a successful delete', () => {
    const {context, environment} = createGalleryAdminContext();
    const image = environment.element('.carousel div.active div');
    const activeParent = environment.element('__active-image-parent__');
    const thumbnailParent = environment.element('__thumbnail-parent__');
    image
        .attr('gallery-id', '9')
        .attr('image-id', '12')
        .attr('alt', 'Delete Me');
    image.parentResult = activeParent;
    environment.element('.gallery img[alt="Delete Me"]').parentResult = thumbnailParent;
    environment.queuePost('/api/delete-gallery-image.php', {type: 'success', data: ''});

    context.deleteImage();

    const dialogConfig = environment.dialogs[0];
    const dialog = environment.createDialog();
    const button = environment.createButton('__delete-image-button__');
    dialogConfig.buttons[0].action.call(button, dialog);

    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/delete-gallery-image.php',
        data: {gallery: '9', image: '12'}
    });
    assert.equal(dialog.closed, true);
    assert.equal(activeParent.removed, true);
    assert.equal(thumbnailParent.removed, true);
    assert.deepEqual(environment.element('.carousel').carouselCalls, ['pause', 'next']);
    assert.equal(button.stopSpinCount, 1);
});

test('saveGallery posts image order and restores the sorting controls', () => {
    const {context, environment} = createGalleryAdminContext();
    const item = environment.element('div.gallery');
    const column = environment.element('__gallery-column__');
    item.attr('image-id', '17').attr('sequence', '3');
    item.parentResult = column;
    item.offsetValue = {top: 42, left: 0};
    column.attr('id', 'left');

    const sortParent = environment.element('__sort-save-parent__');
    const saveParent = environment.element('__save-save-parent__');
    environment.element('#sort-gallery-btn').parentResult = sortParent;
    environment.element('#save-gallery-btn').parentResult = saveParent;
    sortParent.hide();
    saveParent.show();

    environment.queuePost('/api/update-gallery-order.php', {type: 'success', data: ''});

    context.saveGallery(999);

    assert.equal(environment.calls.blockUI.length, 1);
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/update-gallery-order.php',
        data: {
            id: 999,
            imgs: [{
                id: '17',
                sequence: '3',
                col: 'left',
                height: 42
            }]
        }
    });
    const sequenceSelector = Array.from(environment.elements.keys())
        .find((selector) => selector.startsWith('div.gallery[sequence='));
    assert.equal(sequenceSelector, "div.gallery[sequence='3']");
    assert.equal(environment.element(sequenceSelector).attr('new-sequence'), '0');
    assert.equal(environment.element('.image-grid').sortableOptions, 'destroy');
    assert.equal(sortParent.visible, true);
    assert.equal(saveParent.visible, false);
    assert.equal(environment.calls.unblockUI, 1);
});

test('saveGallery surfaces authorization failures and always unblocks the page', () => {
    const {context, environment} = createGalleryAdminContext();
    const item = environment.element('div.gallery');
    const column = environment.element('__gallery-error-column__');
    item.attr('image-id', '17').attr('sequence', '3');
    item.parentResult = column;
    item.offsetValue = {top: 42, left: 0};
    column.attr('id', 'left');
    environment.queuePost('/api/update-gallery-order.php', {
        type: 'failure',
        xhr: {responseText: ''},
        error: 'Unauthorized'
    });

    context.saveGallery(999);

    assert.equal(environment.element('.breadcrumb').afterValues.length, 1);
    assert.match(
        environment.element('.breadcrumb').afterValues[0],
        /session has timed out/
    );
    assert.equal(environment.calls.unblockUI, 1);
});

test('gallery upload lifecycle disables controls, reports upload errors, and restores the dialog', () => {
    const {context, environment} = createGalleryAdminContext();
    environment.queueGet('/api/get-gallery.php', {
        type: 'success',
        data: {title: 'Gallery 999'}
    });

    context.editGallery(999);

    const dialogConfig = environment.dialogs[0];
    const dialog = environment.createDialog();
    dialogConfig.onshown(dialog);

    const uploadButton = dialog.$modalFooter.find('#add-images-button');
    environment.uploadOptions.onSubmit();

    assert.equal(dialog.buttonsEnabled, false);
    assert.equal(dialog.closable, false);
    assert.equal(uploadButton.prop('disabled'), true);
    assert.equal(environment.element('.ajax-file-upload-container').visible, true);

    const statusbar = environment.element('__upload-statusbar__');
    const progressDiv = environment.element('__upload-progress__');
    const filename = environment.element('__upload-filename__');
    environment.uploadOptions.onSuccess(
        ['bad.jpg'],
        JSON.stringify('Image rejected'),
        {},
        {statusbar, progressDiv, filename}
    );

    assert.equal(statusbar.hasClass('alert'), true);
    assert.equal(statusbar.hasClass('alert-danger'), true);
    assert.equal(progressDiv.visible, false);
    assert.deepEqual(filename.afterValues, ['Image rejected']);

    environment.uploadOptions.afterUploadAll();
    assert.equal(dialog.buttonsEnabled, true);
    assert.equal(dialog.closable, true);
    assert.equal(uploadButton.prop('disabled'), false);

    environment.runTimeouts();
    assert.equal(environment.element('.ajax-file-upload-container').visible, false);
});
