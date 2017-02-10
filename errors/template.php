<?php
$referer = "Unknown";
if (isset ( $_SERVER ['HTTP_REFERER'] )) {
    $referer = $_SERVER ['HTTP_REFERER'];
}

$location = "";
if (! is_file ( "${location}header.php" )) {
    $location = "../";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta name='robots' content='noindex'>
    <?php require_once "${location}header.php"; ?>

</head>

<body>

    <?php require_once "${location}nav.php"; ?>

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
						href='mailto:webmaster@saperstonestudios.com'>contact our
						webmaster</a> for more information
				</p>
			</div>
		</div>
		<!-- /.row -->

        <?php require_once "${location}footer.php"; ?>

    </div>
	<!-- /.container -->

	<script type='text/javascript'>
        jQuery(document).ready(function($) {
                //send our message
                $.post(
                        '/api/send-error.php',
                        {
                            error: '<?php echo $title; ?>',
                            page: '<?php echo curPageURL(); ?>',
                            referrer: '<?php echo $referer ?>',
                            resolution: screen.width+'x'+screen.height
                        }
                );
        });
    </script>

</body>

</html>

<?php
function curPageURL() {
    $pageURL = 'http';
    if (isset ( $_SERVER ["HTTPS"] ) && $_SERVER ["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER ["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];
    }
    return $pageURL;
}
?>