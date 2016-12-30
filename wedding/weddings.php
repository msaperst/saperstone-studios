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

    <?php $nav = "wedding"; require_once "../nav.php"; ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Wedding Gallery</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li class="active">Wedding</li>
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
                    <img class="img-responsive" src="img/wedding-favorites.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=24">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Wedding 1"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Wedding 1</span>
                    <img class="img-responsive" src="img/wedding-1.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=25">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Wedding 2"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Wedding 2</span>
                    <img class="img-responsive" src="img/wedding-2.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=26">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-offset-4 col-md-4 col-sm-6 col-xs-12">
                <div section="Wedding 3"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Wedding 3</span>
                    <img class="img-responsive" src="img/wedding-3.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=27">See More</a>
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