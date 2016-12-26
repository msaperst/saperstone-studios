<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    if ($user->isAdmin ()) {
        ?>
    <link
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
    rel="stylesheet">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "portrait"; require_once "../nav.php"; ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Portrait Gallery</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li class="active">Newborn</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Favorites"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Favorites</span>
                    <img class="img-responsive" src="img/favorite.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=13">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="At Your Home"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>At Your Home</span>
                    <img class="img-responsive" src="img/home.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=14">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Studio"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Studio</span>
                    <img class="img-responsive" src="img/studio.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=15">See More</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->
    
    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <?php
    }
    ?>

</body>

</html>