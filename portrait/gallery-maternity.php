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
    $sql = "SELECT * FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.name = 'portrait-maternity';";
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
					<li><a href="home.php">Portraits</a></li>
					<li><a href="gallery.php">Gallery</a></li>
					<li class="active">Maternity</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div id="col-0" class="col-md-3 col-sm-6 col-xs-12 col-gallery col-gallery-left">
			</div>
			<div id="col-1" class="col-md-3 col-sm-6 col-xs-12 col-gallery">
			</div>
			<div id="col-2" class="col-md-3 col-sm-6 col-xs-12 col-gallery">
			</div>
			<div id="col-3" class="col-md-3 col-sm-6 col-xs-12 col-gallery col-gallery-right">
			</div>
		</div>
		<!-- /.row -->

        <?php require "../footer.php"; ?>

    </div>
	<!-- /.container -->
	
	<!-- Gallery JavaScript -->
	<script src="/js/gallery.js"></script>

    <!-- Script to Activate the Gallery -->
	<script>
		var gallery = new Gallery( "gallery-maternity", 4, <?php echo count($images); ?> );
		
 		var loaded = 0;
        $(document).scroll(function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($images); ?> ) {
                loaded = gallery.loadImages();
            }
        });
    </script>
	

</body>

</html>