var currentLocation = window.location;
var folder = "/" + currentLocation.pathname.split("/")[1];
if (folder.endsWith(".php") || folder == "/") {
    folder = "";
}

$(document)
        .ready(
                function() {
                    $('.editable')
                            .append(
                                    '<span class="editme" style="position:absolute; bottom:0px; right:0px; padding:5px;"></span>');
                    $('.editme')
                            .each(
                                    function() {
                                        var img = $(this).parent().find('img');
                                        var this_class = $(this).parent()
                                                .parent().attr('class');
                                        var count = this_class
                                                .match(/col-md-(\d+)/);
                                        count = count[0].match(/(\d+)/)[0];
                                        var min_width = 1200 / 12 * count;
                                        var min_height = 1200 / 12 * count * 2 / 3;
                                        $(this)
                                                .uploadFile(
                                                        {
                                                            url : "/api/upload-image.php",
                                                            uploadStr : "<i class='fa fa-pencil-square-o'></i> Edit This Image",
                                                            multiple : false,
                                                            dragDrop : false,
                                                            fileName : "myfile",
                                                            acceptFiles : "image/*",
                                                            formData : {
                                                                "location" : ".." + folder + "/" + img.attr('src'),
                                                                "min-width" : min_width,
                                                                "min-height" : min_height
                                                            },
                                                            onSubmit : function() {
                                                                $
                                                                        .blockUI({
                                                                            message : '<h1>Uploading Image...</h1>'
                                                                        });
                                                            },
                                                            onSuccess : function(
                                                                    files,
                                                                    data, xhr,
                                                                    pd) {
                                                                if (data !== "") {
                                                                    $
                                                                            .unblockUI();
                                                                    BootstrapDialog
                                                                            .show({
                                                                                draggable : true,
                                                                                title : 'Whoops, Something Went Wrong',
                                                                                message : data,
                                                                                buttons : [ {
                                                                                    label : 'Close',
                                                                                    action : function(
                                                                                            dialog) {
                                                                                        dialog
                                                                                                .close();
                                                                                    }
                                                                                } ]
                                                                            });
                                                                } else {
                                                                    $
                                                                            .blockUI({
                                                                                message : '<h1>Updating Image...</h1>'
                                                                            });
                                                                    if (min_width < 1200) {
                                                                        arrangeImage(img);
                                                                    } else {
                                                                        cropImage(img);
                                                                    }
                                                                }
                                                            },
                                                        });
                                    });
                });

function arrangeImage(img) {
    cleanImage(img);
    var forcedHeight = parseInt(img.parent().width()) * 2 / 3;
    img.parent().css({
        'height' : forcedHeight + 'px',
    });
}

function cropImage(img) {
    cleanImage(img);
    img.parent().resizable({
        handles : "s"
    });
}

function cleanImage(img) {
    // remove our old info
//    img.parent().find('.watermark').remove();
    img.parent().find('.saveme').remove();

    // save off some info
    var link = img.parent().find('.overlay a').attr('href');
    img.parent().attr("link", link);

    // switch out the image
    img.parent().removeClass('hovereffect');
    img.parent().css({
        'overflow' : 'hidden',
        'position' : 'relative',
        'background-color' : 'red',
        'cursor' : 'ns-resize',
    });
    img.css({
        'top' : '0px',
        'height' : '',
    });
    img.parent().find('.overlay').remove();
    var new_img = img.attr('src').replace(/img\/tmp_/, "img/").replace(/img\//,
            "img/tmp_").replace(/\?(\d+)$/, "");
    img.attr('src', new_img + "?" + randomImgNumber());

    // add our watermark
//    var watermark = $("<img>");
//    watermark.addClass("watermark");
//    watermark.attr("src", "/img/watermark.png");
//    watermark.css({
//        'position' : 'absolute',
//        'width' : '200px',
//        'bottom' : '0px',
//        'left' : '0px',
//        'padding' : '5px',
//    });
//    img.parent().append(watermark);

    // add our save button
    var span = $("<span>");
    span.addClass("saveme");
    span.css({
        'position' : 'absolute',
        'bottom' : '0px',
        'left' : '0px',
        'padding' : '5px'
    });
    var button = $("<button>");
    button.addClass("ajax-file-upload");
    button.css({
        'position' : 'relative',
        'overflow' : 'hidden',
        'cursor' : 'pointer'
    });
    var icon = $("<i>");
    icon.addClass("fa fa-floppy-o");
    button.append(icon);
    button.append(" Save This Image");
    button.click(function() {
        saveImg(img);
    });
    span.append(button);
    img.parent().append(span);

    // make the image draggablr
    img.draggable({
        axis : "y",
    // containment : ".editable"
    });
    // all done
    $.unblockUI();
}

function saveImg(img) {
    img.parent().find('.saveme').prop('disabled', true);
    $.post(
            "/api/crop-image.php",
            {
                "image" : ".." + folder + "/" + img.attr('src').split("?")[0],
                "top" : (parseInt(img.css('top')) * -1),
                "bottom" : (parseInt(img.css('top')) * -1 + parseInt(img
                        .parent().css('height'))),
                "max-width" : parseInt(img.width())
            }).done(
            function() {
                // setup the new image
                var new_img = img.attr('src').replace(/^img\/tmp_/, "img/")
                        .replace(/\?(\d+)$/, "");
                img.attr('src', new_img + "?" + randomImgNumber());
                // remove our old info
                img.parent().find('.watermark').remove();
                img.parent().find('.saveme').remove();
                img.draggable("destroy");
                img.parent().addClass('hovereffect');
                img.parent().css({
                    'height' : '',
                    'cursor' : '',
                    'overflow' : '',
                    'position' : '',
                    'background-color' : ''
                });
                img.css({
                    'position' : '',
                    'left' : '',
                    'top' : '',
                });
                // add back the overlay
                var div = $("<div>");
                div.addClass("overlay");
                var header = $("<h2>");
                header.append(img.parent().attr('section'));
                div.append(header);
                var link = $("<a>");
                link.addClass("info");
                link.attr("href", img.parent().attr("link"));
                link.append("See More");
                div.append(link);
                img.parent().append(div);
            });
}

function randomImgNumber() {
    return Math.floor(Math.random() * 100000000);
}