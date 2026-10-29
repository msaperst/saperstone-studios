// Shared collection dialogs for administrators and uploaders. Uses the
// existing album metadata fields, BootstrapDialog, and AJAX error patterns.
function collectionCanOrganize(data, role, userId) {
    return role === 'admin' || (role === 'uploader' &&
        Number(data.owner) === Number(userId) && !data.shared);
}

function collectionRole() {
    return typeof my_role === 'undefined' ? 'admin' : my_role;
}

function collectionActorId() {
    return typeof my_id === 'undefined' ? null : my_id;
}

function collectionError(modal, xhr, fallback) {
    modal.find('.bootstrap-dialog-body').append(
        $('<div>').addClass('alert alert-danger')
            .text(xhr && xhr.responseText ? xhr.responseText : fallback));
}

function collectionReload() {
    // The same editor is used from the management table and the collection
    // landing page. Only the former has an initialized DataTable.
    if ($('#albums').length) {
        $('#albums').DataTable().ajax.reload(null, false);
    } else {
        window.location.reload();
    }
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

function showEditCollectionDialog(data, role) {
    const currentRole = role || collectionRole();
    const canOrganize = collectionCanOrganize(data, currentRole, collectionActorId());
    const actions = [];
    if (canOrganize) {
        actions.push({
            label: 'Add New Child',
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
                collectionReload();
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
            return fields;
        },
        buttons: actions
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
});
