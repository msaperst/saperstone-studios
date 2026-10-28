// The cover endpoint authorizes every request server-side. Render protected
// thumbnails as backgrounds, consistent with the existing album image grid.
function initCollectionCovers() {
    $('.album-collection-cover[data-cover-url]').each(function () {
        var cover = $(this);
        var path = cover.attr('data-cover-url');
        if (path) {
            cover.css('background-image', 'url("' + path + '")');
        }
    });
}
$(document).ready(initCollectionCovers);
