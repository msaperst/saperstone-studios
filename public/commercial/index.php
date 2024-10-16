<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$sql = new Sql ();
$images = $sql->getRows( "SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.title = 'Commercial';" );
$sql->disconnect();
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

    <?php
    $nav = "commercial";
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php";
    ?>
    
    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Commercial</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Commercial</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Main Content -->
        <div class="row" style="margin-bottom: 30px;">
            <!-- Content Column -->
            <div class="col-md-12">
                <!-- Carousel -->
                <div id="commercialCarousel"
                    class="carousel slide carousel-three-by-two">
                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                            echo "<li data-target='#commercialCarousel' data-slide-to='$num'$class></li>";
                        }
                        ?>
                    </ol>

                    <!-- Wrapper for slides -->
                    <div class="carousel-inner">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $active_class = "";
                            if ($num == 0) {
                                $active_class = " active";
                            }
                            echo "<div class='item$active_class'>";
                            echo "    <div class='contain'";
                            echo "        style=\"background-image: url('" . $image ['location'] . "');\"></div>";
                            echo "    <div class='carousel-caption'>";
                            echo "        <h2>" . $image ['caption'] . "</h2>";
                            echo "    </div>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Controls -->
                    <a class="left carousel-control" href="#commercialCarousel"
                        data-slide="prev"> <span class="icon-prev"></span>
                    </a> <a class="right carousel-control" href="#commercialCarousel"
                        data-slide="next"> <span class="icon-next"></span>
                    </a>
                    <?php if ($user->isAdmin ()) { ?>
                    <span
                        style="position: absolute; bottom: 0px; right: 0px; padding: 5px;">
                        <button class="ajax-file-upload"
                            onclick="location.href='galleries.php?w=52'"
                            style="position: relative; overflow: hidden; cursor: pointer;">
                            <i class="fa fa-pencil-square-o"></i> Edit These Images
                        </button>
                    </span>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Commercial Services Section -->
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div section='Details'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Details</span> <img
                        class="img-responsive" src="img/details.jpg<?php echo $rand; ?>"
                        alt="Details">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="details.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div section='Gallery'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Gallery</span> <img
                        class="img-responsive" src="img/gallery.jpg<?php echo $rand; ?>"
                        alt="Gallery">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="gallery.php?w=52">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div section='Retouch'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Retouch</span> <img
                        class="img-responsive" src="img/retouch.jpg<?php echo $rand; ?>"
                        alt="Retouch">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="retouch.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div section='About'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>About</span> <img
                        class="img-responsive" src="img/about.jpg<?php echo $rand; ?>"
                        alt="About">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="/commercial/about.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div section='Raves'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Raves</span> <img
                        class="img-responsive" src="img/reviews.jpg<?php echo $rand; ?>"
                        alt="Raves">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="reviews.php?c=3">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div section='Blog'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <span class='preview-title'>Blog</span> <img class="img-responsive"
                        src="img/blog.jpg<?php echo $rand; ?>" alt="Blog">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info"
                            href="/blog/category.php?t=75">See More</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <?php
        require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php";
        ?>

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