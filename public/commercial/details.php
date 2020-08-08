<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
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
                <h1 class="page-header text-center">Details</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Services"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Services</span> <img
                        class="img-responsive" src="img/services.jpg<?php echo $rand; ?>"
                        alt="Services">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="services.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Background Options"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Background Options</span> <img
                        class="img-responsive" src="img/background-options.jpg<?php echo $rand; ?>"
                        alt="Background Options">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="background.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="What to Expect"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>What to Expect</span> <img
                        class="img-responsive" src="img/what-to-expect.jpg<?php echo $rand; ?>"
                        alt="What to Expect">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="expect.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-offset-2 col-md-4 col-sm-6 col-xs-12">
                <div section="Pricing"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Pricing</span> <img
                        class="img-responsive"
                        src="img/pricing.jpg<?php echo $rand; ?>" alt="Pricing">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="pricing.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="FAQ"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>FAQ</span> <img
                        class="img-responsive"
                        src="img/faq.jpg<?php echo $rand; ?>" alt="FAQ">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="faq.php">See
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