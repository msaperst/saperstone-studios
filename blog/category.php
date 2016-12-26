<?php
$category;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['t'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $category = $_GET ['t'];
}

require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();
$sql = "SELECT * FROM `tags` WHERE id = '$category';";
$details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $details ['tag']) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    $conn->disconnect ();
    exit ();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>

</head>

<body>

    <?php
    require_once "../nav.php";
    // get our blog posts
    $sql = "SELECT * FROM `blog_tags` WHERE tag = $category;";
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
                <h1 class="page-header text-center"><?php echo $details ['tag']; ?> Blog Posts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li><a href="/blog/categories.php">Categories</a></li>
                    <li class="active"><?php echo $details ['tag']; ?></li>
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
    <script src="/js/post.js"></script>
    <script src="/js/posts-full.js"></script>

    <!-- Script to Activate the Gallery -->
    <script>
        var postsFull = new PostsFull( <?php echo count($posts); ?>, <?php echo $category; ?> );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($posts); ?> ) {
                loaded = postsFull.loadPosts();
            }
        });
    </script>

</body>

</html>