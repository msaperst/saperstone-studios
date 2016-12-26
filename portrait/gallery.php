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
                    <li class="active">Gallery</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Maternity"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Maternity</span>
                    <img class="img-responsive" src="img/maternity.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=2">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Newborns"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Newborns</span>
                    <img class="img-responsive" src="img/newborn.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="newborns.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="6 Months"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>6 Months</span>
                    <img class="img-responsive" src="img/6month.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=4">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="First Birthday"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>First Birthday</span>
                    <img class="img-responsive" src="img/1year.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=5">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Kids and Family"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Kids and Family</span>
                    <img class="img-responsive" src="img/family.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=6">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Seniors"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Seniors</span>
                    <img class="img-responsive" src="img/senior.jpg" alt="">
                    <div class="overlay">
                        <br/><br/><br/>
                        <a class="info" href="galleries.php?w=7">See More</a>
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