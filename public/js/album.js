$.fn.isOnScreen = function () {
    var element = this.get(0);
    var bounds = element.getBoundingClientRect();
    return bounds.top < window.innerHeight && bounds.bottom > 0;
};

function Album(albumId, columns, totalImages) {
    var Album = this;

    Album.loaded = 0;
    Album.albumId = albumId;
    Album.columns = columns;
    Album.totalImages = totalImages;

    Album.loadImages();

    if (window.location.hash) {
        if (window.location.hash.length > 1) {
            Album.setImage(window.location.hash.substr(1));
        }
    }

    window.onhashchange = function () {
        if (window.location.hash.length > 1) {
            Album.setImage(window.location.hash.substr(1));
        }
    };

    $('#album').on('hide.bs.modal', function (e) {
        window.location.hash = "";
    });
}

Album.prototype.setImage = function (img) {
    var Album = this;

    $('#album').modal('show');
    $('#album-carousel').carousel({
        interval: false,
        pause: "false",
    });
    var carouselImage = $('#album-carousel .item').index($('#album-carousel .contain[image-id="' + img + '"]').parent());
    $('#album-carousel').carousel(parseInt(carouselImage));
    $('#album .btn-action').each(function () {
        $(this).prop("disabled", true);
    });
    getDetails();
}

Album.prototype.prev = function () {
    var Album = this;

    var prev = parseInt(parseInt(window.location.hash.substring(1)) - 1);
    if (prev < 0) {
        prev = (parseInt(Album.totalImages) - 1);
    }
    window.location.hash = "#" + prev;
}

Album.prototype.next = function () {
    var Album = this;

    var next = parseInt(parseInt(window.location.hash.substring(1)) + 1);
    if (next >= Album.totalImages) {
        next = 0;
    }
    window.location.hash = "#" + next;
}

Album.prototype.loadImages = function () {
    var Album = this;
    $.get("/api/get-album-images.php", {
        albumId: Album.albumId,
        start: Album.loaded,
        howMany: Album.columns
    }, function (data) {
        // load each of our 4 images on the screen
        $.each(data, function (k, v) {
            var shortest = {};
            shortest.height = 999999999;
            $('.col-gallery').each(function () {
                if ($(this).height() < shortest.height) {
                    shortest.obj = $(this);
                    shortest.height = $(this).height();
                }
            });
            // create our holding div
            var holder = $('<div>');
            holder.addClass('gallery hovereffect');
            var rect = shortest.obj[0].getBoundingClientRect();
            var width;
            // `width` is available for IE9+
            if (rect.width) {
                width = rect.width;
                // Calculate width for IE8 and below
            } else {
                width = rect.right - rect.left;
            }
            // Remove the padding width
            width -= (parseInt(shortest.obj.css("padding-left")) + parseInt(shortest.obj.css("padding-right")));
            holder.height(parseInt(v.height * width / v.width));
            // create our image
            var img = $('<img>');
            img.attr('src', v.location);
            img.attr('alt', v.title);
            img.attr('image-id', v.sequence);
            img.attr('width', '100%');
            // create our overlay
            var overlay = $('<div>');
            overlay.addClass('overlay');
            // our view link
            var link = $('<a>');
            link.addClass('info no-border');
            link.attr('href', '#' + parseInt(v.sequence));
            // link.attr('data-toggle', 'modal');
            // link.attr('data-target', '#album');
            // link.on('click', function() {
            //     var carouselImage = $('#album-carousel .item').index($('#album-carousel .contain[image-id="' + v.sequence + '"]').parent());
            //     $('#album-carousel').carousel(parseInt(carouselImage));
            //     $('#album .btn-action').each(function() {
            //         $(this).prop("disabled", true);
            //     });
            //     getDetails();
            // });
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
        if ($('footer').isOnScreen() && Album.totalImages > Album.loaded) {
            Album.loadImages();
        }
    }, "json");
    Album.loaded += Album.columns;
    return Album.loaded;
};

$(document).ready(function () {
    $('#album-carousel').carousel({
        interval: false,
        pause: "false",
    });

    // download an image
    $('#downloadable-image-btn').click(function () {
        var img = $('#album-carousel div.active div');
        downloadImages(img.attr('album-id'), img.attr('image-id'));
    });
    // share an image
    $('#shareable-image-btn').click(function () {
        var img = $('#album-carousel div.active div');
        shareImages(img.attr('album-id'), img.attr('image-id'));
    });
    // submit an image
    $('#submit-image-btn').click(function () {
        var img = $('#album-carousel div.active div');
        $('#submit').attr('what', img.attr('image-id')).modal();
    });
    // quick purchase an image
    $('#not-downloadable-image-btn').click(function () {
        var img = $('#album-carousel div.active div');
        var products = {};
        products['31'] = 1;
        $.post("/api/update-cart-image.php", {
            album: img.attr('album-id'),
            image: img.attr('image-id'),
            products: products
        }).done(function (data) {
            // update our count on the page
            $('#cart-count').html(data).css({
                'padding-left': '10px'
            });
            reviewCart();
        }).fail(function (xhr, status, error) {
            if (xhr.responseText !== "") {
                $('#album .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
            } else if (error === "Unauthorized") {
                $('#album .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
            } else {
                $('#album .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Some unexpected error occurred while updating your cart.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
            }
        });
    })

    // download favorite images
    $('#downloadable-favorites-btn').click(function () {
        downloadImages($('#favorites').attr('album-id'), 'favorites');
    });
    // share favorite images
    $('#shareable-favorites-btn').click(function () {
        shareImages($('#favorites').attr('album-id'), 'favorites');
    });
    // submit favorite images
    $('#submit-favorites-btn').click(function () {
        $('#submit').attr('what', 'favorites').modal();
    });

    // download all images
    $('#downloadable-all-btn').click(function () {
        downloadImages($('#favorites').attr('album-id'), 'all');
    });
    // share all images
    $('#shareable-all-btn').click(function () {
        shareImages($('#favorites').attr('album-id'), 'all');
    });

    // our actual submit button
    $('#submit-send').click(function () {
        submitImages();
    });

    // set a favorite
    $('#set-favorite-image-btn').click(function () {
        setFavoriteImage();
    });
    // unset a favorite
    $('#unset-favorite-image-btn').click(function () {
        unsetFavoriteImage();
    });

    // show our favorites
    $('#favorite-btn').click(function () {
        showFavorites();
    });

    // show our cart image
    $('#cart-image-btn').click(function () {
        showCart();
    });
    // show different tabs on our cart
    $(".nav-tabs a").click(function () {
        $(this).tab('show');
    });
    // update our cart
    $('.product-count input').change(function () {
        updateCart($(this));
    });
    // show our cart review
    $('#cart-btn,#reviewOrder').click(function () {
        reviewCart();
    });
    // cart purchase options
    $('#cart-submit').click(function () {
        submitCart();
    });

    $('#cart-shipping input').bind("change keyup input", function () {
        validateCartInput($(this));
    });

    // on start of slide, disable all buttons
    $('#album-carousel').on('slide.bs.carousel', function () {
        $('#album .btn-action').each(function () {
            $(this).prop("disabled", true);
        });
    });
    // once slide completes, check for a favorite, which will
    // re-enable
    $('#album-carousel').on('slid.bs.carousel', function () {
        getDetails();
    });

    //submit email
    $('#notify-submit').click(function () {
        submitNotifyEmail();
    });
});

function getDetails() {
    $('#album .btn-action').each(function () {
        $(this).prop("disabled", true);
    });
    var img = $('#album-carousel div.active div');
    $.get("/api/is-favorite.php", {
        album: img.attr('album-id'),
        image: img.attr('image-id')
    }).done(function (data) {
        if (data === '1') {
            setFavorite();
        } else {
            unsetFavorite();
        }
        $.get("/api/is-downloadable.php", {
            album: img.attr('album-id'),
            image: img.attr('image-id')
        }).done(function (data) {
            if (data === '1') {
                setDownloadable();
            } else {
                unsetDownloadable();
            }
            $.get("/api/is-shareable.php", {
                album: img.attr('album-id'),
                image: img.attr('image-id')
            }).done(function (data) {
                if (data === '1') {
                    setShareable();
                } else {
                    unsetShareable();
                }

                $('#album .btn-action').each(function () {
                    $(this).prop("disabled", false);
                });
            });
        });
    });
}

function calculateCost() {
    var total = 0;
    $('#cart-table .item-cost').each(function () {
        var price = Number($(this).html().replace(/[^0-9\.]+/g, ""));
        total += price;
    });
    var tax = total * 0.06;
    $('#cart-tax').html("$" + tax.toFixed(2));
    total += tax;
    $('#cart-total').html("$" + total.toFixed(2));
}

function validateCartInput(ele) {
    var id = ele.attr('id');
    var regex = new RegExp(".{3}");
    if (id === "cart-email") {
        // email validation
        regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    } else if (id === "cart-phone") {
        // telephone validation
        regex = /^\(?\d{3}\)?\s?-?\d{3}-?\s?\d{4}$/;
    } else if (id === "cart-address") {
        // address validation
        regex = /^\d+\s\w+/;
    } else if (id === "cart-state") {
        // state validation
        regex = /^[A-Z]{2}$/;
    } else if (id === "cart-zip") {
        // zip validation
        regex = new RegExp("^\\d{5}(-\\d{4})?$");
    }
    // test our regex
    if (regex.test(ele.val())) {
        ele.closest('div').removeClass('has-error');
    } else {
        ele.closest('div').addClass('has-error');
    }
    // check our item options
    if (ele.parent().hasClass('item-option')) {
        if (ele.val() === "") {
            ele.parent().addClass('has-error');
        } else {
            ele.parent().removeClass('has-error');
        }
    }
    // fix our submit button if no errors exist
    if ($('#cart .has-error').length === 0 && $('#cart-items > tr').length > 0) {
        $('#cart-submit').prop("disabled", false);
    } else {
        $('#cart-submit').prop("disabled", true);
    }
}

function setFavoriteImage() {
    var img = $('#album-carousel div.active div');
    // send our update
    $.post("/api/set-favorite.php", {
        album: img.attr('album-id'),
        image: img.attr('image-id')
    }).done(function (data) {
        // update our count on the page
        if (parseInt(data) > 0) {
            $('#favorite-count').html(data).css({
                'padding-left': '10px'
            });
        } else {
            $('#favorite-count').html("").css({
                'padding-left': ''
            });
        }
        setFavorite();
    });
}

function unsetFavoriteImage() {
    var img = $('#album-carousel div.active div');
    // send our update
    $.post("/api/unset-favorite.php", {
        album: img.attr('album-id'),
        image: img.attr('image-id')
    }).done(function (data) {
        // update our count on the page
        if (parseInt(data) > 0) {
            $('#favorite-count').html(data).css({
                'padding-left': '10px'
            });
        } else {
            $('#favorite-count').html("").css({
                'padding-left': ''
            });
        }
        unsetFavorite();
    });
}

function showFavorites() {
    $('#favorites-list').empty();
    $('#favorites').modal();
    $.get("/api/get-favorites.php", {
        album: $('#album').attr('album-id'),
    }, function (data) {
        $.each(data, function (i) {
            var li = $('<li image-id="' + data[i].sequence + '" class="img-favorite">');
            li.css('background-image', 'url("' + data[i].location + '")');
            li.click(function () {
                $.post("/api/unset-favorite.php", {
                    album: $('#album').attr('album-id'),
                    image: $(this).attr('image-id')
                }).done(function (data) {
                    // update our count on the page
                    if (parseInt(data) > 0) {
                        $('#favorite-count').html(data).css({
                            'padding-left': '10px'
                        });
                    } else {
                        $('#favorite-count').html("").css({
                            'padding-left': ''
                        });
                        $("#downloadable-favorites-btn").prop("disabled", true);
                        $("#shareable-favorites-btn").prop("disabled", true);
                        $("#submit-favorites-btn").prop("disabled", true);
                    }
                });
                $(this).remove();
            });
            $('#favorites-list').append(li);
        });
        $("#downloadable-favorites-btn").prop("disabled", false);
        $("#shareable-favorites-btn").prop("disabled", false);
        $("#submit-favorites-btn").prop("disabled", false);
        if (!$("#favorites-list").has("li").length) {
            $("#downloadable-favorites-btn").prop("disabled", true);
            $("#shareable-favorites-btn").prop("disabled", true);
            $("#submit-favorites-btn").prop("disabled", true);
        }
    }, "json");
}

function showCart() {
    var img = $('#album-carousel div.active div');
    $('#cart-image').modal();
    $('.product-count input').each(function () {
        $(this).val("");
    });
    $('.product-total').each(function () {
        $(this).html("--");
    });
    $.get("/api/get-cart-image.php", {
        album: img.attr('album-id'),
        image: img.attr('image-id'),
    }, function (data) {
        for (var i = 0, len = data.length; i < len; i++) {
            var row = $('#cart-image tr[product-id="' + data[i].product + '"]');
            var price = Number($('.product-price', row).html().replace(/[^0-9\.]+/g, ""));
            $('input', row).val(data[i].count);
            $('.product-total', row).html("$" + (Math.round(price * data[i].count * 100) / 100).toFixed(2));
        }
    }, "json");
}

function updateCart(input) {
    var img = $('#album-carousel div.active div');
    var row = input.closest('tr');
    var price = Number($('.product-price', row).html().replace(/[^0-9\.]+/g, ""));
    $('.product-total', row).html("$" + (Math.round(price * input.val() * 100) / 100).toFixed(2));
    if ($('.product-total', row).html() === "$0.00") {
        $('.product-total', row).html("--");
    }
    // update our database
    var products = {};
    $('.product-count input').each(function () {
        var product = $(this).closest('tr').attr('product-id');
        var count = parseInt($(this).val()) || 0;
        if (count !== 0) {
            products[product] = count;
        }
    });
    $.post("/api/update-cart-image.php", {
        album: img.attr('album-id'),
        image: img.attr('image-id'),
        products: products
    }).done(function (data) {
        // update our count on the page
        if (parseInt(data) > 0) {
            $('#cart-count').html(data).css({
                'padding-left': '10px'
            });
        } else {
            $('#cart-count').html("").css({
                'padding-left': ''
            });
        }
    });
}

function reviewCart() {
    $('#cart-image').modal('hide');
    $('#album').modal('hide');
    $('#cart').modal();
    $('#cart-items').empty();
    $.get("/api/get-cart.php", function (data) {
        for (var i = 0, len = data.length; i < len; i++) {
            for (var c = 0, zen = data[i].count; c < zen; c++) {
                var row = $("<tr>");
                row.attr('product-id', data[i].product);
                row.attr('product-type', data[i].product_type);
                row.attr('album-id', data[i].album);
                row.attr('image-id', data[i].image);
                row.attr('image-title', data[i].title);
                var remove = $("<td>");
                remove.addClass("text-center");
                var removeIcon = $("<i>");
                removeIcon.addClass("fa fa-trash error");
                removeIcon.css({
                    "cursor": "pointer"
                });
                removeIcon = removeFromCart(removeIcon);
                remove.append(removeIcon);
                row.append(remove);
                var preview = $("<td>");
                var previewDiv = $("<div>");
                previewDiv.css({
                    "background-image": "url('" + data[i].location + "')",
                    "background-size": "cover",
                    "background-position": "50%",
                    "width": "50px",
                    "height": "50px"
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
                price.html("$" + data[i].price);
                row.append(price);
                var options = $("<td>");
                if (data[i].options.length) {
                    options.addClass("item-option has-error");
                    var select = $("<select>");
                    select.addClass("form-control");
                    var opt = $('<option>');
                    opt.html("");
                    select.append(opt);
                    for (var j = 0, jen = data[i].options.length; j < jen; j++) {
                        opt = $('<option>');
                        opt.html(data[i].options[j]);
                        select.append(opt);
                    }
                    options.append(select);
                }
                row.append(options);
                $('#cart-items').append(row);
            }
        }
        calculateCost();
        $('#cart-shipping input').each(function () {
            validateCartInput($(this));
        });
        $('#cart-table select').off().bind("change keyup input", function () {
            validateCartInput($(this));
        });
    }, "json");
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
function arrayHasJSON(myArray, product, album, image) {
    var hasJSON = false;
    for (var i = 0, len = myArray.length; i < len; i++) {
        if (myArray[i].product === product && myArray[i].album === album && myArray[i].image === image) {
            hasJSON = true;
            break;
        }
    }
    return hasJSON;
}

function incrementProduct(myArray, product, album, image) {
    $.each(myArray, function (i, obj) {
        if (obj.product === product && obj.album === album && obj.image === image) {
            obj.count++;
        }
    });
    return myArray;
}

function removeFromCart(removeIcon) {
    removeIcon.click(function () {
        // TODO - we should really put in a confirm here
        $(this).closest('tr').remove();
        calculateCost();
        // update our database
        var cart = [];
        $('#cart-items tr').each(function () {
            var product = $(this).attr('product-id');
            var image = $(this).attr('image-id');
            var album = $(this).attr('album-id');
            if (arrayHasJSON(cart, product, album, image)) {
                cart = incrementProduct(cart, product, album, image);
            } else {
                var item = {};
                item.product = $(this).attr('product-id');
                item.album = $(this).attr('album-id');
                item.image = $(this).attr('image-id');
                item.count = 1;
                cart.push(item);
            }
        });
        $.post("/api/update-cart.php", {
            images: cart
        }).done(function (data) {
            // update our count on the page
            if (parseInt(data) > 0) {
                $('#cart-count').html(data).css({
                    'padding-left': '10px'
                });
            } else {
                $('#cart-count').html("").css({
                    'padding-left': ''
                });
            }
        });
    });
    return removeIcon;
}

function downloadImages(album, what) {
    BootstrapDialog.show({
        draggable: true,
        title: 'Terms Of Service',
        message: '<em class="fa fa-exclamation-triangle"></em> By downloading the selected files, you are agreeing to the right to copy, display, reproduce, enlarge and distribute said photographs taken by the Photographer in connection with the Services and in connection with the publication known as Saperstone Studios for personal use, and any reprints or reproductions, or excerpts thereof; all other rights are expressly reserved by and to Photographer.<br/><br/>While usage in accordance with above policies of selected files on public social media sites and personal websites for non-profit purposes is acceptable, any use of selected files in any publication, display, exhibit or paid medium are not permitted without express consent from Photographer.<br/><br/>Please note that only images you have expressly purchased rights to will be downloaded, even if additional images were selected for this download.',
        buttons: [{
            icon: 'glyphicon glyphicon-download-alt',
            label: ' Download',
            cssClass: 'btn-success',
            action: function (dialogInItself) {
                var $button = this; // 'this' here is a jQuery
                // object that
                // wrapping the <button> DOM element.
                var modal = $button.closest('.modal-content');
                modal.find('.bootstrap-dialog-body').append('<div id="compressing-download" class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>We are compressing your images for download. They should automatically start downloading shortly.</div>');
                $button.spin();
                dialogInItself.enableButtons(false);
                dialogInItself.setClosable(false);
                // send our update
                $.post("/api/download-selected-images.php", {
                    album: album,
                    what: what
                }, "json").done(function (data) {
                    data = jQuery.parseJSON(data);
                    if (data.hasOwnProperty('file')) {
                        window.location = data.file;
                        dialogInItself.close();
                    } else if (data.hasOwnProperty('error')) {
                        modal.find('.bootstrap-dialog-body').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data.error + '</div>');
                    } else {
                        modal.find('.bootstrap-dialog-body').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
                    }
                }).fail(function (xhr, status, error) {
                    if (xhr.responseText !== "") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
                    } else if (error === "Unauthorized") {
                        modal.find('.bootstrap-dialog-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
                    } else {
                        modal.find('.bootstrap-dialog-body').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
                    }
                }).always(function () {
                    $('#compressing-download').remove();
                    $button.stopSpin();
                    dialogInItself.enableButtons(true);
                    dialogInItself.setClosable(true);
                });
            }
        }, {
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }]
    });
}

function shareImages(album, what) {
    BootstrapDialog.show({
        draggable: true,
        title: '<em class="fa fa-frown-o"></em> Sorry',
        message: '<em class="fa fa-exclamation-triangle"> This functionality isn\'t available yet. Please check back soon.',
        buttons: [{
            label: 'Close',
            action: function (dialogInItself) {
                dialogInItself.close();
            }
        }]
    });
}

function submitImages() {
    $("#submit-send").prop("disabled", true);
    $("#submit-send").next().prop("disabled", true);
    $("#submit-send em").removeClass('fa fa-paper-plane').addClass('glyphicon glyphicon-asterisk icon-spin');
    $.post("/api/send-selected-images.php", {
        album: $('#submit').attr('album-id'),
        what: $('#submit').attr('what'),
        name: $('#submit-name').val(),
        email: $('#submit-email').val(),
        comment: $('#submit-comment').val()
    }).done(function (data) {
        if (data !== "") {
            $("#submit .modal-body").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data + '</div>');
        } else {
            $("#submit").modal('hide')
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#submit .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#submit .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $("#submit .modal-body").append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
        }
    }).always(function () {
        $('#submit-send').prop("disabled", false);
        $('#submit-send').next().prop("disabled", false);
        $('#submit-send em').addClass('fa fa-paper-plane').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}

function submitCart() {
    $('#cart-submit').prop("disabled", true);
    $('#cart-submit').next().prop("disabled", true);
    $('#cart-submit em').removeClass('fa fa-credit-card').addClass('glyphicon glyphicon-asterisk icon-spin');
    $('#cart .modal-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Thank you for submitting your request. Your request is being processed, " + "and you should be forwarded to paypal's payment screen within a few seconds. " + "If you are not, please <a class='gen' target='_blank' " + "href='mailto:billingerror@saperstonestudios.com'>contact us</a> and we'll try to resolve " + "your issue as soon as we can.</div>");

    var coupon;
    if ($('#cart-coupon').val() !== "") {
        coupon = $('#cart-coupon').val();
    }
    var order = [];
    $('#cart-items tr').each(function () {
        order.push({
            product: $(this).attr('product-id'),
            type: $(this).attr('product-type'),
            img: $(this).attr('image-id'),
            title: $(this).attr('image-title'),
            option: $(this).find('td.item-option select').val()
        });
    });
    var user = {
        name: $('#cart-name').val(),
        email: $('#cart-email').val(),
        phone: $('#cart-phone').val(),
        address: $('#cart-address').val(),
        city: $('#cart-city').val(),
        state: $('#cart-state').val(),
        zip: $('#cart-zip').val(),
    };
    $.post("/api/checkout.php", {
        user: user,
        order: order,
        coupon: coupon
    }, "json").done(function (data) {
        data = jQuery.parseJSON(data);
        if (data.hasOwnProperty('response') && data.response.Ack === "Success") {
            var link = "https://www.paypal.com/webscr?cmd=_express-checkout&token=" + data.response.Token;
            $('#cart .modal-body').append("<div class='alert alert-info'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Please wait while you are forwarded to paypal to pay your invoice. Alternatively, you can go to <a class='gen' href='" + link + "'>this link</a></div>.");
            window.location = link;
        } else if (data.hasOwnProperty('response') && data.response.Ack === "Failure" && data.response.Errors.LongMessage === "This transaction cannot be processed. The amount to be charged is zero.") {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Because your request was totaled for $0, there is no need to be forwarded to paypal. We will be contacting you shortly with more details about your order.</div>");
            // TODO - do stuff
        } else if (data.hasOwnProperty('response') && data.response.Ack === "Failure" && data.response.hasOwnProperty('Errors')) {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>There was a problem with submitting your order.<br/>" + data.response.Errors.LongMessage + "<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>contact our System Administrators</a> for more details, or try resubmitting.</div>");
        } else if (data.hasOwnProperty('error')) {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>There was a problem with submitting your order.<br/>" + data.error + "<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        } else {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>There was a problem with submitting your order.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $('#cart .modal-body').append("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>There was a problem with submitting your order.<br/>Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>Contact our System Administrators</a> for more details, or try resubmitting.</div>");
        }
    }).always(function () {
        $('#cart-submit').prop("disabled", false);
        $('#cart-submit').next().prop("disabled", false);
        $('#cart-submit em').addClass('fa fa-credit-card').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}

function submitNotifyEmail() {
    $("#notify-submit").prop("disabled", true);
    $("#notify-submit").next().prop("disabled", true);
    $("#notify-submit em").removeClass('fa fa-paper-plane').addClass('glyphicon glyphicon-asterisk icon-spin');
    $.post("/api/add-notification-email.php", {
        album: $('#submit').attr('album-id'),
        email: $('#notify-email').val()
    }).done(function (data) {
        if (data !== "") {
            $("#album-thumbs").after('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' + data + '</div>');
        } else {
            $("#album-thumbs").empty().after('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Your email address was successfully recorded. You will be notified once the images have been uploaded.</div>');
        }
    }).fail(function (xhr, status, error) {
        if (xhr.responseText !== "") {
            $('#album-thumbs').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>" + xhr.responseText + "</div>");
        } else if (error === "Unauthorized") {
            $('#album-thumbs').after("<div class='alert alert-danger'><a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>Your session has timed out, and you have been logged out. Please login again, and repeat your action.</div>");
        } else {
            $("#album-thumbs").after('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>Some unexpected error occurred while downloading your files. Please try again in a bit</div>');
        }
    }).always(function () {
        $('#notify-submit').prop("disabled", false);
        $('#notify-submit').next().prop("disabled", false);
        $('#notify-submit em').addClass('fa fa-paper-plane').removeClass('glyphicon glyphicon-asterisk icon-spin');
    });
}