var gallery

$.fn.isOnScreen = function () {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Gallery(gallery_id, gallery, totalImages) {
    var Gallery = this;

    Gallery.loaded = 0;
    Gallery.gallery_id = gallery_id;
    Gallery.gallery = gallery;
    Gallery.totalImages = totalImages;

    Gallery.loadImages();

    if (window.location.hash && window.location.hash.length > 1) {
        Gallery.setImage(window.location.hash.substr(1));
    }

    window.onhashchange = function () {
        if (window.location.hash.length > 1) {
            Gallery.setImage(window.location.hash.substr(1));
        }
    };

    $('#' + Gallery.gallery).on('hide.bs.modal', function () {
        window.location.hash = "";
    });
}

Gallery.prototype.setImage = function (img) {
    var Gallery = this;

    $('#' + Gallery.gallery).modal('show');
    $('#' + Gallery.gallery).carousel({
        interval: false,
        pause: "false",
    });
    $('#' + Gallery.gallery).carousel(parseInt(img));
}

Gallery.prototype.prev = function () {
    var Gallery = this;

    var prev = parseInt(parseInt(window.location.hash.substring(1)) - 1);
    if (prev < 0) {
        prev = (parseInt(Gallery.totalImages) - 1);
    }
    window.location.hash = "#" + prev;
}

Gallery.prototype.next = function () {
    var Gallery = this;

    var next = parseInt(parseInt(window.location.hash.substring(1)) + 1);
    if (next >= Gallery.totalImages) {
        next = 0;
    }
    window.location.hash = "#" + next;
}

Gallery.prototype.loadImages = function (howMany) {
    howMany = typeof howMany !== 'undefined' ? howMany : 4;

    var Gallery = this;
    $.get("/api/get-gallery-images.php", {
        gallery: Gallery.gallery_id,
        start: Gallery.loaded,
        howMany: howMany
    }, function (data) {
        // load each of our 4 images on the screen
        $.each(data, function (k, v) {
            var shortest = {};
            shortest.height = 999999999;
            $('.col-gallery').each(function () {
                if ($(this).height() < shortest.height) {
                    shortest.obj = $(this);
                    shortest.height = $(this).height();
                }
            });
            // create our holding div
            var holder = $('<div>');
            holder.addClass('gallery hovereffect');
            holder.attr({
                'image-id': v.id,
                'sequence': v.sequence
            });
            var rect = shortest.obj[0].getBoundingClientRect();
            var width;
            // `width` is available for IE9+
            if (rect.width) {
                width = rect.width;
                // Calculate width for IE8 and below
            } else {
                width = rect.right - rect.left;
            }
            // Remove the padding width
            width -= (parseInt(shortest.obj.css("padding-left")) + parseInt(shortest.obj.css("padding-right")));
            holder.height(parseInt(v.height * width / v.width));
            // create our image
            var img = $('<img>');
            img.attr('src', v.location);
            img.attr('alt', v.title);
            img.attr('width', '100%');
            // create our overlay
            var overlay = $('<div>');
            overlay.addClass('overlay');
            // our view link
            var link = $('<a>');
            link.addClass('info no-border');
            link.attr('href', '#' + parseInt(v.sequence));
            // add our image icon
            var view = $('<i>');
            view.addClass('fa fa-search fa-2x');
            // put them all together
            link.append(view);
            overlay.append(link);
            holder.append(img);
            holder.append(overlay);
            shortest.obj.append(holder);
        });
        // when we done, see if we need to load more
        if ($('footer').isOnScreen() && Gallery.loaded < Gallery.totalImages) {
            Gallery.loadImages();
        }
        if (data.length < 4) {
            Gallery.loaded = Gallery.loaded - howMany + data.length;
        }
    }, "json");
    Gallery.loaded += howMany;
    return Gallery.loaded;
};
