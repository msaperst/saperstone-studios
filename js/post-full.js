function PostFull(post) {
    this.post = post;
    this.loadPosts();
}

PostFull.prototype.loadPosts = function() {
    var PostFull = this;
    $.get("/api/get-blog-full.php", {
        post : PostFull.post,
    }, function(data) {
        // load our post on the screen
        // set our title
        $('h1').html(data.title);
        $('#breadcrumb-title').html(data.title);
        // set some more details
        $.each(data.tags, function(k, v) {
            var tag_link = $('<a>');
            tag_link.attr('href', '/blog/category.php?t=' + v.id);
            tag_link.html(v.tag);
            $('#post-tags').append(tag_link);
            if (k < data.tags.length - 1) {
                $('#post-tags').append(", ");
            }
        });
        $('#post-date').html(data.date);

        // set our post content
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
                    max_height = Math.max(max_height,
                            (parseFloat(w.top) + parseFloat(w.height)));
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
                image.attr('src','/img/image.png');
                protect.append(image);
                row.append(protect);
            }
            row.append(content);
            $('#post-content').append(row);
        });
    }, "json");
};