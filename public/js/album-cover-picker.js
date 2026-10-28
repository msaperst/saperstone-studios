// Shared protected existing-image picker for individual albums.
// All candidates and images go through server-authorized collection endpoints.
// No duplicate images, direct originals, or public upload/crop routes.
function saveAlbumCoverChoice(albumId, imageId, dialog) {
    $.post('/api/set-album-cover.php', {
        album: albumId,
        image: imageId
    }).done(function () {
        dialog.close();
        if ($('#albums').length) {
            $('#albums').DataTable().ajax.reload(null, false);
        }
    }).fail(function (xhr) {
        const message = xhr.responseText || 'Unable to save the selected cover.';
        $('#album-cover-error').text(message).show();
    });
}

function loadCoverCandidatePage(albumId, start, dialog) {
    $.get('/api/get-cover-candidates.php', {
        album: albumId,
        start: start,
        count: 36
    }, function (data) {
        const holder = $('#album-cover-options');
        holder.find('.album-cover-more').remove();
        data.candidates.forEach(function (candidate) {
            if (candidate.previewAvailable === false) {
                holder.append($('<span>').addClass('album-cover-unavailable')
                    .text('Generate thumbnails to preview this photo'));
                return;
            }
            const button = $('<button>').attr({
                type: 'button',
                'aria-label': 'Use image from ' + candidate.galleryName + ' as cover'
            }).addClass('album-cover-option btn btn-default')
                .css('background-image', 'url("' + candidate.preview + '")');
            button.click(function () {
                saveAlbumCoverChoice(albumId, candidate.id, dialog);
            });
            holder.append(button);
        });
        if (data.nextStart !== null) {
            const more = $('<button>').attr('type', 'button')
                .addClass('album-cover-more btn btn-default')
                .text('Load More Photos');
            more.click(function () {
                more.prop('disabled', true);
                loadCoverCandidatePage(albumId, data.nextStart, dialog);
            });
            holder.append(more);
        }
    }, 'json').fail(function (xhr) {
        $('#album-cover-error').text(xhr.responseText || 'Unable to load cover options.').show();
    });
}

function openAlbumCoverPicker(albumId) {
    BootstrapDialog.show({
        draggable: true,
        size: BootstrapDialog.SIZE_WIDE,
        title: 'Choose Album Cover',
        message: function () {
            const content = $('<div>');
            content.append($('<p>').addClass('text-muted')
                .text('Choose a photo already in this gallery. Missing previews require thumbnail generation.'));
            content.append($('<div>').attr('id', 'album-cover-error')
                .addClass('alert alert-danger').hide());
            content.append($('<div>').attr('id', 'album-cover-options')
                .addClass('album-cover-options'));
            return content;
        },
        onshown: function (dialog) {
            loadCoverCandidatePage(albumId, 0, dialog);
        },
        buttons: [{
            label: 'Use Automatic',
            cssClass: 'btn-default',
            action: function (dialog) { saveAlbumCoverChoice(albumId, '', dialog); }
        }, {
            label: 'Close',
            action: function (dialog) { dialog.close(); }
        }]
    });
}
