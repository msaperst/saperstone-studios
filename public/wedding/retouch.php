<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
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
    $nav = "wedding";
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
                    <li><a href="index.php">Weddings</a></li>
                    <li class="active">Retouch</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Wedding Retouch -->
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
        images[0] = { thumb:'/retouch/wedding/DSC_5338.jpg', orig:'/retouch/wedding/DSC_5338before.jpg', edit:'/retouch/wedding/DSC_5338after.jpg', width:'1140', height:'829', text:'The groomsmen at the bar is such a great shot!  I wanted to align everyone evenly under the purple lights but there was a column to my left that prevented that.  My symmetry OCD kicked in and I added the additional lights on the right in post production.' };
        images[1] = { thumb:'/retouch/wedding/KimmyTim_06012013_0117.jpg', orig:'/retouch/wedding/KimmyTim_06012013_0117before.jpg', edit:'/retouch/wedding/KimmyTim_06012013_0117after.jpg', width:'1140', height:'1713', text:'Paper aisle runners outdoors are always nice in theory, but the wind tends to take them away.' };
        images[2] = { thumb:'/retouch/wedding/MeganBen_20160807_0018.jpg', orig:'/retouch/wedding/MeganBen_20160807_0018before.jpg', edit:'/retouch/wedding/MeganBen_20160807_0018after.jpg', width:'1140', height:'761', text:'When you\'re at your engagement session and you don\'t have kids (yet).' };
        images[3] = { thumb:'/retouch/wedding/MeganBen_20160807_0188.jpg', orig:'/retouch/wedding/MeganBen_20160807_0188before.jpg', edit:'/retouch/wedding/MeganBen_20160807_0188after.jpg', width:'1140', height:'1708', text:'Night time photography is dramatic but sometimes I like to add an additional flare.' };
        images[4] = { thumb:'/retouch/wedding/MonicaRay_20130407_0176.jpg', orig:'/retouch/wedding/MonicaRay_20130407_0176before.jpg', edit:'/retouch/wedding/MonicaRay_20130407_0176after.jpg', width:'1140', height:'758', text:'When you want the whole park to yourself' };
        images[5] = { thumb:'/retouch/wedding/NickJM_20131218_0021.jpg', orig:'/retouch/wedding/NickJM_20131218_0021before.jpg', edit:'/retouch/wedding/NickJM_20131218_0021after.jpg', width:'1140', height:'1713', text:'Sometimes it surprises me how oblivious people are to their surroundings.  Proposals are spontaneous but that doesn\'t mean you want people ruining your perfect backdrop.' };
        images[6] = { thumb:'/retouch/wedding/Proposal_20160625_0051.jpg', orig:'/retouch/wedding/Proposal_20160625_0051before.jpg', edit:'/retouch/wedding/Proposal_20160625_0051after.jpg', width:'1140', height:'1708', text:'This ferris wheel is where they had their first date!  Guy in green was not invited.' };
        images[7] = { thumb:'/retouch/wedding/TeaCeremony_20130921_0109.jpg', orig:'/retouch/wedding/TeaCeremony_20130921_0109before.jpg', edit:'/retouch/wedding/TeaCeremony_20130921_0109after.jpg', width:'1140', height:'759', text:'At this couples Tea Ceremony, it is traditional for the guy to prove his worthiness of the Bride by passing a series of tests set by the Bridesmaids and finally reaching his Bride.  Only one photographer needed for this moment though!' };
        images[8] = { thumb:'/retouch/wedding/TimVanessa_05032013_0081.jpg', orig:'/retouch/wedding/TimVanessa_05032013_0081before.jpg', edit:'/retouch/wedding/TimVanessa_05032013_0081after.jpg', width:'1140', height:'759', text:'The way her legs are tucked behind her to the side was a bit odd.  No problem, retouched in post!' };
        var retouch = new Retouch( $('#holder'), images, true );
    </script>

</body>

</html>