function PostsFull(totalPosts, tag) {
    this.loaded = 0;
    this.loading = false;
    this.complete = totalPosts === 0;
    this.totalPosts = totalPosts;
    this.tag = tag;

    this.loadPosts();
}

PostsFull.prototype.loadPosts = function () {
    var postsFull = this;

    if (postsFull.loading || postsFull.complete || postsFull.loaded >= postsFull.totalPosts) {
        if (postsFull.loaded >= postsFull.totalPosts) {
            postsFull.complete = true;
        }
        return postsFull.loaded;
    }

    postsFull.loading = true;
    $.get("/api/get-blogs-full.php", {
        start: postsFull.loaded,
        tag: postsFull.tag
    }, function (data) {
        // from post.js
        loadPost(data, "<h2>");

        postsFull.loaded++;
        postsFull.loading = false;

        if (postsFull.loaded >= postsFull.totalPosts) {
            postsFull.complete = true;
            return;
        }

        if ($('footer').isOnScreen()) {
            postsFull.loadPosts();
        }
    }, "json").fail(function () {
        postsFull.loading = false;
    });

    return postsFull.loaded;
};
