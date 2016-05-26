$(document).ready(function() {
    $('#albums').DataTable({
        "ajax" : "/api/get-albums.php",
        "order": [[ 2, "asc" ]],
        "columnDefs": [
            {
                "orderable" : false,
                "searchable": false,
                "width": "39px",
                "data" : function(row, type, val, meta) {
                    var buttons = '<button type="button" class="btn btn-xs btn-warning edit-album-btn">' +
                        '<i class="fa fa-pencil-square-o"></i></button> ' +
                        '<button type="button" class="btn btn-xs btn-danger delete-album-btn">' +
                        '<i class="fa fa-trash-o"></i></button>' +
                        '<button type="button" class="btn btn-xs btn-success save-album-btn hidden">' +
                        '<i class="fa fa-floppy-o"></i></button> ' +
                        '<button type="button" class="btn btn-xs btn-danger cancel-album-btn hidden">' +
                        '<i class="fa fa-ban"></i></button>';
                    return buttons;
                },
                "targets" : 0
            }, {
                "data" : "id",
                "className" : "album-id",
                "visible": false,
                "searchable": false,
                "targets" : 1
            }, {
                "data" : function(row, type, val, meta) {
                    return "<a href='album.php?album=" + row.id + "'>" + row.name + "</a>";
                },
                "className" : "album-name",
                "targets" : 2
            }, {
                "data" : "description",
                "className" : "album-description",
                "targets" : 3
            }, {
                "data" : "date",
                "className" : "album-date",
                "targets" : 4
            }, {
                "data" : "images",
                "className" : "album-images",
                "targets" : 5
            }, {
                "data" : "lastAccessed",
                "className" : "album-last-accessed",
                "targets" : 6
            }
        ],
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('album-id',aData['id']);
        }
    });
    $('#albums').on( 'draw.dt search.dt', function () {
        setupAlbumTable();
    });

    $('#add-album-btn').click(function(){
        BootstrapDialog.show({
            title: 'Add A New Album',
            message: '<input placeholder="Album Name" type="text" class="form-control"/>' +
                '<input placeholder="Album Description" type="text" class="form-control"/>' +
                '<input placeholder="Album Date" type="date" class="form-control"/>',
            buttons: [{
                icon: 'glyphicon glyphicon-save',
                label: 'Save',
                cssClass: 'btn-success',
                action: function(dialogItself){
                    dialogItself.close();
                    //send our update
                    $.post("/api/add-album.php", {
                        id : $(row).attr('album-id')
                    }).done(function(data) {
                        var table = $('#albums').DataTable();
                        table.row( $(row) ).remove().draw();
                    });                    
                }
            }, {
                label: 'Close',
                action: function(dialogItself){
                    dialogItself.close();
                }
            }]
        });
    });
});

function setupAlbumTable() {
    $('.edit-album-btn').off().click(function(){  //our inline edit functionality
        //switch our buttons
        $(this).closest('td').find('.edit-album-btn').addClass('hidden');
        $(this).closest('td').find('.delete-album-btn').addClass('hidden');
        $(this).closest('td').find('.save-album-btn').removeClass('hidden');
        $(this).closest('td').find('.cancel-album-btn').removeClass('hidden');

        //setup our name cell for editing
        var nameCell = $(this).closest('tr').find('.album-name');
        var nameInput = $('<input />');
        nameInput.attr('type', 'text');
        nameInput.attr('class', 'form-control');
        nameInput.val($('a',nameCell).html());
        nameInput.attr('original-value', nameCell.html());
        nameCell.empty().append(nameInput);

        //setup our description cell for editing
        var descriptionCell = $(this).closest('tr').find('.album-description');
        var descriptionInput = $('<input />');
        descriptionInput.attr('type', 'text');
        descriptionInput.attr('class', 'form-control');
        descriptionInput.val(descriptionCell.html());
        descriptionInput.attr('original-value', descriptionCell.html());
        descriptionCell.empty().append(descriptionInput);

        //setup our date cell for editing
        var dateCell = $(this).closest('tr').find('.album-date');
        var dateInput = $('<input />');
        dateInput.attr('type', 'date');
        dateInput.attr('class', 'form-control');
        dateInput.val(dateCell.html());
        dateInput.attr('original-value', dateCell.html());
        dateCell.empty().append(dateInput);
    });
    $('.save-album-btn').off().click(function(){  //our inline save functionality
        //switch our buttons
        $(this).closest('td').find('.edit-album-btn').removeClass('hidden');
        $(this).closest('td').find('.delete-album-btn').removeClass('hidden');
        $(this).closest('td').find('.save-album-btn').addClass('hidden');
        $(this).closest('td').find('.cancel-album-btn').addClass('hidden');
        
        //reset our name cell
        var nameCell = $(this).closest('tr').find('.album-name');
        var nameInput = $('input', nameCell).val();
        nameCell.empty().append("<a href='album.php?album=" + $(this).closest('tr').attr('album-id') + "'>" + nameInput + "</a>");
        
        //reset our description cell
        var descriptionCell = $(this).closest('tr').find('.album-description');
        var descriptionInput = $('input', descriptionCell).val();
        descriptionCell.empty().append(descriptionInput);
        
        //reset our date
        var dateCell = $(this).closest('tr').find('.album-date');
        var dateInput = $('input', dateCell).val();
        dateCell.empty().append(dateInput);
        
        //send our update
        $.post("/api/update-album.php", {
            id : $(this).closest('tr').attr('album-id'),
            name : $(this).closest('tr').find('.album-name a').html(),
            description : $(this).closest('tr').find('.album-description').html(),
            date : $(this).closest('tr').find('.album-date').html(),
        }).done(function(data) {
        });
    });
    $('.cancel-album-btn').off().click(function(){  //our inline cancel functionality
        //switch our buttons
        $(this).closest('td').find('.edit-album-btn').removeClass('hidden');
        $(this).closest('td').find('.delete-album-btn').removeClass('hidden');
        $(this).closest('td').find('.save-album-btn').addClass('hidden');
        $(this).closest('td').find('.cancel-album-btn').addClass('hidden');

        //reset our name cell
        var nameCell = $(this).closest('tr').find('.album-name');
        var nameInput = $('input', nameCell).attr('original-value');
        nameCell.empty().append(nameInput);
        
        //reset our description cell
        var descriptionCell = $(this).closest('tr').find('.album-description');
        var descriptionInput = $('input', descriptionCell).attr('original-value');
        descriptionCell.empty().append(descriptionInput);
        
        //reset our date
        var dateCell = $(this).closest('tr').find('.album-date');
        var dateInput = $('input', dateCell).attr('original-value');
        dateCell.empty().append(dateInput);
    });
    $('.delete-album-btn').off().click(function(){
        var row = $(this).closest('tr');
        BootstrapDialog.show({
            title: 'Are You Sure?',
            message: 'Are you sure you want to delete the album <b>' + $(row).find('.album-name a').html() + '</b>',
            buttons: [{
                icon: 'glyphicon glyphicon-trash',
                label: 'Delete',
                cssClass: 'btn-danger',
                action: function(dialogItself){
                    dialogItself.close();
                    //send our update
                    $.post("/api/delete-album.php", {
                        id : $(row).attr('album-id'),
                    }).done(function(data) {
                        var table = $('#albums').DataTable();
                        table.row( $(row) ).remove().draw();
                    });                    
                }
            }, {
                label: 'Close',
                action: function(dialogItself){
                    dialogItself.close();
                }
            }]
        });
    });
}