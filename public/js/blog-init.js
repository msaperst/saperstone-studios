$(document).ready(function () {
    const config = $('#blog-page-config');
    if (!config.length) {
        return;
    }

    const type = config.attr('data-loader');
    const total = Number.parseInt(config.attr('data-total'), 10) || 0;
    let loader;

    if (type === 'posts') {
        loader = new Posts(Number.parseInt(config.attr('data-columns'), 10) || 3, total, config.attr('data-search') || undefined);
    } else if (type === 'posts-full') {
        const tag = config.attr('data-tags');
        loader = new PostsFull(total, tag ? JSON.parse(tag) : undefined);
    } else if (type === 'post-full') {
        window.post = new PostFull(Number.parseInt(config.attr('data-post'), 10));
        return;
    } else {
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
