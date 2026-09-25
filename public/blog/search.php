<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$errors = new Errors();

// if no album is set, throw a 404 error
if (! isset ( $_GET ['s'] ) || $_GET ['s'] == '' ) {
    $errors->throw404();
} else {
    $search = $_GET['s'];
}
$posts = $sql->getRows(
    "SELECT details.id AS blog
     FROM blog_details AS details
     WHERE details.active = 1
       AND (
           details.title LIKE ?
           OR details.safe_title LIKE ?
           OR EXISTS (
               SELECT 1
               FROM blog_texts AS texts
               WHERE texts.blog = details.id
                 AND texts.text LIKE ?
           )
       )",
    ["%$search%", "%$search%", "%$search%"]
);
$sql->disconnect();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="<?php echo Strings::assetUrl('/css/hover-effect.css'); ?>" rel="stylesheet">

</head>

<body>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>
    
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

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Gallery JavaScript -->
    <script src="<?php echo Strings::assetUrl('/js/blog-common.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/post.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/posts-search.js'); ?>"></script>

    <!-- Script to Activate the Gallery -->
    <script>
        var posts = new Posts( 3, <?php echo count($posts); ?>, "<?php echo addslashes($search); ?>" );

        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() ) {
                posts.loadImages();
            }
        });
    </script>

</body>

</html>
