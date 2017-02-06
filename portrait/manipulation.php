<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once "../header.php";
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

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
                <h1 class="page-header text-center">Other Image Edits</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="retouch.php">Retouch</a></li>
                    <li class="active">Other</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Portraits Retouch -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-lg-12">
                <p class='error'>Below are some examples of when a little retouch TLC goes a long
                    way when it comes to making your images perfect. Most of the time,
                    you won't even realize this behind the scenes magic has even
                    happened by the time you see your images. If you would like any
                    additional retouch after seeing your images I'm happy to
                    accommodate if the requests are minimal/standard. Otherwise a small
                    fee may be negotiated.</p>
                <p>Click the thumbnails below and use the slider at the bottom of
                    the image to see the before/after transformation.</p>
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

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <script src='/js/retouch.js'></script>
    <script>
        var images = [];
        images[0] = { thumb:'/retouch/img/portrait/manipulation/0001.jpg', orig:'/retouch/img/portrait/manipulation/0001_o.jpg', edit:'/retouch/img/portrait/manipulation/0001_e.jpg', width:'750', height:'589', text:'' };
        images[1] = { thumb:'/retouch/img/portrait/manipulation/0002.jpg', orig:'/retouch/img/portrait/manipulation/0002_o.jpg', edit:'/retouch/img/portrait/manipulation/0002_e.jpg', width:'750', height:'589', text:'' };
        images[2] = { thumb:'/retouch/img/portrait/manipulation/0009.jpg', orig:'/retouch/img/portrait/manipulation/0009_o.jpg', edit:'/retouch/img/portrait/manipulation/0009_e.jpg', width:'499', height:'750', text:'' };
        images[3] = { thumb:'/retouch/img/portrait/manipulation/0011.jpg', orig:'/retouch/img/portrait/manipulation/0011_o.jpg', edit:'/retouch/img/portrait/manipulation/0011_e.jpg', width:'750', height:'499', text:'' };
        var retouch = new Retouch( $('#holder'), images, true );
    </script>

</body>

</html>