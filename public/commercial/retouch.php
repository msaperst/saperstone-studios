<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
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

    <?php
    $nav = "commercial";
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
                    <li><a href="index.php">Commercial</a></li>
                    <li class="active">Retouch</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Commercial Retouch -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-lg-12">
                <p>Below are some examples of when a little retouch TLC goes a long
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

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <script src='/js/retouch.js'></script>
    <script>
        var images = [];
        images[0] = { thumb:'/retouch/commercial/Evolent_20190307_0023.jpg', orig:'/retouch/commercial/Evolent_20190307_0023-before.jpg', edit:'/retouch/commercial/Evolent_20190307_0023-after.jpg', width:'1140', height:'761', text:'' };
        images[1] = { thumb:'/retouch/commercial/FairfaxClinic_20190719_0001.jpg', orig:'/retouch/commercial/FairfaxClinic_20190719_0001-before.jpg', edit:'/retouch/commercial/FairfaxClinic_20190719_0001-after.jpg', width:'1140', height:'759', text:'' };
        images[2] = { thumb:'/retouch/commercial/JayCTyrolerMDOffice_20180815_0005.jpg', orig:'/retouch/commercial/JayCTyrolerMDOffice_20180815_0005-before.jpg', edit:'/retouch/commercial/JayCTyrolerMDOffice_20180815_0005-after.jpg', width:'1140', height:'761', text:'' };
        images[3] = { thumb:'/retouch/commercial/KatieBittner-Cassett_20190401_0014.jpg', orig:'/retouch/commercial/KatieBittner-Cassett_20190401_0014-before.jpg', edit:'/retouch/commercial/KatieBittner-Cassett_20190401_0014-after.jpg', width:'1140', height:'1708', text:'' };
        var retouch = new Retouch( $('#holder'), images, true );
    </script>

</body>

</html>