var maxHeight = 550;

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

    var imgWidth = img.attr('imgWidth');
    var imgHeight = img.attr('imgHeight');
    var heightP = imgHeight / imgWidth * 100;

    this.ele.find('#heighter').css({
        'margin-top': heightP + '%'
    });

    var width = this.ele.parent().width();
    var height = width * imgHeight / imgWidth;
    // if our height is too big to fit on the page
    if (height > maxHeight) {
        width = maxHeight * imgWidth / imgHeight;
    }
    this.ele.width(width);
    this.slider.width(width);

    var orig = img.attr('imgOrig');
    var edit = img.attr('imgEdit');
    this.ele.find('#original img').attr({
        'src': orig
    }).width(width);
    this.ele.find('#edit img').attr({
        'src': edit
    }).width(width);
    this.slider.val(0);
    this.ele.parent().find('.comment').html(img.attr('text'));

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
        cellImg.attr({
            'hash': i,
            'imgOrig': image.orig,
            'imgEdit': image.edit,
            'imgWidth': image.width,
            'imgHeight': image.height,
            'text': image.text,
            'src': image.thumb,
            'alt': image.edit
        }).click(function () {
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