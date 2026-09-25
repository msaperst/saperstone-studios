$(document).ready(function () {
    var config = $('#blog-page-config');
    if (!config.length) {
        return;
    }

    var type = config.attr('data-loader');
    var total = parseInt(config.attr('data-total'), 10) || 0;
    var loader;

    if (type === 'posts') {
        loader = new Posts(parseInt(config.attr('data-columns'), 10) || 3, total, config.attr('data-search') || undefined);
    } else if (type === 'posts-full') {
        var tag = config.attr('data-tags');
        loader = new PostsFull(total, tag ? JSON.parse(tag) : undefined);
    } else if (type === 'post-full') {
        new PostFull(parseInt(config.attr('data-post'), 10));
        return;
    }

    $(window, document).on('scroll resize', function () {
        if (!$('footer').isOnScreen()) {
            return;
        }
        if (type === 'posts-full') {
            loader.loadPosts();
        } else {
            loader.loadImages();
        }
    });
});
