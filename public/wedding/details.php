<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        $rand = "?" . Strings::randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "wedding"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Wedding Session Details</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <p>You said Yes! Congrats on stepping into this next chapter of your
                    lives as a force to be reckoned with. Nothing can take you guys
                    down, including the wedding planning process!</p>

                <p>
                    2017 wedding packages start at $2,700 but are customizable to fit
                    your needs for your day. All of my packages come with photography
                    time on your wedding day, digital files on a USB with personal
                    print release as well as an <a href='engagement.php'>engagement
                        session</a>. I love being able to spend time with my couples at an
                    engagement shoot before the rush of the big day. Who do you spend
                    the most time with on your wedding day? Most likely it's your
                    photographer so spending some time together to see how I work and
                    become comfortable in front of the lens can make a world of
                    difference :)
                </p>

                <p>
                    Want to know more about what it's like to work with Saperstone
                    Studios? Check out more about <a href='experience.php'>The Wedding
                        Experience</a>
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
                <div section="The Wedding Experience"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>The Wedding Experience</span> <img
                        class="img-responsive" src="img/wedding.jpg<?php echo $rand; ?>"
                        alt="The Wedding Experience">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="experience.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Engagements"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Engagements</span> <img
                        class="img-responsive"
                        src="img/engagement.jpg<?php echo $rand; ?>" alt="Engagements">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="engagement.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="The Process"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
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
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Products and Investment</span> <img
                        class="img-responsive" src="img/products.jpg<?php echo $rand; ?>"
                        alt="Products and Investment">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="products.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Night Photography"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Night Photography</span> <img
                        class="img-responsive" src="img/night.jpg<?php echo $rand; ?>"
                        alt="Night Photography">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="night.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Photobooth"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
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