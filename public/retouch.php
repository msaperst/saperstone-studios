<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php";
    $rand = "";
    if ($user->isAdmin ()) {
        $rand = "?" . Strings::randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php";
    ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Retouch</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Retouch</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Portraits Retouch -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-lg-12">
                <p>How many times have you loved your smile in one photograph but
                    your sister thought she looked better in a different one? I can
                    make you both happy by combining the two images into one,
                    flawlessly. Honestly, I would challenge you to pick out which
                    images have been altered and you wouldn't be able to tell.</p>
                <p>I've taken on several clients retouch challenges including
                    removing Ex's from family photos, adding a granddaughter to the
                    family reunion photo when she wasn't able to make it or even
                    opening someone's eyes when they blink.</p>
                <p>Have an old photograph that needs a little TLC and restoration?
                    Whether it's in need of some color correction, its faded or worn,
                    has scratches, tears or even missing parts, I'll be able to restore
                    your photo to it's original look.</p>
                <p>I handle each retouch request on a case by case basis. After taking
                    a look at your files I'll be able to come up with a cost based on
                    time involved so <a href='/contact.php'>contact me</a> today for a
                    complimentary, no obligation quote.
                </p>
            </div>
        </div>

        <!-- Products Section -->
        <div class="row" style='padding-top: 30px'>
            <div class="col-md-6 col-xs-12">
                <div section='Portrait Retouch'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Portrait Retouch</span> <img
                        class="img-responsive"
                        src="img/portrait-retouch.jpg<?php echo $rand; ?>" width="100%"
                        alt="Portrait Retouch">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="portrait/retouch.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <div section='Wedding Retouch'
                     class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Wedding Retouch</span> <img
                            class="img-responsive"
                            src="img/wedding-retouch.jpg<?php echo $rand; ?>" width="100%"
                            alt="Wedding Retouch">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="wedding/retouch.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <div section='Mitzvah Retouch'
                     class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>B'Nai Mitzvah Retouch</span> <img
                            class="img-responsive"
                            src="img/b-nai-retouch.jpg<?php echo $rand; ?>" width="100%"
                            alt="Mitzvah Retouch">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="b-nai-mitzvah/retouch.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <div section='Commercial Retouch'
                     class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Commercial Retouch</span> <img
                            class="img-responsive"
                            src="img/commercial-retouch.jpg<?php echo $rand; ?>" width="100%"
                            alt="Commercial Retouch">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="commercial/retouch.php">See
                            More</a>
                    </div>
                </div>
            </div>
        </div>

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