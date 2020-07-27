<?php
$referer = "Unknown";
if (isset ( $_SERVER ['HTTP_REFERER'] )) {
    $referer = $_SERVER ['HTTP_REFERER'];
}
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
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
                    style='width: 150px; float: left; margin: 0px 20px 20px 0px;'
                    alt='Where am I?' />
                <p class='lead'>Whoops, something went wrong!</p>
                <p class='lead'><?php echo $message; ?></p>
                <p class='lead'>
                    Try going <a href='javascript:window.history.back()'>back one page</a>
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

    <script type='text/javascript'>
        jQuery(document).ready(function($) {
                //send our message
                $.post(
                        '/api/send-error.php',
                        {
                            error: '<?php echo $title; ?>',
                            page: '<?php echo getCurrentPage(); ?>',
                            referrer: '<?php echo $referer ?>',
                            resolution: screen.width+'x'+screen.height
                        }
                );
        });
    </script>

</body>
</html>