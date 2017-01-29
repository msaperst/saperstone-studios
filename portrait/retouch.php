<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once "../header.php";
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
    rel="stylesheet">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    rel="stylesheet">
    <?php
    }
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

</head>

<body>

    <?php
    $nav = "portrait";
    require_once "../nav.php";
    ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Retouch</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li class="active">Retouch</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Portraits Retouch -->
        <div class="row" style="margin-top: 30px;">
            <div class="col-lg-12">
                <p class='error'>NEED SOME BASIC RETOUCH TEST.</p>
            </div>
        </div>

        <!-- Products Section -->
        <div class="row" style='padding-top: 30px'>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Retouch'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Portrait Retouch</span> <img
                        class="img-responsive"
                        src="img/portrait-retouch.jpg<?php echo $rand; ?>" width="100%"
                        alt="Retouch">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="portrait-retouch.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Restoration'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Restoration</span> <img
                        class="img-responsive"
                        src="img/restoration.jpg<?php echo $rand; ?>" width="100%"
                        alt="Restoration">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="restoration.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section='Manipulation'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Other</span> <img
                        class="img-responsive"
                        src="img/manipulation.jpg<?php echo $rand; ?>" width="100%"
                        alt="Manipulation">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="manipulation.php">See
                            More</a>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <?php
if ($user->isAdmin ()) {
    ?>
        <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <?php
}
?>
    
</body>

</html>