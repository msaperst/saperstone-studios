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
    <div id="retouch-config" class="hidden" data-instructions="true" data-images="[{&quot;thumb&quot;:&quot;/retouch/commercial/Evolent_20190307_0023.jpg&quot;,&quot;orig&quot;:&quot;/retouch/commercial/Evolent_20190307_0023-before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/commercial/Evolent_20190307_0023-after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;761&quot;,&quot;text&quot;:&quot;&quot;},{&quot;thumb&quot;:&quot;/retouch/commercial/FairfaxClinic_20190719_0001.jpg&quot;,&quot;orig&quot;:&quot;/retouch/commercial/FairfaxClinic_20190719_0001-before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/commercial/FairfaxClinic_20190719_0001-after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;759&quot;,&quot;text&quot;:&quot;&quot;},{&quot;thumb&quot;:&quot;/retouch/commercial/JayCTyrolerMDOffice_20180815_0005.jpg&quot;,&quot;orig&quot;:&quot;/retouch/commercial/JayCTyrolerMDOffice_20180815_0005-before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/commercial/JayCTyrolerMDOffice_20180815_0005-after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;761&quot;,&quot;text&quot;:&quot;&quot;},{&quot;thumb&quot;:&quot;/retouch/commercial/KatieBittner-Cassett_20190401_0014.jpg&quot;,&quot;orig&quot;:&quot;/retouch/commercial/KatieBittner-Cassett_20190401_0014-before.jpg&quot;,&quot;edit&quot;:&quot;/retouch/commercial/KatieBittner-Cassett_20190401_0014-after.jpg&quot;,&quot;width&quot;:&quot;1140&quot;,&quot;height&quot;:&quot;1708&quot;,&quot;text&quot;:&quot;&quot;}]"></div>
    <script src="<?php echo Strings::assetUrl('/js/retouch-init.js'); ?>"></script>

</body>

</html>