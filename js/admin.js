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
                "data" : function(row, type, val, meta) {
                    var buttons = '<button type="button" class="btn btn-xs btn-success add-images-btn">' +
                        '<i class="fa fa-plus"></i></button>';
                    return row.images + buttons;
                },
                "className" : "album-images",
                "targets" : 5
            }, {
                "data" : "lastAccessed",
                "className" : "album-last-accessed",
                "targets" : 6
            }
        ],
        "fnCreatedRow": function( nRow, aData, iDataIndex ) {
            $(nRow).attr('album-id',aData.id);
        }
    });
    $('#albums').on( 'draw.dt search.dt', function () {
        setupAlbumTable();
    });

    $('#add-album-btn').click(function(){
        BootstrapDialog.show({
            title: 'Add A New Album',
            message: '<input placeholder="Album Name" id="new-album-name" type="text" class="form-control"/>' +
                '<input placeholder="Album Description" id="new-album-description" type="text" class="form-control"/>' +
                '<input placeholder="Album Date" id="new-album-date" type="date" class="form-control"/>' +
                '<div id="new-album-error" class="error"></div>' +
                '<div id="new-album-message" class="success"></div>',
            buttons: [{
                icon: 'glyphicon glyphicon-folder-close',
                label: ' Create Album',
                cssClass: 'btn-success',
                action: function(dialogItself){
                    var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                    $button.spin();
                    dialogItself.enableButtons(false);
                    dialogItself.setClosable(false);
                    //send our update
                    $.post("/api/add-album.php", {
                        name : $('#new-album-name').val(),
                        description : $('#new-album-description').val(),
                        date : $('#new-album-date').val()
                    }).done(function(data) {
                        if( Math.round(data) == data ) {
                            var table = $('#albums').DataTable();
                            table.row.add({
                                "id":           data,
                                "name":         $('#new-album-name').val(),
                                "description":  $('#new-album-description').val(),
                                "date":         $('#new-album-date').val(),
                                "images":       "0",
                                "lastAccessed": "0000-00-00 00:00:00",
                                "location":     ""
                            }).draw(false);
                            dialogItself.close();
//                            addImages( data );
                        } else {
                            $('#new-album-error').html(data);
                        }
                        $button.stopSpin();
                        dialogItself.enableButtons(true);
                        dialogItself.setClosable(true);
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

function addImages(id) {
    $('#addImagesModal').modal();
    $.get( 
        "/api/get-album.php",
        { id: id },
        function( data ) {
            $('#addImagesModal').attr('album-id',id);
            $('#addImagesModal .modal-title').empty().append( "Add Images to " + data.name );
            $('#addImagesModal .modal-body').empty();
            $('.ajax-file-upload-container').remove();
            $('#add-images-button').remove();
            $('.modal-body').uploadFile({
                url: "/api/upload-images.php",
                multiple: true,
                dragDrop: true,
                uploadButtonLocation: $('.modal-footer'),
                uploadContainer: $('.modal-body'),
                uploadButtonClass: "btn btn-default btn-success",
                statusBarWidth: "48%",
                dragdropWidth: "100%",
                fileName: "myfile",
                sequential: true,
                sequentialCount: 5,
                acceptFiles: "image/*",
                uploadQueueOrder: "bottom",
                dynamicFormData: function() {
                    var data = { album: $('#addImagesModal').attr('album-id') };
                    return data;
                },
                onSubmit: function(files) {
                    $('.ajax-file-upload-container').show();
                    // make modal non closable, and spin icons
                },
                onSuccess: function(files,data,xhr,pd) {
                    setTimeout(function() {pd.statusbar.remove();}, 5000);
                    $('#albums').dataTable().fnUpdate('Zebra' , $("tr[album-id='"+$('#addImagesModal').attr('album-id')+"']"), 4 );
                },
                afterUploadAll: function(obj) {
                    setTimeout(function() {$('.ajax-file-upload-container').hide();}, 5000);
                    // do something to update the row with the images
                },
            });
        },
        "json"
    );
    
    $('#make-thumbs-button').off().click(function(){
        $("#resize-progress").show();
        $.post("/api/make-thumbs.php", {
            id : $("#addImagesModal").attr('album-id'),
        }).done(function(data) {
            var myVar = setInterval( function(){ 
                $.get(
                    "/scripts/status.txt",
                    function (data){
                        $('#resize-progress .progress-bar').html( data );
                        if( data.indexOf("Done") === 0 ) {
                            clearInterval(myVar);
                            $('#resize-progress .progress-bar').removeClass('active');
                            setTimeout(function() {$('#resize-progress').hide();}, 5000);
                        }
                        if( data.indexOf("Error") === 0 ) {
                            clearInterval(myVar);
                            $('#resize-progress .progress-bar').removeClass('active').addClass('progress-bar-danger');
                        }
                    }
                ); 
            }, 100);
        });
    });
}

function setupAlbumTable() {
    $('.add-images-btn').off().click(function(){
        addImages($(this).closest('tr').attr('album-id'));
    });
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
                label: ' Delete',
                cssClass: 'btn-danger',
                action: function(dialogItself){
                    var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                    $button.disable();
                    $button.spin();
                    dialogItself.setClosable(false);
                    //send our update
                    $.post("/api/delete-album.php", {
                        id : $(row).attr('album-id'),
                    }).done(function(data) {
                        var table = $('#albums').DataTable();
                        table.row( $(row) ).remove().draw( false );
                        dialogItself.close();
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