<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

$album;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['album'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $album = ( int ) $_GET ['album'];
}

include_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

$sql = "SELECT * FROM `albums` WHERE id = '" . $album . "';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
// if the album doesn't exist, throw a 404 error
if (! $album_info ['name']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    $conn->disconnect ();
    exit ();
}

// if not an admin and no code exists for the album
if (! $user->isAdmin () && ($album_info ['code'] == "" || ($album_info ['code'] != "" && (! isset ( $_SESSION ['searched'] ) || ! isset ( $_SESSION ['searched'] [$album] ) || ! $_SESSION ['searched'] [$album])))) {
    // if not logged in, throw an error
    if (! $user->isLoggedIn ()) {
        header ( 'HTTP/1.0 401 Unauthorized' );
        include "../errors/401.php";
        $conn->disconnect ();
        exit ();
        // if logged in
    } else {
        $sql = "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "';";
        $result = mysqli_query ( $conn->db, $sql );
        $albums = array ();
        while ( $r = mysqli_fetch_assoc ( $result ) ) {
            $albums [] = $r ['album'];
        }
        // and if not in album user list
        if (! in_array ( $album, $albums )) {
            header ( 'HTTP/1.0 401 Unauthorized' );
            include "../errors/401.php";
            $conn->disconnect ();
            exit ();
        }
    }
}

// update our last accessed
if (! $user->isAdmin ()) {
    $sql = "UPDATE `albums` SET `lastAccessed` = now() WHERE id = '" . $album . "';";
    mysqli_query ( $conn->db, $sql );
}
if ($user->isLoggedIn ()) {
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Visited Album', NULL, $album );" );
}

$isAlbumDownloadable = mysqli_num_rows ( mysqli_query ( $conn->db, "SELECT * FROM `download_rights` WHERE user = '0' AND album = '" . $album . "';" ) );
$isAlbumSharable = mysqli_num_rows ( mysqli_query ( $conn->db, "SELECT * FROM `share_rights` WHERE user = '0' AND album = '" . $album . "';" ) );
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">
<link href="/css/uploadfile.css" rel="stylesheet">
<style>
footer {
    margin-bottom: 55px;
}
</style>

</head>

<body>

    <?php
    require_once "../nav.php";
    
    // get our gallery images
    $sql = "SELECT album_images.*, albums.name, albums.description, albums.date FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '" . $album . "' ORDER BY `sequence`;";
    $result = mysqli_query ( $conn->db, $sql );
    $images = array ();
    while ( $row = mysqli_fetch_assoc ( $result ) ) {
        $images [] = $row;
    }
    ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">
                    <span id='album-title'><?php echo $album_info['name']; ?></span> <small><?php echo $album_info['description']; ?></small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <?php
                    if ($user->isLoggedIn ()) {
                        echo "<li><a href=\"index.php\">Albums</a></li>";
                    }
                    ?>
                    <li class="active"><?php echo $album_info['name']; ?></li>
                    <?php
                    if ($user->isAdmin () || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner'])) {
                        ?>
                    <li class="no-before pull-right"><button
                            type="button" id="edit-album-btn" class="btn btn-xs btn-warning"
                            data-toggle="tooltip" data-placement="left"
                            title="Edit Album Details">
                            <i class="fa fa-pencil-square-o"></i>
                        </button></li>
                    <?php
                    }
                    ?>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div id="album-thumbs" class="row">
            <?php
            if (count ( $images ) > 0) {
                ?>
            <div id="col-0" class="col-md-3 col-sm-6 col-gallery"></div>
            <div id="col-1" class="col-md-3 col-sm-6 col-gallery"></div>
            <div id="col-2" class="col-md-3 col-sm-6 col-gallery"></div>
            <div id="col-3" class="col-md-3 col-sm-6 col-gallery"></div>
            <?php
            } else {
                ?>
            <div class="col-md-12 text-center">Sorry, no images have
                been uploaded to your gallery yet. You can submit your email to be
                notified when images are added if you would like. Your email address
                will not be used for any other purposes.</div>
            <div class="col-md-4 col-md-offset-4 text-center">
                <form>
                    <label class="sr-only" for="cart-email">Email</label> <input
                        id="notify-email" type="email" placeholder="Email"
                        class="form-control" value="<?php echo $user->getEmail(); ?>"
                        required />
                </form>
                <button id="notify-submit" type="submit" class="btn btn-primary">
                    <em class="fa fa-paper-plane-o" aria-hidden="true"></em> Submit
                </button>
            </div>
            <?php
            }
            ?>
        </div>
        <!-- /.row -->
        
        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Slideshow Modal -->
    <div id="album" album-id="<?php echo $album; ?>"
        class="modal fade modal-carousel" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $album_info['name']; ?>
                        <small><?php echo $album_info['description']; ?></small>
                    </h4>
                </div>
                <div class="modal-body">
                    <!-- Carousel -->
                    <div id="album-carousel"
                        class="carousel slide carousel-three-by-two" data-pause="false"
                        data-interval="false">
                        <!-- Indicators -->
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                        }
                        ?>

                        <!-- Wrapper for slides -->
                        <div class="carousel-inner">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $active_class = "";
                            if ($num == 0) {
                                $active_class = " active";
                            }
                            echo "<div class='item$active_class'>";
                            echo "    <div class='contain' album-id='" . $album_info ['id'] . "' image-id='" . $image ['sequence'] . "'";
                            echo "        alt='" . $image ['title'] . "' style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                            echo "    <div class='carousel-caption'>";
                            echo "        <h2>" . $image ['caption'] . "</h2>";
                            echo "    </div>";
                            echo "</div>";
                        }
                        ?>
                        </div>

                        <!-- Controls -->
                        <a class="left carousel-control" href="#album-carousel"
                            data-slide="prev"> <span class="icon-prev"></span>
                        </a> <a class="right carousel-control" href="#album-carousel"
                            data-slide="next"> <span class="icon-next"></span>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="pull-left">
                        <?php
                        if (! $user->isLoggedIn () && ! $isAlbumDownloadable) {
                            ?>
                        <div class="tooltip-wrapper disabled"
                            data-toggle="tooltip" data-placement="top"
                            title="Login or create an account for this feature.">
                            <button type="button" class="btn btn-default" disabled>
                                <em class="fa fa-download"></em> Download
                            </button>
                        </div>
                        <?php
                        } else {
                            ?>
                        <button id="downloadable-image-btn"
                            type="button"
                            class="btn btn-default btn-action btn-success hidden">
                            <em class="fa fa-download"></em> Download
                        </button>
                        <button id="not-downloadable-image-btn" type="button"
                            class="btn btn-default btn-action btn-warning hidden">
                            <em class="fa fa-credit-card"></em> Purchase
                        </button>
                        <?php
                        }
                        if (! $user->isLoggedIn () && ! $isAlbumSharable) {
                            ?>
                        <div class="tooltip-wrapper disabled"
                            data-toggle="tooltip" data-placement="top"
                            title="Login or create an account for this feature.">
                            <button type="button" class="btn btn-default" disabled>
                                <em class="fa fa-share"></em> Share
                            </button>
                        </div>
                        <?php
                        } else {
                            ?>
                        <button id="shareable-image-btn" type="button"
                            class="btn btn-default btn-action btn-success hidden">
                            <em class="fa fa-share"></em> Share
                        </button>
                        <div id="not-shareable-image-btn" class="tooltip-wrapper disabled"
                            data-toggle="tooltip" data-placement="top"
                            title="Purchase social media rights to this image in order to share it on social media.">
                            <button type="button" class="btn btn-default btn-action">
                                <em class="fa fa-share"></em> Share
                            </button>
                        </div>
                        <?php
                        }
                        if (! $user->isLoggedIn ()) {
                            ?>
                        <div class="tooltip-wrapper disabled"
                            data-toggle="tooltip" data-placement="top"
                            title="Login or create an account for this feature.">
                            <button id="cart-image-btn" type="button"
                                class="btn btn-default btn-warning" disabled>
                                <em class="fa fa-shopping-cart"></em> Add to Cart
                            </button>
                        </div>
                        <?php
                        } else {
                            ?>
                        <button id="cart-image-btn" type="button"
                            class="btn btn-default btn-warning btn-action">
                            <em class="fa fa-shopping-cart"></em> Add to Cart
                        </button>
                        <?php
                        }
                        ?>
                        <button id="submit-image-btn" type="button"
                            class="btn btn-default btn-action btn-success">
                            <em class="fa fa-paper-plane"></em> Submit
                        </button>
                        <button id="set-favorite-image-btn" type="button"
                            class="btn btn-default btn-action">
                            <em class="fa fa-heart"></em> Favorite
                        </button>
                        <button id="unset-favorite-image-btn" type="button"
                            class="btn btn-default btn-success btn-action hidden">
                            <em class="fa fa-heart error"></em> Favorite
                        </button>
                        <?php
                        if ($user->isAdmin ()) {
                            ?>
                        <button id="access-image-btn" type="button"
                            class="btn btn-default btn-info btn-action">
                            <em class="fa fa-picture-o"></em> Access
                        </button>
                        <button id="delete-image-btn" type="button"
                            class="btn btn-default btn-danger btn-action">
                            <em class="fa fa-trash"></em> Delete
                        </button>
                        <?php
                        }
                        ?>
                    </span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->

    <!-- Favorites Modal -->
    <div id="favorites" album-id="<?php echo $album; ?>"
        class="modal fade modal-carousel" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <span>My Favorite Images for</span> <strong><?php echo $album_info['name']; ?></strong>
                    </h4>
                </div>
                <div class="modal-body mine">
                    <ul id="favorites-list" class="list-inline"></ul>
                </div>
                <?php
                if ($user->isAdmin ()) {
                    ?>
                <div class="modal-body all hidden">
                    <ul id="favorites-all-title" class="nav nav-tabs"></ul>
                    <div id="favorites-all-content" class="tab-content"></div>
                </div>
                <?php
                }
                ?>
                <div class="modal-footer">
                    <span class="pull-left">
                        <?php
                        if (! $user->isLoggedIn () && ! $isAlbumDownloadable) {
                            ?>
                        <div class="tooltip-wrapper disabled"
                            data-toggle="tooltip" data-placement="top"
                            title="Login or create an account for this feature.">
                            <button type="button" class="btn btn-default" disabled>
                                <em class="fa fa-download"></em> Download Favorites
                            </button>
                        </div>
                        <?php
                        } else {
                            ?>
                        <button id="downloadable-favorites-btn"
                            type="button" class="btn btn-default btn-action btn-success">
                            <em class="fa fa-download"></em> Download Favorites
                        </button> 
                        <?php
                        }
                        if (! $user->isLoggedIn () && ! $isAlbumSharable) {
                            ?>
                        <div class="tooltip-wrapper disabled"
                            data-toggle="tooltip" data-placement="top"
                            title="Login or create an account for this feature.">
                            <button type="button" class="btn btn-default" disabled>
                                <em class="fa fa-share"></em> Share Favorites
                            </button>
                        </div>
                        <?php
                        } else {
                            ?>
                        <button id="shareable-favorites-btn"
                            type="button" class="btn btn-default btn-action btn-success">
                            <em class="fa fa-share"></em> Share Favorites
                        </button> 
                        <?php
                        }
                        ?>
                        <button id="submit-favorites-btn" type="button"
                            class="btn btn-default btn-action btn-success">
                            <em class="fa fa-paper-plane"></em> Submit Favorites
                        </button>
                    </span>
                    <?php
                    if ($user->isAdmin ()) {
                        ?>
                    <button id="view-all-favorites-btn" type="button"
                        class="btn btn-default btn-action btn-info">
                        <em class="fa fa-search"></em> View All Favorites
                    </button>
                    <button id="view-my-favorites-btn" type="button"
                        class="btn btn-default btn-action btn-info hidden">
                        <em class="fa fa-search"></em> View My Favorites
                    </button> 
                        <?php
                    }
                    ?>
                    <button type="button" class="btn btn-default"
                        data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->

    <!-- Single Image Cart Modal -->
    <div id="cart-image" album-id="<?php echo $album; ?>"
        class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">What Product Do You Want?</h4>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs">
                        <?php
                        $sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'product_types' AND COLUMN_NAME = 'category';";
                        $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
                        $categories = explode ( ",", str_replace ( "'", "", substr ( $row ['COLUMN_TYPE'], 5, (strlen ( $row ['COLUMN_TYPE'] ) - 6) ) ) );
                        $categories = array_diff ( $categories, [ 
                                "other" 
                        ] );
                        
                        $counter = 0;
                        foreach ( $categories as $category ) {
                            ?>
                        <li
                            <?php if ($counter == 0) { echo " class='active'"; }?>><a
                            href="#<?php echo $category; ?>"><?php echo ucwords($category); ?></a></li>
                        <?php
                            $counter ++;
                        }
                        ?>
                    </ul>
                    <div class="tab-content">
                           <?php
                        $counter = 0;
                        foreach ( $categories as $category ) {
                            ?>
                           <div id="<?php echo $category; ?>"
                            class="row tab-pane fade<?php if ($counter == 0) { echo " in active"; }?>">
                               <?php
                            $sql = "SELECT `id`,`name` FROM `product_types` WHERE `category` = '$category';";
                            $result = mysqli_query ( $conn->db, $sql );
                            while ( $r = mysqli_fetch_assoc ( $result ) ) {
                                ?>
                            <div class="col-md-4 col-sm-6"
                                product-type='<?php echo $r['id']; ?>'>
                                <h3><?php echo ucwords($r['name']); ?></h3>
                                <table class="table borderless">
                                <?php
                                $sql = "SELECT * FROM `products` WHERE `product_type` = '" . $r ['id'] . "';";
                                $sesult = mysqli_query ( $conn->db, $sql );
                                while ( $s = mysqli_fetch_assoc ( $sesult ) ) {
                                    $max = "";
                                    if ($r ['id'] == "10") {
                                        $max = " max='1'";
                                    }
                                    ?>
                                <tr product-id='<?php echo $s['id']; ?>'>
                                        <td class="product-size"><?php echo $s['size']; ?></td>
                                        <td class="product-count"><input class="form-control input-sm"
                                            type="number" min="0" <?php echo $max; ?> /></td>
                                        <td class="product-price">$<?php echo $s['price']; ?></td>
                                        <td class="product-total" style="width: 25%">--</td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </table>
                            </div>
                            <?php
                            }
                            ?>
                           </div>
                           <?php
                            $counter ++;
                        }
                        ?>
                       </div>
                </div>
                <div class="modal-footer">
                    <button id="reviewOrder" type="button"
                        class="btn btn-default btn-warning">
                        <em class="fa fa-shopping-cart"></em> Review Order & Checkout
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->

    <!-- Gallery Cart Modal -->
    <div id="cart" album-id="<?php echo $album; ?>" class="modal fade"
        role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Review Your Cart</h4>
                </div>
                <div class="modal-body">
                    <div id="cart-shipping">
                        <div class="row">
                            <p class="text-center">Shipping Information</p>
                        </div>
                        <div class="row">
                            <div class="col-md-4 has-error">
                                <label class="sr-only" for="cart-name">Name</label> <input
                                    id="cart-name" type="text" placeholder="Name"
                                    class="form-control" value="<?php echo $user->getName(); ?>"
                                    required />
                            </div>
                            <div class="col-md-4 has-error">
                                <label class="sr-only" for="cart-email">Email</label> <input
                                    id="cart-email" type="email" placeholder="Email"
                                    class="form-control" value="<?php echo $user->getEmail(); ?>"
                                    required />
                            </div>
                            <div class="col-md-4 has-error">
                                <label class="sr-only" for="cart-phone">Phone</label> <input
                                    id="cart-phone" type="tel" placeholder="Phone"
                                    class="form-control" required />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 has-error">
                                <label class="sr-only" for="cart-address">Address</label> <input
                                    id="cart-address" type="text" placeholder="Address"
                                    class="form-control" required />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 has-error">
                                <label class="sr-only" for="cart-city">City</label> <input
                                    id="cart-city" type="text" placeholder="City"
                                    class="form-control" required />
                            </div>
                            <div class="col-md-4 has-error">
                                <label class="sr-only" for="cart-state">State</label> <input
                                    id="cart-state" type="text" placeholder="State"
                                    class="form-control" required />
                            </div>
                            <div class="col-md-4 has-error">
                                <label class="sr-only" for="cart-zip">Zip</label> <input
                                    id="cart-zip" type="text" placeholder="Zip Code"
                                    class="form-control" required />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <p></p>
                        <p class="text-center">Please confirm details for each item</p>
                    </div>
                    <div class="row">
                        <table id="cart-table" class="table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Preview</th>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Price</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">
                                    
                                    <th>Tax:</th>
                                    <th id="cart-tax"></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td colspan="3"></td>
                                    <!--                                     <td colspan="3"><label class="sr-only" for="cart-coupon">Coupon</label> -->
                                    <!--                                         <input id="cart-coupon" type="text" placeholder="Coupon Code" -->
                                    <!--                                         class="form-control" /></td> -->
                                    <th id="cart-total"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bootstrap-dialog">
                    <button id="cart-submit" type="button"
                        class="btn btn-default btn-success" disabled>
                        <em class="fa fa-credit-card"></em> Place Order
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->

    <!-- Submit Selections Modal -->
    <div id="submit" album-id="<?php echo $album; ?>" what=""
        class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Submit My Selection</h4>
                </div>
                <div class="modal-body">
                    <p>
                        <em class="fa fa-exclamation-triangle"></em> Have you finished
                        making your selections?
                    </p>
                    <p>Submit your selections to us, along with any comments you may
                        have. We will receive your request and start processing your order
                        as soon as possible.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="sr-only" for="submit-name">Name</label> <input
                                id="submit-name" type="text" placeholder="Name"
                                class="form-control" value="<?php echo $user->getName(); ?>"
                                required />
                        </div>
                        <div class="col-md-6">
                            <label class="sr-only" for="submit-email">Email</label> <input
                                id="submit-email" type="email" placeholder="Email"
                                class="form-control" value="<?php echo $user->getEmail(); ?>"
                                required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="sr-only" for="submit-comment">Comment</label>
                            <textarea id="submit-comment" type="text" placeholder="Comment"
                                class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bootstrap-dialog">
                    <button id="submit-send" type="button"
                        class="btn btn-default btn-success">
                        <em class="fa fa-paper-plane"></em> Submit Selection
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Modal -->

    <!-- Actions For the Page -->
    <nav class="navbar navbar-actions navbar-fixed-bottom breadcrumb">
        <div class="container text-center">
        
            <?php
            if (! $user->isLoggedIn () && ! $isAlbumDownloadable) {
                ?>
            <span class="text-center"><div
                    class="tooltip-wrapper disabled" data-toggle="tooltip"
                    data-placement="top"
                    title="Login or create an account for this feature.">
                    <button type="button" class="btn btn-default" disabled>
                        <em class="fa fa-download"></em> Download All
                    </button>
                </div></span> 
                <?php
            } else {
                ?>
                <span class="text-center"><button
                    id="downloadable-all-btn" type="button"
                    class="btn btn-default btn-action btn-success">
                    <em class="fa fa-download"></em> Download All
                </button></span>
                <?php
            }
            if (! $user->isLoggedIn () && ! $isAlbumSharable) {
                ?>
                 <span class="text-center"><div
                    class="tooltip-wrapper disabled" data-toggle="tooltip"
                    data-placement="top"
                    title="Login or create an account for this feature.">
                    <button type="button" class="btn btn-default" disabled>
                        <em class="fa fa-share"></em> Share All
                    </button>
                </div></span>
                <?php
            } else {
                ?> <span class="text-center"><button
                    id="shareable-all-btn" type="button"
                    class="btn btn-default btn-action btn-success">
                    <em class="fa fa-share"></em> Share All
                </button></span>
                <?php
            }
            if (! $user->isLoggedIn ()) {
                ?>
                <span class="text-center"><div
                    class="tooltip-wrapper disabled" data-toggle="tooltip"
                    data-placement="top"
                    title="Login or create an account for this feature.">
                    <button id="cart-btn" type="button"
                        class="btn btn-default btn-warning" disabled>
                        <em class="fa fa-shopping-cart"></em> Cart <strong id="cart-count"
                            class="error"></strong>
                    </button>
                </div></span>
            <?php
            } else {
                $sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '" . $user->getId () . "';";
                $result = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
                if ($result ['total'] > 0) {
                    ?>
            <span class="text-center"><button id="cart-btn"
                    type="button" class="btn btn-default btn-warning">
                    <em class="fa fa-shopping-cart"></em> Cart <strong id="cart-count"
                        class="error" style="padding-left: 10px;"><?php echo $result['total']; ?></strong>
                </button></span>
            <?php
                } else {
                    ?>
            <span class="text-center"><button id="cart-btn"
                    type="button" class="btn btn-default btn-warning">
                    <em class="fa fa-shopping-cart"></em> Cart <strong id="cart-count"
                        class="error"></strong>
                </button></span>
            <?php
                }
            }
            ?>
            <?php
            $sql = "SELECT COUNT(*) AS total FROM `favorites` WHERE `user` = '" . $user->getId () . "' AND `album` = '" . $album . "';";
            $result = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
            if ($result ['total'] > 0) {
                ?>
            <span class="text-center"><button id="favorite-btn"
                    type="button" class="btn btn-default btn-success">
                    <em class="fa fa-heart error"></em> Favorites <strong
                        id="favorite-count" class="error" style="padding-left: 10px;"><?php echo $result['total']; ?></strong>
                </button></span>
            <?php
            } else {
                ?>
            <span class="text-center"><button id="favorite-btn"
                    type="button" class="btn btn-default btn-success">
                    <em class="fa fa-heart error"></em> Favorites <strong
                        id="favorite-count" class="error"></strong>
                </button></span>
            <?php
            }
            if ($user->isAdmin ()) {
                ?>
            <span class="text-center"><button id="access-btn"
                    type="button" class="btn btn-default btn-info">
                    <em class="fa fa-picture-o"></em> Set Access
                </button></span>
            <?php
            }
            ?>
            
        </div>
    </nav>

    <!-- Gallery JavaScript -->
    <script src="/js/album.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.3.1/js/md5.min.js"></script>
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/album-admin.js"></script>
    <script src="/js/albums-admin.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    }
    if ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner']) {
        ?>
        <script src="/js/albums-uploader.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
        $conn->disconnect ();
    }
    ?>

    <!-- Script to Activate the Gallery -->
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        var album = new Album( "<?php echo $album_info['id']; ?>", 4, <?php echo count($images); ?> );
        
         var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($images); ?> ) {
                loaded = album.loadImages();
            }
        });
    </script>

</body>

</html>
