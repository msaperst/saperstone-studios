var post_table;
var resultsSelected = false;

$(document).ready(
        function() {
            if ($('#posts').length) {
                post_table = $('#posts').DataTable(
                        {
                            "ajax" : "/api/get-blogs-details.php?a=1",
                            "order" : [ [ 2, "desc" ] ],
                            "columnDefs" : [
                                    {
                                        "orderable" : false,
                                        "searchable" : false,
                                        "data" : function(row) {
                                            var buttons = '<button type="button" class="btn btn-xs btn-info quick-edit-post-btn">' + '<i class="fa fa-pencil-square-o"></i></button>';
                                            buttons += ' <button type="button" class="btn btn-xs btn-warning edit-post-btn" onclick="window.location.href=\'/blog/new.php?p=' + row.id + '\'">'
                                                    + '<i class="fa fa-pencil-square-o"></i></button>';
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
    $('.selected-tag').each(function(){
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
        $('#post').attr('post-location', location);
        for ( var i in data.tags) {
            console.log(data.tags[i]);
            $('#post-tags-select').val(data.tags[i].id);
            addTag($('#post-tags-select'));
        }
    }, "json");
}

function setPreview() {
    $('#post-preview-holder img').remove();
    var img = $('<img>');
    img.attr('src', $('#post').attr('post-location') + '/' + $('#post-preview-image').val());
    img.css({
        width : '300px'
    });
    $('#post-preview-holder').append(img);
    img.draggable({
        axis : "y",
    });
}