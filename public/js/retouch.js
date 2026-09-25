var maxHeight = 550;

function sanitizeImageUrl(url) {
    if (typeof url !== 'string') {
        return '';
    }

    var trimmed = url.trim();
    if (!trimmed) {
        return '';
    }

    if (/^https?:\/\//i.test(trimmed) || /^\/\//.test(trimmed) || /^\/(?!\/)/.test(trimmed) || /^(?:\.\.?\/)?[A-Za-z0-9._~!$&'()*+,;=@%/?#-]+$/.test(trimmed)) {
        return trimmed;
    }

    return '';
}

function Retouch(ele, images, instruct) {
    var Retouch = this;

    Retouch.ele = ele;
    Retouch.images = images;
    Retouch.instruct = instruct;

    Retouch.slider = Retouch.createSlider();
    Retouch.selector = Retouch.addSelector();

    setInterval(function () {
        Retouch.slide();
    }, 50);

    if (window.location.hash) {
        var img = window.location.hash.substr(1);
        Retouch.setSelect(Retouch.selector.find('img[hash=' + img + ']'));
    }
}

Retouch.prototype.createSlider = function () {
    var protector = $('<img>');
    protector.addClass('protect');
    protector.attr({
        'src': '/img/image.png',
        'alt': 'placeholder'
    });
    this.ele.append(protector);

    if (this.instruct) {
        var instructions = $('<div>');
        instructions.attr({
            'id': 'instructions'
        });
        instructions.html('Select an Image then drag slider');
        this.ele.before(instructions)
    }

    var heighter = $('<div>');
    heighter.attr({
        'id': 'heighter'
    });
    heighter.css({
        'margin-top': '0%'
    });
    this.ele.append(heighter);

    var original = $('<div>');
    original.addClass('images original');
    original.css({
        'z-index': '1'
    });
    original.attr({
        'id': 'original'
    });
    var origImg = $('<img>');
    original.append(origImg);
    this.ele.append(original);

    var edit = $('<div>');
    edit.addClass('images edit');
    edit.css({
        'z-index': '1'
    });
    edit.attr({
        'id': 'edit'
    });
    var editImg = $('<img>');
    edit.append(editImg);
    this.ele.append(edit);

    var container = $('<div>');
    var slider = $('<input>');
    slider.addClass('slider');
    slider.attr({
        'type': 'range',
        'name': 'slider',
        'value': '0',
        'min': '0',
        'max': '100'
    });
    container.append(slider);

    var comment = $('<p>');
    comment.addClass('comment');
    container.append(comment);

    this.ele.after(container);

    return slider;
}

Retouch.prototype.setSelect = function (img) {
    var selectedIndex = Number.parseInt(img.attr('hash'), 10);
    var image = this.images[selectedIndex];
    if (!image) {
        return;
    }

    var imgWidth = image.width;
    var imgHeight = image.height;
    var heightP = imgHeight / imgWidth * 100;

    this.ele.find('#heighter').css({
        'margin-top': heightP + '%'
    });

    var width = this.ele.parent().width();
    var height = width * imgHeight / imgWidth;
    if (height > maxHeight) {
        width = maxHeight * imgWidth / imgHeight;
    }
    this.ele.width(width);
    this.slider.width(width);

    var originalImage = this.ele.find('#original img');
    originalImage[0].src = sanitizeImageUrl(image.orig);
    originalImage.width(width);
    var editedImage = this.ele.find('#edit img');
    editedImage[0].src = sanitizeImageUrl(image.edit);
    editedImage.width(width);
    this.slider.val(0);

    var comment = this.ele.parent().find('.comment');
    comment.empty();
    comment.append(document.createTextNode(image.text || ''));

    this.selector.find('img.thumb').css({
        'border': '2px transparent solid'
    });
    img.css({
        'border': '2px #9dcb3b solid'
    });
    this.slide();
}

Retouch.prototype.addSelector = function () {
    var Retouch = this;

    var selector = $('<div>');
    selector.addClass('col-md-12');
    selector.css({
        'overflow': 'auto'
    });

    var row = $('<div>');
    row.addClass('row-fluid text-center');
    row.css({
        'white-space': 'nowrap'
    });

    for (var i = 0; i < Retouch.images.length; i++) {
        var image = Retouch.images[i];

        var cell = $('<div>');
        cell.addClass('col-lg-1');
        cell.css({
            'display': 'inline-block',
            'float': 'none'
        });

        var cellImg = $('<img>');
        cellImg.addClass('thumb');
        cellImg.attr('hash', i);
        cellImg[0].src = sanitizeImageUrl(image.thumb);
        cellImg.attr('alt', 'Retouched image ' + (i + 1));
        cellImg.click(function () {
            window.location.hash = $(this).attr('hash');
            Retouch.setSelect($(this));
        });

        cell.append(cellImg);
        row.append(cell);
    }

    selector.append(row);
    Retouch.ele.closest('[class^=col]').after(selector);

    return selector;
}

Retouch.prototype.slide = function () {
    this.ele.find('#edit').width(this.slider.val() + "%");
}