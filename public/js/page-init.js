$(document).ready(function () {
    $('.carousel').each(function () {
        var carousel = $(this);
        if (!carousel.find('.item').length) {
            return;
        }
        carousel.carousel({
            interval: 4000,
            duration: 2000
        });
    });

    $('[data-toggle="tooltip"]').tooltip();

    $('[data-hash]').click(function () {
        window.location.hash = $(this).attr('data-hash');
    });

    $('[data-href]').click(function () {
        window.location.href = $(this).attr('data-href');
    });
});
