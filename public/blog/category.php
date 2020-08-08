<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors/errors.php";

$categories;
$where;
// if no album is set, throw a 404 error
if (! isset ( $_GET ['t'] )) {
    throw404();
} else {
    $categories = array_map ( 'intval', explode ( ',', $_GET ['t'] ) );
    $where = "`id` = '" . implode ( "' OR `id` = '", $categories ) . "';";
}
new Session();
$sql = new Sql ();
$string = new Strings ();
$tags = $sql->getRows( "SELECT tag FROM `tags` WHERE $where" );
if (empty ( $tags )) {
    throw404();
}
$posts = $sql->getRows( "SELECT * FROM `blog_tags` WHERE " . str_replace ( $where, "id", "tag" ) );
$sql->disconnect ();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>

</head>

<body>

    <?php require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
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