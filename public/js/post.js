$(document).ready(function () {
    $('#post-comment-message').keyup(function () {
        checkPost();
    });
    $('#post-comment-submit').click(function () {
        submitPost();
    })
});

function getLink(data) {
    return '/blog/post.php?p=' + data.id;
}

function loadPostPreview(k, v) {
    // create our holding div
    var holder = $('<div>');
    holder.addClass('post hovereffect');
    holder.height($('.col-gallery').width() / 1.7);
    // create our image
    holder.css({
        'background-image': 'url("' + v.preview + '")',
        'background-position': '0 ' + v.offset + 'px',
        'background-size': $('.col-gallery').width() + 'px',
    });
    // our post title
    var title = $('<span>');
    title.addClass('preview-title');
    title.html(v.title);
    holder.append(title);
    // create our overlay
    var overlay = $('<div>');
    overlay.addClass('overlay');
    // our view link
    var link = $('<a>');
    link.addClass('info no-border');
    link.attr('href', '/blog/post.php?p=' + v.id);
    // add our image icon
    var view = $('<i>');
    view.addClass('fa fa-search fa-2x');
    // put them all together
    link.append(view);
    overlay.append(link);
    holder.append(overlay);
    if ($(window).width() < 767) {
        k = 1;
    }
    $('#post-' + k).append(holder);
}

function loadPost(data, header) {
    var link = getLink(data);

    // create our holding div
    var holder = $('<div>');

    if (header === "<h1>") {
        $('h1').html(data.title);
        $('#breadcrumb-title').html(data.title);
    } else {
        // setup our title
        var title_row = $('<div>');
        title_row.addClass('row');
        var title = $('<div>');
        title.addClass('col-md-12 text-center');
        var header_span = $(header);
        var link_a = $('<a>');
        link_a.attr('href', link);
        link_a.addClass('plain');
        link_a.append(data.title)
        header_span.append(link_a);
        title.append(header_span);
        title_row.append(title);
        holder.append(title_row);
    }

    // setup our post details
    var details_row = $('<div>');
    details_row.addClass('row');
    var details_tags = $('<div>');
    details_tags.addClass('col-md-4 text-left');
    $.each(data.tags, function (k, v) {
        var tag_link = $('<a>');
        tag_link.attr('href', '/blog/category.php?t=' + v.id);
        tag_link.html(v.tag);
        details_tags.append(tag_link);
        if (k < data.tags.length - 1) {
            details_tags.append(", ");
        }
    });
    details_row.append(details_tags);
    var details_date = $('<div>');
    details_date.addClass('col-md-4 text-center');
    details_date.append("<strong>" + data.date + "</strong>");
    details_row.append(details_date);

    if (socialTrackingAllowed()) {
        details_row.append(addSocialMedias(data));
    }
    holder.append(details_row);

    // setup our post content
    $.each(data.content, function (k, v) {
        var row = $('<div>');
        row.addClass('row');

        var content = $('<div>');
        // if it's a text content
        if (v[0].hasOwnProperty('text')) {
            content.addClass('post-text col-md-12');
            $.each(v, function (l, w) {
                content.append(w.text);
            });
        } else {
            content.addClass('post-images col-md-12');
            var max_height = 0;
            $.each(v, function (l, w) {
                var image = $('<img>');
                image.addClass('post-image');
                image.attr('src', w.location);
                image.css({
                    'height': w.height + 'px',
                    'width': w.width + 'px',
                    'left': parseFloat(w.left) + 15 + 'px',
                    'top': w.top + 'px'
                });
                content.append(image);
                max_height = Math.max(max_height, (parseFloat(w.top) + parseFloat(w.height)));
            });
            content.css({
                'height': max_height + 'px'
            });

            // setup our protecting div
            var protect = $('<div>');
            protect.addClass('post-protects');
            protect.css({
                'height': max_height + 'px'
            });

            var image = $('<img>');
            image.addClass('post-protect');
            image.attr('src', '/img/image.png');
            protect.append(image);
            row.append(protect);
        }
        row.append(content);
        holder.append(row);
    });
    $('#post-content').append(holder);

    // load our comments
    $.each(data.comments.reverse(), function (k, v) {
        addComment(v);
    });
    if (data.comments) {
        var comments_header = (data.comments.length !== 1) ? data.comments.length + " Comments" : data.comments.length + " Comment";
        $('#post-comments h2').html(comments_header);
    }
    if (socialTrackingAllowed()) {
        loadSM();
    }
    addShares(data);
}

function addSocialMedias(data) {
    var link = getLink(data);

    var details_likes = $('<div>');
    details_likes.addClass('col-md-4 text-right');

    // our facebook likes button
    var facebook = $('<div>');
    facebook.addClass('fbook');
    var facebook_div = $('<div>');
    facebook_div.addClass('fb-like col-xs-6 text-left');
    facebook_div.attr({
        "data-href": link,
        "data-send": "false",
        "data-layout": "button_count",
        "data-show-faces": "false"
    });
    facebook.append(facebook_div);
    details_likes.append(facebook);

    // our twitter likes button
    var twitter = $('<div>');
    twitter.addClass('tweet col-xs-6 text-right');
    var twitter_a = $('<a>');
    twitter_a.addClass('btn btn-xs btn-info');
    twitter_a.attr({
        "href": "https://twitter.com/intent/like?tweet_id=" + data.twitter
    });
    var twitter_em = $('<em>');
    twitter_em.addClass('fa fa-twitter');
    var twitter_eml = $('<em>');
    twitter_eml.addClass('fa fa-heart error');
    twitter_a.append(twitter_em);
    twitter_a.append(" Like ");
    twitter_a.append(twitter_eml);

    twitter.append(twitter_a);
    details_likes.append(twitter);

    return details_likes;
}

function getShareUrl(data) {
    var link = getLink(data);
    if (window.location && window.location.origin) {
        return window.location.origin + link;
    }
    return link;
}

function addShares(data) {
    var row = $('<div>');
    row.addClass('row');

    var shares = $('<div>');
    shares.addClass('col-md-12 blog-share-actions');

    if (typeof navigator.share === 'function') {
        var shareButton = $('<button>');
        shareButton.addClass('btn btn-default blog-share-button blog-share-native');
        shareButton.attr('type', 'button');

        var shareIcon = $('<em>');
        shareIcon.addClass('fa fa-share-alt');
        shareButton.append(shareIcon);
        shareButton.append(' Share');
        shareButton.click(function () {
            sharePost(data);
        });
        shares.append(shareButton);
    }

    var copyButton = $('<button>');
    copyButton.addClass('btn btn-default blog-share-button blog-share-copy');
    copyButton.attr('type', 'button');

    var copyIcon = $('<em>');
    copyIcon.addClass('fa fa-link');
    copyButton.append(copyIcon);
    copyButton.append(' Copy Link');
    copyButton.click(function () {
        copyShareLink(data, copyButton);
    });
    shares.append(copyButton);

    row.append(shares);
    $('#post-content').append(row);
}

function sharePost(data) {
    if (typeof navigator.share !== 'function') {
        return copyShareLink(data);
    }

    return navigator.share({
        title: data.title,
        url: getShareUrl(data)
    }).catch(function (error) {
        if (!error || error.name !== 'AbortError') {
            return copyShareLink(data);
        }
    });
}

function copyShareLink(data, button) {
    var url = getShareUrl(data);

    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        return navigator.clipboard.writeText(url).then(function () {
            if (button) {
                button.html('<em class="fa fa-check"></em> Copied');
            }
            return url;
        });
    }

    if (window.prompt) {
        window.prompt('Copy this link:', url);
    }
    return Promise.resolve(url);
}

function addComment(comment) {
    var comment_row = $('<div>');
    comment_row.addClass('row');

    var comment_set = $('<div>');
    comment_set.addClass('col-lg-12');

    var comment_block = $('<blockquote>');
    if (comment.delete) {
        comment_block.addClass('deletable');
        comment_block.click(comment.id, deletePost);
    }
    var comment_comment = $('<p>');
    comment_comment.append(comment.comment);
    var comment_break = $('<br>');
    var comment_date = $('<em>');
    comment_date.append(comment.date);
    var comment_footer = $('<footer>');
    comment_footer.append(comment.name);
    comment_footer.append(comment_break);
    comment_footer.append(comment_date);

    comment_block.append(comment_comment);
    comment_block.append(comment_footer);
    comment_set.append(comment_block);
    comment_row.append(comment_set);

    $('#post-comments > div:first-child').after(comment_row);
    setCommentHeader(1);
}

function loadSM() {
    loadExternalScript('facebook-jssdk', 'https://connect.facebook.net/en_US/all.js#xfbml=1', function () {
        if (window.FB) {
            window.FB.XFBML.parse();
        }
    });
}

function socialTrackingAllowed() {
    return typeof hasCookiePreference === 'function' && hasCookiePreference('social');
}

function loadExternalScript(id, source, onload) {
    var existing = document.getElementById(id);
    if (existing) {
        if (existing.dataset.loaded === 'true') {
            onload();
        } else {
            existing.addEventListener('load', onload, {once: true});
        }
        return;
    }
    var script = document.createElement('script');
    script.id = id;
    script.async = true;
    script.src = source;
    script.onload = function () {
        script.dataset.loaded = 'true';
        onload();
    };
    document.head.appendChild(script);
}

function deletePost(post) {
    BootstrapDialog.show({
        draggable: true,
        title: 'Are You Sure?',
        message: 'Are you sure you want to delete this comment?',
        buttons: [{
            icon: 'glyphicon glyphicon-trash',
            label: ' Delete',
            cssClass: 'btn-danger',
            action: function (dialogInItself) {
                var $button = this; // 'this' here is a jQuery object that
                                    // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/delete-blog-comment.php", {
                    comment: post.data
                }).done(function () {
                    dialogInItself.close();
                    // cleanup the dom
                    post.currentTarget.remove();
                    setCommentHeader(-1);
                }).fail(function (xhr, status, error) {
                    appendBlogRequestError(
                        modal.find('.bootstrap-dialog-body'),
                        xhr,
                        error,
                        "Some unexpected error occurred while deleting your image.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting."
                    );
                }).always(function () {
                    $button.stopSpin();
                    dialogInItself.enableButtons(true);
                    dialogInItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }]
    });
}

function checkPost() {
    var message = $('#post-comment-message').val();
    var badStrings = ["asshole", "bastard", "bitch", "cunt", "fuck", "nigger", "shit", "whore"];
    var goodMessage = true;
    if (message.length === 0) {
        goodMessage = false;
    }
    if (new RegExp(badStrings.join("|"), 'i').test(message)) {
        // At least one match
        goodMessage = false;
    }
    if (goodMessage) {
        $('#post-comment-submit').prop("disabled", false);
        $('#post-comment-submit').removeClass("disabled");
    } else {
        $('#post-comment-submit').prop("disabled", true);
        $('#post-comment-submit').addClass("disabled");
    }
    return goodMessage;
}

function submitPost() {
    if (!checkPost()) {
        return;
    }
    $('#post-comment-message-message').empty();
    $('#post-comment-submit').prop('disabled', true);
    $.post("/api/create-blog-comment.php", {
        post: $('#post-comment-submit').attr('post-id'),
        name: $('#post-comment-name').val(),
        email: $('#post-comment-email').val(),
        message: $('#post-comment-message').val()
    }).done(function (data) {
        if ($.isNumeric(data) && data !== '0') {
            var current_datetime = new Date();
            var formatted_date = current_datetime.getFullYear() + "-" + appendLeadingZeroes(current_datetime.getMonth() + 1) + "-" + appendLeadingZeroes(current_datetime.getDate()) + " " + appendLeadingZeroes(current_datetime.getHours()) + ":" + appendLeadingZeroes(current_datetime.getMinutes()) + ":" + appendLeadingZeroes(current_datetime.getSeconds());
            var comment = {
                id: data,
                delete: Boolean($('#my-user-id').val()),
                date: formatted_date,
                name: $('#post-comment-name').val(),
                comment: $('#post-comment-message').val()
            };
            addComment(comment);
            $('#post-comment-message').val("");
        } else if (data === '0') {
            $('#post-comment-message-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while adding your comment.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        } else {
            $('#post-comment-message-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function (xhr, status, error) {
        appendBlogRequestError(
            $('#post-comment-message-message'),
            xhr,
            error,
            "Some unexpected error occurred while adding your comment.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting."
        );
    }).always(function () {
        checkPost();
    });
}

function setCommentHeader(increment) {
    if ($('#post-comments h2').length) {
        var comments_present = $('#post-comments h2').html().split(" ")[0];
        comments_present = parseInt(comments_present) + parseInt(increment);
        var comments_header = (comments_present !== 1) ? comments_present + " Comments" : comments_present + " Comment";
        $('#post-comments h2').html(comments_header);
    }
}

function appendLeadingZeroes(n) {
    if (n <= 9) {
        return "0" + n;
    }
    return n
}
