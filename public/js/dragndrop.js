var textAreas = 0;
var imageAreas = 0;

var elementBuilder = "#post-content";
var imageBuilder = ".image-builder";
var imagePlace = "#post-image-holder";
var showGhost = false;
var imageOrder = {};
var tempArray = [];
var currentMousePos = {
    x: -1,
    y: -1
};
var moveTo = null;

// a function to set all of our globals
var isDragged = false;

function DragNDrop(elementBuilderString, imageBuilderString, imagePlaceString,
                   showGhostBool) {
    elementBuilder = elementBuilderString;
    imageBuilder = imageBuilderString;
    imagePlace = imagePlaceString;
    showGhost = showGhostBool;
}

// add a new textarea
function addTextArea(text) {
    if (text === undefined) {
        text = "";
    }

    // create our element
    var textHolder = $('<li>');
    textHolder.attr('id', "textArea-" + textAreas++);
    textHolder.addClass('blog-editable-text');
    textHolder.html(text);
    $(elementBuilder).append(textHolder);
    textHolder.summernote();

    // set our removal functionality
    textHolder.dblclick(function () {
        text = $(this).parent().find('textarea').val();
        if (text !== "") {
            var r = confirm("This input area has text in it, are you sure you want to delete it?");
            if (r === false) {
                return;
            }
        }
        $(this).parent().remove();
    });
    makeSortable();
}

// add a new image area
function addImageArea(images) {
    var imageAreaID = "imageArea-" + imageAreas++;
    var imageArray = [];
    if (images === undefined) {
        images = [];
    }
    images.sort(compareLeft);
    images.sort(compareTop);
    var imageEle = $('<li>');
    imageEle.addClass('blog-editable-images');
    var maxHeight = 100;
    for (var ndx = 0; ndx < images.length; ndx++) {
        maxHeight = Math.max(maxHeight,
            (images[ndx].height * 1 + images[ndx].top * 1));
    }
    var imageBuilder = $("<div id='" + imageAreaID
        + "' class='image-builder' style='height:" + maxHeight
        + "px;'></div>");
    var curTop = 0; // for keeping track of when we need breaks
    for (ndx = 0; ndx < images.length; ndx++) {
        // setup our image element
        var ele = $("<img id='image-" + imageId++
            + "' class='draggable' src='" + images[ndx].location
            + "' style='position:absolute;z-index:90;width:"
            + images[ndx].width + "px;height:" + images[ndx].height
            + "px;left:" + images[ndx].left + "px;top:" + images[ndx].top
            + "px' />");
        // add our images to our image area
        imageBuilder.append(ele);
        // add our images to our imageOrder
        if (images[ndx].top !== curTop) {
            imageArray.push("BREAK");
            curTop = images[ndx].top;
        }
        imageArray.push(ele);
    }
    imageOrder[imageAreaID] = imageArray;
    imageEle.append(imageBuilder);
    imageEle
        .append("<div id='temp' style='background:lightblue;width:1px;height:1px;'></div>");
    $(elementBuilder).append(imageEle);
    imageBuilder.dblclick(function () {
        if (!$(this).children().length) {
            var r = confirm("Do you want to delete this image area?");
            if (r === false) {
                return;
            }
            $(this).parent().remove();
        }
    });
    // setup our draggable objects
    $(".draggable").dblclick(function () {
        removeImage($(this));
    });
    $(".draggable").draggable();
    $("div#" + imageAreaID).droppable(
        {
            tolerance: "pointer",
            over: function () {
                isDragged = true;
            },
            out: function () {
                isDragged = false;
            },
            activate: function (event, ui) {
                ui.draggable.css({
                    'cursor': 'move',
                    'z-index': 99
                });
            },
            deactivate: function (event, ui) {
                ui.draggable.css({
                    'cursor': 'pointer',
                    'z-index': 90
                });
            },
            drop: function (event, ui) {
                isDragged = false;
                if (ui.draggable[0].tagName === "IMG"
                    || ui.draggable[0].tagName === "img") {
                    placeImage(ui.draggable, imageAreaID);
                }
            },
        });
    // setup our sortable images
    makeSortable();
}

function makeSortable() {
    var moveTo;
    $(elementBuilder).sortable({
        handle: '.panel-heading',
        start: function (event, ui) {
            moveTo = ui.item.prev();
        },
        update: function (event, ui) {
            moveTo.insertBefore(ui.item);
        }
    });
}

function placeImage(ele, arrayName) {
    // get our mouse position relative to the current image holder
    var xPos = currentMousePos.x - $("div#" + arrayName).offset().left;
    var yPos = currentMousePos.y - $("div#" + arrayName).offset().top;

    // first check if our image is already in the array
    for (var ndx in imageOrder[arrayName]) {
        if (typeof imageOrder[arrayName][ndx].attr !== 'function') {
            continue;
        }
        if (imageOrder[arrayName][ndx].attr('id') === ele.attr('id')) {
            removeImage(ele);
            break;
        }
    }
    // move our image into the div
    ele.appendTo("div#" + arrayName);
    // determine placement
    var eachImageRow = Array();
    var lastStart = 0;
    for (ndx in imageOrder[arrayName]) {
        if (imageOrder[arrayName][ndx] === "BREAK") {
            eachImageRow.push(imageOrder[arrayName].slice(lastStart, ndx));
            ndx = ndx * 1 + 1;
            lastStart = ndx;
        } else if (parseInt(ndx) === parseInt(imageOrder[arrayName].length - 1)) {
            eachImageRow.push(imageOrder[arrayName].slice(lastStart));
        }
    }
    imageOrder[arrayName] = []; // clear out our array
    for (ndx in eachImageRow) {
        if (eachImageRow.hasOwnProperty(ndx)) {
            var thisRow = eachImageRow[ndx];
            var rowHeight = thisRow[0].height();
            var rowTop = thisRow[0].css('top');
            rowTop = rowTop.substr(0, rowTop.length - 2);
            //if this is not in our row, do nothing
            if (yPos < rowTop || yPos > (rowTop * 1) + (rowHeight * 1)) {
                // if we are in the top %15 of the pixels of the row
                // unshift an image and a break
            } else if (yPos < (rowHeight * 0.15) + (rowTop * 1)) {
                thisRow.unshift(ele, "BREAK");
                // if we are in the bottom 15% of the pixels of the row
                // push a break and an image
            } else if (yPos > (rowHeight * 0.85) + (rowTop * 1)) {
                thisRow.push("BREAK", ele);
                // search through each image to see where it fits
            } else {
                for (ndx in thisRow) {
                    if (thisRow.hasOwnProperty(ndx)) {
                        var imgWidth = thisRow[ndx].width();
                        var imgLeft = thisRow[ndx].css('left');
                        imgLeft = imgLeft.substr(0, imgLeft.length - 2);
                        if (xPos < (imgLeft * 1) + (imgWidth / 2)) {
                            // insert the image
                            thisRow.splice(ndx, 0, ele);
                            break;
                        } else if (parseInt(ndx) === parseInt(thisRow.length - 1)) {
                            // push the image
                            thisRow.push(ele);
                        }
                    }
                }
            }
            // combine our arrays with breaks in between
            for (ndx in thisRow) {
                if (thisRow.hasOwnProperty(ndx)) {
                    imageOrder[arrayName].push(thisRow[ndx]);
                }
            }
            imageOrder[arrayName].push("BREAK");
        }
    }
    if (imageOrder[arrayName].length === 0) {
        imageOrder[arrayName].push(ele);
    }
    resizeImages(arrayName);
}

function removeImage(ele) {
    // get parent id
    var arrayName = ele.parent().attr('id');
    // remove image from image array
    for (var ndx = 0; ndx < imageOrder[arrayName].length; ndx++) {
        if (typeof imageOrder[arrayName][ndx].attr !== 'function') {
            continue;
        }
        if (imageOrder[arrayName][ndx].attr('id') === ele.attr('id')) {
            imageOrder[arrayName].splice(ndx, 1);
        }
    }
    ele.appendTo(imagePlace);
    ele.css({
        'position': 'relative',
        'left': '',
        'top': '',
        'width': '',
        'height': '',
        'z-index': '',
        'cursor': '',
    });
    resizeImages(arrayName);
}

function resizeImages(arrayName) {
    // /////////
    // fix any bad entries
    // /////////
    // remove breaks at beginning
    while (imageOrder[arrayName][0] === "BREAK") {
        imageOrder[arrayName].shift();
    }
    // remove breaks at end
    while (imageOrder[arrayName][imageOrder[arrayName].length - 1] === "BREAK") {
        imageOrder[arrayName].pop();
    }
    // remove any double BREAKS
    var lastEntry = "NOTANENTRY";
    for (var ndx = 0; ndx < imageOrder[arrayName].length; ndx++) {
        if (imageOrder[arrayName][ndx] === lastEntry) {
            imageOrder[arrayName].splice(ndx, 1);
            ndx = ndx - 1;
        }
        lastEntry = imageOrder[arrayName][ndx];
    }
    // ////////////
    // setup an array for each row we are working with
    // ////////////
    var eachImageRow = [];
    var lastStart = 0;
    for (ndx in imageOrder[arrayName]) {
        if (imageOrder[arrayName][ndx] === "BREAK") {
            eachImageRow.push(imageOrder[arrayName].slice(lastStart, ndx));
            ndx = ndx * 1 + 1;
            lastStart = ndx;
        } else if (parseInt(ndx) === parseInt(imageOrder[arrayName].length - 1)) {
            eachImageRow.push(imageOrder[arrayName].slice(lastStart));
        }
    }
    var startHeight = 0; // what top positioning each file will have
    for (ndx in eachImageRow) {
        if (eachImageRow.hasOwnProperty(ndx)) {
            var thisRow = eachImageRow[ndx];
            // get our total width
            var totalUnitWidth = 0;
            for (ndx in thisRow) {
                if (thisRow.hasOwnProperty(ndx)) {
                    var eleWidth = thisRow[ndx].width();
                    var eleHeight = thisRow[ndx].height();
                    totalUnitWidth += eleWidth / eleHeight;
                }
            }
            var widthScaling = $("div#" + arrayName).width() / totalUnitWidth;
            var startWidth = 0; // what left positioning each file will have
            for (ndx in thisRow) {
                if (thisRow.hasOwnProperty(ndx)) {
                    eleWidth = thisRow[ndx].width();
                    eleHeight = thisRow[ndx].height();
                    var newWidth = Math.round(eleWidth / eleHeight * widthScaling);
                    thisRow[ndx].css({
                        'position': 'absolute',
                        'left': startWidth,
                        'top': startHeight,
                        'width': newWidth,
                        'height': widthScaling
                    });
                    startWidth += newWidth * 1;
                }
            }
            startHeight += widthScaling * 1;
        }
    }
    if (startHeight !== 0) {
        $("div#" + arrayName).height(startHeight);
    }
}

function compareLeft(a, b) {
    if (a.left < b.left)
        return -1;
    if (a.left > b.left)
        return 1;
    return 0;
}

function compareTop(a, b) {
    if (a.top < b.top)
        return -1;
    if (a.top > b.top)
        return 1;
    return 0;
}

$(function () {
    isDragged = false;
    $("body").mousemove(function (e) {
        currentMousePos.x = e.pageX;
        currentMousePos.y = e.pageY;
        if (isDragged && showGhost) { // setup our ghost
            tempArray = imageOrder.slice(0);
            placeImage($('#temp'), "tempArray");
        }
    });
});