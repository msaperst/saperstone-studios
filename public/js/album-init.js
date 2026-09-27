$(document).ready(function () {
    const config = $('#album-page-config');
    if (!config.length) {
        return;
    }

    window.albumCanDownload = config.attr('data-can-download') === 'true';
    window.showImageTitle = config.attr('data-show-title') === 'true';
    window.album = new Album(config.attr('data-album-id'), 4, Number.parseInt(config.attr('data-total'), 10) || 0);

    const $window = $(window);
    const $breadcrumb = $('.breadcrumb');
    const $logo1 = $('#nav-logo-link-1');
    const $logo2 = $('#nav-logo-link-2');
    const initialBreadcrumbTop = $breadcrumb.offset().top;

    $window.on('scroll resize', function () {
        window.album.loadImages();

        const navbarBottom = $('.navbar-fixed-top').outerHeight();
        const effectiveFixedTop = Math.max(80, navbarBottom);

        if ($window.scrollTop() > (initialBreadcrumbTop - effectiveFixedTop)) {
            $breadcrumb.addClass('breadcrumb-fixed').css('top', effectiveFixedTop + 'px');
            $('.breadcrumb-fixed').css('width', $('.page-header').width() + 'px');
            $logo1.hide();
            $logo2.hide();
        } else {
            $breadcrumb.removeClass('breadcrumb-fixed').css('top', '');
            $logo1.show();
            $logo2.show();
        }
    });
});
