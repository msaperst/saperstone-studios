var instruction_height = $('span#instructions').outerHeight();

function createSlider(ele, instruct) {
    var protector = $('<img>');
    protector.addClass('protect');
    protector.attr({
        'src' : '/img/image.png',
        'alt' : 'placeholder'
    });
    ele.append(protector);

    if (instruct) {
        var instructions = $('<span>');
        instructions.attr({
            'id' : 'instructions'
        });
        instructions.html('Select an Image then drag slider');
        ele.append(instructions)
    }

    var original = $('<div>');
    original.addClass('images original');
    original.css({
        'z-index' : '1'
    });
    original.attr({
        'id' : 'original'
    });
    var origImg = $('<img>');
    original.append(origImg);
    ele.append(original);

    var edit = $('<div>');
    edit.addClass('images edit');
    edit.css({
        'z-index' : '1'
    });
    edit.attr({
        'id' : 'edit'
    });
    var editImg = $('<img>');
    edit.append(editImg);
    ele.append(edit);

    var slider = $('<input>');
    slider.addClass('slider');
    slider.attr({
        'type' : 'range',
        'name' : 'slider',
        'value' : '0',
        'min' : '0',
        'max' : '100'
    });
    ele.after(slider);
}

function sliderSim(ele, before, after) {
    createSlider(ele, false);

    var width = after.width;
    ele.width(width);
    ele.next().width(width);
    var height = after.height;
    height = height / width * ele.outerWidth();
    ele.height(height + $('span#instructions').outerHeight());

    resizeSlider(ele);
    $(window).bind("resize orientationchange", function() {
        resizeSlider(ele);
    });
    setInterval(function() {
        slide(ele);
    }, 50);
}
function slider(ele, images) {
    createSlider(ele, true);

    addSelector(ele, images);

    resizeSlider(ele);
    $(window).bind("resize orientationchange", function() {
        resizeSlider(ele);
    });
    setInterval(function() {
        slide(ele);
    }, 50);

    $('img.thumb').click(function() {
        var orig = $(this).attr('imgOrig');
        var edit = $(this).attr('imgEdit');
        var width = $(this).attr('imgWidth');
        var height = $(this).attr('imgHeight');
        ele.find('#original img').attr('src', orig);
        ele.find('#edit img').attr('src', edit);
        ele.width(width);
        ele.next().width(width);
        ele.next().val(0);
        $('img.thumb').css({
            'border' : '2px transparent solid'
        });
        $(this).css({
            'border' : '2px #9dcb3b solid'
        });
        slide(ele);
        resizeSlider(ele);
        var max_height = height / width * ele.outerWidth();
        instruction_height = $('span#instructions').outerHeight();
        ele.animate({
            height : max_height + instruction_height + 5
        });
        ele.css({
            'height' : height + instruction_height + 5,
            'max-height' : max_height + instruction_height + 5
        });
    });
}

function addSelector(ele, images) {

    var selector = $('<div>');
    selector.addClass('span11');
    selector.css({
        'overflow' : 'auto'
    });

    var row = $('<div>');
    row.addClass('row-fluid');
    row.css({
        'white-space' : 'nowrap'
    });

    for (var i = 0; i < images.length; i++) {
        var cell = $('<div>');
        cell.addClass('col-lg-1');
        cell.css({
            'display' : 'inline-block',
            'float' : 'none'
        });

        var cellImg = $('<img>');
        cellImg.addClass('thumb');
        cellImg.attr({
            'id' : 'thumb' + i,
            'imgOrig' : images[i].orig,
            'imgEdit' : images[i].edit,
            'imgWidth' : images[i].width,
            'imgHeight' : images[i].height,
            'src' : images[i].thumb,
            'alt' : images[i].edit
        });
        
        cell.append(cellImg);
        row.append(cell);
    }
    
    selector.append( row );
    ele.parent().append( selector );
}

function slide(ele) {
    $(ele).find('#edit').css('width', $(ele).next().val() + "%");
}
function resizeSlider(ele) {
    // if (usableScreen.width <= contentWidth) { //if we have a small screen
    // size
    // if (ele === "" || ele === undefined || ele === null) {
    // console.log('Need to resize all');
    // ele = $(document);
    // }
    // $(ele).find('#original img').css({'max-width':usableScreen.width });
    // $(ele).find('#edit img').css({'max-width':usableScreen.width });
    // $(ele).css({'max-width':usableScreen.width });
    // $(ele).next().css({'max-width':usableScreen.width });
    // $(ele).next().next().css({'max-width':usableScreen.width });
    //        
    // var max_height = (parseInt($(ele).css('height'), 10) - insruction_height)
    // / parseInt($(ele).css('width'), 10) *
    // Math.min(parseInt($(ele).css('width'), 10),
    // parseInt($(ele).css('max-width'), 10));
    // $(ele).css({'max-height':max_height + insruction_height});
    // }
}