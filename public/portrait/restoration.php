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
    ?>
    <link href="<?php echo Strings::assetUrl('/css/hover-effect.css'); ?>" rel="stylesheet">

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
                <h1 class="page-header text-center">Restoration</h1>
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
        <div class="row u-mt-30">
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
    <div id="retouch-config" class="hidden" data-instructions="true" data-images="<?php echo Strings::escapeHtmlAttribute('[{"thumb":"/retouch/portrait/restoration/10.jpg","orig":"/retouch/portrait/restoration/10-BEFORE.jpg","edit":"/retouch/portrait/restoration/10-AFTER.jpg","width":"400","height":"500","text":""},{"thumb":"/retouch/portrait/restoration/11_DSC_2405.jpg","orig":"/retouch/portrait/restoration/11_DSC_2405-BEFORE.jpg","edit":"/retouch/portrait/restoration/11_DSC_2405-AFTER.jpg","width":"680","height":"862","text":""},{"thumb":"/retouch/portrait/restoration/7.jpg","orig":"/retouch/portrait/restoration/7-BEFORE.jpg","edit":"/retouch/portrait/restoration/7-AFTER.jpg","width":"573","height":"716","text":""},{"thumb":"/retouch/portrait/restoration/CeliaGettinger.jpg","orig":"/retouch/portrait/restoration/CeliaGettinger-BEFORE.jpg","edit":"/retouch/portrait/restoration/CeliaGettinger-AFTER.jpg","width":"1000","height":"1394","text":""},{"thumb":"/retouch/portrait/restoration/Yayas_rt.jpg","orig":"/retouch/portrait/restoration/Yayas_rt-BEFORE.jpg","edit":"/retouch/portrait/restoration/Yayas_rt-AFTER.jpg","width":"2000","height":"1538","text":""}]'); ?>"></div>
    <script src="<?php echo Strings::assetUrl('/js/retouch-init.js'); ?>"></script>

</body>

</html>