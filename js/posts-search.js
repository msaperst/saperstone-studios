$.fn.isOnScreen = function() {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Posts(columns, totalImages, searchTerm) {
    this.loaded = 0;
    this.columns = columns;
    this.totalImages = totalImages;
    this.searchTerm = searchTerm;

    this.loadImages();
}

Posts.prototype.loadImages = function() {
    var Posts = this;
    $.get("/api/get-blogs-search-details.php", {
        start : Posts.loaded,
        howMany : Posts.columns,
        searchTerm : Posts.searchTerm
    }, function(data) {
        // load each of our posts on the screen
        $.each(data, function(k, v) {
            // from post.js
            loadPostPreview(k, v);
        });
        // when we done, see if we need to load more
        if ($('footer').isOnScreen()) {
            Posts.loadImages();
        }
    }, "json");
    Posts.loaded += Posts.columns;
    return Posts.loaded;
};
