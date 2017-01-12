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
				<h1 class="page-header text-center">Photobooth</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="index.php">Weddings</a></li>
					<li><a href="details.php">Details</a></li>
					<li class="active">Photobooth</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Features Section -->
		<div class="row">
			<div class="col-lg-12">
				<p>
					Perfect for any wedding or event, photo booths are a fantastic way
					to add instant fun to any occasion. Here are the basics, please <a
						href='/contact.php'>contact us</a> for more details!
				</p>
				<ul>
					<li>Open backdrop to ensure large group shots are possible</li>
					<li>Attendant is included for setup/breakdown and is there the
						entire time to make sure everything runs smoothly</li>
					<li>3 photos taken per group to be put into photo strips</li>
					<li>Strips are printed throughout the photobooth for guests to come back
						and collect</li>
					<li>Photo strips are customized for your event! Add a logo, the
						date or your names for a personal touch</li>
					<li>All individual images as well as photo strips available for
						download via web gallery within a week of your event</li>
					<li>Fun props included!</li>
				</ul>
			</div>
		</div>
		<!-- /.row -->

		<div class="row" style='padding-top: 30px'>
			<div class="col-lg-12">
				<!-- Carousel -->
				<div id="photobooth-carousel"
					class="carousel slide carousel-three-by-two">
					<!-- Indicators -->
					<ol class="carousel-indicators">
						<li data-target="#photobooth-carousel" data-slide-to="0" class="active"></li>
							<?php
    for($i = 1; $i < 9; $i ++) {
        ?>
						<li data-target="#photobooth-carousel"
							data-slide-to="<?php echo $i; ?>"></li>
        <?php
    }
    ?>
					</ol>

					<!-- Wrapper for slides -->
					<div class="carousel-inner">
						<div class="item active">
							<div class="contain"
								style="background-image: url('photobooth/000.jpg');"></div>
						</div>
						<?php
    for($i = 1; $i < 9; $i ++) {
        ?>
						<div class="item">
							<div class="contain"
								style="background-image: url('photobooth/<?php echo str_pad($i, 3, "0", STR_PAD_LEFT); ?>.jpg');"></div>
						</div>
        <?php
    }
    ?>
					</div>

					<!-- Controls -->
					<a class="left carousel-control" href="#photobooth-carousel"
						data-slide="prev"> <span class="icon-prev"></span>
					</a> <a class="right carousel-control" href="#photobooth-carousel"
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