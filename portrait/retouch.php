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

        <!-- Portraits Retouch -->
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

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <script src='/js/retouch.js'></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <script>
        var images = [];
        images.push({ thumb:'/retouch/img/Avon_20141127_0092.jpg', orig:'/retouch/img/Avon_20141127_0092_o.jpg', edit:'/retouch/img/Avon_20141127_0092_e.jpg', width:'1140', height:'759', text:'When your adorable 2 year old isn\'t trusted with your 1 week old quite yet.' });
        images.push({ thumb:'/retouch/img/Eliza_20160729_0008.jpg', orig:'/retouch/img/Eliza_20160729_0008_o.jpg', edit:'/retouch/img/Eliza_20160729_0008_e.jpg', width:'1140', height:'761', text:'Newborns are heavy!  Getting the right angle for both of these cuties required a bit of post retouch.' });
        images.push({ thumb:'/retouch/img/Luke7mo_20160914_0055.jpg', orig:'/retouch/img/Luke7mo_20160914_0055_o.jpg', edit:'/retouch/img/Luke7mo_20160914_0055_e.jpg', width:'1140', height:'761', text:'Drool happens. Need I say more?' });
        images.push({ thumb:'/retouch/img/VanderhoofMaternity_20160612_0117.jpg', orig:'/retouch/img/VanderhoofMaternity_20160612_0117_o.jpg', edit:'/retouch/img/VanderhoofMaternity_20160612_0117_e.jpg', width:'1140', height:'761', text:'When you have a gorgeous gown that needs to flow naturally but there\'s no breeze in sight.  Recruit the husband to fluff the dress and run!' });
        slider($('#holder'),images);
    </script>

</body>

</html>