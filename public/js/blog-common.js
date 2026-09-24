$.fn.isOnScreen = function () {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function createPostPreviewLoader(endpoint, responseMapper) {
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
        var posts = this;

        if (posts.loading || posts.complete || posts.loaded >= posts.totalImages) {
            if (posts.loaded >= posts.totalImages) {
                posts.complete = true;
            }
            return posts.loaded;
        }

        var request = {
            start: posts.loaded,
            howMany: posts.columns
        };
        if (posts.searchTerm !== undefined) {
            request.searchTerm = posts.searchTerm;
        }

        posts.loading = true;
        $.get(endpoint, request, function (data) {
            var results = responseMapper(data);
            $.each(results, function (k, v) {
                loadPostPreview(k, v);
            });

            posts.loaded += results.length;
            posts.loading = false;

            if (results.length < posts.columns || posts.loaded >= posts.totalImages) {
                posts.complete = true;
                return;
            }

            if ($('footer').isOnScreen()) {
                posts.loadImages();
            }
        }, "json").fail(function () {
            posts.loading = false;
        });

        return posts.loaded;
    };

    return Posts;
}

function appendBlogRequestError(container, xhr, error, fallbackMessage) {
    var message = fallbackMessage;
    if (xhr && xhr.responseText) {
        message = xhr.responseText;
    } else if (error === "Unauthorized") {
        message = "Your session has timed out, and you have been logged out. Please login again, and repeat your action.";
    }

    container.append(
        "<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' " +
        "aria-label='close' title='close'>×</a>" + message + "</div>"
    );
}

function setBlogControlsDisabled(selectors, disabled) {
    if (!Array.isArray(selectors)) {
        selectors = [selectors];
    }
    $.each(selectors, function (index, selector) {
        $(selector).prop("disabled", disabled);
    });
}
