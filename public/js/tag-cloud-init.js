$(document).ready(function () {
    var config = $('#tag-cloud-config');
    if (!config.length) {
        return;
    }
    $('#tag-cloud').jQCloud(JSON.parse(config.attr('data-tags') || '[]'));
});
