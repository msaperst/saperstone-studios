var album_table;

$(document).ready(function() {
    album_table = $('#albums').DataTable({
        "ajax" : "/api/get-albums.php",
        "order" : [ [ 0, "asc" ] ],
        "columnDefs" : [ {
            "data" : function(row) {
                return "<a href='album.php?album=" + row.id + "'>" + row.name + "</a>";
            },
            "className" : "album-name",
            "targets" : 0
        }, {
            "data" : "description",
            "className" : "album-description",
            "targets" : 1
        }, {
            "data" : "date",
            "className" : "album-date",
            "targets" : 2
        }, {
            "data" : "images",
            "className" : "album-images",
            "targets" : 3
        } ],
        "fnCreatedRow" : function(nRow, aData) {
            $(nRow).attr('album-id', aData.id);
        }
    });

    $('#add-album-div').keypress(function(e) {
        if (e.which === 13) {
            addAlbum();
        }
    });
    $('#album-code-add').click(function() {
        addAlbum();
    });
});

function addAlbum() {
    // spin our button
    $("#album-code-add").prop("disabled", true);
    $("#album-code-add em").removeClass('fa fa-plus-circle').addClass('glyphicon glyphicon-asterisk icon-spin');
    // make our call
    $.get("/api/find-album.php", {
        code : $('#album-code').val(),
        albumAdd : 1,
    }).done(function(data) {
        // goto album url if it exists
        if ($.isNumeric(data) && data !== '0') {
            $('#add-album-div').append("<div id='album-code-add-message' class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Added album to your list</div>");                        
            // refresh our tables
            album_table.ajax.reload(null, false);
            setTimeout(function(){
                $('#album-code-add-message').remove();
            }, 10000);
        } else if (data === '0') {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        } else {
            $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + data + "</div>");
        }
    }).fail(function(){
        $('#add-album-div').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while searching for your album.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
    }).always(function(){
        //fix our button
        $("#album-code-add").prop("disabled", false);
        $("#album-code-add em").addClass('fa fa-plus-circle').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}