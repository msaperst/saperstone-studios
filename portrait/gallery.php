<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

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
                <div class="hovereffect img-portfolio">
                    <img class="img-responsive" src="/img/portrait/maternity/VanderhoofMaternity_20160612_0092.jpg"
                        alt="">
                    <div class="overlay">
                        <h2>Maternity</h2>
                        <a class="info" href="galleries.php?w=2">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="hovereffect img-portfolio">
                    <img class="img-responsive" src="/img/portrait/newborn/DSC_5904-Edit.jpg"
                        alt="">
                    <div class="overlay">
                        <h2>Newborns</h2>
                        <a class="info" href="galleries.php?w=3">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="hovereffect img-portfolio">
                    <img class="img-responsive" src="/img/portrait/6month/Tucker_20140719_0072-Edit.jpg"
                        alt="">
                    <div class="overlay">
                        <h2>6 Months</h2>
                        <a class="info" href="galleries.php?w=4">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="hovereffect img-portfolio">
                    <img class="img-responsive" src="/img/portrait/1year/0105ROBERTS_20120921_0056.jpg"
                        alt="">
                    <div class="overlay">
                        <h2>First Birthday</h2>
                        <a class="info" href="galleries.php?w=5">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="hovereffect img-portfolio">
                    <img class="img-responsive" src="/img/portrait/family/GarcesFamily_20131228_0016.jpg"
                        alt="">
                    <div class="overlay">
                        <h2>Kids and Family</h2>
                        <a class="info" href="galleries.php?w=6">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="hovereffect img-portfolio">
                    <img class="img-responsive" src="/img/portrait/senior/Rose_20150425_0166.jpg"
                        alt="">
                    <div class="overlay">
                        <h2>Seniors</h2>
                        <a class="info" href="galleries.php?w=7">See More</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

</body>

</html>