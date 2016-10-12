var imageId = 0;

$(document).ready(function() {
    $('#post-tags-select').change(function() {
        addTag($(this));
    });

    $('#add-text-button').click(function() {
        addTextArea();
    });

    $('#add-image-button').click(function() {
        addImageArea();
    });

    $('#save-post').click(function() {
        collectPost();
    });

    $('#schedule-post').click(function() {
        collectPost(schedulePost);
    });

    $('#publish-post').click(function() {
        collectPost(publishPost);
    });

    $('#post-preview-image').change(function() {
        setPreview();
    });

    $('#post-image-holder').uploadFile({
        url : "/api/upload-blog-images.php",
        uploadStr : "<span class='bootstrap-dialog-button-icon glyphicon glyphicon-upload'></span> Upload Images",
        multiple : true,
        dragDrop : true,
        uploadButtonLocation : $('#post-button-holder'),
        uploadContainer : $('#post-button-holder'),
        uploadButtonClass : "btn btn-default btn-info",
        statusBarWidth : "auto",
        dragdropWidth : "100%",
        fileName : "myfile",
        sequential : true,
        sequentialCount : 5,
        acceptFiles : "image/*",
        uploadQueueOrder : "bottom",
        onSubmit : function() {
            $('.ajax-file-upload-container').show();
        },
        onSuccess : function(files, data, xhr, pd) {
            data = JSON.parse(data);
            pd.statusbar.remove();
            if ($.isArray(data)) {
                $.each(files, function(key, value) {
                    var img = $("<img>");
                    img.attr({
                        id : "image-" + imageId++,
                        src : "/tmp/" + value,
                    });
                    img.addClass('draggable');
                    img.draggable();
                    img.dblclick(function() {
                        removeImage($(this));
                    });
                    $('#post-image-holder').append(img);
                    var option = $('<option>');
                    option.text(value)
                    $('#post-preview-image').append(option);
                });
            } else {
                $('#post-image-holder').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");

            }
        },
        afterUploadAll : function() {
            setTimeout(function() {
                $('.ajax-file-upload-container').hide();
            }, 5000);
        },
    });

    addImageArea();

    $('#post-image-holder').height($(window).height() - $('#post-image-holder').offset().top - 70);
});

function newTag(ele) {
    BootstrapDialog
            .show({
                draggable : true,
                title : 'Add a New Category',
                message : '<input placeholder="Category Name" id="new-category-name" type="text" class="form-control"/>',
                buttons : [
                        {
                            icon : 'glyphicon glyphicon-plus',
                            label : ' Create Category',
                            hotkey : 13,
                            cssClass : 'btn-success',
                            action : function(dialogItself) {
                                var $button = this; // 'this' here is a jQuery
                                // object that
                                // wrapping the <button> DOM element.
                                var modal = $button.closest('.modal-content');
                                $button.spin();
                                dialogItself.enableButtons(false);
                                dialogItself.setClosable(false);
                                // send our update
                                $
                                        .post("/api/create-blog-tag.php", {
                                            tag : $('#new-category-name').val(),
                                        })
                                        .done(
                                                function(data) {
                                                    // add the option to the
                                                    // select field, and select
                                                    // it
                                                    if ($.isNumeric(data) && data !== '0') {
                                                        modal
                                                                .find('.bootstrap-dialog-body')
                                                                .append(
                                                                        "<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Successfully added category</div>");
                                                        var option = $('<option>');
                                                        option.val(data);
                                                        option.html($('#new-category-name').val());
                                                        ele.append(option);
                                                        ele.val(data);
                                                        addTag(ele);
                                                        dialogItself.close();
                                                    } else if (data === '0') {
                                                        modal
                                                                .find('.bootstrap-dialog-body')
                                                                .append(
                                                                        "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog category.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                                                    } else {
                                                        modal.find('.bootstrap-dialog-body').append(
                                                                "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data
                                                                        + "</div>");
                                                    }
                                                })
                                        .fail(
                                                function() {
                                                    modal
                                                            .find('.bootstrap-dialog-body')
                                                            .append(
                                                                    "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog category.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                                                }).always(function() {
                                            $button.stopSpin();
                                            dialogItself.enableButtons(true);
                                            dialogItself.setClosable(true);
                                        });
                            }
                        }, {
                            label : 'Close',
                            action : function(dialogItself) {
                                dialogItself.close();
                            }
                        } ],
            });
}

function addTag(ele) {
    if (ele.val() === "0") {
        newTag(ele);
        return;
    }
    var tagSpan = $('<span>');
    tagSpan.addClass('selected-tag');
    tagSpan.attr('tag-id', ele.val());
    tagSpan.html($('option:selected', ele).text());
    tagSpan.click(function() {
        removeTag($(this));
    });
    ele.parent().append(tagSpan);
    $('option:selected', ele).remove();
}

function removeTag(ele) {
    var option = $('<option>');
    option.val(ele.attr('tag-id'));
    option.html(ele.text());
    $('#post-tags-select').append(option);
    ele.remove();

}

function collectPost(callback) {
    $('.btn').each(function(){
        $(this).prop("disabled", true);
    });
    if ($('#post-title-input').val() === "") {
        BootstrapDialog.alert("Please enter a title for your post");
        $('.btn').each(function(){
            $(this).prop("disabled", false);
        });
        return;
    }
    var tags = [];
    $('#post-tags span').each(function() {
        tags.push($(this).attr('tag-id'));
    });
    var preview = {};
    preview.img = $('#post-preview-image').val();
    preview.offset = $('#post-preview-holder img').css('top');
    if (preview.img === "") {
        BootstrapDialog.alert("Please select a preview image for your post");
        $('.btn').each(function(){
            $(this).prop("disabled", false);
        });
        return;
    }
    var content = {};
    var group = 0;
    $('#post-content>li').each(function() {
        var elements = {};
        elements.group = ++group;
        if ($(this).hasClass('blog-editable-text')) {
            elements.type = "text";
            elements.text = $(this).summernote('code');
        } else if ($(this).hasClass('blog-editable-images')) {
            elements.type = "images";
            elements.imgs = [];
            $('img', this).each(function() {
                var img = {};
                img.location = $(this).attr('src');
                img.top = $(this).css('top');
                img.left = $(this).css('left');
                img.width = $(this).css('width');
                img.height = $(this).css('height');
                elements.imgs.push(img);
            });
        } else {
            $('#post-title-input').closest('div').append(
                    "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog post.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");

        }
        content[group] = elements;
    });
    if (tags.length === 0) {
        BootstrapDialog.confirm("You didn't enter any tags. Are you sure you want to save this post?", function(result) {
            if (result) {
                savePost(tags, preview, content, callback);
            } else {
                $('.btn').each(function(){
                    $(this).prop("disabled", false);
                });
            }
        });
    } else {
        savePost(tags, preview, content, callback);
    }

}

function savePost(tags, preview, content, callback) {
    $.post("/api/create-blog-post.php", {
        title : $('#post-title-input').val(),
        date : $('#post-date-input').val(),
        tags : tags,
        preview : preview,
        content : content,
    }).done(function(data) {
        if ($.isNumeric(data) && data !== '0') {
            $('#post-title-input').closest('div').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your blog post has been saved.</div>");
            if ($.isFunction(callback)) {
                $('#post-title-input').closest('div').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Please wait while your post is finished processing.</div>");
                callback(data);
            } else {
                window.location.href = "/blog/post.php?p=" + data
            }
        } else if (data === '0') {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function() {
        $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
    }).always(function() {
        $('.btn').each(function(){
            $(this).prop("disabled", false);
        });
    });
}

function schedulePost() {
    BootstrapDialog.alert("This doesn't work yet");
}

function publishPost(post) {
    $('.btn').each(function(){
        $(this).prop("disabled", true);
    });
    $.post("/api/publish-blog-post.php", {
        post : post
    }).done(function(data) {
        if (data === "") {
            $('#post-title-input').closest('div').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your blog post has been published.</div>");
            window.location.href = "/blog/post.php?p=" + post
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function() {
        $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
    }).always(function() {
        $('.btn').each(function(){
            $(this).prop("disabled", false);
        });
    });
}

function setPreview() {
    $('#post-preview-holder img').remove();
    var img = $('<img>');
    img.attr('src', '/tmp/' + $('#post-preview-image').val());
    img.css({
        width : '300px'
    });
    $('#post-preview-holder').append(img);
    img.draggable({
        axis : "y",
    });
}