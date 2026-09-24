class MockElement {
    constructor(environment, selector, length = 1) {
        this.environment = environment;
        this.selector = selector;
        this.length = length;
        this.attributes = new Map();
        this.properties = new Map();
        this.styles = {};
        this.classes = new Set();
        this.handlers = new Map();
        this.appended = [];
        this.value = '';
        this.htmlValue = '';
        this.textValue = '';
        this.visible = true;
        this.removed = false;
        this.spinCount = 0;
        this.stopSpinCount = 0;
        this.closestResult = null;
        this.parentResult = null;
        this.uploadOptions = null;
        this.widthValue = 300;
        this.heightValue = 100;
        this.offsetValue = {top: 0, left: 0};
        this.afterValues = [];
        this.draggableOptions = null;
        this.summernoteCode = '';
        this.modalCalls = [];
        this.rect = {top: 0, bottom: 100, width: 300, height: 100};
        this.nativeElement = {
            complete: false,
            naturalWidth: 0,
            getBoundingClientRect: () => this.rect
        };
    }

    get(index) {
        return index === 0 ? this.nativeElement : undefined;
    }

    each(callback) {
        if (this.length > 0) {
            callback.call(this, 0, this);
        }
        return this;
    }

    DataTable(config) {
        if (config) {
            this.environment.dataTable.config = config;
        }
        return this.environment.dataTable.instance;
    }

    attr(name, value) {
        if (typeof name === 'object') {
            for (const [key, item] of Object.entries(name)) {
                this.attributes.set(key, String(item));
            }
            return this;
        }
        if (value === undefined) {
            return this.attributes.get(name);
        }
        this.attributes.set(name, String(value));
        return this;
    }

    prop(name, value) {
        if (value === undefined) {
            return this.properties.get(name);
        }
        this.properties.set(name, value);
        return this;
    }

    val(value) {
        if (value === undefined) {
            return this.value;
        }
        this.value = value;
        return this;
    }

    html(value) {
        if (value === undefined) {
            return this.htmlValue;
        }
        this.htmlValue = value;
        return this;
    }

    text(value) {
        if (value === undefined) {
            return this.textValue;
        }
        this.textValue = value;
        return this;
    }

    append(value) {
        this.appended.push(value);
        return this;
    }

    addClass(classNames) {
        for (const className of String(classNames).split(/\s+/).filter(Boolean)) {
            this.classes.add(className);
        }
        return this;
    }

    removeClass(classNames) {
        for (const className of String(classNames).split(/\s+/).filter(Boolean)) {
            this.classes.delete(className);
        }
        return this;
    }

    toggleClass(className, force) {
        if (force === undefined) {
            if (this.classes.has(className)) {
                this.classes.delete(className);
            } else {
                this.classes.add(className);
            }
        } else if (force) {
            this.classes.add(className);
        } else {
            this.classes.delete(className);
        }
        return this;
    }

    hasClass(className) {
        return this.classes.has(className);
    }

    css(nameOrValues, value) {
        if (typeof nameOrValues === 'string') {
            if (value === undefined) {
                return this.styles[nameOrValues];
            }
            this.styles[nameOrValues] = value;
        } else {
            Object.assign(this.styles, nameOrValues);
        }
        return this;
    }

    width(value) {
        if (value === undefined) {
            return this.widthValue;
        }
        this.widthValue = value;
        return this;
    }

    height(value) {
        if (value === undefined) {
            return this.heightValue;
        }
        this.heightValue = value;
        return this;
    }

    offset() {
        return this.offsetValue;
    }

    after(value) {
        this.afterValues.push(value);
        return this;
    }

    empty() {
        this.appended = [];
        this.htmlValue = '';
        this.textValue = '';
        return this;
    }

    draggable(options) {
        this.draggableOptions = options || {};
        return this;
    }

    summernote(action) {
        if (action === 'code') {
            return this.summernoteCode;
        }
        return this;
    }

    modal(action) {
        this.modalCalls.push(action);
        return this;
    }

    show() {
        this.visible = true;
        return this;
    }

    hide() {
        this.visible = false;
        return this;
    }

    remove() {
        this.removed = true;
        return this;
    }

    removeAttr(name) {
        this.attributes.delete(name);
        return this;
    }

    off() {
        this.handlers.clear();
        return this;
    }

    on(event, handler) {
        this.handlers.set(event, handler);
        return this;
    }

    one(event, handler) {
        this.handlers.set(event, handler);
        return this;
    }

    click(dataOrHandler, handler) {
        if (typeof handler === 'function') {
            this.handlers.set('click', (eventData = {}) => handler({
                ...eventData,
                data: dataOrHandler,
                currentTarget: eventData.currentTarget || this
            }));
            return this;
        }
        if (typeof dataOrHandler === 'function') {
            this.handlers.set('click', dataOrHandler);
            return this;
        }
        return this.trigger('click');
    }

    dblclick(handler) {
        this.handlers.set('dblclick', handler);
        return this;
    }

    keyup(handler) {
        this.handlers.set('keyup', handler);
        return this;
    }

    keypress(handler) {
        this.handlers.set('keypress', handler);
        return this;
    }

    change(handler) {
        this.handlers.set('change', handler);
        return this;
    }

    ready(handler) {
        if (this.environment.autoReady) {
            handler();
        } else {
            this.environment.readyCallbacks.push(handler);
        }
        return this;
    }

    trigger(event, eventData = {}) {
        const handler = this.handlers.get(event);
        if (handler) {
            handler.call(this, eventData);
        }
        return this;
    }

    closest() {
        return this.closestResult || this;
    }

    parent() {
        return this.parentResult || this;
    }

    find(selector) {
        return this.environment.element(`${this.selector} ${selector}`);
    }

    prependTo() {
        return this;
    }

    tooltip() {
        return this;
    }

    uploadFile(options) {
        this.uploadOptions = options;
        this.environment.uploadOptions = options;
        return this;
    }

    spin() {
        this.spinCount += 1;
        return this;
    }

    stopSpin() {
        this.stopSpinCount += 1;
        return this;
    }

    is(query) {
        if (query === ':checked') {
            return Boolean(this.properties.get('checked'));
        }
        return false;
    }
}

function createDeferred(response) {
    const result = response || {type: 'success', data: ''};
    return {
        done(callback) {
            if (result.type === 'success') {
                callback(result.data);
            }
            return this;
        },
        fail(callback) {
            if (result.type === 'failure') {
                callback(
                    result.xhr || {responseText: ''},
                    result.status || 'error',
                    result.error || 'Error'
                );
            }
            return this;
        },
        always(callback) {
            callback();
            return this;
        }
    };
}

function createJQueryEnvironment(options = {}) {
    const elements = new Map();
    const calls = {
        get: [],
        post: [],
        reload: [],
        rowAdd: [],
        columnSearch: []
    };
    const ajaxResponses = {
        get: new Map(),
        post: new Map()
    };
    const dialogs = [];
    const timeouts = [];
    const intervals = [];
    const readyCallbacks = [];
    const documentObject = {};

    function lengthFor(selector) {
        if (Object.prototype.hasOwnProperty.call(options.lengths || {}, selector)) {
            return options.lengths[selector];
        }
        return 1;
    }

    function element(selector) {
        if (!elements.has(selector)) {
            elements.set(selector, new MockElement(environment, selector, lengthFor(selector)));
        }
        return elements.get(selector);
    }

    const dataTableInstance = {
        ajax: {
            reload(...args) {
                calls.reload.push(args);
            }
        },
        column(index) {
            return {
                search(value) {
                    calls.columnSearch.push({index, value});
                    return {
                        draw() {
                            return this;
                        }
                    };
                }
            };
        },
        row: {
            add(data) {
                calls.rowAdd.push(data);
                return {
                    draw() {
                        return this;
                    }
                };
            }
        }
    };

    function queueAjax(method, url, response) {
        const queue = ajaxResponses[method].get(url) || [];
        queue.push(response);
        ajaxResponses[method].set(url, queue);
    }

    function consumeAjax(method, url) {
        const queue = ajaxResponses[method].get(url) || [];
        return queue.length ? queue.shift() : {type: 'success', data: ''};
    }

    function ajax(method, url, data, success) {
        const response = consumeAjax(method, url);
        calls[method].push({url, data});

        if (response.type === 'success' && typeof success === 'function') {
            success(response.data);
        }

        return createDeferred(response);
    }

    function $(selector) {
        if (typeof selector === 'function') {
            selector();
            return element('__ready__');
        }
        if (selector === documentObject) {
            return element('__document__');
        }
        if (selector instanceof MockElement) {
            return selector;
        }
        if (typeof selector === 'string' && selector.startsWith('<')) {
            return new MockElement(environment, `__created_${elements.size}__`);
        }
        return element(String(selector));
    }

    $.get = function (url, data, success) {
        if (typeof data === 'function') {
            success = data;
            data = undefined;
        }
        return ajax('get', url, data, success);
    };

    $.post = function (url, data) {
        return ajax('post', url, data);
    };

    $.fn = MockElement.prototype;

    $.isNumeric = function (value) {
        return value !== null && value !== '' && !Number.isNaN(Number(value));
    };

    $.isArray = Array.isArray;
    $.isFunction = function (value) {
        return typeof value === 'function';
    };

    $.each = function (collection, callback) {
        if (Array.isArray(collection)) {
            collection.forEach((value, index) => callback(index, value));
            return;
        }
        for (const [key, value] of Object.entries(collection)) {
            callback(key, value);
        }
    };

    const BootstrapDialog = {
        SIZE_WIDE: 'wide',
        show(config) {
            dialogs.push(config);
            return createDialog();
        }
    };

    function createDialog() {
        return {
            buttonsEnabled: true,
            closable: true,
            closed: false,
            $modalFooter: element(`__dialog_footer_${dialogs.length}__`),
            enableButtons(value) {
                this.buttonsEnabled = value;
            },
            setClosable(value) {
                this.closable = value;
            },
            close() {
                this.closed = true;
            }
        };
    }

    function setTimeoutMock(callback, delay) {
        timeouts.push({callback, delay});
        return timeouts.length;
    }

    function setIntervalMock(callback, delay) {
        const interval = {callback, delay, cleared: false};
        intervals.push(interval);
        return intervals.length;
    }

    function clearIntervalMock(id) {
        if (intervals[id - 1]) {
            intervals[id - 1].cleared = true;
        }
    }

    const environment = {
        $,
        BootstrapDialog,
        document: documentObject,
        autoReady: options.autoReady !== false,
        readyCallbacks,
        element,
        elements,
        calls,
        dialogs,
        dataTable: {
            config: null,
            instance: dataTableInstance
        },
        uploadOptions: null,
        queueGet(url, response) {
            queueAjax('get', url, response);
        },
        queuePost(url, response) {
            queueAjax('post', url, response);
        },
        createDialog,
        createButton(selector = '__button__') {
            return element(selector);
        },
        setTimeout: setTimeoutMock,
        setInterval: setIntervalMock,
        clearInterval: clearIntervalMock,
        runReady() {
            for (const callback of readyCallbacks.splice(0)) {
                callback();
            }
        },
        runTimeouts() {
            for (const timeout of timeouts.splice(0)) {
                timeout.callback();
            }
        },
        runIntervalsOnce() {
            for (const interval of intervals) {
                if (!interval.cleared) {
                    interval.callback();
                }
            }
        }
    };

    return environment;
}

module.exports = {
    MockElement,
    createJQueryEnvironment
};
