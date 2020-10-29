$.fn.isOnScreen = function() {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Posts(columns, totalImages) {
    this.loaded = 0;
    this.columns = columns;
    this.totalImages = totalImages;

    this.loadImages();
}

Posts.prototype.loadImages = function() {
    var Posts = this;
    $.get("/api/get-blogs-details.php", {
        start : Posts.loaded,
        howMany : Posts.columns
    }, function(data) {
        data = data.data;
        // load each of our 4 images on the screen
        $.each(data, function(k, v) {
            // from post.js
            loadPostPreview(k, v);
        });
        // when we done, see if we need to load more
        if ($('footer').isOnScreen() && Posts.loaded < Posts.totalImages) {
            Posts.loadImages();
        }
    }, "json");
    Posts.loaded += Posts.columns;
    return Posts.loaded;
};
