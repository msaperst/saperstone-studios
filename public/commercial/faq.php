<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
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
                <h1 class="page-header text-center">FAQ</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">FAQs</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How do I schedule a session?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Scheduling your session is as easy as a quick phone
                        call or e mail to Saperstone Studios. I'll answer
                        any questions and gather more info on what type
                        of session you'd like. More details can be found
                        <a href='expect.php'>here</a></p>
            </div>

            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How should I dress for my photoshoot?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <ul>
                    <li>For a professional look, be sure to wear clothes
                            that won't distract from your facial features,
                            nothing overly patterned</li>
                    <li>When in doubt, go for a solid colored shirt and dark jacket</li>
                    <li>Keep your background color in mind! If you've chosen a white
                            backdrop, wearing a white shirt won't give you much separation
                            from the background to make you stand out</li>
                    <li>Simple tip: Make sure your clothing is clean and pressed</li>
                </ul>
            </div>

            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> What does retouch include?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Retouch is done on your final selects and includes skin
                        smoothing/blemish removal, removing flyaway hairs and
                        eye enhancement. Additional requests are accepted but
                        handled on a case by case basis and may require an
                        additional fee.</p>
            </div>

            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How long until I can expect the final images?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>For individual headshot sessions, expect an e mail with your
                        web gallery within 24 hours of your session. For large
                        companies, turn around time is 72 hours but can be rushed
                        if needed. Selects made will be given final retouch and
                        delivered within 72 hours of being submitted.</p>
            </div>

            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How can I use my images once high
                        resolution files are received?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>After you've made your final image selections, you'll
                        receive access to high-resolution digital files and
                        limited general licensing for the images. This means
                        you can use them for printing, posting online and sharing
                        with family and friends. Exceptions such as placement on
                        products for sale and/or books applies and may incur
                        additional fees. Contact us directly for more information.</p>
            </div>

            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> Our team is growing. What happens
                        when we add new staff?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Have your employees reach out to schedule their session.
                        We'll ensure the new hires photos match the look of
                        your previous session. On location discounts are provided
                        for rain dates, reshoots or new hires.</p>
            </div>

            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> Where is your studio located?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Saperstone Studios is located in Fairfax, VA right next
                        to Fair Lakes shopping center. We're also available
                        for travel within the northern Virginia DC/metro area.</p>
            </div>
        </div>
        <!-- /.row -->

        <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

    </div>
    <!-- /.container -->

    <!-- Content JavaScript -->
    <script src="/js/dynamic-content.js"></script>

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