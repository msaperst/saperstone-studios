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
    var link = '/blog/post.php?p=' + data.id;
    
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
    var details_likes = $('<div>');
    details_likes.addClass('col-md-4 text-right');

    // our facebook likes button
    var facebook = $('<div>');
    facebook.addClass('fbook');
    var facebook_div = $('<div>');
    facebook_div.addClass('fb-like col-md-4 text-left');
    facebook_div.attr({
        "data-href" : link,
        "data-send" : "false",
        "data-layout" : "button_count",
        "data-show-faces" : "false"
    });
    facebook.append(facebook_div);
    details_likes.append(facebook);

    var twitter = $('<div>');
    twitter.addClass('tweet col-md-4 text-center');
    var twitter_a = $('<a>');
//    twitter_a.addClass('twitter-share-button');
//    twitter_a.attr({
//        "href" : "https://twitter.com/share",
//        "data-url" : link,
//        "data-text" : data.title,
//        "data-via" : "LASaperstone",
//        "data-lang" : "en",
//        "data-hashtags" : "SaperstoneStudios"
//    });
//    twitter_a.html("Tweet");
    
//    twitter_a.addClass('twitter-share-button');
    twitter_a.attr({
        "href": "https://twitter.com/intent/like?tweet_id=" + data.twitter
    });
    twitter_a.css({
        "background-image": "url('/img/twitter.png')",
        "background-position": "center",
//        "background-size": "20px",
        "background-repeat": "no-repeat",
        "color": "transparent",
    });
    twitter_a.html("LikeLike");
    
    twitter.append(twitter_a);
    details_likes.append(twitter);

    var gplus = $('<div>');
    gplus.addClass('gplus col-md-4 text-right');
    var gplus_div = $('<div>');
    gplus_div.addClass('g-plusone');
    gplus_div.attr({
        "data-size" : "small",
        "data-href" : link
    });
    gplus.append(gplus_div);
    details_likes.append(gplus);

    details_row.append(details_likes);
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
    loadSM();
}

function loadSM() {
    try {
        FB.XFBML.parse();
        twttr.widgets.load();
        gapi.plusone.go();
    } catch (err) {
        setTimeout(loadSM, 100);
    }
}

//////////////////////////scripts to load it all/////////////////////////
(function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = '//connect.facebook.net/en_US/all.js#xfbml=1';
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

!function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if(!d.getElementById(id)) {
        js=d.createElement(s);
        js.id=id;js.src='//platform.twitter.com/widgets.js';
        fjs.parentNode.insertBefore(js,fjs);
    }
}(document,'script','twitter-wjs');