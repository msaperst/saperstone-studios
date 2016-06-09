$.fn.isOnScreen = function(){
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Album(albumId, columns, totalImages) {
    this.loaded = 0;
    this.albumId = albumId;
    this.columns = columns;
    this.totalImages = totalImages;
    
    this.loadImages();
}

Album.prototype.loadImages = function() {
    var Album = this;
    $.get( "/api/get-album-images.php",
            { albumId: Album.albumId, start: Album.loaded, howMany: Album.columns },
            function( data ) {
                // load each of our 4 images on the screen
                $.each(data, function(k, v) {
                    shortest = {};
                    shortest.height = 9999;
                    $('.col-gallery').each(function(){
                        if( $(this).height() < shortest.height ) {
                            shortest.obj = $(this);
                            shortest.height = $(this).height();
                        }
                    });
                    // create our holding div
                    var holder = $('<div>');
                    holder.addClass('gallery hovereffect');
                    // holder.width( shortest.obj.width() );
                    holder.height( parseInt( v.height * shortest.obj.width() / v.width ) );
                    // create our image
                    var img = $('<img>');
                    img.attr('src',v.location);
                    img.attr('alt',v.title);
                    img.attr('width','100%');
                    // create our overlay
                    var overlay = $('<div>');
                    overlay.addClass('overlay');
                    // our view link
                    var link = $('<a>');
                    link.addClass('info no-border');
                    //link.attr('href','javascript:void(0);');
                    link.attr('data-toggle','modal');
                    link.attr('data-target','#album');
                    link.on('click', function() {
                        $('#album').carousel(parseInt( v.sequence));
                    });
                    // add our image icon
                    var view = $('<i>');
                    view.addClass('fa fa-search fa-2x');
                    // put them all together
                    link.append(view);
                    overlay.append(link);
                    holder.append(img);
                    holder.append(overlay);
                    shortest.obj.append(holder);
                });
                // when we done, see if we need to load more
                if( $('footer').isOnScreen() && Album.totalImages > Album.loaded) {
                    Album.loadImages();
                } else {
                }
            },
            "json"
    );
    Album.loaded += Album.columns;
    return Album.loaded;
};

$(document).ready(function() {
    $('#delete-image-btn').click(function(){
        var img = $('#album-carousel div.active div');
        $('#album-carousel').carousel("pause");
        BootstrapDialog.show({
            draggable: true,
            title: 'Are You Sure?',
            message: 'Are you sure you want to delete the image <b>' + img.attr('alt') + '</b>',
            buttons: [{
                icon: 'glyphicon glyphicon-trash',
                label: ' Delete',
                cssClass: 'btn-danger',
                action: function(dialogInItself){
                    var $button = this; // 'this' here is a jQuery object that wrapping the <button> DOM element.
                    $button.spin();
                    dialogInItself.enableButtons(false);
                    dialogInItself.setClosable(false);
                    //send our update
                    $.post("/api/delete-image.php", {
                        album : img.attr('album-id'),
                        image : img.attr('image-id')
                    }).done(function(data) {
                        dialogInItself.close();
                        //go to the next image
                        $('#album-carousel').carousel("next");
                        $('#album-carousel').carousel();
                        //cleanup the dom
                        $('.gallery img[alt="'+img.attr('alt')+'"]').parent().remove();
                        img.parent().remove();
                        //TODO - need to redo the sequence or how we sequence after deletion, or carousel displays wrong image
                    });
                }
            }, {
                label: 'Close',
                action: function(dialogInItself){
                    $('#album-carousel').carousel("cycle");
                    dialogInItself.close();
                }
            }]
        });
    });
});