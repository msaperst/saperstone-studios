<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

// TODO - need to put in some authentication checks here

if (! isset ( $_GET ['album'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
}
require "../php/sql.php";
$sql = "SELECT * FROM `albums` WHERE id = '" . $_GET ['album'] . "';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $db, $sql ) );
if (! $album_info ['name']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

	<?php require_once "../header.php"; ?>
	<link href="/css/hover-effect.css" rel="stylesheet">

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
				<h1 class="page-header text-center"><?php echo $album_info['name']; ?>
					<small><?php echo $album_info['description']; ?></small>
				</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<?php
    if (getRole () == "admin") {
        echo "<li><a href=\"/albums/manage.php\">Albums</a></li>";
    } else {
        echo "<li><a href=\"/albums/index.php\">Albums</a></li>";
    }
    ?>
					<li class="active"><?php echo $album_info['name']; ?></li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div id="col-0"
				class="col-md-3 col-sm-6 col-gallery col-gallery-left"></div>
			<div id="col-1" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-2" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-3"
				class="col-md-3 col-sm-6 col-gallery col-gallery-right"></div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Slideshow Modal -->
	<div id="album" class="modal fade modal-carousel" role="dialog">
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
						class="carousel slide carousel-three-by-two">
						<!-- Indicators -->
						<!-- 						<ol class="carousel-indicators"> -->
            			<?php
            foreach ( $images as $num => $image ) {
                $class = "";
                if ($num == 0) {
                    $class = " class='active'";
                }
                // echo "<li data-target='#album-carousel' data-slide-to='$num'$class></li>";
            }
            ?>
<!--             		</ol> -->

						<!-- Wrapper for slides -->
						<div class="carousel-inner">
            			<?php
            foreach ( $images as $num => $image ) {
                $active_class = "";
                if ($num == 0) {
                    $active_class = " active";
                }
                echo "<div class='item$active_class'>";
                echo "	<div class='contain'";
                echo "		style=\"background-image: url('" . $image ['location'] . "');\"></div>";
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
    $disabled = "";
    if (! isLoggedIn ()) {
        ?>
        					<div class="tooltip-wrapper disabled" data-toggle="tooltip"
							data-placement="top"
							title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled>Purcahse/Download
								Image</button>
						</div>
						<div class="tooltip-wrapper disabled" data-toggle="tooltip"
							data-placement="top"
							title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled>Purcahse/Share
								Image</button>
						</div>
						<div class="tooltip-wrapper disabled" data-toggle="tooltip"
							data-placement="top"
							title="Login or create an account for this feature.">
							<button type="button" class="btn btn-default" disabled>Add to
								Cart</button>
						</div>
					    <?php
    } else {
        ?>
    					<button type="button" class="btn btn-default">Purcahse/Download
							Image</button>
						<button type="button" class="btn btn-default">Purcahse/Share Image</button>
						<button type="button" class="btn btn-default">Add to Cart</button>
						<?php
    }
    ?>
    					<button type="button" class="btn btn-default">Set/Unset As
							Favorite</button>
					</span>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>

		</div>
	</div>
	<!-- End of Modal -->

	<!-- Gallery JavaScript -->
	<script src="/js/album.js"></script>

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