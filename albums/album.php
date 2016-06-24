<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (! isset ( $_GET ['album'] )) { // if no album is set, throw a 404 error
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
}
require "../php/sql.php";
$sql = "SELECT * FROM `albums` WHERE id = '" . $_GET ['album'] . "';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if (! $album_info ['name']) { // if the album doesn't exist, throw a 404 error
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
}

if (getRole () != "admin" && $album_info ['code'] == "") { // if not an admin and no code exists for the album
    if (! isLoggedIn ()) { // if not logged in, throw an error
        header ( 'HTTP/1.0 401 Unauthorized' );
        include "../errors/401.php";
        exit ();
    } else { // if logged in
        $sql = "SELECT * FROM albums_for_users WHERE user = '" . getUserId () . "';";
        $result = mysqli_query ( $db, $sql );
        $albums = array ();
        while ( $r = mysqli_fetch_assoc ( $result ) ) {
            $albums [] = $r ['album'];
        }
        if (! in_array ( $_GET ['album'], $albums )) { // and if not in album user list
            header ( 'HTTP/1.0 401 Unauthorized' );
            include "../errors/401.php";
            exit ();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

	<?php require_once "../header.php"; ?>
	<link href="/css/hover-effect.css" rel="stylesheet">
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
    require "../php/sql.php";
    $sql = "SELECT album_images.*, albums.name, albums.description, albums.date FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '" . $_GET ['album'] . "' ORDER BY `sequence`;";
    $result = mysqli_query ( $db, $sql );
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
				<h1 class="page-header text-center"><span id='album-title'><?php echo $album_info['name']; ?></span>
					<small><?php echo $album_info['description']; ?></small>
				</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<?php
                    if (isLoggedIn ()) {
                        echo "<li><a href=\"/albums/index.php\">Albums</a></li>";
                    }
                    ?>
					<li class="active"><?php echo $album_info['name']; ?></li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div id="album-thumbs" class="row">
			<div id="col-0"	class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-1" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-2" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-3" class="col-md-3 col-sm-6 col-gallery"></div>
		</div>
		<!-- /.row -->
		
        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Slideshow Modal -->
	<div id="album" album-id="<?php echo $_GET ['album']; ?>" class="modal fade modal-carousel" role="dialog">
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
						class="carousel slide carousel-three-by-two" data-pause="false" data-interval="false">
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
                            echo "	<div class='contain' album-id='" . $album_info['id'] . "' image-id='" . $image['sequence'] . "'";
                            echo "		alt='" . $image['title'] . "' style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                            echo "	<div class='carousel-caption'>";
                            echo "		<h2>" . $image ['caption'] . "</h2>";
                            echo "	</div>";
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
                        if (! isLoggedIn ()) {
                        ?>
    					<div class="tooltip-wrapper disabled" data-toggle="tooltip"
								data-placement="top"
								title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled><i class="fa fa-download"></i> Download</button>
						</div>
						<div class="tooltip-wrapper disabled" data-toggle="tooltip"
								data-placement="top"
								title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled><i class="fa fa-share"></i> Share</button>
						</div>
						<div class="tooltip-wrapper disabled" data-toggle="tooltip"
								data-placement="top"
								title="Login or create an account for this feature.">
							<button id="cart-image-btn" type="button" class="btn btn-default btn-warning" disabled><i class="fa fa-shopping-cart"></i> Add to
								Cart</button>
						</div>
					    <?php
                        } else {
                        ?>
    					<button type="button" class="btn btn-default btn-action"><i class="fa fa-download"></i> Download</button>
						<button type="button" class="btn btn-default btn-action"><i class="fa fa-share"></i> Share</button>
						<button id="cart-image-btn" type="button" class="btn btn-default btn-warning btn-action"><i class="fa fa-shopping-cart"></i> Add to Cart</button>
						<?php
                        }
                        ?>
    					<button id="set-favorite-image-btn" type="button" class="btn btn-default btn-action"><i class="fa fa-heart"></i> Favorite</button>
    					<button id="unset-favorite-image-btn" type="button" class="btn btn-default btn-success btn-action hidden"><i class="fa fa-heart error"></i> Favorite</button>
    					<?php 
                        if (getRole () == "admin") {
                        ?>
    					<button id="access-image-btn" type="button"
								class="btn btn-default btn-info btn-action"><i class="fa fa-picture-o"></i> Access</button>
    					<button id="delete-image-btn" type="button"
								class="btn btn-default btn-danger btn-action"><i class="fa fa-trash"></i> Delete</button>
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
	<div id="favorites" album-id="<?php echo $_GET ['album']; ?>" class="modal fade modal-carousel" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">My Favorite Images for <b><?php echo $album_info['name']; ?></b></h4>
				</div>
				<div class="modal-body">
					<ul id="favorites-list" class="list-inline"></ul>
				</div>
				<div class="modal-footer">
					<span class="pull-left">
						<?php
                        if (! isLoggedIn ()) {
                        ?>
    					<div class="tooltip-wrapper disabled" data-toggle="tooltip"
								data-placement="top"
								title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled><i class="fa fa-download"></i> Download
								Favorites</button>
						</div>
						<div class="tooltip-wrapper disabled" data-toggle="tooltip"
								data-placement="top"
								title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled><i class="fa fa-share"></i> Share
								Favorites </button>
						</div>
					    <?php
                        } else {
                        ?>
    					<button type="button" class="btn btn-default btn-action"><i class="fa fa-download"></i> Download Image</button>
						<button type="button" class="btn btn-default btn-action"><i class="fa fa-share"></i> Share Image</button>
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
	
	<!-- Actions For the Page -->
	<nav class="navbar navbar-actions navbar-fixed-bottom breadcrumb">
		<div class="container text-center">
		
			<?php
            if (! isLoggedIn ()) {
            ?>
			<span class="text-center"><div class="tooltip-wrapper disabled" data-toggle="tooltip"
					data-placement="top"
					title="Login or create an account for this feature.">
				<button type="button" class="btn btn-default" disabled>Purcahse/Download
						All</button>
			</div></span>
			<span class="text-center"><div class="tooltip-wrapper disabled" data-toggle="tooltip"
					data-placement="top"
					title="Login or create an account for this feature.">
				<button type="button" class="btn btn-default" disabled>Purcahse/Share
						All</button>
			</div></span>
			<span class="text-center"><div class="tooltip-wrapper disabled" data-toggle="tooltip"
					data-placement="top"
					title="Login or create an account for this feature.">
				<button id="cart-btn" type="button" class="btn btn-default btn-warning" disabled>Cart</button>
			</div></span>
		    <?php
            } else {
            ?>
			<span class="text-center"><button type="button" class="btn btn-default"><i class="fa fa-credit-card"></i>/<i class="fa fa-download"></i> Purcahse/Download
					All</button></span>
			<span class="text-center"><button type="button" class="btn btn-default"><i class="fa fa-credit-card"></i>/<i class="fa fa-share"></i> Purcahse/Share 
					All</button></span>
			<span class="text-center"><button id="cart-btn" type="button" class="btn btn-default btn-warning"><i class="fa fa-shopping-cart"></i> Cart</button></span>
			<?php
            }
            ?>
			<span class="text-center"><button id="favorite-btn" type="button" class="btn btn-default btn-success"><i class="fa fa-heart error"></i> Favorites</button></span>
			<?php
            if (getRole () == "admin") {
            ?>
            <span class="text-center"><button id="access-btn" type="button" class="btn btn-default btn-info"><i class="fa fa-picture-o"></i> Access</button></span>
			<?php
            }
            ?>
			
		</div>
	</nav>

	<!-- Gallery JavaScript -->
	<script src="/js/album.js"></script>
	<?php
	if(getRole() == "admin") {
	?>
	<script src="/js/album-admin.js"></script>
	<?php
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