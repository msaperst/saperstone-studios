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
    require_once "../php/sql.php";
    $conn = new sql ();
    $conn->connect ();
    $sql = "SELECT * FROM `blog_details`;";
    $result = mysqli_query ( $conn->db, $sql );
    $posts = array ();
    while ( $row = mysqli_fetch_assoc ( $result ) ) {
        $posts [] = $row;
    }
    $conn->disconnect ();
    ?>
    
    <!-- Page Content -->
	<div class="page-content container">

		<!-- Page Heading/Breadcrumbs -->
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header text-center">Recent Blog Posts</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li><a href="/blog/">Blog</a></li>
					<li class="active">Posts</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Services Section -->
		<div class="row">
			<div id="col-0" class="col-md-4 col-gallery"></div>
			<div id="col-1" class="col-md-4 col-gallery"></div>
			<div id="col-2" class="col-md-4 col-gallery"></div>
		</div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Gallery JavaScript -->
	<script src="/js/posts.js"></script>

	<!-- Script to Activate the Gallery -->
	<script>
        var posts = new Posts( 3, <?php echo count($posts); ?> );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($posts); ?> ) {
                loaded = posts.loadImages();
            }
        });
    </script>

</body>

</html>