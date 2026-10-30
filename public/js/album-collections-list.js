// Child rows are an extension of the existing albums DataTable, never a
// replacement table. All mutation endpoints recheck ownership server-side.
// Match the existing DataTable's eight columns, rather than rendering an
// unaligned list that hides metadata when an album joins a collection.
function collectionChildItem(child, canManage) {
    const row = $('<tr>').addClass('collection-child-item')
        .attr('data-child-id', String(child.id));

    const actions = $('<td>').addClass('collection-child-actions');
    if (canManage) {
        if (collectionRole() === 'admin') {
            actions.append($('<button>')
                .attr({type: 'button', 'aria-label': 'Edit ' + child.name + ' album'})
                .addClass('btn btn-xs btn-warning collection-child-edit-album')
                .attr('data-album-id', String(child.id))
                .append($('<i>').addClass('fa fa-pencil-square-o')));
        }
        actions.append($('<span>').addClass('collection-drag-handle')
            .attr('title', 'Drag to reorder')
            .append($('<i>').addClass('fa fa-arrows-v')));
        actions.append($('<button>').attr({type: 'button', 'aria-label': 'Move ' + child.name + ' up'})
            .addClass('btn btn-xs btn-default collection-move-up').text('↑'));
        actions.append($('<button>').attr({type: 'button', 'aria-label': 'Move ' + child.name + ' down'})
            .addClass('btn btn-xs btn-default collection-move-down').text('↓'));
    }
    row.append(actions);

    row.append($('<td>').addClass('collection-child-name')
        .append($('<a>').attr('href', '/user/album.php?album=' + encodeURIComponent(String(child.id)))
            .text(child.name)));
    row.append($('<td>').addClass('collection-child-description').text(child.description || '—'));
    row.append($('<td>').addClass('collection-child-date')
        .text(child.date ? String(child.date).slice(0, 10) : '—'));
    row.append($('<td>').addClass('collection-child-count').text(String(child.imageCount)));

    const thumbnails = $('<td>').addClass('collection-child-thumbnails');
    if (canManage) {
        const status = albumThumbnailState({
            images: child.imageCount,
            thumbsCreated: child.thumbsCreated
        });
        thumbnails.append($('<span>')
            .addClass('collection-thumbnail-status label label-' + status.style)
            .text(status.label));
    }
    row.append(thumbnails);
    row.append($('<td>').addClass('collection-child-last-access')
        .text(child.lastAccessed || '—'));
    row.append($('<td>').addClass('collection-child-code')
        .text(child.code || '—'));
    return row;
}

function collectionOrderIds(list) {
    return list.children('.collection-child-item').map(function () {
        return Number($(this).attr('data-child-id'));
    }).get();
}

function saveCollectionOrder(id, list, holder) {
    $.post('/api/reorder-collection.php', {
        collection: id,
        order: JSON.stringify(collectionOrderIds(list))
    }).fail(function (xhr) {
        BootstrapDialog.alert(xhr.responseText || 'Unable to reorder galleries.');
        // Restore the authoritative order instead of leaving a misleading UI.
        renderCollectionChildren(id, holder);
    });
}

function renderCollectionChildren(id, holder) {
    $.get('/api/get-collection.php', {id: id}, function (data) {
        const allowed = collectionCanOrganize(data, collectionRole(), collectionActorId());
        const list = $('<tbody>').addClass('collection-children');
        data.children.forEach(function (child) {
            list.append(collectionChildItem(child, allowed));
        });
        const header = $('<tr>');
        ['Actions', 'Album', 'Description', 'Date', 'Photos', 'Thumbnails',
            'Last Accessed', 'Access Code'].forEach(function (label) {
            header.append($('<th>').attr('scope', 'col').text(label));
        });
        const table = $('<table>')
            .addClass('table table-condensed collection-children-table')
            .append($('<thead>').addClass('sr-only').append(header))
            .append(list);
        holder.empty().append($('<div>').addClass('table-responsive').append(table));
        if (!allowed) return;
        list.sortable({
            axis: 'y',
            handle: '.collection-drag-handle',
            update: function () { saveCollectionOrder(id, list, holder); }
        });
        if (collectionRole() === 'admin') {
            list.on('click', '.collection-child-edit-album', function () {
                editAlbum(Number($(this).attr('data-album-id')));
            });
        }
        list.on('click', '.collection-move-up, .collection-move-down', function () {
            const child = $(this).closest('.collection-child-item');
            if ($(this).hasClass('collection-move-up')) {
                const previous = child.prev('.collection-child-item');
                if (!previous.length) return;
                child.insertBefore(previous);
            } else {
                const next = child.next('.collection-child-item');
                if (!next.length) return;
                child.insertAfter(next);
            }
            saveCollectionOrder(id, list, holder);
        });
    }, 'json').fail(function (xhr) {
        holder.empty().append($('<div>').addClass('alert alert-danger')
            .text(xhr.responseText || 'Unable to load collection.'));
    });
}

$(document).ready(function () {
    $('#albums').on('click', '.collection-expand-btn', function () {
        const row = $(this).closest('tr');
        const item = $('#albums').DataTable().row(row);
        if (item.child.isShown()) {
            item.child.hide();
            row.removeClass('shown');
            return;
        }
        const holder = $('<div>').addClass('collection-child-holder');
        item.child(holder).show();
        row.addClass('shown');
        renderCollectionChildren(row.attr('album-id'), holder);
    });
});
