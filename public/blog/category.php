<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$errors = new Errors();

// if no album is set, throw a 404 error
if (! isset ( $_GET ['t'] ) || $_GET ['t'] == "") {
    $errors->throw404();
} else {
    $categories = array_map ( 'intval', explode ( ',', $_GET ['t'] ) );
    $where = "`id` = '" . implode ( "' OR `id` = '", $categories ) . "';";
}
$session = new Session();
$session->initialize();
$sql = new Sql ();
$tags = array_column($sql->getRows( "SELECT tag FROM `tags` WHERE $where" ), 'tag');
if (empty ( $tags )) {
    $errors->throw404();
}
$postCount = $sql->getRowCount( "SELECT * FROM `blog_tags` WHERE " . str_replace ( "id", "tag", $where ) );
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
                <h1 class="page-header text-center"><?php echo Strings::commaSeparate($tags); ?> Blog Posts</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li><a href="/blog/categories.php">Categories</a></li>
                    <li class="active"><?php echo Strings::commaSeparate($tags); ?></li>
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
        var postsFull = new PostsFull( <?php echo $postCount; ?>, <?php echo "[" . implode($categories,",") . "]"; ?> );
        
        var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo $postCount; ?> ) {
                loaded = postsFull.loadPosts();
            }
        });
    </script>

</body>

</html>