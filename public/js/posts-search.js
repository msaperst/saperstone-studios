$.fn.isOnScreen = function () {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Posts(columns, totalImages, searchTerm) {
    this.loaded = 0;
    this.loading = false;
    this.complete = totalImages === 0;
    this.columns = columns;
    this.totalImages = totalImages;
    this.searchTerm = searchTerm;

    this.loadImages();
}

Posts.prototype.loadImages = function () {
    var Posts = this;

    if (Posts.loading || Posts.complete || Posts.loaded >= Posts.totalImages) {
        if (Posts.loaded >= Posts.totalImages) {
            Posts.complete = true;
        }
        return Posts.loaded;
    }

    Posts.loading = true;
    $.get("/api/get-blogs-search-details.php", {
        start: Posts.loaded,
        howMany: Posts.columns,
        searchTerm: Posts.searchTerm
    }, function (data) {
        // load each of our posts on the screen
        $.each(data, function (k, v) {
            // from post.js
            loadPostPreview(k, v);
        });

        Posts.loaded += data.length;
        Posts.loading = false;

        // A short/empty page means the server has no more matching posts.
        if (data.length < Posts.columns || Posts.loaded >= Posts.totalImages) {
            Posts.complete = true;
            return;
        }

        // when we done, see if we need to load more
        if ($('footer').isOnScreen()) {
            Posts.loadImages();
        }
    }, "json").fail(function () {
        Posts.loading = false;
    });

    return Posts.loaded;
};
