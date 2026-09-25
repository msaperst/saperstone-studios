$(document).ready(function () {
    var config = $('#post-editor-config');
    if (!config.length) {
        return;
    }

    var tags = JSON.parse(config.attr('data-tags') || '[]');
    $.each(tags, function (index, tag) {
        $('#post-tags-select').val(tag);
        addTag($('#post-tags-select'));
    });

    var groups = JSON.parse(config.attr('data-groups') || '[]');
    if (!groups.length) {
        addImageArea();
    } else {
        $.each(groups, function (index, group) {
            if (group.type === 'text') {
                addTextArea(group.text);
            } else if (group.type === 'images') {
                addImageArea(group.images);
            }
        });
    }

    $('#post-preview-holder img').draggable({axis: 'y'});
});
