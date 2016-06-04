var album_table;

$(document).ready(function() {
    album_table = $('#albums').DataTable({
        "ajax" : "/api/get-albums.php",
        "order": [[ 0, "asc" ]],
        "columnDefs": [
            {
                "data" : function(row, type, val, meta) {
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
            }
        ],
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('album-id',aData.id);
        }
    });
});