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
    require_once"../php/sql.php";
    $conn = new sql ();
    $conn->connect ();
    $sql = "SELECT * FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.name = 'portrait-home';";
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
				<h1 class="page-header text-center">Portraits</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Portraits</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Main Content -->
		<div class="row" style="margin-bottom: 30px;">
			<!-- Content Column -->
			<div class="col-md-9">
				<!-- Carousel -->
				<div id="portraitCarousel" class="carousel slide carousel-three-by-two">
					<!-- Indicators -->
					<ol class="carousel-indicators">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                            echo "<li data-target='#portraitCarousel' data-slide-to='$num'$class></li>";
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
                            echo "    <div class='contain'";
                            echo "        style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                            echo "    <div class='carousel-caption'>";
                            echo "        <h2>" . $image ['caption'] . "</h2>";
                            echo "    </div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

					<!-- Controls -->
					<a class="left carousel-control" href="#portraitCarousel"
						data-slide="prev"> <span class="icon-prev"></span>
					</a> <a class="right carousel-control" href="#portraitCarousel"
						data-slide="next"> <span class="icon-next"></span>
					</a>
				</div>
			</div>
			<!-- Sidebar Column -->
			<div class="col-md-3">
				Some content for smooshy! <br /> <br /> <br /> Some content for
				smooshy! <br /> <br /> <br /> Some content for smooshy! <br /> <br />
				<br /> Some content for smooshy! <br /> <br /> <br /> Some content
				for smooshy!
			</div>
		</div>

		<!-- Portrait Services Section -->
		<div class="row">
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="hovereffect img-portfolio">
					<img class="img-responsive" src="/img/portrait/maternity/VanderhoofMaternity_20160612_0032.jpg"
						alt="">
					<div class="overlay">
						<h2>Details</h2>
						<a class="info" href="details.php">See More</a>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="hovereffect img-portfolio">
					<img class="img-responsive" src="/img/portrait/family/AlipuiFamily_20151007_0095.jpg"
						alt="">
					<div class="overlay">
						<h2>Gallery</h2>
						<a class="info" href="gallery.php">See More</a>
					</div>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="hovereffect img-portfolio">
					<img class="img-responsive" src="http://placehold.it/1200x250"
						alt="">
					<div class="overlay">
						<h2>Retouch</h2>
						<a class="info" href="#">See More</a>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="hovereffect img-portfolio">
					<img class="img-responsive" src="/img/portrait/senior/Rose_20150425_0170.jpg"
						alt="">
					<div class="overlay">
						<h2>About</h2>
						<a class="info" href="/about.php">See More</a>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="hovereffect img-portfolio">
					<img class="img-responsive" src="/img/portrait/family/Przybysz_20150530_0072.jpg"
						alt="">
					<div class="overlay">
						<h2>Reviews</h2>
						<a class="info" href="#">See More</a>
					</div>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="hovereffect img-portfolio">
					<img class="img-responsive" src="/img/portrait/newborn/Emilia_20160622_0014.jpg"
						alt="">
					<div class="overlay">
						<h2>Blog</h2>
						<a class="info" href="#">See More</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php
        require_once "../footer.php";
        $conn->disconnect ();
        ?>

    </div>
	<!-- /.container -->

</body>

</html>