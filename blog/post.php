<?php
$post;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['p'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include "../errors/404.php";
    exit ();
} else {
    $post = $_GET ['p'];
}

require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();
$sql = "SELECT * FROM `blog_details` WHERE id = '$post';";
$details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $details ['title']) {
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

    <?php require_once "../nav.php"; ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Recent Blog Posts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li><a href="/blog/posts.php">Posts</a></li>
                    <li class="active" id="breadcrumb-title"></li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Post Section -->
        <div class="row">
            <div id="post-tags" class="col-md-4 text-left"></div>
            <div class="col-md-4 text-center">
                <strong id="post-date"></strong>
            </div>
            <div id="post-likes" class="col-md-4 text-right"></div>
        </div>
        <!-- /.row -->

        <div id="post-content"></div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Gallery JavaScript -->
    <script src="/js/post-full.js"></script>

    <!-- Script to Activate the Gallery -->
    <script>
        new PostFull( <?php echo $post; ?> );
    </script>

</body>

</html>