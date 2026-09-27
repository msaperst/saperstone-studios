<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$postCount = $sql->getRowCount("SELECT * FROM `blog_details` WHERE `active` = 1;");
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

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Gallery JavaScript -->
    <script src="<?php echo Strings::assetUrl('/js/blog-common.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/post.js'); ?>"></script>
    <script src="<?php echo Strings::assetUrl('/js/posts-full.js'); ?>"></script>

    <div id="blog-page-config" class="hidden" data-loader="posts-full" data-total="<?php echo $postCount; ?>"></div>
    <script src="<?php echo Strings::assetUrl('/js/blog-init.js'); ?>"></script>

</body>

</html>