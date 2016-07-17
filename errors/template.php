<?php
    $referer = "Unknown";
    if( isset( $_SERVER['HTTP_REFERER'] ) ) {
        $referer = $_SERVER['HTTP_REFERER'];
    }
    $host = "";
    if( isset( $_SERVER['HTTP_HOST'] ) ) {
        $host = $_SERVER['HTTP_HOST'];
    }
    $scripts = "<meta name='robots' content='noindex'>\n";
    $scripts .= "<script type='text/javascript'>
        jQuery(document).ready(function($) {
                //send our inputs
                $.post(
                        '/Includes/php/contact.php',
                        {
                            to: 'Webmaster<msaperst@gmail.com>', 
                            name: 'Unknown', 
                            email: 'Unknown', 
                            topic: '$title', 
                            message: 'Page Error $title on page ".$host.$_SERVER['REQUEST_URI'].".<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page $referer', 
                            resolution: screen.width+'x'+screen.height, 
                            position: lat+','+lon
                        }
                );
        });
    </script>\n";

    $style = "<style>#confused { padding:50px; }</style>\n";

    $header = "Whoops, something went wrong!";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>

</head>

<body>

    <?php require_once "../nav.php"; ?>

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
                <img id='confused' src='/img/confused.png' style='width:150px; float:left; margin:0px 20px 20px 0px;' alt='Where am I?' />
                <p class='lead'>Whoops, something went wrong!</p>
                <p class='lead'><?php echo $message; ?></p>
                <p class='lead'>Try going <a href='javascript:window.history.back()'>back one page</a> or going back to our <a href='http://$host'>homepage</a></p>
                <p class='lead'>We have been notifed of this error, however, feel free to <a href='mailto:webmaster@saperstonestudios.com'>contact our webmaster</a> for more information</p>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Gallery JavaScript -->
    <script src="/js/album.js"></script>

    <!-- Script to Activate the Gallery -->
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        var album = new Album( "<?php echo $album_info['id']; ?>", 4, <?php echo count($images); ?> );
        
         var loaded = 0;
        $(window,document).on("scroll resize", function(){
            if( $('footer').isOnScreen() && loaded < <?php echo count($images); ?> ) {
                loaded = album.loadImages();
            }
        });
    </script>

</body>

</html>