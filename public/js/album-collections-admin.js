// Shared collection dialogs for administrators and uploaders. Uses the
// existing album metadata fields, BootstrapDialog, and AJAX error patterns.
function collectionCanOrganize(data, role, userId) {
    return role === 'admin' || (role === 'uploader' &&
        Number(data.owner) === Number(userId) && !data.shared);
}

function collectionRole() {
    const role = $('#collection-user-context').attr('data-role');
    if (role) {
        return role;
    }
    return typeof my_role === 'undefined' ? 'admin' : my_role;
}

function collectionActorId() {
    const id = $('#collection-user-context').attr('data-id');
    if (id) {
        return Number(id);
    }
    return typeof my_id === 'undefined' ? null : my_id;
}

function collectionError(modal, xhr, fallback) {
    modal.find('.bootstrap-dialog-body').append(
        $('<div>').addClass('alert alert-danger')
            .text(xhr && xhr.responseText ? xhr.responseText : fallback));
}

function collectionReload() {
    // Membership changes affect the cards, so refresh the collection page
    // when necessary; metadata saves update in place separately below.
    if ($('#albums').length) {
        $('#albums').DataTable().ajax.reload(null, false);
    } else {
        window.location.reload();
    }
}

function updateCollectionPageDetails(fields) {
    if (!$('#collection-page-name').length) {
        collectionReload();
        return;
    }
    $('#collection-page-name').text(fields.name);
    $('#collection-breadcrumb-name').text(fields.name);
    $('#collection-page-description').text(fields.description)
        .toggleClass('hidden', !fields.description);
    $('#collection-page-date').text(fields.date ? String(fields.date).slice(0, 10) : '')
        .toggleClass('hidden', !fields.date);
}

function saveCollectionMemberOrder(id, orderedIds, onSuccess, onError) {
    $.post('/api/reorder-collection.php', {
        collection: id,
        order: JSON.stringify(orderedIds)
    }).done(onSuccess).fail(function (xhr) {
        onError(xhr.responseText || 'Unable to save album order.');
    });
}

function currentCollectionMemberIds(list) {
    return list.children('.collection-member-row').map(function () {
        return Number($(this).attr('data-child-id'));
    }).get();
}

function restoreCollectionMemberOrder(list, children) {
    children.forEach(function (child) {
        list.append(list.find('.collection-member-row[data-child-id="' + Number(child.id) + '"]'));
    });
}

function applyCollectionCardOrder(ids) {
    const holder = $('#collection-galleries');
    if (!holder.length) {
        collectionReload();
        return;
    }
    ids.forEach(function (id) {
        holder.append(holder.find('.album-collection-card[data-child-album-id="' + Number(id) + '"]'));
    });
}

function persistCollectionMemberOrder(data, list) {
    const ids = currentCollectionMemberIds(list);
    if (ids.every(function (id, index) { return Number(data.children[index].id) === id; })) {
        return;
    }
    saveCollectionMemberOrder(data.id, ids, function () {
        const byId = new Map(data.children.map(function (child) { return [Number(child.id), child]; }));
        data.children = ids.map(function (id) { return byId.get(id); });
        applyCollectionCardOrder(ids);
    }, function (message) {
        restoreCollectionMemberOrder(list, data.children);
        BootstrapDialog.alert(message);
    });
}


function openCreateCollectionDialog() {
    BootstrapDialog.show({
        draggable: true,
        title: 'Add A New Collection',
        message: function () {
            return albumMetadataInputs('new-collection', 'Collection', {});
        },
        buttons: [{
            label: 'Create Collection',
            cssClass: 'btn-success',
            action: function (dialog) {
                const button = this;
                button.spin();
                dialog.enableButtons(false);
                $.post('/api/create-collection.php', {
                    name: $('#new-collection-name').val(),
                    description: $('#new-collection-description').val(),
                    date: $('#new-collection-date').val()
                }).done(function (id) {
                    if ($.isNumeric(id) && Number(id) > 0) {
                        collectionReload();
                        dialog.close();
                    } else {
                        collectionError(button.closest('.modal-content'), null, 'Unable to create collection.');
                    }
                }).fail(function (xhr) {
                    collectionError(button.closest('.modal-content'), xhr, 'Unable to create collection.');
                }).always(function () {
                    button.stopSpin();
                    dialog.enableButtons(true);
                });
            }
        }, {
            label: 'Cancel',
            action: function (dialog) { dialog.close(); }
        }]
    });
}

function confirmDeleteCollection(data, editDialog) {
    const count = data.children.length;
    BootstrapDialog.show({
        draggable: true,
        title: 'Delete Collection?',
        message: 'Delete "' + escapeAlbumListHtml(data.name) + '"? ' +
            count + ' child albums will remain as standalone albums. ' +
            'All photos and individual album codes will be preserved, but inherited access ' +
            'will end; some albums may become difficult for clients to find.',
        buttons: [{
            label: 'Delete Collection',
            cssClass: 'btn-danger',
            action: function (dialog) {
                const button = this;
                button.spin();
                dialog.enableButtons(false);
                $.post('/api/delete-collection.php', {id: data.id}).done(function () {
                    dialog.close();
                    if (editDialog) editDialog.close();
                    if ($('#albums').length) {
                        collectionReload();
                    } else {
                        // The current URL is no longer valid after deletion.
                        window.location.assign('/user/index.php');
                    }
                }).fail(function (xhr) {
                    collectionError(button.closest('.modal-content'), xhr, 'Unable to delete collection.');
                }).always(function () {
                    button.stopSpin();
                    dialog.enableButtons(true);
                });
            }
        }, {
            label: 'Cancel',
            action: function (dialog) { dialog.close(); }
        }]
    });
}

function openAttachAlbumDialog(collection) {
    if ($('#albums').length) {
        showAttachableAlbums(collection, $('#albums').DataTable().rows().data().toArray());
        return;
    }
    // The collection landing page has no DataTable; use its existing
    // authorized album-list API to populate exactly the same picker.
    $.get('/api/get-albums.php', function (result) {
        showAttachableAlbums(collection, result.data);
    }, 'json').fail(function (xhr) {
        BootstrapDialog.alert(xhr.responseText || 'Unable to load available albums.');
    });
}

function showAttachableAlbums(collection, albums) {
    const rows = albums.filter(function (row) {
        return !row.isCollection && !row.parent &&
            (collectionRole() === 'admin' || Number(row.owner) === Number(collectionActorId()));
    });
    BootstrapDialog.show({
        draggable: true,
        title: 'Attach Existing Album',
        message: function () {
            const holder = $('<div>');
            holder.append($('<label>').attr('for', 'collection-attach-album').text('Choose an album'));
            const select = $('<select>').attr('id', 'collection-attach-album').addClass('form-control');
            rows.forEach(function (row) {
                select.append($('<option>').val(String(row.id)).text(row.name));
            });
            holder.append(select);
            return holder;
        },
        buttons: [{
            label: 'Attach Album',
            cssClass: 'btn-success',
            action: function (dialog) {
                const button = this;
                button.spin();
                dialog.enableButtons(false);
                $.post('/api/set-album-parent.php', {
                    album: $('#collection-attach-album').val(),
                    parent: collection.id
                }).done(function () {
                    collectionReload();
                    dialog.close();
                }).fail(function (xhr) {
                    collectionError(button.closest('.modal-content'), xhr, 'Unable to attach album.');
                }).always(function () {
                    button.stopSpin();
                    dialog.enableButtons(true);
                });
            }
        }, {
            label: 'Cancel',
            action: function (dialog) { dialog.close(); }
        }]
    });
}


// Use the same collection payload and escaping everywhere. Detaching only
// clears album membership; it must never call the album deletion endpoint.
function collectionMembersMarkup(data, canOrganize) {
    const children = data.children || [];
    const rows = children.map(function (child) {
        const id = Number(child.id);
        const name = escapeAlbumListHtml(child.name);
        const count = Number(child.imageCount);
        const reorder = canOrganize
            ? '<span class="collection-member-drag-handle" title="Drag to reorder" aria-hidden="true">☷</span>' +
              '<button type="button" class="btn btn-xs btn-default collection-member-move-up" ' +
              'aria-label="Move ' + name + ' up">↑</button>' +
              '<button type="button" class="btn btn-xs btn-default collection-member-move-down" ' +
              'aria-label="Move ' + name + ' down">↓</button>'
            : '';
        const remove = canOrganize
            ? '<button type="button" class="btn btn-xs btn-default collection-member-remove" ' +
              'data-child-id="' + id + '" aria-label="Remove ' + name +
              ' from collection">Remove from collection</button>'
            : '';
        return '<div class="list-group-item collection-member-row" data-child-id="' + id + '">' +
            reorder + '<a href="/user/album.php?album=' + id + '">' + name + '</a>' +
            '<span class="text-muted"> — ' + count + ' photos</span>' + remove + '</div>';
    }).join('');
    return '<div class="collection-editor-members">' +
        '<h4>Albums in this collection (' + children.length + ')</h4>' +
        '<div id="collection-members" class="list-group">' +
        (rows || '<p class="text-muted">No albums in this collection yet.</p>') +
        '</div></div>';
}

function confirmRemoveFromCollection(collection, child, onSuccess) {
    BootstrapDialog.show({
        draggable: true,
        title: 'Remove Album from Collection?',
        message: 'Remove "' + escapeAlbumListHtml(child.name) + '" from "' +
            escapeAlbumListHtml(collection.name) + '"? The album and all of its photos, ' +
            'favorites and individual code will remain available as a standalone album. ' +
            'Any inherited access through this collection will end.',
        buttons: [{
            label: 'Remove from Collection',
            cssClass: 'btn-warning',
            action: function (dialog) {
                const button = this;
                button.spin();
                dialog.enableButtons(false);
                $.post('/api/set-album-parent.php', {
                    album: child.id, parent: ''
                }).done(function () {
                    dialog.close();
                    onSuccess();
                }).fail(function (xhr) {
                    collectionError(button.closest('.modal-content'), xhr, 'Unable to remove album from collection.');
                }).always(function () {
                    button.stopSpin();
                    dialog.enableButtons(true);
                });
            }
        }, {
            label: 'Cancel',
            action: function (dialog) { dialog.close(); }
        }]
    });
}

function showEditCollectionDialog(data, role) {
    const currentRole = role || collectionRole();
    const canOrganize = collectionCanOrganize(data, currentRole, collectionActorId());
    const actions = [];
    if (canOrganize) {
        actions.push({
            label: 'Add New Album',
            cssClass: 'btn-info',
            action: function () { openCreateAlbumDialog(data.id); }
        }, {
            label: 'Attach Existing Album',
            cssClass: 'btn-info',
            action: function () { openAttachAlbumDialog(data); }
        }, {
            label: 'Delete Collection',
            cssClass: 'btn-danger',
            action: function (dialog) { confirmDeleteCollection(data, dialog); }
        });
    }
    actions.push({
        label: 'Save Details',
        cssClass: 'btn-success',
        action: function (dialog) {
            const button = this;
            button.spin();
            dialog.enableButtons(false);
            const fields = {
                id: data.id,
                name: $('#edit-collection-name').val(),
                description: $('#edit-collection-description').val(),
                date: $('#edit-collection-date').val()
            };
            if (currentRole === 'admin') {
                fields.code = $('#collection-code').val();
            }
            $.post('/api/update-collection.php', fields).done(function () {
                updateCollectionPageDetails(fields);
                dialog.close();
            }).fail(function (xhr) {
                collectionError(button.closest('.modal-content'), xhr, 'Unable to save collection.');
            }).always(function () {
                button.stopSpin();
                dialog.enableButtons(true);
            });
        }
    }, {
        label: 'Close',
        action: function (dialog) { dialog.close(); }
    });
    BootstrapDialog.show({
        draggable: true,
        size: BootstrapDialog.SIZE_WIDE,
        cssClass: 'album-management-dialog',
        title: 'Edit Collection',
        message: function () {
            let fields = albumMetadataInputs('edit-collection', 'Collection', data);
            if (currentRole === 'admin') {
                fields += '<input id="collection-code" aria-label="Access Code" ' +
                    'placeholder="Access Code" class="form-control" type="text" value="' +
                    escapeAlbumListHtml(data.code || '') + '"/>';
            }
            if (data.shared && currentRole !== 'admin') {
                fields += '<p class="text-muted">Only administrators may reorganize a shared collection.</p>';
            }
            return fields + collectionMembersMarkup(data, canOrganize);
        },
        buttons: actions,
        onshown: function (dialog) {
            if (!canOrganize) {
                return;
            }
            const list = dialog.getModal().find('#collection-members');
            if ((data.children || []).length > 1) {
                list.sortable({
                    axis: 'y',
                    handle: '.collection-member-drag-handle',
                    update: function () { persistCollectionMemberOrder(data, list); }
                });
            }
            dialog.getModal().on('click', '.collection-member-move-up, .collection-member-move-down', function () {
                const member = $(this).closest('.collection-member-row');
                if ($(this).hasClass('collection-member-move-up')) {
                    const previous = member.prev('.collection-member-row');
                    if (!previous.length) return;
                    member.insertBefore(previous);
                } else {
                    const next = member.next('.collection-member-row');
                    if (!next.length) return;
                    member.insertAfter(next);
                }
                persistCollectionMemberOrder(data, list);
            });
            dialog.getModal().on('click', '.collection-member-remove', function () {
                const childId = Number($(this).attr('data-child-id'));
                const child = (data.children || []).find(function (entry) {
                    return Number(entry.id) === childId;
                });
                if (!child) {
                    return;
                }
                confirmRemoveFromCollection(data, child, function () {
                    // Reuse the existing list/page refresh and authorization
                    // logic; the album survives as a standalone entry.
                    collectionReload();
                    dialog.close();
                });
            });
        }
    });
}

function editCollection(id) {
    $.get('/api/get-collection.php', {id: id}, function (data) {
        showEditCollectionDialog(data);
    }, 'json').fail(function (xhr) {
        BootstrapDialog.alert(xhr.responseText || 'Unable to load collection.');
    });
}

$(document).ready(function () {
    $('#add-collection-btn').click(openCreateCollectionDialog);
    $('#edit-collection-btn').click(function () {
        editCollection(Number($(this).attr('data-collection-id')));
    });
    $(document).on('click', '.collection-edit-album-btn', function () {
        editAlbum(Number($(this).attr('data-album-id')));
    });
});
