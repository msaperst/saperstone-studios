$(document).ready(function () {
    var config = $('#retouch-config');
    if (!config.length) {
        return;
    }

    var images = JSON.parse(config.attr('data-images') || '[]');
    window.retouch = new Retouch($('#holder'), images, config.attr('data-instructions') !== 'false');
});
