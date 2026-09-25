$(document).ready(function () {
    var config = $('#gallery-config');
    if (!config.length) {
        return;
    }

    var total = parseInt(config.attr('data-total'), 10) || 0;
    window.gallery = new Gallery(
        parseInt(config.attr('data-gallery-id'), 10),
        config.attr('data-modal-id'),
        total
    );

    $(window, document).on('scroll resize', function () {
        if ($('footer').isOnScreen() && window.gallery.loaded < total) {
            window.gallery.loadImages();
        }
    });

    $('#' + config.attr('data-modal-id')).carousel({interval: false, pause: 'false'});
    $('.gallery-prev').click(function () { window.gallery.prev(); });
    $('.gallery-next').click(function () { window.gallery.next(); });
});
