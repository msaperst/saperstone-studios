<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php $nav = "portrait"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">What to Wear</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">What to Wear</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <p>I get this question a lot, and rightfully so! You want to look
                    fab for your portrait session and coordinate as a group so here are
                    some pointers</p>
                <ul>
                    <li>Keep it simple! Avoid overly complex patterns as well as
                        graphics/logos</li>
                    <li>When in doubt, variations of black and white throughout the
                        group is easy</li>
                    <li>Don’t be afraid of color though! Select 2-3 colors and have the
                        group wear all or some of the selected colors for great
                        coordination.</li>
                    <li>Avoid being duplicates with everyone in the group such as all
                        wearing white tops with jeans; This is TOO matchy matchy and
                        should be avoided</li>
                </ul>
                <p>
                    Want to see more examples of what to wear for your photo session?
                    Check out my <a target="_blank"
                        href="http://www.pinterest.com/lsaperstone/what-to-wear-for-photos/">Pinterest
                        Board</a>!
                </p>
                <p>
                    Curious why you should choose Saperstone Studios over everyone else
                    out there? Check out more about <a href="/about.php">how we
                        differentiate ourselves</a>.
                </p>
            </div>
        </div>
        <!-- /.row -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <!-- Carousel -->
                <div id="whatToWearCarousel"
                    class="carousel slide carousel-three-by-two">
                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                        <li data-target="#whatToWearCarousel" data-slide-to="0"
                            class="active"></li>
                        <li data-target="#whatToWearCarousel" data-slide-to="1"></li>
                        <li data-target="#whatToWearCarousel" data-slide-to="2"></li>
                        <li data-target="#whatToWearCarousel" data-slide-to="3"></li>
                    </ol>

                    <!-- Wrapper for slides -->
                    <div class="carousel-inner">
                        <div class="item active">
                            <div class="contain"
                                style="background-image: url('what-to-wear/000.jpg');"></div>
                        </div>
                        <div class="item">
                            <div class="contain"
                                style="background-image: url('what-to-wear/001.jpg');"></div>
                        </div>
                        <div class="item">
                            <div class="contain"
                                style="background-image: url('what-to-wear/002.jpg');"></div>
                        </div>
                        <div class="item">
                            <div class="contain"
                                style="background-image: url('what-to-wear/003.jpg');"></div>
                        </div>
                    </div>

                    <!-- Controls -->
                    <a class="left carousel-control" href="#whatToWearCarousel"
                        data-slide="prev"> <span class="icon-prev"></span>
                    </a> <a class="right carousel-control" href="#whatToWearCarousel"
                        data-slide="next"> <span class="icon-next"></span>
                    </a>
                </div>
            </div>
        </div>

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

</body>

</html>