<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once "../header.php";
    if ($user->isAdmin ()) {
        ?>
    <link
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
    rel="stylesheet">
    <?php
    }
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
                <h1 class="page-header text-center">Retouch</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Retouch</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Studio Slideshow -->
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
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
        <script>
        var images = [];
        images.push({ thumb:'/retouch/img/Avon_20141127_0092.jpg', orig:'/retouch/img/Avon_20141127_0092_o.jpg', edit:'/retouch/img/Avon_20141127_0092_e.jpg', width:'1140', height:'759' });
        images.push({ thumb:'/retouch/img/Eliza_20160729_0008.jpg', orig:'/retouch/img/Eliza_20160729_0008_o.jpg', edit:'/retouch/img/Eliza_20160729_0008_e.jpg', width:'1140', height:'761' });
        images.push({ thumb:'/retouch/img/Kaminski_20161022_0017.jpg', orig:'/retouch/img/Kaminski_20161022_0017_o.jpg', edit:'/retouch/img/Kaminski_20161022_0017_e.jpg', width:'1140', height:'1708' });
        images.push({ thumb:'/retouch/img/Luke7mo_20160914_0055.jpg', orig:'/retouch/img/Luke7mo_20160914_0055_o.jpg', edit:'/retouch/img/Luke7mo_20160914_0055_e.jpg', width:'1140', height:'761' });
        images.push({ thumb:'/retouch/img/VanderhoofMaternity_20160612_0117.jpg', orig:'/retouch/img/VanderhoofMaternity_20160612_0117_o.jpg', edit:'/retouch/img/VanderhoofMaternity_20160612_0117_e.jpg', width:'1140', height:'761' });
        slider($('#holder'),images);
    </script>

</body>

</html>