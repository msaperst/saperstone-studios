<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql();
$user = new User($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
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
                <h1 class="page-header text-center">Background Options</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Backgrounds</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12"></div>

            <div class="col-lg-12">
                <h2 class="page-header">Studio Sessions</h2>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Whether you schedule a studio session here at Saperstone
                        Studios or if I bring everything to you, here are some
                        of the looks that can be achieved. Have another color/look
                        in mind? Let me know! Odds are we can make it happen.</p>
            </div>

            <div class="col-lg-12">
                <h3 class="page-header">Solid Grey</h3>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/solid-grey-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Solid Grey">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/solid-grey-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Solid Grey">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/solid-grey-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Solid Grey">
                </div>
            </div>

            <div class="col-lg-12">
                <h3 class="page-header">Solid White</h3>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/solid-white-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Solid White">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/solid-white-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Solid White">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/solid-white-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Solid White">
                </div>
            </div>

            <div class="col-lg-12">
                <h3 class="page-header">Muslin Grey</h3>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/muslin-grey-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Muslin Grey">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/muslin-grey-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Muslin Grey">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/muslin-grey-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Muslin Grey">
                </div>
            </div>

            <div class="col-lg-12">
                <h3 class="page-header">Muslin Brown</h3>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/muslin-brown-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Muslin Brown">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/muslin-brown-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Muslin Brown">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/muslin-brown-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Muslin Brown">
                </div>
            </div>


            <div class="col-lg-12">
                <h2 class="page-header">Environmental</h2>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Use the backdrop of your own office/surroundings to tell
                        the story of your profession/company.</p>
            </div>

            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/environmental-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Environmental">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/environmental-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Environmental">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/environmental-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Environmental">
                </div>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Outdoors</h2>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Take the session outside for an easy, natural and relaxed look.</p>
            </div>

            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/outdoors-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Outdoors">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/outdoors-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Outdoors">
                </div>
            </div>
            <div class="col-xs-4">
                <div class="<?php if ($user->isAdmin ()) { echo " editable square"; } ?>">
                    <img src="img/outdoors-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Outdoors">
                </div>
            </div>
        </div>
        <!-- /.row -->

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