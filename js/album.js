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
                        $('#album-carousel').carousel(parseInt( carouselImage ));
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
//    
//    $("#album,#favorites,#cart-image,#cart").draggable({
//        handle: ".modal-header"
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
    
    //show our cart image
    $('#cart-image-btn').click(function(){
        var img = $('#album-carousel div.active div');
        $('#cart-image').modal();
        $('.product-count input').each(function(){
            $(this).val("");
        });
        $('.product-total').each(function(){
            $(this).html("--");
        });
        $.get("/api/get-cart-image.php", {
            album : img.attr('album-id'),
            image : img.attr('image-id'),
        }, function(data) {
            for (var i = 0, len = data.length; i < len; i++) {
                var row =  $('#cart-image tr[product-id="'+data[i].product+'"]');
                var price = Number($('.product-price', row).html().replace(/[^0-9\.]+/g,""));
                $('input', row).val(data[i].count);
                $('.product-total', row).html("$"+price*data[i].count);
            }
        }, "json" );
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
        $.post("/api/update-cart-image.php", {
            album : img.attr('album-id'),
            image : img.attr('image-id'),
            products : products
        }).done(function(data){
            //update our count on the page
            if( Math.round(data) == data && data > 0 ) {
                $('#cart-count').html(data).css({'padding-left':'10px'});
            } else {
                $('#cart-count').html("").css({'padding-left':''});
            }
        });
    });
    
    //show our cart review
    $('#cart-btn,#reviewOrder').click(function(){
        $('#cart-image').modal('hide');
        $('#album').modal('hide');
        $('#cart').modal();
        $('#cart-items').empty();
        $.get("/api/get-cart.php", {
            album : $('#album').attr('album-id'),
        }, function(data) {
            for (var i = 0, len = data.length; i < len; i++) {
                for( var c = 0, zen = data[i].count; c < zen; c++) {
                    var row = $("<tr>");
                    row.attr('product-id',data[i].product);
                    row.attr('image-id',data[i].image);
                    row.attr('image-title',data[i].title);
                    var remove = $("<td>");
                    remove.addClass("text-center");
                    removeIcon = $("<i>");
                    removeIcon.addClass("fa fa-trash error");
                    removeIcon.css({"cursor":"pointer"});
                    removeIcon.click(function(){
                        $(this).closest('tr').remove();
                        calculateCost();
                        //update our database
                        var cart = [];
                        $('#cart-items tr').each(function(){
                            var product = $(this).attr('product-id');
                            var image = $(this).attr('image-id');
                            if( arrayHasJSON(cart, product, image) ) {
                                cart = incrementProduct(cart, product, image);
                            } else {
                                var item = {};
                                item.product = $(this).attr('product-id');
                                item.image = $(this).attr('image-id');
                                item.count = 1
                                cart.push(item);
                            }
                        });
                        $.post("/api/update-cart.php", {
                            album : $('#album').attr('album-id'),
                            images : cart
                        }).done(function(data){
                            //update our count on the page
                            if( Math.round(data) == data && data > 0 ) {
                                $('#cart-count').html(data).css({'padding-left':'10px'});
                            } else {
                                $('#cart-count').html("").css({'padding-left':''});
                            }
                        });
                    });
                    remove.append(removeIcon);
                    row.append(remove);
                    var preview = $("<td>");
                    var previewDiv = $("<div>");
                    previewDiv.css({
                        "background-image":"url("+data[i].location+")",
                        "background-size":"cover",
                        "background-position":"50%",
                        "width":"50px",
                        "height":"50px"
                    });
                    preview.append(previewDiv);
                    row.append(preview);
                    var product = $("<td>");
                    product.html(data[i].name);
                    row.append(product);
                    var size = $("<td>");
                    size.html(data[i].size);
                    row.append(size);
                    var price = $("<td>");
                    price.addClass("item-cost");
                    price.html("$"+data[i].price);
                    row.append(price);
                    $('#cart-items').append(row);
                }
            }
            calculateCost();
            $('#cart-shipping input').each(function(){
                validateCartInput($(this));
            });
        }, "json" );
    });
    
    $('#cart-shipping input').bind("change keyup input", function () {
        validateCartInput($(this));
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

function calculateCost() {
    var total = 0;
    $('#cart-table .item-cost').each(function(){
        var price = Number($(this).html().replace(/[^0-9\.]+/g,""));
        total += price;
    });
    var tax = total * 0.06;
    $('#cart-tax').html("$"+tax.toFixed(2));
    total += tax;
    $('#cart-total').html("$"+total.toFixed(2));
}

function validateCartInput(ele) {
    var id = ele.attr('id');
    var regex = new RegExp(".{3}");
    if (id === "cart-email") {
        //email validation
        regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    } else if (id === "cart-phone") {
        //telephone validation
        regex = /^\(?\d{3}\)?\s?-?\d{3}-?\s?\d{4}$/;
    } else if (id === "cart-address") {
        //address validation
        regex = /^\d+\s\w+/;
    } else if (id === "cart-state") {
        //state validation
        regex = /^[A-Z]{2}$/;
    } else if (id === "cart-zip") {
        //zip validation
        regex = new RegExp("^\\d{5}(-\\d{4})?$");
    }
    if (regex.test(ele.val())) {
        ele.closest('div').removeClass('has-error');
    } else {
        ele.closest('div').addClass('has-error');
    }
    if ($('#cart .has-error').length === 0) {
        $('#cart-submit').prop("disabled", false);
    } else {
        $('#cart-submit').prop("disabled", true);
    }
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

// functions for dealing with the cart
function arrayHasJSON(myArray,product,image) {
    var hasJSON = false;
    for (var i = 0, len = myArray.length; i < len; i++) {
        if (myArray[i].product === product && myArray[i].image === image) {
            hasJSON = true;
            break;
        }
    };
    return hasJSON;
}
function incrementProduct(myArray,product,image) {
    $.each(myArray, function(i,obj) {
        if (obj.product === product && obj.image === image) {
            obj.count++;
        }
    });
    return myArray;
}