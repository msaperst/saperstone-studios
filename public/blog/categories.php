<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
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
    rel="stylesheet">
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
                <div id="tag-cloud" style="height: 400px;"></div>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqcloud/1.0.4/jqcloud-1.0.4.min.js"></script>
    <script type="text/javascript">
        /*!
         * Create an array of word objects, each representing a word in the cloud
         */
        var tag_array = [
             <?php
            foreach ( $tags as $tag ) {
                echo "{text: \"" . $tag ['tag'] . "\", weight: " . $tag ['count'] . ", link: '/blog/category.php?t=" . $tag ['id'] . "'},\n";
            }
            ?>
        ];
        $(function() {
          // When DOM is ready, select the container element and call the jQCloud method, passing the array of words as the first argument.
          $("#tag-cloud").jQCloud(tag_array);
        });
    </script>

</body>

</html>
