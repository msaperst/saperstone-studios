const assert = require('node:assert/strict');
const {test} = require('node:test');
const {MockElement, createJQueryEnvironment} = require('./helpers/jquery-mock');
const {loadBrowserScript} = require('./helpers/load-browser-script');

function createUploadPluginContext() {
    const environment = createJQueryEnvironment({autoReady: false});
    const baseJQuery = environment.$;
    const created = {
        inputs: []
    };

    function jquery(selector) {
        const element = baseJQuery(selector);
        if (typeof selector === 'string' && selector.startsWith('<') && element.tagName === 'input') {
            created.inputs.push(element);
        }
        return element;
    }

    Object.assign(jquery, baseJQuery);
    jquery.fn = baseJQuery.fn;
    jquery.extend = (...args) => Object.assign(...args);
    jquery.type = (value) => {
        if (value === null) {
            return 'null';
        }
        if (Array.isArray(value)) {
            return 'array';
        }
        return typeof value;
    };

    const originalInsertAfter = MockElement.prototype.insertAfter;
    const originalUnbind = MockElement.prototype.unbind;
    const originalAjaxForm = MockElement.prototype.ajaxForm;
    const originalSubmit = MockElement.prototype.submit;

    environment.ajaxForms = [];

    MockElement.prototype.insertAfter = function (target) {
        this.insertedAfter = target;
        return this;
    };
    MockElement.prototype.unbind = function (event) {
        this.handlers.delete(event);
        return this;
    };
    MockElement.prototype.ajaxForm = function (options) {
        this.ajaxFormOptions = options;
        environment.ajaxForms.push(this);
        return this;
    };
    MockElement.prototype.submit = function () {
        this.submitCount = (this.submitCount || 0) + 1;
        return this;
    };

    loadBrowserScript('public/js/jquery.uploadfile.js', {
        $: jquery,
        jQuery: jquery,
        document: environment.document,
        window: {
            FormData: class FormData {},
            setTimeout: environment.setTimeout
        }
    });

    function restore() {
        if (originalInsertAfter === undefined) {
            delete MockElement.prototype.insertAfter;
        } else {
            MockElement.prototype.insertAfter = originalInsertAfter;
        }
        if (originalUnbind === undefined) {
            delete MockElement.prototype.unbind;
        } else {
            MockElement.prototype.unbind = originalUnbind;
        }
        if (originalAjaxForm === undefined) {
            delete MockElement.prototype.ajaxForm;
        } else {
            MockElement.prototype.ajaxForm = originalAjaxForm;
        }
        if (originalSubmit === undefined) {
            delete MockElement.prototype.submit;
        } else {
            MockElement.prototype.submit = originalSubmit;
        }
    }

    return {created, environment, restore};
}

test('upload plugin fork honors separate button and upload containers', (t) => {
    const {environment, restore} = createUploadPluginContext();
    t.after(restore);

    const target = environment.element('#upload-target');
    const buttonLocation = environment.element('#button-location');
    const uploadContainer = environment.element('#upload-container');

    target.uploadFile({
        uploadButtonLocation: buttonLocation,
        uploadContainer,
        dragDrop: true,
        autoSubmit: false
    });

    assert.equal(buttonLocation.prepended.length, 1);
    const uploadButton = buttonLocation.prepended[0];
    assert.equal(uploadButton.attr('id'), 'add-images-button');
    assert.equal(uploadButton.hasClass('ajax-file-upload'), true);

    assert.equal(uploadContainer.appended.length, 1);
    const dragDrop = uploadContainer.appended[0];
    assert.equal(dragDrop.hasClass('ajax-upload-dragdrop'), true);
    assert.equal(dragDrop.hasClass('upload-dragdrop-top'), true);
});

test('upload plugin fork passes the failed upload xhr to first-party error handlers', (t) => {
    const {created, environment, restore} = createUploadPluginContext();
    t.after(restore);

    const target = environment.element('#upload-target');
    let errorArguments = null;

    target.uploadFile({
        dragDrop: false,
        onError(...args) {
            errorArguments = args;
        }
    });

    const fileInput = created.inputs.find((input) => input.handlers.has('change'));
    assert.ok(fileInput, 'upload plugin did not create its interactive file input');

    fileInput.val('photo.jpg');
    fileInput.trigger('change');

    assert.equal(environment.ajaxForms.length, 1);
    const uploadForm = environment.ajaxForms[0];
    const xhr = {
        statusText: 'error',
        responseText: 'Image does not meet minimum width'
    };

    uploadForm.ajaxFormOptions.error(xhr, 'error', 'Bad Request');

    assert.ok(errorArguments);
    assert.equal(errorArguments[0][0], 'photo.jpg');
    assert.equal(errorArguments[1], 'error');
    assert.equal(errorArguments[2], 'Bad Request');
    assert.equal(errorArguments[4], xhr);
});
