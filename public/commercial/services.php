<?php require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/strings.php";
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "commercial"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Commercial Services</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li class="active">Services</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Headshots"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Headshots</span> <img
                        class="img-responsive" src="img/headshots.jpg<?php echo $rand; ?>"
                        alt="Headshots">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="headshots.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Events"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Events</span> <img
                        class="img-responsive" src="img/events.jpg<?php echo $rand; ?>"
                        alt="Events">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="events.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Professional Branding"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Professional Branding</span> <img
                        class="img-responsive" src="img/branding.jpg<?php echo $rand; ?>"
                        alt="Professional Branding">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="branding.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-offset-4 col-md-4 col-sm-6 col-xs-12">
                <div section="Photobooth"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Photobooth</span> <img
                        class="img-responsive"
                        src="img/photobooth.jpg<?php echo $rand; ?>" alt="Photobooth">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="photobooth.php">See
                            More</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    }
    ?>

</body>

</html>