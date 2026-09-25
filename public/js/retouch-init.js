$(document).ready(function () {
    const config = $('#retouch-config');
    if (!config.length) {
        return;
    }

    const images = JSON.parse(config.attr('data-images') || '[]');
    window.retouch = new Retouch($('#holder'), images, config.attr('data-instructions') !== 'false');
});
