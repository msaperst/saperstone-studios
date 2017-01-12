<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php $nav = "wedding"; require_once "../nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Night Photography</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="index.php">Weddings</a></li>
					<li><a href="details.php">Details</a></li>
					<li class="active">Night</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Features Section -->
		<div class="row">
			<div class="col-lg-12">
				<p>Oh.my. how I adore night photography. It's such a quiet time as
					you step away from the hustle of the party for a moment just for
					the two of you. I highly suggest working this into the timeline for
					your day if possible. It can take as little as 20 minutes but with
					more time allowed, you'll get more variations and angles.</p>
			</div>
		</div>
		<!-- /.row -->

		<div class="row" style='padding-top: 30px'>
			<div class="col-lg-12">
				<!-- Carousel -->
				<div id="night-carousel"
					class="carousel slide carousel-three-by-two">
					<!-- Indicators -->
					<ol class="carousel-indicators">
						<li data-target="#night-carousel" data-slide-to="0" class="active"></li>
							<?php
    for($i = 1; $i < 16; $i ++) {
        ?>
						<li data-target="#night-carousel"
							data-slide-to="<?php echo $i; ?>"></li>
        <?php
    }
    ?>
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner">
						<div class="item active">
							<div class="contain"
								style="background-image: url('night/000.jpg');"></div>
						</div>
						<?php
    for($i = 1; $i < 16; $i ++) {
        ?>
						<div class="item">
							<div class="contain"
								style="background-image: url('night/<?php echo str_pad($i, 3, "0", STR_PAD_LEFT); ?>.jpg');"></div>
						</div>
        <?php
    }
    ?>
					</div>

					<!-- Controls -->
					<a class="left carousel-control" href="#night-carousel"
						data-slide="prev"> <span class="icon-prev"></span>
					</a> <a class="right carousel-control" href="#night-carousel"
						data-slide="next"> <span class="icon-next"></span>
					</a>
				</div>
			</div>
		</div>

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

</body>

</html>