// Child rows are an extension of the existing albums DataTable, never a
// replacement table. All mutation endpoints recheck ownership server-side.
function collectionChildItem(child, canManage) {
    const item = $('<li>').addClass('list-group-item collection-child-item')
        .attr('data-child-id', String(child.id));
    if (canManage) {
        item.append($('<span>').addClass('collection-drag-handle')
            .attr('title', 'Drag to reorder')
            .append($('<i>').addClass('fa fa-arrows-v')));
    }
    item.append($('<a>').attr('href', '/user/album.php?album=' + encodeURIComponent(String(child.id)))
        .text(child.name));
    item.append($('<span>').addClass('text-muted').text(' — ' + child.imageCount + ' photos'));
    if (canManage) {
        const status = albumThumbnailState({
            images: child.imageCount,
            thumbsCreated: child.thumbsCreated
        });
        item.append($('<span>')
            .addClass('collection-thumbnail-status label label-' + status.style)
            .text(status.label));
    }
    if (canManage) {
        item.append($('<button>').attr({type: 'button', 'aria-label': 'Move ' + child.name + ' up'})
            .addClass('btn btn-xs btn-default collection-move-up').text('↑'));
        item.append($('<button>').attr({type: 'button', 'aria-label': 'Move ' + child.name + ' down'})
            .addClass('btn btn-xs btn-default collection-move-down').text('↓'));
        item.append($('<button>').attr({type: 'button', 'aria-label': 'Detach ' + child.name})
            .addClass('btn btn-xs btn-default collection-detach').text('Remove from collection'));
    }
    return item;
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
        const list = $('<ul>').addClass('list-group collection-children');
        data.children.forEach(function (child) {
            list.append(collectionChildItem(child, allowed));
        });
        holder.empty().append(list);
        if (!allowed) return;
        list.sortable({
            axis: 'y',
            handle: '.collection-drag-handle',
            update: function () { saveCollectionOrder(id, list, holder); }
        });
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
        list.on('click', '.collection-detach', function () {
            const child = $(this).closest('.collection-child-item');
            $.post('/api/set-album-parent.php', {
                album: child.attr('data-child-id'),
                parent: ''
            }).done(function () {
                collectionReload();
                renderCollectionChildren(id, holder);
            }).fail(function (xhr) {
                BootstrapDialog.alert(xhr.responseText || 'Unable to detach gallery.');
            });
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
