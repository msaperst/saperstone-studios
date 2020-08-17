<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        $rand = "?" . Strings::randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "commercial"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Pricing</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Pricing</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12"></div>

            <div class="col-lg-12">
                <h2 class="page-header">The Fab Five | $100</h2>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable vertical"; } ?>">
                    <img src="img/fab-five.jpg<?php echo $rand; ?>" width="100%"
                        alt="The Fab Five">
                </div>
            </div>
            <div class="col-xs-8">
                <p>Down and dirty, we get this done QUICK. These sessions
                        are only done outside at Saperstone Studios and perfect
                        for an easy breezy updated social media/headshot photo</p>
                <p><ul>
                    <li>5 minute session</li>
                    <li>One look/outfit</li>
                    <li><a href='galleries.php?w=54'>On Location Headshot</a> at Saperstone Studios (located in Fairfax, VA)</li>
                    <li>Same day web gallery with 10-15 unretouched selects to choose from</li>
                    <li>1 file of your choice retouched with print release</li>
                    <li>Additional files available for purchase</li>
                </ul></p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">The Basic | $200</h2>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable vertical"; } ?>">
                    <img src="img/basic.jpg<?php echo $rand; ?>" width="100%"
                        alt="The Basic">
                </div>
            </div>
            <div class="col-xs-8">
                <p>More time to allow for additional looks and poses</p>
                <p><ul>
                    <li>20 minute session</li>
                    <li>Up to two looks/outfits</li>
                    <li><a href='galleries.php?w=54'>On Location Headshot</a> at a local park to Saperstone Studios
                            (Located in Fairfax, VA) or a <a href='galleries.php?w=53'>Studio Headshot</a> session</li>
                    <li>1 background for <a href='galleries.php?w=53'>Studio</a> session</li>
                    <li>Same day web gallery with 40+ unretouched selects to choose from</li>
                    <li>1 file of your choice retouched with print release</li>
                    <li>Additional files available for purchase</li>
                </ul></p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">The Professional | $400</h2>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable vertical"; } ?>">
                    <img src="img/professional.jpg<?php echo $rand; ?>" width="100%"
                        alt="The Professional">
                </div>
            </div>
            <div class="col-xs-8">
                <p>The full works, perfect for professionals and creatives to give a
                        full variety of backdrops, outfits and poses</p>
                <p><ul>
                    <li>40 minute session</li>
                    <li>Up to three looks/outfits</li>
                    <li><a href='galleries.php?w=54'>On Location Headshot</a> at a local park to Saperstone Studios
                                (Located in Fairfax, VA) or a <a href='galleries.php?w=53'>Studio Headshot</a> session</li>
                    <li>3 background changes for <a href='galleries.php?w=53'>Studio</a> session</li>
                    <li>Same day web gallery with 75+ unretouched selects to choose from</li>
                    <li>3 files of your choice retouched with print release</li>
                    <li>Additional files available for purchase</li>
                </ul></p>
            </div>
        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Need More Information?</h2>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Looking for more info on events or company headshots?
                        <a href='/contact.php'>Please reach out</a> as more
                        details are needed to provide a full custom quote!</p>
            </div>
        </div>

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
    }
    ?>

</body>

</html>