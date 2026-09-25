<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$errors = new Errors();

// if no album is set, throw a 404 error
if (!isset ($_GET ['t']) || $_GET ['t'] == "") {
    $errors->throw404();
} else {
    $categories = array_map('intval', explode(',', $_GET ['t']));
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
}
$session = new Session();
$session->initialize();
$sql = new Sql ();
$tags = array_column($sql->getRows("SELECT tag FROM `tags` WHERE `id` IN ($placeholders)", $categories), 'tag');
if (empty ($tags)) {
    $errors->throw404();
}
$countQuery = "SELECT COUNT(DISTINCT details.id) AS count FROM blog_tags AS a1";
$countWhere = " JOIN blog_details AS details ON a1.blog = details.id WHERE details.active = 1 AND ";
for ($i = 1; $i <= sizeof($categories); $i++) {
    if ($i != 1) {
        $countQuery .= " JOIN blog_tags AS a$i USING (blog)";
    }
    $countWhere .= "a$i.tag = ? AND ";
}
$countWhere = substr($countWhere, 0, -4);
$postCount = (int)$sql->getRow($countQuery . $countWhere, $categories)['count'];
$sql->disconnect();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>

</head>

<body>

<?php require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

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

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

</div>
<!-- /.container -->

<!-- Gallery JavaScript -->
<script src="<?php echo Strings::assetUrl('/js/blog-common.js'); ?>"></script>
<script src="<?php echo Strings::assetUrl('/js/post.js'); ?>"></script>
<script src="<?php echo Strings::assetUrl('/js/posts-full.js'); ?>"></script>

<!-- Script to Activate the Gallery -->
<script>
    var postsFull = new PostsFull( <?php echo $postCount; ?>, <?php echo "[" . implode(",", $categories) . "]"; ?> );
    $(window,document).on("scroll resize", function(){
        if( $('footer').isOnScreen() ) {
            postsFull.loadPosts();
        }
    });
</script>

</body>

</html>
