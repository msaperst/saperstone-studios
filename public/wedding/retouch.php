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
    <link href="<?php echo Strings::assetUrl('/css/uploadfile.css'); ?>" rel="stylesheet">
    <?php
    }
    ?>
    <link href="<?php echo Strings::assetUrl('/css/hover-effect.css'); ?>" rel="stylesheet">

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
        <div class="row u-mt-30">
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
        <div class="row u-mt-30">
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

    <script src='<?php echo Strings::assetUrl('/js/retouch.js'); ?>'></script>
    <div id="retouch-config" class="hidden" data-instructions="true" data-images="[{&quot;thumb&quot;:&quot;/retouch/wedding/DSC_5338.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/DSC_5338before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/DSC_5338after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;829&quot;,&quot;text&quot;:&quot;The groomsmen at the bar is such a great shot!  I wanted to align everyone evenly under the purple lights but there was a column to my left that prevented that.  My symmetry OCD kicked in and I added the additional lights on the right in post production.&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/KimmyTim_06012013_0117.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/KimmyTim_06012013_0117before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/KimmyTim_06012013_0117after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;1713&quot;,&quot;text&quot;:&quot;Paper aisle runners outdoors are always nice in theory, but the wind tends to take them away.&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/MeganBen_20160807_0018.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/MeganBen_20160807_0018before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/MeganBen_20160807_0018after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;761&quot;,&quot;text&quot;:&quot;When you&#039;re at your engagement session and you don&#039;t have kids (yet).&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/MeganBen_20160807_0188.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/MeganBen_20160807_0188before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/MeganBen_20160807_0188after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;1708&quot;,&quot;text&quot;:&quot;Night time photography is dramatic but sometimes I like to add an additional flare.&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/MonicaRay_20130407_0176.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/MonicaRay_20130407_0176before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/MonicaRay_20130407_0176after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;758&quot;,&quot;text&quot;:&quot;When you want the whole park to yourself&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/NickJM_20131218_0021.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/NickJM_20131218_0021before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/NickJM_20131218_0021after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;1713&quot;,&quot;text&quot;:&quot;Sometimes it surprises me how oblivious people are to their surroundings.  Proposals are spontaneous but that doesn&#039;t mean you want people ruining your perfect backdrop.&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/Proposal_20160625_0051.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/Proposal_20160625_0051before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/Proposal_20160625_0051after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;1708&quot;,&quot;text&quot;:&quot;This ferris wheel is where they had their first date!  Guy in green was not invited.&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/TeaCeremony_20130921_0109.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/TeaCeremony_20130921_0109before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/TeaCeremony_20130921_0109after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;759&quot;,&quot;text&quot;:&quot;At this couples Tea Ceremony, it is traditional for the guy to prove his worthiness of the Bride by passing a series of tests set by the Bridesmaids and finally reaching his Bride.  Only one photographer needed for this moment though!&quot;},{&quot;thumb&quot;:&quot;/retouch/wedding/TimVanessa_05032013_0081.jpg&quot;,&quot;orig&quot;:&quot;/retouch/wedding/TimVanessa_05032013_0081before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/wedding/TimVanessa_05032013_0081after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;759&quot;,&quot;text&quot;:&quot;The way her legs are tucked behind her to the side was a bit odd.  No problem, retouched in post!&quot;}]"></div>
    <script src="<?php echo Strings::assetUrl('/js/retouch-init.js'); ?>"></script>

</body>

</html>