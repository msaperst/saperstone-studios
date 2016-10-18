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
                    <li class="active">Gallery</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Surprise Proposals"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img class="img-responsive" src="img/surprise.jpg" alt="">
                    <div class="overlay">
                        <h2>Maternity</h2>
                        <a class="info" href="galleries.php?w=9">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Engagements"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img class="img-responsive" src="img/engagement.jpg" alt="">
                    <div class="overlay">
                        <h2>Engagements</h2>
                        <a class="info" href="galleries.php?w=10">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Weddings"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img class="img-responsive" src="img/wedding.jpg" alt="">
                    <div class="overlay">
                        <h2>Weddings</h2>
                        <a class="info" href="galleries.php?w=11">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-offset-4 col-md-4 col-sm-6 col-xs-12">
                <div section="Night Photography"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img class="img-responsive" src="img/night.jpg" alt="">
                    <div class="overlay">
                        <h2>Night Photography</h2>
                        <a class="info" href="galleries.php?w=12">See More</a>
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