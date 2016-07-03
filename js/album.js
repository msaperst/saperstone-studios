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
                    img.attr('image-id',v.sequence);
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
                        var carouselImage = $('#album-carousel .item').index( $('#album-carousel .contain[image-id="'+v.sequence+'"]').parent() );
                        $('#album').carousel(parseInt( carouselImage ));
                        $('#album .btn-action').each(function(){
                            $(this).prop("disabled",true);
                        });
                        getDetails();
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
    $('#album-carousel').carousel({
        interval: false,
        pause: "false",
    });
//    $(document).on('mouseleave','#album-carousel', function(){
//        $(this).carousel('pause');
//    });
    
    $('#set-favorite-image-btn').click(function(){
        var img = $('#album-carousel div.active div');
        //send our update
        $.post("/api/set-favorite.php", {
            album : img.attr('album-id'),
            image : img.attr('image-id')
        }).done(function(data) {
            setFavorite();
        });
    });
    $('#unset-favorite-image-btn').click(function(){
        var img = $('#album-carousel div.active div');
        //send our update
        $.post("/api/unset-favorite.php", {
            album : img.attr('album-id'),
            image : img.attr('image-id')
        }).done(function(data) {
            unsetFavorite();
        });
    });
    
    $('#favorite-btn').click(function(){
        $('#favorites-list').empty();
        $('#favorites').modal();
        $.get("/api/get-favorites.php", {
            album : $('#album').attr('album-id'),
        }, function(data) {
            $.each(data, function(i, image) {
                var li = $('<li image-id="' + data[i].sequence + '" class="img-favorite">');
                li.css('background-image', 'url(' + data[i].location + ')');
                li.click(function(){
                    $.post("/api/unset-favorite.php", {
                        album : $('#album').attr('album-id'),
                        image : $(this).attr('image-id')
                    });
                    $(this).remove();
                });
                $('#favorites-list').append( li );
            });
        }, "json" );
    });
    
    //show our cart
    $('#cart-image-btn').click(function(){
        $('#cart').modal();
    });
    //show different tabs on our cart
    $(".nav-tabs a").click(function(){
        $(this).tab('show');
    });
    //update our cart
    $('.product-count input').change(function(){
        var img = $('#album-carousel div.active div');
        var row = $(this).closest('tr');
        var price = Number($('.product-price', row).html().replace(/[^0-9\.]+/g,""));
        $('.product-total', row).html("$"+price*$(this).val());
        if( $('.product-total', row).html() == "$0" ) {
            $('.product-total', row).html("--");
        }
        //update our database
        var products = {};
        $('.product-count input').each(function(){
            var product = $(this).closest('tr').attr('product-id');
            var count = parseInt($(this).val()) || 0;
            if( count !== 0 ) {
                products[product] = count;
            }
        });
        $.post("/api/update-cart.php", {
            album : img.attr('album-id'),
            image : img.attr('image-id'),
            products : products
        });
        //update our count on the page
        var total = 0;
        $('.product-count input').each(function(){
            var num = parseInt($(this).val()) || 0;
            total += num;
        });
        if( total > 0 ) {
            $('#cart-count').html(total).css({'padding-left':'10px'});
        } else {
            $('#cart-count').html("").css({'padding-left':''});
        }
    });
    
    //on start of slide, disable all buttons
    $('#album-carousel').on('slide.bs.carousel', function(){
        $('#album .btn-action').each(function(){
            $(this).prop("disabled",true);
        });
    });
    //once slide completes, check for a favorite, which will re-enable
    $('#album-carousel').on('slid.bs.carousel', function(){
        getDetails();
    });
});

function getDetails() {
    $('#album .btn-action').each(function(){
        $(this).prop("disabled",true);
    });
    var img = $('#album-carousel div.active div');
    $.get("/api/is-favorite.php", {
        album : img.attr('album-id'),
        image : img.attr('image-id')
    }).done(function(data) {
        if( Math.round(data) == data && data == 1 ) {
            setFavorite();
        } else {
            unsetFavorite();
        }
        $.get("/api/is-downloadable.php", {
            album : img.attr('album-id'),
            image : img.attr('image-id')
        }).done(function(data) {
            if( Math.round(data) == data && data == 1 ) {
                setDownloadable();
            } else {
                unsetDownloadable();
            }
            $.get("/api/is-shareable.php", {
                album : img.attr('album-id'),
                image : img.attr('image-id')
            }).done(function(data) {
                if( Math.round(data) == data && data == 1 ) {
                    setShareable();
                } else {
                    unsetShareable();
                }
                
                $('#album .btn-action').each(function(){
                    $(this).prop("disabled",false);
                });
            });
        });
    });
}

function setFavorite() {
    $('#set-favorite-image-btn').addClass('hidden');
    $('#unset-favorite-image-btn').removeClass('hidden');
}
function unsetFavorite() {
    $('#set-favorite-image-btn').removeClass('hidden');
    $('#unset-favorite-image-btn').addClass('hidden');
}

function setDownloadable() {
    $('#not-downloadable-image-btn').addClass('hidden');
    $('#downloadable-image-btn').removeClass('hidden');
}
function unsetDownloadable() {
    $('#not-downloadable-image-btn').removeClass('hidden');
    $('#downloadable-image-btn').addClass('hidden');
}

function setShareable() {
    $('#not-shareable-image-btn').addClass('hidden');
    $('#shareable-image-btn').removeClass('hidden');
}
function unsetShareable() {
    $('#not-shareable-image-btn').removeClass('hidden');
    $('#shareable-image-btn').addClass('hidden');
}