<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$images = $sql->getRows( "SELECT gallery_images.* FROM `gallery_images` JOIN `galleries` ON gallery_images.gallery = galleries.id WHERE galleries.id = 62;" );
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

    <?php $nav = "commercial"; require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Photobooth</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Photobooth</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <p>
                    Perfect for any event, photo booths are a fantastic way
                    to add instant fun to any occasion. Here are the basics, please <a
                        href='/contact.php'>contact us</a> for more details!
                </p>
                <ul>
                    <li>Open backdrop to ensure large group shots are possible</li>
                    <li>Attendant is included for setup/breakdown and is there the
                        entire time to make sure everything runs smoothly</li>
                    <li>3 photos taken per group to be put into photo strips</li>
                    <li>Strips are printed throughout the photobooth for guests to come
                        back and collect</li>
                    <li>Photo strips are customized for your event! Add a logo, the
                        date or your names for a personal touch</li>
                    <li>All individual images as well as photo strips available for
                        download via web gallery within a week of your event</li>
                    <li>Fun props included!</li>
                </ul>
            </div>
        </div>
        <!-- /.row -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <!-- Carousel -->
                <div id="photobooth-carousel"
                    class="carousel slide carousel-three-by-two">
                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                    <?php
                    foreach ( $images as $num => $image ) {
                        $class = "";
                        if ($num == 0) {
                            $class = " class='active'";
                        }
                        echo "<li data-target='#photobooth-carousel' data-slide-to='$num'$class></li>";
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
                    <a class="left carousel-control" href="#photobooth-carousel"
                        data-slide="prev"> <span class="icon-prev"></span>
                    </a> <a class="right carousel-control" href="#photobooth-carousel"
                        data-slide="next"> <span class="icon-next"></span>
                    </a>
                    <?php if ($user->isAdmin ()) { ?>
                    <span
                        style="position: absolute; bottom: 0px; right: 0px; padding: 5px;">
                        <button class="ajax-file-upload"
                            onclick="location.href='/commercial/galleries.php?w=62'"
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