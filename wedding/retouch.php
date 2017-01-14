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
    $nav = "wedding";
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
                    <li><a href="index.php">Weddings</a></li>
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
        images.push({ thumb:'/retouch/img/DSC_5338.jpg', orig:'/retouch/img/DSC_5338before.jpg', edit:'/retouch/img/DSC_5338after.jpg', width:'1140', height:'829' });
        images.push({ thumb:'/retouch/img/KimmyTim_06012013_0117.jpg', orig:'/retouch/img/KimmyTim_06012013_0117before.jpg', edit:'/retouch/img/KimmyTim_06012013_0117after.jpg', width:'1140', height:'1713' });
        images.push({ thumb:'/retouch/img/MeganBen_20160807_0018.jpg', orig:'/retouch/img/MeganBen_20160807_0018before.jpg', edit:'/retouch/img/MeganBen_20160807_0018after.jpg', width:'1140', height:'761' });
        images.push({ thumb:'/retouch/img/MeganBen_20160807_0188.jpg', orig:'/retouch/img/MeganBen_20160807_0188before.jpg', edit:'/retouch/img/MeganBen_20160807_0188after.jpg', width:'1140', height:'1708' });
        images.push({ thumb:'/retouch/img/MonicaRay_20130407_0176.jpg', orig:'/retouch/img/MonicaRay_20130407_0176before.jpg', edit:'/retouch/img/MonicaRay_20130407_0176after.jpg', width:'1140', height:'758' });
        images.push({ thumb:'/retouch/img/NickJM_20131218_0021.jpg', orig:'/retouch/img/NickJM_20131218_0021before.jpg', edit:'/retouch/img/NickJM_20131218_0021after.jpg', width:'1140', height:'1713' });
        images.push({ thumb:'/retouch/img/Proposal_20160625_0051.jpg', orig:'/retouch/img/Proposal_20160625_0051before.jpg', edit:'/retouch/img/Proposal_20160625_0051after.jpg', width:'1140', height:'1708' });
        images.push({ thumb:'/retouch/img/TeaCeremony_20130921_0109.jpg', orig:'/retouch/img/TeaCeremony_20130921_0109before.jpg', edit:'/retouch/img/TeaCeremony_20130921_0109after.jpg', width:'1140', height:'759' });
        images.push({ thumb:'/retouch/img/TimVanessa_05032013_0081.jpg', orig:'/retouch/img/TimVanessa_05032013_0081before.jpg', edit:'/retouch/img/TimVanessa_05032013_0081after.jpg', width:'1140', height:'759' });
        slider($('#holder'),images);
    </script>

</body>

</html>