<?php
$what;
if (! isset ( $_GET ['w'] )) { // if no album is set, throw a 404 error
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $what = $_GET ['w'];
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
    $nav = "portrait";
    require_once "../nav.php";
    
    // get our gallery images
    require_once "../php/sql.php";
    $conn = new sql ();
    $conn->connect ();
    $sql = "SELECT * FROM `galleries` WHERE id = '$what';";
    $details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
    $sql = "SELECT * FROM `gallery_images` WHERE gallery = '$what' ORDER BY `sequence`;";
    $result = mysqli_query ( $conn->db, $sql );
    $images = array ();
    while ( $row = mysqli_fetch_assoc ( $result ) ) {
        $images [] = $row;
    }
    $conn->disconnect ();
    ?>
    
    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center"><?php echo $details['title']; ?> Gallery</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="index.php">Portraits</a></li>
					<li><a href="gallery.php">Gallery</a></li>
					<li class="active"><?php echo $details['title']; ?></li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div id="col-0" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-1" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-2" class="col-md-3 col-sm-6 col-gallery"></div>
			<div id="col-3" class="col-md-3 col-sm-6 col-gallery"></div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Slideshow Modal -->
	<div id="<?php echo $details['name']; ?>" class="modal fade modal-carousel"
		role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><?php echo $details['title']; ?> Gallery</h4>
				</div>
				<div class="modal-body">
					<!-- Carousel -->
					<div id="<?php echo $details['name']; ?>-carousel"
						class="carousel slide carousel-three-by-two">
						<!-- Indicators -->
						<ol class="carousel-indicators">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                            echo "<li data-target='#${details['name']}-carousel' data-slide-to='$num'$class></li>";
                        }
                        ?>
                    </ol>

						<!-- Wrapper for slides -->
						<div class="carousel-inner">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $active_class = "";
                            if ($num == 0) {
                                $active_class = " active";
                            }
                            echo "<div class='item$active_class'>";
                            echo "    <div class='contain' gallery-id='$what' image-id='" . $image ['sequence'] . "'";
                            echo "        alt='" . $image ['title'] . "' style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                            echo "    <div class='carousel-caption'>";
                            echo "        <h2>" . $image ['caption'] . "</h2>";
                            echo "    </div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

						<!-- Controls -->
						<a class="left carousel-control"
							href="#<?php echo $details['name']; ?>-carousel" data-slide="prev"> <span
							class="icon-prev"></span>
						</a> <a class="right carousel-control"
							href="#<?php echo $details['name']; ?>-carousel" data-slide="next"> <span
							class="icon-next"></span>
						</a>
					</div>
				</div>
				<div class="modal-footer">
					<span class="pull-left">
						<?php
                        if ($user->isAdmin ()) {
                        ?>
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

	<!-- Gallery JavaScript -->
	<script src="/js/gallery.js"></script>
	
	<?php
	if ($user->isAdmin ()) {
	?>
	<script src="/js/gallery-admin.js"></script>
	<?php
    }
    ?>

	<!-- Script to Activate the Gallery -->
	<script>
        var gallery = new Gallery( "<?php echo $details['name']; ?>", 4, <?php echo count($images); ?> );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($images); ?> ) {
                loaded = gallery.loadImages();
            }
        });
    </script>

</body>

</html>