<?php
$categories;
$where;
$tags = array ();
// if no album is set, throw a 404 error
if (! isset ( $_GET ['t'] )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/404.php";
    exit ();
} else {
    $categories = array_map ( 'intval', explode ( ',', $_GET ['t'] ) );
    $where = "`id` = '" . implode ( "' OR `id` = '", $categories ) . "';";
}

require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/strings.php";
$string = new Strings ();

require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$sql = new Sql ();
$sql = "SELECT * FROM `tags` WHERE $where";
$result = mysqli_query ( $conn->db, $sql );
while ( $row = mysqli_fetch_assoc ( $result ) ) {
    array_push ( $tags, $row ['tag'] );
}
if (empty ( $tags )) {
    header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found" );
    include dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/404.php";
    $conn->disconnect ();
    exit ();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>

</head>

<body>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php";
    // get our blog posts
    $sql = "SELECT * FROM `blog_tags` WHERE " . str_replace ( $where, "id", "tag" );
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
                <h1 class="page-header text-center"><?php echo $string->commaSeparate($tags); ?> Blog Posts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li><a href="/blog/categories.php">Categories</a></li>
                    <li class="active"><?php echo $string->commaSeparate($tags); ?></li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Post Section -->
        <div id="post-content"></div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Gallery JavaScript -->
    <script src="/js/post.js"></script>
    <script src="/js/posts-full.js"></script>

    <!-- Script to Activate the Gallery -->
    <script>
        var postsFull = new PostsFull( <?php echo count($posts); ?>, <?php echo "[" . implode($categories,",") . "]"; ?> );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($posts); ?> ) {
                loaded = postsFull.loadPosts();
            }
        });
    </script>

</body>

</html>