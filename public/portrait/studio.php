<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
$sql = new Sql ();
$images = $sql->getRows( "SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.title = 'Home Studio';" );
$sql->disconnect();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php";
    if ($user->isAdmin ()) {
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">


</head>

<body>

    <?php $nav = explode ( "/", $_SERVER ['REQUEST_URI'] ) [1]; require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Home Studio</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php"><?php echo ucwords($nav); ?></a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Studio</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Welcome to the studio!</h2>
            </div>
            <div class="col-md-12">
                <p>
                    We're located in Fairfax, VA over by Fair Lakes Shopping Center.
                    While most of my sessions are photographed on location, I do
                    accomodate business headshot's and <a
                        href='../portrait/gallery.php?w=1'>children 1 and younger</a> at
                    my home studio location. Come visit me but please be sure to <a
                        href='/contact.php'>book an appointment</a> first!
                </p>
            </div>
        </div>
        <!-- /.row -->

        <!-- Studio Slideshow -->
        <div class="row" style="margin-top: 30px;">
            <!-- Content Column -->
            <div class="col-md-12">
                <!-- Carousel -->
                <div id="studioCarousel"
                    class="carousel slide carousel-three-by-two">
                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                        <?php
                        foreach ( $images as $num => $image ) {
                            $class = "";
                            if ($num == 0) {
                                $class = " class='active'";
                            }
                            echo "<li data-target='#studioCarousel' data-slide-to='$num'$class></li>";
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
                    <a class="left carousel-control" href="#studioCarousel"
                        data-slide="prev"> <span class="icon-prev"></span>
                    </a> <a class="right carousel-control" href="#studioCarousel"
                        data-slide="next"> <span class="icon-next"></span>
                    </a>
                    <?php if ($user->isAdmin ()) { ?>
                    <span
                        style="position: absolute; bottom: 0px; right: 0px; padding: 5px;">
                        <button class="ajax-file-upload"
                            onclick="location.href='/portrait/galleries.php?w=16'"
                            style="position: relative; overflow: hidden; cursor: pointer;">
                            <i class="fa fa-pencil-square-o"></i> Edit These Images
                        </button>
                    </span>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

</body>

</html>