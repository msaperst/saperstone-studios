<?php
$referer = "Unknown";
if (isset ( $_SERVER ['HTTP_REFERER'] )) {
    $referer = $_SERVER ['HTTP_REFERER'];
}
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta name='robots' content='noindex'>
    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>

</head>

<body>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center"><?php echo $title; ?>
                    <small><?php echo $subtitle; ?></small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active"><?php echo $title; ?></li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Services Section -->
        <div class="row">
            <div class="col-lg-12">
                <img id='confused' src='/img/confused.png'
                    alt='Where am I?' />
                <p class='lead'>Whoops, something went wrong!</p>
                <p class='lead'><?php echo $message; ?></p>
                <p class='lead'>
                    Try going <a href='#' id='error-back-link'>back one page</a>
                    or going back to our <a href='http://$host'>homepage</a>
                </p>
                <p class='lead'>
                    We have been notifed of this error, however, feel free to <a
                        target="_blank" href='mailto:webmaster@saperstonestudios.com'>contact
                        our webmaster</a> for more information
                </p>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <div id="error-report-config" class="hidden"
         data-error="<?php echo Strings::escapeHtmlAttribute($title); ?>"
         data-page="<?php echo Strings::escapeHtmlAttribute($session->getCurrentPage()); ?>"
         data-referrer="<?php echo Strings::escapeHtmlAttribute($referer); ?>"></div>
    <script src="<?php echo Strings::assetUrl('/js/error-report.js'); ?>"></script>

</body>
</html>