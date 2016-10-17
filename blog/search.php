<?php
$search;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['s'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $search = $_GET ['s'];
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
    require_once "../nav.php";
    
    // get our gallery images
    require_once "../php/sql.php";
    $conn = new Sql ();
    $conn->connect ();
    $sql = "SELECT * FROM (SELECT id AS blog FROM `blog_details` WHERE `title` LIKE '%$search%' UNION ALL SELECT blog FROM `blog_texts` WHERE `text` LIKE '%$search%') AS x GROUP BY `blog`;";
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
                <h1 class="page-header text-center">Blog Posts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li class="active">Search</li>
                    <li class="active"><?php echo $search; ?></li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div id="post-0" class="col-md-4 col-gallery"></div>
            <div id="post-1" class="col-md-4 col-gallery"></div>
            <div id="post-2" class="col-md-4 col-gallery"></div>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Gallery JavaScript -->
    <script src="/js/posts-search.js"></script>

    <!-- Script to Activate the Gallery -->
    <script>
        var posts = new Posts( 3, <?php echo count($posts); ?>, "<?php echo addslashes($search); ?>" );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($posts); ?> ) {
                loaded = posts.loadImages();
            }
        });
    </script>

</body>

</html>
