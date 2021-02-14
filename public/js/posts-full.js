$.fn.isOnScreen = function () {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function PostsFull(totalPosts, tag) {
    this.loaded = 0;
    this.totalPosts = totalPosts;
    this.tag = tag;

    this.loadPosts();
}

PostsFull.prototype.loadPosts = function () {
    var PostsFull = this;
    $.get("/api/get-blogs-full.php", {
        start: PostsFull.loaded,
        tag: PostsFull.tag
    }, function (data) {
        // from post.js
        loadPost(data, "<h2>");

        // when we done, see if we need to load more
        if ($('footer').isOnScreen() && Posts.loaded < Posts.totalImages) {
            PostsFull.loadPosts();
        }
    }, "json");
    PostsFull.loaded++;
    return PostsFull.loaded;
};