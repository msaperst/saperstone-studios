$(document).ready(function() {
    $('#post-comment-message').keyup(function(){
        checkPost();
    });
    $('#post-comment-submit').click(function(){
        submitPost();
    })
});

function getLink( data ) {
    return window.location.protocol + '//' + window.location.hostname + '/blog/post.php?p=' + data.id;
}

function loadPostPreview(k, v) {
    // create our holding div
    var holder = $('<div>');
    holder.addClass('post hovereffect');
    holder.height($('.col-gallery').width() / 1.7);
    // create our image
    holder.css({
        'background-image' : 'url("' + v.preview + '")',
        'background-position' : '0 ' + v.offset + 'px',
        'background-size' : $('.col-gallery').width() + 'px',
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
    var link = getLink( data );
    
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
    $.each(data.tags, function(k, v) {
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

    

    details_row.append( addSocialMedias( data ) );
    holder.append(details_row);

    // setup our post content
    $.each(data.content, function(k, v) {
        var row = $('<div>');
        row.addClass('row');

        var content = $('<div>');
        // if it's a text content
        if (v[0].hasOwnProperty('text')) {
            content.addClass('post-text col-md-12');
            $.each(v, function(l, w) {
                content.append(w.text);
            });
        } else {
            content.addClass('post-images col-md-12');
            var max_height = 0;
            $.each(v, function(l, w) {
                var image = $('<img>');
                image.addClass('post-image');
                image.attr('src', w.location);
                image.css({
                    'height' : w.height + 'px',
                    'width' : w.width + 'px',
                    'left' : parseFloat(w.left) + 15 + 'px',
                    'top' : w.top + 'px'
                });
                content.append(image);
                max_height = Math.max(max_height, (parseFloat(w.top) + parseFloat(w.height)));
            });
            content.css({
                'height' : max_height + 'px'
            });

            // setup our protecting div
            var protect = $('<div>');
            protect.addClass('post-protects');
            protect.css({
                'height' : max_height + 'px'
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
    $.each(data.comments, function(k, v) {
        addComment(v);
    });
    if( data.comments ) {
        var comments_header = (data.comments.length > 1) ? data.comments.length + " Comments" : data.comments.length + " Comment";
        $('#post-comments h2').html(comments_header);
    }
    loadSM();
    addShares(data);
}

function addSocialMedias(data) {
    var link = getLink( data );

    var details_likes = $('<div>');
    details_likes.addClass('col-md-4 text-right');

    // our facebook likes button
    var facebook = $('<div>');
    facebook.addClass('fbook');
    var facebook_div = $('<div>');
    facebook_div.addClass('fb-like col-xs-6 text-left');
    facebook_div.attr({
        "data-href" : link,
        "data-send" : "false",
        "data-layout" : "button_count",
        "data-show-faces" : "false"
    });
    facebook.append(facebook_div);
    details_likes.append(facebook);

    // our twitter likes button
    var twitter = $('<div>');
    twitter.addClass('tweet col-xs-6 text-right');
    var twitter_a = $('<a>');
    twitter_a.addClass('btn btn-xs btn-info');
    twitter_a.attr({
        "href" : "https://twitter.com/intent/like?tweet_id=" + data.twitter
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

function addShares(data) {
    var link = getLink(data);
    var title = data.title;
    
    var row = $('<div>');
    row.addClass('row');
    
    var shares = $('<div>');
    shares.addClass("col-md-12 a2a_kit a2a_kit_size_48 a2a_default_style");
    
    var addAny_a = $('<a>');
    addAny_a.addClass('a2a_dd col-md-1');
    addAny_a.attr('href','https://www.addtoany.com/share?linkurl='+link+'&amp;linkname='+title);
    shares.append(addAny_a);
    
    var addEmail_a = $('<a>');
    addEmail_a.addClass('a2a_button_email col-md-1');
    shares.append(addEmail_a);
    
    var addFacebook_a = $('<a>');
    addFacebook_a.addClass('a2a_button_facebook col-md-1');
    shares.append(addFacebook_a);
    
    var addTwitter_a = $('<a>');
    addTwitter_a.addClass('a2a_button_twitter col-md-1');
    shares.append(addTwitter_a);
    
    var addGooglePlus_a = $('<a>');
    addGooglePlus_a.addClass('a2a_button_google_plus col-md-1');
    shares.append(addGooglePlus_a);
    
    var addPinterest_a = $('<a>');
    addPinterest_a.addClass('a2a_button_pinterest col-md-1');
    shares.append(addPinterest_a);
    
    var addLinkedIn_a = $('<a>');
    addLinkedIn_a.addClass('a2a_button_linkedin col-md-1');
    shares.append(addLinkedIn_a);
    
    var addReddit_a = $('<a>');
    addReddit_a.addClass('a2a_button_reddit col-md-1');
    shares.append(addReddit_a);
    
    var addTumblr_a = $('<a>');
    addTumblr_a.addClass('a2a_button_tumblr col-md-1');
    shares.append(addTumblr_a);
    
    row.append( shares );
    $('#post-content').append( row );

    var a2a_config = a2a_config || {};
    a2a_config.linkname = title;
    a2a_config.linkurl = link;
    a2a_config.num_services = 10;
    
    $.getScript( "https://static.addtoany.com/menu/page.js" );
}

function addComment(comment) {
    var comment_row = $('<div>');
    comment_row.addClass('row');

    var comment_set = $('<div>');
    comment_set.addClass('col-lg-12');

    var comment_block = $('<blockquote>');
    if( comment.delete ) {
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
    
    $('#post-comments').append(comment_row);
}

function loadSM() {
    try {
        FB.XFBML.parse();
        twttr.widgets.load();
        gapi.plusone.go();
    } catch (err) {
        setTimeout(loadSM, 100);
    } finally {
// setTimeout(function(){ console.log( "iFrame: " +
// $("iframe").contents().find('._2tga._49ve')) }, 3000 );
    }
}

function deletePost(post) {
    BootstrapDialog.show({
        draggable : true,
        title : 'Are You Sure?',
        message : 'Are you sure you want to delete this comment?',
        buttons : [ {
            icon : 'glyphicon glyphicon-trash',
            label : ' Delete',
            cssClass : 'btn-danger',
            action : function(dialogInItself) {
                var $button = this; // 'this' here is a jQuery object that
                                    // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/delete-blog-comment.php", {
                    comment : post.data
                }).done(function() {
                    dialogInItself.close();
                    // cleanup the dom
                    post.currentTarget.remove();
                }).fail(function(xhr, status, error) {
                    if ( xhr.responseText !== "" ) {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if ( error === "Unauthorized" ) {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while deleting your image.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                    }
                });
            }
        }, {
            label : 'Close',
            action : function(dialogInItself) {
                dialogInItself.close();
            }
        } ]
    });
}

function checkPost() {
    var message = $('#post-comment-message').val();
    var badStrings = [ "asshole", "bastard", "bitch", "cunt", "fuck", "nigger", "shit", "whore" ];
    var goodMessage = true;
    if( message.length === 0 ) {
        goodMessage = false;
    }
    if (new RegExp(badStrings.join("|"),'i').test(message)) {
        // At least one match
        goodMessage = false;
    }
    if( goodMessage ) {
        $('#post-comment-submit').prop("disabled", false);
        $('#post-comment-submit').removeClass("disabled");
    } else {
        $('#post-comment-submit').prop("disabled", true);
        $('#post-comment-submit').addClass("disabled");
    }
    return goodMessage;
}

function submitPost() {
    if( !checkPost() ) {
        return;
    }
    $('#post-comment-message-message').empty();
    $.post("/api/create-blog-comment.php", {
        post : $('#post-comment-submit').attr('post-id'),
        name : $('#post-comment-name').val(),
        email : $('#post-comment-email').val(),
        message : $('#post-comment-message').val()
    }).done(function(data) {
        if ($.isNumeric(data) && data !== '0') {
            var comment = {
                    id: data,
                    delete: true,
                    date: '',
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
        
    }).fail(function(xhr, status, error) {
        if ( xhr.responseText !== "" ) {
            $('#post-comment-message-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if ( error === "Unauthorized" ) {
            $('#post-comment-message-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#post-comment-message-message').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while adding your comment.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function(){
    });
}

// ////////////////////////scripts to load it all/////////////////////////
(function() {
    var po = document.createElement('script');
    po.type = 'text/javascript';
    po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(po, s);
})();

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id))
        return;
    js = d.createElement(s);
    js.id = id;
    js.src = '//connect.facebook.net/en_US/all.js#xfbml=1';
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

!function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (!d.getElementById(id)) {
        js = d.createElement(s);
        js.id = id;
        js.src = '//platform.twitter.com/widgets.js';
        fjs.parentNode.insertBefore(js, fjs);
    }
}(document, 'script', 'twitter-wjs');