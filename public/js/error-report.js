$(document).ready(function () {
    $('#error-back-link').click(function (event) {
        event.preventDefault();
        window.history.back();
    });

    var config = $('#error-report-config');
    if (!config.length) {
        return;
    }

    $.post('/api/send-error.php', {
        error: config.attr('data-error'),
        page: config.attr('data-page'),
        referrer: config.attr('data-referrer'),
        resolution: screen.width + 'x' + screen.height
    });
});
