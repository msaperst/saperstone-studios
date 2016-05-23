<!DOCTYPE html>
<html lang="en">

<head>

	<?php require "../header.php"; ?>
	<link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php
    $nav = "portrait";
    require "../nav.php";
    
    // get our gallery images
    require "../php/sql.php";
    $sql = "SELECT * FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.name = 'portrait-maternity' ORDER BY `sequence`;";
    $result = mysqli_query ( $db, $sql );
    $images = array ();
    while ( $row = mysqli_fetch_assoc ( $result ) ) {
        $images [] = $row;
    }
    ?>
    
    <!-- Page Content -->
	<div class="container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Maternity Gallery</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="index.php">Portraits</a></li>
					<li><a href="gallery.php">Gallery</a></li>
					<li class="active">Maternity</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div id="col-0"
				class="col-md-3 col-sm-6 col-xs-12 col-gallery col-gallery-left"></div>
			<div id="col-1" class="col-md-3 col-sm-6 col-xs-12 col-gallery"></div>
			<div id="col-2" class="col-md-3 col-sm-6 col-xs-12 col-gallery"></div>
			<div id="col-3"
				class="col-md-3 col-sm-6 col-xs-12 col-gallery col-gallery-right"></div>
		</div>
		<!-- /.row -->

        <?php require "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Slideshow Modal -->
	<div id="gallery-maternity" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Maternity Gallery</h4>
				</div>
				<div class="modal-body">
					<!-- Carousel -->
					<div id="gallery-maternity-carousel" class="carousel slide"
						style="height: 400px;">
						<!-- Indicators -->
						<ol class="carousel-indicators">
            			<?php
            foreach ( $images as $num => $image ) {
                $class = "";
                if ($num == 0) {
                    $class = " class='active'";
                }
                echo "<li data-target='#gallery-maternity-carousel' data-slide-to='$num'$class></li>";
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
                echo "	<div class='fill'";
                echo "		style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                echo "	<div class='carousel-caption'>";
                echo "		<h2>" . $image ['caption'] . "</h2>";
                echo "	</div>";
                echo "</div>";
            }
            ?>
            		</div>

						<!-- Controls -->
						<a class="left carousel-control"
							href="#gallery-maternity-carousel" data-slide="prev"> <span
							class="icon-prev"></span>
						</a> <a class="right carousel-control"
							href="#gallery-maternity-carousel" data-slide="next"> <span
							class="icon-next"></span>
						</a>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>

		</div>
	</div>
    <!-- End of Modal -->
    
    <!-- Gallery JavaScript -->
	<script src="/js/gallery.js"></script>

	<!-- Script to Activate the Gallery -->
	<script>
		var gallery = new Gallery( "gallery-maternity", 4, <?php echo count($images); ?> );
		
 		var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($images); ?> ) {
                loaded = gallery.loadImages();
            }
        });
    </script>

</body>

</html>