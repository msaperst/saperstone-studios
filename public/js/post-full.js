function PostFull(post) {
    this.post = post;
    this.loadPosts();
    
    $('#edit-post-btn').click(function() {
        window.location.href='new.php?p=' + post;
    });
}

PostFull.prototype.loadPosts = function() {
    var PostFull = this;
    $.get("/api/get-blog-full.php", {
        post : PostFull.post,
    }, function(data) {
        // from post.js
        loadPost(data, "<h1>");
    }, "json");
};