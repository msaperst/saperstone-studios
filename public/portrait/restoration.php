<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php";
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php
    $nav = "portrait";
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
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="retouch.php">Retouch</a></li>
                    <li class="active">Restoration</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Portraits Retouch -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-lg-12">
                <p>
                    Have an old photograph that needs a little TLC and restoration?
                    Whether it's in need of some color correction, its faded or worn,
                    has scratches, water damage, tears or even missing parts, I'll be
                    able to restore your photo to it's original look. <a
                        href='/contact.php'>Contact me</a> to make an appointment at my
                    studio. We'll take a look at your photos and discuss what you'd
                    like done. Quotes are based on how much time I estimate the
                    restoration to take.
                </p>
            </div>
        </div>
        <div class="row" style="margin-top: 30px;">
            <!-- Content Column -->
            <div class="col-md-offset-2 col-md-8">
                <div class='text-center'>
                    <div id='holder' class='holder'></div>
                </div>
            </div>
        </div>

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script src='/js/retouch.js'></script>
    <script>
    var images = [];
    images[0] = { thumb:'/retouch/portrait/restoration/10.jpg', orig:'/retouch/portrait/restoration/10-BEFORE.jpg', edit:'/retouch/portrait/restoration/10-AFTER.jpg', width:'400', height:'500', text:'' };
    images[1] = { thumb:'/retouch/portrait/restoration/11_DSC_2405.jpg', orig:'/retouch/portrait/restoration/11_DSC_2405-BEFORE.jpg', edit:'/retouch/portrait/restoration/11_DSC_2405-AFTER.jpg', width:'680', height:'862', text:'' };
    images[2] = { thumb:'/retouch/portrait/restoration/7.jpg', orig:'/retouch/portrait/restoration/7-BEFORE.jpg', edit:'/retouch/portrait/restoration/7-AFTER.jpg', width:'573', height:'716', text:'' };
    images[3] = { thumb:'/retouch/portrait/restoration/CeliaGettinger.jpg', orig:'/retouch/portrait/restoration/CeliaGettinger-BEFORE.jpg', edit:'/retouch/portrait/restoration/CeliaGettinger-AFTER.jpg', width:'1000', height:'1394', text:'' };
    images[4] = { thumb:'/retouch/portrait/restoration/Yayas_rt.jpg', orig:'/retouch/portrait/restoration/Yayas_rt-BEFORE.jpg', edit:'/retouch/portrait/restoration/Yayas_rt-AFTER.jpg', width:'2000', height:'1538', text:'' };
    var retouch = new Retouch( $('#holder'), images, true );
    </script>

</body>

</html>