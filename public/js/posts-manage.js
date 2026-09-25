var post_table;
var resultsSelected = false;
var postManageControls = [
    '#post-update-button',
    '#post-delete-button',
    '#post-update-close-button'
];

function setPostManageControlsDisabled(disabled) {
    setBlogControlsDisabled(postManageControls, disabled);
}

$(document).ready(function () {
    if ($('#posts').length) {
        post_table = $('#posts').DataTable({
            "ajax": "/api/get-blogs-details.php?a=1",
            "order": [[2, "desc"]],
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "data": function (row) {
                    var buttons = '<button type="button" class="btn btn-xs btn-info quick-edit-post-btn" data-toggle="tooltip" data-placement="right" title="Edit Post Details"><i class="fa fa-pencil-square-o"></i></button>';
                    buttons += ' <button type="button" class="btn btn-xs btn-warning edit-post-btn" data-post-id="' + row.id + '" data-toggle="tooltip" data-placement="right" title="Edit Full Post">' + '<i class="fa fa-pencil-square-o"></i></button>';
                    return buttons;
                },
                "targets": 0
            }, {
                "data": function (row) {
                    return "<a href='/blog/post.php?p=" + row.id + "'>" + row.title + "</a>";
                },
                "className": "post-title",
                "targets": 1
            }, {
                "data": "date",
                "className": "post-date",
                "targets": 2
            }, {
                "data": function (row) {
                    return (Number(row.active) === 1) ? "true" : "false";
                },
                "className": "post-active",
                "targets": 3
            }],
            "fnCreatedRow": function (nRow, aData) {
                $(nRow).attr('post-id', aData.id);
            }
        });
    }

    $('#posts').on('draw.dt search.dt', function () {
        setupEdit();
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('#post-delete-button').click(function () {
        deleteManagedPost($('#post').attr('post-id'));
    });

    $('#post-update-button').click(function () {
        updateManagedPost($('#post').attr('post-id'));
    });
});

function setupEdit() {
    $('.edit-post-btn').off().click(function () {
        var postId = Number($(this).attr('data-post-id'));
        if (Number.isInteger(postId) && postId > 0) {
            window.location.href = '/blog/new.php?p=' + postId;
        }
    });

    $('.quick-edit-post-btn').off().click(function () {
        var post = post_table.row($(this).closest('tr')).data();
        editManagedPost(post);
    });
}

function editManagedPost(post) {
    // remove any old values
    $('#post-preview-holder img').remove();
    $('#post-preview-image option').each(function () {
        $(this).remove();
    });
    var option = $('<option>');
    $('#post-preview-image').append(option);
    $('.selected-tag').each(function () {
        removeTag($(this));
    });

    // setup our basic post information
    $('#post').attr('post-id', post.id);
    $('#post .modal-title').html("Quick Edit of <strong> " + post.title + "</strong>");
    $('#post-title-input').val(post.title);
    $('#post-date-input').val(post.date);
    $('#post-active-input').prop('checked', Number(post.active) === 1);
    // setup our preview image
    var img = $('<img>');
    img.attr('src', post.preview);
    img.css({
        width: '300px',
        top: post.offset + 'px'
    });
    $('#post-preview-holder').append(img);
    img.draggable({
        axis: "y",
    });
    $('#post').modal();
    $.get("/api/get-blog-full.php", {
        post: post.id
    }, function (data) {
        var location;
        for (var i in data.content) {
            if (data.content.hasOwnProperty(i)) {
                for (var j in data.content[i]) {
                    if (data.content[i][j].hasOwnProperty('location')) {
                        var bits = data.content[i][j].location.split('/');
                        var option = $('<option>');
                        option.text(bits.pop());
                        $('#post-preview-image').append(option);
                        location = bits.join('/');
                    }
                }
            }
        }
        $('#post').attr('post-location', location);
        for (var k in data.tags) {
            if (data.tags.hasOwnProperty(k)) {
                $('#post-tags-select').val(data.tags[k].id);
                addTag($('#post-tags-select'));
            }
        }
    }, "json");
}

function deleteManagedPost(post) {
    setPostManageControlsDisabled(true);
    BootstrapDialog.confirm("Are you sure you want to delete this post?", function (result) {
        if (result) {
            $.post("/api/delete-blog.php", {
                post: post
            }).done(function (data) {
                if (data !== "") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                } else {
                    $('#post').modal('hide');
                    post_table.row($('tr[post-id=' + post + ']')).remove().draw();
                }
            }).fail(function (xhr, status, error) {
                appendBlogRequestError(
                    $('#post .modal-body'),
                    xhr,
                    error,
                    "Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting."
                );
            }).always(function () {
                setPostManageControlsDisabled(false);
            });
        } else {
            setPostManageControlsDisabled(false);
        }
    });
}

function updateManagedPost(post) {
    setPostManageControlsDisabled(true);
    // get our updated content
    var tags = [];
    $('#post-tags span').each(function () {
        tags.push($(this).attr('tag-id'));
    });
    var preview = {};
    preview.img = $('#post-preview-image').val();
    preview.offset = $('#post-preview-holder img').css('top');
    // send the content
    $.post("/api/update-blog-post.php", {
        post: post,
        title: $('#post-title-input').val(),
        date: $('#post-date-input').val(),
        tags: tags,
        preview: preview,
        active: $('#post-active-input').is(':checked') ? 1 : 0,
    }).done(function (data) {
        if (data === "published") {
            setPostManageControlsDisabled(true);
            $.post("/api/publish-blog-post.php", {
                post: post
            }).done(function (data) {
                if (data !== "") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                } else {
                    $('#post').modal('hide');
                    post_table.ajax.reload(null, false);
                }
            }).fail(function (xhr, status, error) {
                appendBlogRequestError(
                    $('#post .modal-body'),
                    xhr,
                    error,
                    "Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting."
                );
            }).always(function () {
                setPostManageControlsDisabled(false);
            });
        } else if (data !== "") {
            $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        } else {
            $('#post').modal('hide');
            post_table.ajax.reload(null, false);
        }
    }).fail(function (xhr, status, error) {
        appendBlogRequestError(
            $('#post .modal-body'),
            xhr,
            error,
            "Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting."
        );
    }).always(function () {
        setPostManageControlsDisabled(false);
    });
}
