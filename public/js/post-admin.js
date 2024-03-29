var imageId = 0;

$(document).ready(function () {
    sortOptions();

    $('#post-tags-select').change(function () {
        addTag($(this));
    });

    $('#add-text-button').click(function () {
        addTextArea();
    });

    $('#add-image-button').click(function () {
        addImageArea();
    });

    $('#edit-post').click(function () {
        editPost();
    });

    $('#preview-post').click(function () {
        previewPost();
    });

    $('#save-post').click(function () {
        collectPost(savePost);
    });

    $('#update-post').click(function () {
        collectPost(updatePost);
    });

    $('#schedule-post').click(function () {
        collectPost(savePost, schedulePost);
    });

    $('#schedule-saved-post').click(function () {
        collectPost(updatePost, schedulePost);
    });

    $('#publish-post').click(function () {
        collectPost(savePost, publishPost);
    });

    $('#publish-saved-post').click(function () {
        collectPost(updatePost, publishPost);
    });

    $('#post-preview-image').change(function () {
        setPreview();
    });

    if ($('#post-image-holder').length) {
        uploadForPost()
    }
});

function uploadForPost() {
    $('#post-image-holder').uploadFile({
        url: "/api/upload-blog-images.php",
        uploadStr: "<span class='bootstrap-dialog-button-icon glyphicon glyphicon-upload'></span> Upload Images",
        multiple: true,
        dragDrop: true,
        uploadButtonLocation: $('#post-button-holder'),
        uploadContainer: $('#post-button-holder'),
        uploadButtonClass: "btn btn-default btn-info",
        statusBarWidth: "auto",
        dragdropWidth: "100%",
        fileName: "myfile",
        sequential: true,
        sequentialCount: 5,
        acceptFiles: "image/*",
        uploadQueueOrder: "bottom",
        onSubmit: function () {
            $('.ajax-file-upload-container').show();
        },
        onSuccess: function (files, data, xhr, pd) {
            data = JSON.parse(data);
            if ($.isArray(data)) {
                pd.statusbar.remove();
                $.each(files, function (key, value) {
                    var img = $("<img>");
                    img.attr({
                        id: "image-" + imageId++,
                        src: "../tmp/" + value,
                    });
                    img.addClass('draggable');
                    img.draggable();
                    img.dblclick(function () {
                        removeImage($(this));
                    });
                    $('#post-image-holder').append(img);
                    var option = $('<option>');
                    option.text(value)
                    $('#post-preview-image').append(option);
                });
            } else {
                pd.statusbar.parent().removeClass('ajax-file-upload-container');
                pd.statusbar.prepend("<a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>");
                pd.statusbar.addClass('alert alert-danger');
                pd.progressDiv.hide();
                pd.filename.after(data);
            }
        },
        afterUploadAll: function () {
            setTimeout(function () {
                $('.ajax-file-upload-container').hide();
            }, 5000);
        },
    });

    $('#post-image-holder').height($(window).height() - $('#post-image-holder').offset().top - 70);
}

function newTag(ele) {
    BootstrapDialog.show({
        draggable: true,
        title: 'Add a New Category',
        message: '<input placeholder="Category Name" id="new-category-name" type="text" class="form-control"/>',
        buttons: [{
            icon: 'glyphicon glyphicon-plus',
            label: ' Create Category',
            hotkey: 13,
            cssClass: 'btn-success',
            action: function (dialogItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogItself.enableButtons(false);
                dialogItself.setClosable(false);
                // send our update
                $.post("/api/create-blog-tag.php", {
                    tag: $('#new-category-name').val(),
                }).done(function (data) {
                    // add the option to the
                    // select field, and select
                    // it
                    if ($.isNumeric(data) && data !== '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Successfully added category</div>");
                        var option = $('<option>');
                        option.val(data);
                        option.html($('#new-category-name').val());
                        ele.append(option);
                        ele.val(data);
                        addTag(ele);
                        dialogItself.close();
                    } else if (data === '0') {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog category.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog category.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                }).always(function () {
                    $button.stopSpin();
                    dialogItself.enableButtons(true);
                    dialogItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogItself) {
                dialogItself.close();
            }
        }],
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
    tagSpan.click(function () {
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

    sortOptions();
}

function sortOptions() {
    var my_options = $("#post-tags-select option");
    var selected = $("#post-tags-select").val();

    my_options.sort(function (a, b) {
        if (a.text > b.text) {
            return 1;
        }
        if (a.text < b.text) {
            return -1;
        }
        return 0
    });

    $("#post-tags-select").empty().append(my_options);
    $("#post-tags-select option:nth-child(1)").after($("#post-tags-select option[value=0]"));
    $("#post-tags-select").val(selected);
}

function previewPost() {
    // fix our buttons
    $('#preview-post').hide();
    $('#edit-post').show();

    // setup preview for our preview image
    $('#post-preview-image').hide();
    $('#post-preview-holder').addClass('post hovereffect');
    var titleSpan = $('<span>');
    titleSpan.addClass('preview-title');
    titleSpan.html($('#post-title-input').val());
    titleSpan.css('z-index', '100');
    $('#post-preview-holder').append(titleSpan);
    var overlay = $('<div>');
    overlay.addClass('overlay');
    overlay.css('z-index', '95');
    var link = $('<a>');
    link.addClass('info no-border');
    var icon = $('<em>');
    icon.addClass('fa fa-search fa-2x');
    link.append(icon);
    overlay.append(link)
    $('#post-preview-holder').append(overlay);

    // setup our blog header information for preview
    $('#post-title-input').hide();
    var titleHeader = $('<h2>');
    titleHeader.html($('#post-title-input').val());
    titleHeader.addClass('text-center');
    titleHeader.attr('id', 'post-title-preview');
    $('#post-title-input').after(titleHeader);

    $('#post-date-input').hide();
    var dateSpan = $('<span>');
    var date = new Date($('#post-date-input').val());
    var options = {
        year: "numeric",
        month: "long",
        day: "numeric"
    };
    dateSpan.html(date.toLocaleDateString("en-us", options));
    dateSpan.attr('id', 'post-date-preview');
    $('#post-date-input').after(dateSpan);

    var facebookDiv = $('<div>');
    facebookDiv.addClass('sm-preview col-md-4 text-center');
    var facebookButton = $('<button>');
    facebookButton.addClass('btn btn-xs btn-info');
    facebookButton.css({
        'background-color': '#4267b2',
        'border': '#4267b2'
    });
    var facebookIcon = $('<em>');
    facebookIcon.addClass('fa fa-thumbs-up');
    facebookButton.append(facebookIcon).append(" Like <i>0</i>");
    facebookDiv.append(facebookButton);
    $('#post-likes').append(facebookDiv);

    var twitterDiv = $('<div>');
    twitterDiv.addClass('sm-preview col-md-4 text-center');
    var twitterButton = $('<button>');
    twitterButton.addClass('btn btn-xs btn-info');
    var twitterIcon1 = $('<em>');
    twitterIcon1.addClass('fa fa-twitter');
    var twitterIcon2 = $('<em>');
    twitterIcon2.addClass('fa fa-heart error');
    twitterButton.append(twitterIcon1).append(" Like ").append(twitterIcon2);
    twitterDiv.append(twitterButton);
    $('#post-likes').append(twitterDiv);

    var gplusDiv = $('<div>');
    gplusDiv.addClass('sm-preview col-md-4 text-center');
    var gplusButton = $('<button>');
    gplusButton.addClass('btn btn-xs');
    gplusButton.css({
        'background-color': 'transparent',
        'border': '1px grey solid'
    });
    var gplusIcon = $('<em>');
    gplusIcon.addClass('fa fa-google-plus error');
    gplusButton.append(gplusIcon).append("  <i>0</i>");
    gplusDiv.append(gplusButton);
    $('#post-likes').append(gplusDiv);

    $('#post-tags-select').hide();
    var tagsSpan = $('<span>');
    tagsSpan.attr('id', 'post-tags-preview');
    $('.selected-tag').each(function () {
        $(this).hide();
        var tagLink = $('<a>');
        tagLink.html($(this).html());
        tagsSpan.append(tagLink);
        tagsSpan.append(", ");
    });
    $('#post-tags').append(tagsSpan);

    // setup our texts for previews
    $('.blog-editable-text').each(function () {
        $(this).show().html($(this).summernote('code'));
    });
    $('.note-editor').each(function () {
        $(this).hide();
    });
}

function editPost() {
    // fix our buttons
    $('#preview-post').show();
    $('#edit-post').hide();

    // remove preview for our preview image
    $('#post-preview-image').show();
    $('#post-preview-holder').removeClass('post hovereffect');
    $('#post-preview-holder .preview-title').remove();
    $('#post-preview-holder .overlay').remove();

    // cleanup our blog header information from preview
    $('#post-title-input').show();
    $('#post-title-preview').remove();

    $('#post-date-input').show();
    $('#post-date-preview').remove();

    $('.sm-preview').remove();

    $('#post-tags-select').show();
    $('#post-tags-preview').remove();
    $('.selected-tag').each(function () {
        $(this).show();
    });

    // fix our texts for editing
    $('.blog-editable-text').each(function () {
        $(this).hide();
    });
    $('.note-editor').each(function () {
        $(this).show();
    });
}

function collectPost(callback1, callback2) {
    $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Saving your post.</div>");
    $('.btn').each(function () {
        $(this).prop("disabled", true);
    });
    if ($('#post-title-input').val() === "") {
        BootstrapDialog.alert("Please enter a title for your post");
        $('.btn').each(function () {
            $(this).prop("disabled", false);
        });
        $('#post-information-message').remove();
        return;
    }
    var tags = [];
    $('#post-tags span').each(function () {
        tags.push($(this).attr('tag-id'));
    });
    var preview = {};
    preview.img = $('#post-preview-holder img').attr('src');
    preview.offset = $('#post-preview-holder img').css('top');
    if (!$('#post-preview-holder img').length) {
        BootstrapDialog.alert("Please select a preview image for your post");
        $('.btn').each(function () {
            $(this).prop("disabled", false);
        });
        $('#post-information-message').remove();
        return;
    }
    var content = {};
    var group = 0;
    $('#post-content>li').each(function () {
        var elements = {};
        elements.group = ++group;
        if ($(this).hasClass('blog-editable-text')) {
            elements.type = "text";
            elements.text = $(this).summernote('code');
        } else if ($(this).hasClass('blog-editable-images')) {
            elements.type = "images";
            elements.imgs = [];
            $('img', this).each(function () {
                var img = {};
                img.location = $(this).attr('src');
                img.top = $(this).css('top');
                img.left = $(this).css('left');
                img.width = $(this).css('width');
                img.height = $(this).css('height');
                elements.imgs.push(img);
            });
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your new blog post.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
            $('#post-information-message').remove();
        }
        content[group] = elements;
    });
    if (tags.length === 0) {
        BootstrapDialog.confirm("You didn't enter any tags. Are you sure you want to save this post?", function (result) {
            if (result) {
                callback1(tags, preview, content, callback2);
            } else {
                $('.btn').each(function () {
                    $(this).prop("disabled", false);
                });
                $('#post-information-message').remove();
            }
        });
    } else {
        callback1(tags, preview, content, callback2);
    }
}

function savePost(tags, preview, content, callback) {
    $.post("/api/create-blog-post.php", {
        title: $('#post-title-input').val(),
        date: $('#post-date-input').val(),
        tags: tags,
        preview: preview,
        content: content,
    }).done(function (data) {
        if ($.isNumeric(data) && data !== '0') {
            $('#post-information-message').remove();
            $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your blog post has been saved.</div>");
            if ($.isFunction(callback)) {
                $('#post-information-message').remove();
                $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Please wait while your post is finished processing.</div>");
                callback(data);
            } else {
                window.location.href = "/blog/post.php?p=" + data
            }
        } else if (data === '0') {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
            $('#post-information-message').remove();
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
            $('#post-information-message').remove();
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
        $('#post-information-message').remove();
    }).always(function () {
        $('.btn').each(function () {
            $(this).prop("disabled", false);
        });
    });
}

function updatePost(tags, preview, content, callback) {
    $.post("/api/update-blog-post.php", {
        post: $('#post').attr('post-id'),
        title: $('#post-title-input').val(),
        date: $('#post-date-input').val(),
        tags: tags,
        preview: preview,
        content: content,
    }).done(function (data) {
        if (data === "" || data === "published") {
            $('#post-information-message').remove();
            $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your blog post has been saved.</div>");
            if ($.isFunction(callback)) {
                $('#post-information-message').remove();
                $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Please wait while your post is finished processing.</div>");
                callback($('#post').attr('post-id'));
            } else {
                window.location.href = "/blog/post.php?p=" + $('#post').attr('post-id');
            }
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
            $('#post-information-message').remove();
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
        $('#post-information-message').remove();
    }).always(function () {
        $('.btn').each(function () {
            $(this).prop("disabled", false);
        });
    });
}

function schedulePost(post) {
    $('.btn').each(function () {
        $(this).prop("disabled", true);
    });
    BootstrapDialog.show({
        draggable: true,
        title: 'Select A Time',
        message: 'When do you want to schedule this post to be published' + '<input id="post-publish-date" class="form-control" type="date"/>' + '<input id="post-publish-time" class="form-control" type="time"/>',
        buttons: [{
            icon: 'glyphicon glyphicon-time',
            label: ' Schedule',
            cssClass: 'btn-success',
            action: function (dialogItself) {
                $('#post-information-message').remove();
                $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Scheduling your post.</div>");
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogItself.enableButtons(false);
                dialogItself.setClosable(false);
                // send our update
                $.post("/api/schedule-blog-post.php", {
                    post: post,
                    date: $('#post-publish-date').val(),
                    time: $('#post-publish-time').val()
                }).done(function (data) {
                    if (data === "") {
                        dialogItself.close();
                        $('#post-information-message').remove();
                        $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your blog post has been scheduled.</div>");
                        window.location.href = "/blog/post.php?p=" + post
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while scheduling your blog post.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                }).always(function () {
                    $('.btn').each(function () {
                        $(this).prop("disabled", false);
                    });
                    $button.stopSpin();
                    dialogItself.enableButtons(true);
                    dialogItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogItself) {
                dialogItself.close();
            }
        }]
    });
}

function publishPost(post) {
    $('#post-information-message').remove();
    $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Publishing your post.</div>");
    $('.btn').each(function () {
        $(this).prop("disabled", true);
    });
    $.post("/api/publish-blog-post.php", {
        post: post
    }).done(function (data) {
        if (data === "") {
            $('#post-information-message').remove();
            $('#post-title-input').closest('div').append("<div id='post-information-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your blog post has been published.</div>");
            window.location.href = "/blog/post.php?p=" + post
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#post-title-input').closest('div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while publishing your blog post.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function () {
        $('.btn').each(function () {
            $(this).prop("disabled", false);
        });
    });
}

function setPreview() {
    $('#post-preview-holder img').remove();
    var img = $('<img>');
    img.attr('src', $('#post').attr('post-location') + '/' + $('#post-preview-image').val());
    img.css({
        width: '300px'
    });
    $('#post-preview-holder').append(img);
    img.draggable({
        axis: "y",
    });
}