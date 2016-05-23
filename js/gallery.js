$.fn.isOnScreen = function(){
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
}

function Gallery(gallery, columns, totalImages) {
    this.loaded = 0;
    this.gallery = gallery;
    this.columns = columns;
    this.totalImages = totalImages;
    
    this.loadImages();
}

Gallery.prototype.loadImages = function() {
    var Gallery = this;
    $.get( "/api/get-gallery-images.php",
            { gallery: Gallery.gallery, start: Gallery.loaded, howMany: Gallery.columns },
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
                    holder.width( shortest.obj.width() );
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
                    link.attr('href','javascript:void(0);');
                    link.attr('data-toggle','modal');
                    link.attr('data-target','#'+Gallery.gallery);
                    link.on('click', function() {
                        $('#'+Gallery.gallery).carousel(parseInt( v.sequence));
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
                if( $('footer').isOnScreen() ) {
                    Gallery.loadImages();
                } else {
                }
            },
            "json"
    );
    Gallery.loaded += Gallery.columns;
    return Gallery.loaded;
};