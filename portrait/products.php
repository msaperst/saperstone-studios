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

    <?php
    $nav = "portrait";
    require_once "../nav.php";
    ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Products & Investments</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Products & Investments</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2>Investing in Custom Photography</h2>
            </div>
            <div class="col-md-12">
                <p>Saperstone Studios is a full-service studio, which means that I
                    go above and beyond simply providing you a USB of images that is
                    destined to get thrown into your junk drawer. My goal is to provide
                    you with custom artwork of you and your family to cherish for
                    generations to come. I offer a variety of high quality,
                    professional products for you to choose from, including canvas,
                    metal prints, keepsake albums, and yes – digital files.</p>
                <p>In today’s digital world, I know it is important for most people
                    to share and preserve images digitally via social media but I want
                    your images to have more longevity than the few days of 'likes' and
                    comments on your facebook page. Hanging your favorite images on the
                    walls of your home ensures that you, and generations after, can
                    enjoy them.</p>
                <p>All available products can be seen during your studio session and
                    at your image review session. You are also more than welcome to
                    schedule an appointment to visit the studio and view my available
                    products before booking your session. You can also get a sampling
                    of what I offer by viewing the links below.</p>
                <p>
                    If you are looking for beautiful artwork of your family and a fully
                    custom experience, then <a href='/contact.php'>contact me</a> today
                    :)
                </p>
            </div>
        </div>
        <!-- /.row -->

        <hr />

        <!-- Products Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2>Available Products</h2>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Story Grids'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Story Grids</span> <img
                        class="img-responsive"
                        src="img/story-grid.jpg<?php echo $rand; ?>" width="100%"
                        alt="Story Grids">
                    <div class="overlay">
                        <br />
                        <br />
                        <br /> <a class="info" href="galleries.php?w=29">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Heirloom Albums'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Keepsake Albums</span> <img
                        class="img-responsive"
                        src="img/keepsake-album.jpg<?php echo $rand; ?>" width="100%"
                        alt="Heirloom Albums">
                    <div class="overlay">
                        <br />
                        <br />
                        <br /> <a class="info" href="galleries.php?w=30">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Acrylic Prints'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Acrylic Prints</span> <img
                        class="img-responsive"
                        src="img/acrylic-print.jpg<?php echo $rand; ?>" width="100%"
                        alt="Acrylic Prints">
                    <div class="overlay">
                        <br />
                        <br />
                        <br /> <a class="info" href="galleries.php?w=31">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Keepsake Box'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Keepsake Box</span> <img
                        class="img-responsive"
                        src="img/keepsake-box.jpg<?php echo $rand; ?>" width="100%"
                        alt="Keepsake Box">
                    <div class="overlay">
                        <br />
                        <br />
                        <br /> <a class="info" href="galleries.php?w=32">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Bamboo Mounts'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Bamboo Mounts</span> <img
                        class="img-responsive"
                        src="img/bamboo-mount.jpg<?php echo $rand; ?>" width="100%"
                        alt="Bamboo Mounts">
                    <div class="overlay">
                        <br />
                        <br />
                        <br /> <a class="info" href="galleries.php?w=33">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Canvas Prints'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Canvas Prints</span> <img
                        class="img-responsive"
                        src="img/canvas-print.jpg<?php echo $rand; ?>" width="100%"
                        alt="Canvas Prints">
                    <div class="overlay">
                        <br />
                        <br />
                        <br /> <a class="info" href="galleries.php?w=34">See More</a>
                    </div>
                </div>
            </div>
        </div>

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