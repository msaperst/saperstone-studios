<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$tags = $sql->getRows( "SELECT `tags`.`id`, `tags`.`tag`, COUNT(`blog_tags`.`tag`) AS `count` FROM blog_tags JOIN tags ON blog_tags.tag = tags.id GROUP BY blog_tags.tag;" );
$sql->disconnect ();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/jqcloud/1.0.4/jqcloud.css"
    rel="stylesheet"
    integrity="sha384-bmbMNxNx5UJRg3nIoSFEwRjPmKK31MegSkqlNjFsmGhAF988pBW9ZsQdNL9xcLek"
    crossorigin="anonymous">
</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Blog Categories</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog/">Blog</a></li>
                    <li class="active">Categories</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Intro Content -->
        <div class="row">
            <div class="col-md-12">
                <div id="tag-cloud" class="u-height-400"></div>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqcloud/1.0.4/jqcloud-1.0.4.min.js"
        integrity="sha384-kY7q7DuOP+WW41bViWAPpY319DRoRI2Oc/nIahugatbE1qgicrItO4gBzIp7kUBy"
        crossorigin="anonymous"></script>
    <?php
    $tagCloud = array();
    foreach ($tags as $tag) {
        $tagCloud[] = array(
            'text' => $tag['tag'],
            'weight' => (int)$tag['count'],
            'link' => '/blog/category.php?t=' . $tag['id']
        );
    }
    ?>
    <div id="tag-cloud-config" class="hidden"
         data-tags="<?php echo Strings::escapeHtmlAttribute(json_encode($tagCloud)); ?>"></div>
    <script src="<?php echo Strings::assetUrl('/js/tag-cloud-init.js'); ?>"></script>

</body>

</html>
