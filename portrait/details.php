<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
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
                <h1 class="page-header text-center">Portrait Session Details</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <!--         <div class="row"> -->
        <!--             <div class="col-xs-4"> -->
        <!--                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>"> -->
        <!--                    <img width="100%" src="img/portrait-main-1.jpg<?php echo $rand; ?>" -->
        <!--                         alt="Portraits"> -->
        <!--                 </div> -->
        <!--             </div> -->
        <!--             <div class="col-xs-8"> -->
        <!--                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>"> -->
        <!--                    <img width="100%" src="img/portrait-main-2.jpg<?php echo $rand; ?>" -->
        <!--                         alt="Portraits"> -->
        <!--                 </div> -->
        <!--             </div> -->
        <!--         </div> -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <p>No matter what stage of life you find yourself in, portraits are
                    always special. Take the moment to document where you are as an
                    individual, couple or family by being in front of the lens AND
                    having fun. Each time I pull out my camera I strive to provide a
                    unique, fun photography experience. Often when clients are in front
                    of the lens having their portraits taken they need a bit of
                    direction on what to do. No fear, that's why you've hired me. It's
                    my job to pose everyone in a way that's natural as well as
                    flattering. To ensure that your posed moments are still ones that
                    are candid, fun and personable, I'll have you interact with each
                    other or myself...simple, right? But it's just oh so effective in
                    making the moment your own. :) We'll walk, we'll talk, and I'll
                    most likely crack some jokes at my own expense but it's all in the
                    name of having fun and capturing natural, fun moments that reflect
                    who you are. My photography style is vibrant and colorful to
                    reflect how you love life.</a>
                </p>

                <p>
                    Curious why you should choose Saperstone Studios over everyone else
                    out there? Check out more about <a href='/about.php'>how we
                        differentiate ourselves</a>
                </p>
            </div>
        </div>

        <div class="row" style='padding-top: 30px'>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Session Information"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Session Information</span> <img
                        class="img-responsive" src="img/session.jpg<?php echo $rand; ?>"
                        alt="Session Information">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="sessions.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="The Process"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>The Process</span> <img
                        class="img-responsive" src="img/process.jpg<?php echo $rand; ?>"
                        alt="The Process">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="process.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Products and Investment"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Products and Investment</span> <img
                        class="img-responsive" src="img/products.jpg<?php echo $rand; ?>"
                        alt="Products and Investment">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="products.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="What to Wear"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>What to Wear</span> <img
                        class="img-responsive"
                        src="img/what-to-wear.jpg<?php echo $rand; ?>" alt="What to Wear">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="what-to-wear.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Home Studio"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Home Studio</span> <img
                        class="img-responsive" src="img/studio.jpg<?php echo $rand; ?>"
                        alt="Home Studio">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="studio.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="FAQs"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>FAQs</span> <img
                        class="img-responsive"
                        src="img/faq.jpg<?php echo $rand; ?>" alt="FAQs">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="faq.php">See
                            More</a>
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