var post_table;
var resultsSelected = false;

$(document).ready(function() {
    if ($('#posts').length) {
        post_table = $('#posts').DataTable({
            "ajax" : "/api/get-blogs-details.php?a=1",
            "order" : [ [ 2, "desc" ] ],
            "columnDefs" : [ {
                "orderable" : false,
                "searchable" : false,
                "data" : function(row) {
                    var buttons = '<button type="button" class="btn btn-xs btn-info quick-edit-post-btn" data-toggle="tooltip" data-placement="right" title="Edit Post Details"><i class="fa fa-pencil-square-o"></i></button>';
                    buttons += ' <button type="button" class="btn btn-xs btn-warning edit-post-btn" data-toggle="tooltip" data-placement="right" title="Edit Full Post" onclick="window.location.href=\'/blog/new.php?p=' + row.id + '\'">' + '<i class="fa fa-pencil-square-o"></i></button>';
                    return buttons;
                },
                "targets" : 0
            }, {
                "data" : function(row) {
                    return "<a href='/blog/post.php?p=" + row.id + "'>" + row.title + "</a>";
                },
                "className" : "post-title",
                "targets" : 1
            }, {
                "data" : "date",
                "className" : "post-date",
                "targets" : 2
            }, {
                "data" : function(row) {
                    return (row.active === "1") ? "true" : "false";
                },
                "className" : "post-active",
                "targets" : 3
            } ],
            "fnCreatedRow" : function(nRow, aData) {
                $(nRow).attr('post-id', aData.id);
            }
        });
    }

    $('#posts').on('draw.dt search.dt', function() {
        setupEdit();
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('#post-delete-button').click(function() {
        deletePost($('#post').attr('post-id'));
    });

    $('#post-update-button').click(function() {
        updatePost($('#post').attr('post-id'));
    });
});

function setupEdit() {
    $('.quick-edit-post-btn').off().click(function() {
        var post = post_table.row($(this).closest('tr')).data();
        editPost(post);
    });
}

function editPost(post) {
    // remove any old values
    $('#post-preview-holder img').remove();
    $('#post-preview-image option').each(function() {
        $(this).remove();
    });
    var option = $('<option>');
    $('#post-preview-image').append(option);
    $('.selected-tag').each(function() {
        removeTag($(this));
    });

    // setup our basic post information
    $('#post').attr('post-id', post.id);
    $('#post .modal-title').html("Quick Edit of <strong> " + post.title + "</strong>");
    $('#post-title-input').val(post.title);
    $('#post-date-input').val(post.date);
    (post.active === "1") ? $('#post-active-input').prop('checked', true) : $('#post-active-input').prop('checked', false);
    // setup our preview image
    var img = $('<img>');
    img.attr('src', post.preview);
    img.css({
        width : '300px',
        top : post.offset + 'px'
    });
    $('#post-preview-holder').append(img);
    img.draggable({
        axis : "y",
    });
    $('#post').modal();
    $.get("/api/get-blog-full.php", {
        post : post.id
    }, function(data) {
        var location;
        for ( var i in data.content) {
            if (data.content.hasOwnProperty(i)) {
                for ( var j in data.content[i]) {
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
        for ( var k in data.tags) {
            if (data.tags.hasOwnProperty(k)) {
                $('#post-tags-select').val(data.tags[k].id);
                addTag($('#post-tags-select'));
            }
        }
    }, "json");
}

function deletePost(post) {
    $('#post-update-button').prop('disabled', true);
    $('#post-delete-button').prop('disabled', true);
    $('#post-update-close-button').prop('disabled', true);
    BootstrapDialog.confirm("Are you sure you want to delete this post?", function(result) {
        if (result) {
            $.post("/api/delete-blog.php", {
                post : post
            }).done(function(data) {
                if (data !== "") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                } else {
                    $('#post').modal('hide');
                    post_table.row($('tr[post-id=' + post + ']')).remove().draw();
                }
            }).fail(function(xhr, status, error) {
                if (xhr.responseText !== "") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                } else if (error === "Unauthorized") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                } else {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                }
            }).always(function() {
                $('#post-update-button').prop('disabled', false);
                $('#post-delete-button').prop('disabled', false);
                $('#post-update-close-button').prop('disabled', false);
            });
        } else {
            $('#post-update-button').prop('disabled', false);
            $('#post-delete-button').prop('disabled', false);
            $('#post-update-close-button').prop('disabled', false);
        }
    });
}

function updatePost(post) {
    $('#post-update-button').prop('disabled', true);
    $('#post-delete-button').prop('disabled', true);
    $('#post-update-close-button').prop('disabled', true);
    // get our updated content
    var tags = [];
    $('#post-tags span').each(function() {
        tags.push($(this).attr('tag-id'));
    });
    var preview = {};
    preview.img = $('#post-preview-image').val();
    preview.offset = $('#post-preview-holder img').css('top');
    // send the content
    $.post("/api/update-blog-post.php", {
        post : post,
        title : $('#post-title-input').val(),
        date : $('#post-date-input').val(),
        tags : tags,
        preview : preview,
        active : $('#post-active-input').is(':checked') ? 1 : 0,
    }).done(function(data) {
        if (data === "published") {
            $('#post-update-button').prop('disabled', true);
            $('#post-delete-button').prop('disabled', true);
            $('#post-update-close-button').prop('disabled', true);
            $.post("/api/publish-blog-post.php", {
                post : post
            }).done(function(data) {
                if (data !== "") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
                } else {
                    $('#post').modal('hide');
                    post_table.ajax.reload(null, false);
                }
            }).fail(function(xhr, status, error) {
                if (xhr.responseText !== "") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                } else if (error === "Unauthorized") {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                } else {
                    $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
                }
            }).always(function() {
                $('#post-update-button').prop('disabled', false);
                $('#post-delete-button').prop('disabled', false);
                $('#post-update-close-button').prop('disabled', false);
            });
        } else if (data !== "") {
            $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        } else {
            $('#post').modal('hide');
            post_table.ajax.reload(null, false);
        }
    }).fail(function(xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#post .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while creating your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function() {
        $('#post-update-button').prop('disabled', false);
        $('#post-delete-button').prop('disabled', false);
        $('#post-update-close-button').prop('disabled', false);
    });
}
