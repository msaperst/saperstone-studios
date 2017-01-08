<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
<link
	href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
	rel="stylesheet">
<link
	href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
	rel="stylesheet">
<?php
    }
    ?>

</head>

<body>

    <?php $nav = "portrait"; require_once "../nav.php"; ?>

    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Portrait Session Details</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="index.php">Portraits</a></li>
					<li class="active">Details</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Features Section -->
		<div class="row">
			<div class="col-lg-12">
				<h2 class="page-header">What to expect for your photo session</h2>
			</div>
			<div class="col-md-12">
				<p>Each time I pull out my camera I strive to provide a unique, fun
					photography experience. Often when clients are in front of the lens
					having their portraits taken they need a bit of direction on what
					to do. No fear, that's why you've hired me. It's my job to pose
					everyone in a way that's natural as well as flattering. To ensure
					that your posed moments are still ones that are candid, fun and
					personable, I'll have you interact with each other...simple, right?
					But it's just oh so effective in making the moment your own. :)
					We'll walk, we'll talk, and I'll most likely crack some jokes at my
					own expense but it's all in the name of having fun and capturing
					natural, fun moments that reflect who you are as a couple or
					family. My photography style is vibrant and colorful to reflect how
					you love life.</p>
			</div>
			<div class="col-lg-12">
				<h2 class="page-header">What to Wear?</h2>
			</div>
			<div class="col-md-12">
				<p>
					Need a little help coordinating a winning outfit combo for the
					group? Be sure to check out <a href="what-to-wear.php">what to wear</a>
					for your photography session.
				</p>
			</div>
			<div class="col-lg-12">
				<h2 class="page-header">Turnaround time</h2>
			</div>
			<div class="col-md-12">
				<p>
					Please allow 2-3 weeks after your portrait session for images to be
					completed. During this time I carefully <a class='error' href='#'>make
						selects, color correct and retouch</a> your images to be clean,
					consistent and vibrant.
				</p>
			</div>
			<div class="col-lg-12">
				<h2 class="page-header">What's Next?</h2>
			</div>
			<div class="col-md-12">
				<p>
					When I'm close to completing your images I will reach out and
					schedule a time for you to visit my <a href="studio.php">home
						studio</a> in Fairfax, VA.
				</p>
				<p>A typical session will have approx 100 images of you and your
					loved ones to go through. I've found through the years that my
					clients were overwhelmed with just being sent a web gallery link
					and would delay making any decisions, therefore not enjoying their
					images as soon as possible. I remove that stress factor by giving
					you personalized service at your image review where I help you flag
					your favorite images in person. We're able to pull up multiple
					similar poses side by side and zoom in on images to make your
					selection process as smooth as possible.</p>
				<p>
					You'll select your 5 images that come with your session at this
					time and we'll go though each <a href="products-investments.php">product
						choice</a> in person. We take as much time as needed to answer all
					of your questions, and make sure that when you leave, you feel
					completely confident in your order. While I recommend getting
					prints for your walls that generations to come will enjoy, no
					additional purchases are required. On average, my clients invest
					$800-$1,200 on their custom portraits.
				</p>
			</div>
			<div class="col-lg-12">
				<h2 class="page-header">Come Prepared!</h2>
			</div>
			<div class="col-md-12">
				<p>As a visual person myself, I wouldn't be able to make decisions
					in regard to prints on my walls without seeing it myself first. If
					you have a particular wall you want to hang photos on in your home,
					send me a snapshot (from your phone is fine) of the wall with a
					standard piece of office paper taped to it. I have an amazing ipad
					application that allows me to show you how large/small images will
					look on your actual wall to scale. The piece of paper taped to the
					wall helps the application resize the image accordingly. It makes
					customizing a cluster of images a breeze and it's fun to visualize
					exactly what your wall will look like.</p>
				<p>Please click the below links to check out more details on your
					specific photography session.</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4 col-sm-6 col-xs-12">
				<div section="Bellies and Babies"
					class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
					<span class='preview-title'>Session Information</span> <img
						class="img-responsive" src="img/session.jpg<?php echo $rand; ?>" alt="">
					<div class="overlay">
						<br /> <br /> <br /> <a class="info" href="sessions.php">See More</a>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6 col-xs-12">
				<div section="Newborn FAQ"
					class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
					<span class='preview-title'>Newborn FAQ</span> <img
						class="img-responsive" src="img/newbornfaq.jpg<?php echo $rand; ?>" alt="">
					<div class="overlay">
						<br /> <br /> <br /> <a class="info" href="newborn-faq.php">See
							More</a>
					</div>
				</div>
			</div>
			<div class="col-md-4 col-sm-6 col-xs-12">
				<div section="Home Studio"
					class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
					<span class='preview-title'>Home Studio</span> <img
						class="img-responsive" src="img/studio.jpg<?php echo $rand; ?>" alt="">
					<div class="overlay">
						<br /> <br /> <br /> <a class="info" href="studio.php">See More</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->
    
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
	<script src="/js/jquery.uploadfile.js"></script>
	<script
		src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
	<script
		src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <?php
    }
    ?>

</body>

</html>