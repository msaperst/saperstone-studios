<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>

</head>

<body>

    <?php
    require_once "../nav.php";
    // get our blog posts
    require_once "../php/sql.php";
    $conn = new sql ();
    $conn->connect ();
    $sql = "SELECT * FROM `blog_details`;";
    $posts = array ();
    $result = mysqli_query ( $conn->db, $sql );
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
				<h1 class="page-header text-center">Blog Posts</h1>
				<ol class="breadcrumb">
					<li><a href="/">Home</a></li>
					<li class="active">Blog</li>
				</ol>
			</div>
		</div>
		<!-- /.row -->

		<!-- Post Section -->
		<div id="post-content"></div>
		<!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
	<!-- /.container -->

	<!-- Gallery JavaScript -->
	<script src="/js/posts-full.js"></script>

	<!-- Script to Activate the Gallery -->
	<script>
        var postsFull = new PostsFull( <?php echo count($posts); ?> );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($posts); ?> ) {
                loaded = postsFull.loadPosts();
            }
        });
    </script>

</body>

</html>