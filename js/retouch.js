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
        ele.before(instructions)
    }
    
    var heighter = $('<div>');
    heighter.attr({'id':'heighter'});
    heighter.css({'margin-top':'0%'});
    ele.append(heighter);
    

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
    
    var comment = $('<p>');
    comment.addClass('comment');
    slider.after( comment );
}

function sliderSim(ele, before, after) {
    createSlider(ele, false);

    var width = after.width;
    ele.width(width);
    ele.next().width(width);
    var height = after.height;
    height = height / width * ele.outerWidth();
    ele.height(height + $('span#instructions').outerHeight());

    setInterval(function() {
        slide(ele);
    }, 50);
}
function slider(ele, images) {
    createSlider(ele, true);

    addSelector(ele, images);

    setInterval(function() {
        slide(ele);
    }, 50);

    $('img.thumb').click(function() {
        var imgWidth = $(this).attr('imgWidth');
        var imgHeight = $(this).attr('imgHeight');
        var height = imgHeight/imgWidth*100;
        ele.find('#heighter').css({'margin-top':height + '%'});
        
        var orig = $(this).attr('imgOrig');
        var edit = $(this).attr('imgEdit');
        ele.find('#original img').attr({'src':orig}).css({'width':ele.width()});
        ele.find('#edit img').attr({'src':edit}).css({'width':ele.width()});
        ele.parent().find('.slider').val(0);
        ele.parent().find('.comment').html( $(this).attr('text') );
        
        $('img.thumb').css({
            'border' : '2px transparent solid'
        });
        $(this).css({
            'border' : '2px #9dcb3b solid'
        });
        slide(ele);
    });
}

function addSelector(ele, images) {

    var selector = $('<div>');
    selector.addClass('col-md-12');
    selector.css({
        'overflow' : 'auto'
    });

    var row = $('<div>');
    row.addClass('row-fluid text-center');
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
            'text' : images[i].text,
            'src' : images[i].thumb,
            'alt' : images[i].edit
        });
        
        cell.append(cellImg);
        row.append(cell);
    }
    
    selector.append( row );
    ele.closest('[class^=col]').after( selector );
}

function slide(ele) {
    $(ele).find('#edit').css('width', $(ele).next().val() + "%");
}