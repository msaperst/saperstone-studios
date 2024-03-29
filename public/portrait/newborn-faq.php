<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
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

    <?php $nav = "portrait"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Newborn Frequently Asked Questions</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Newborns</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/newborn-faq-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/newborn-faq-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> When should I book my session?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Newborn sessions are done within the first 2 weeks of life to get
                    those precious sleepy baby photos so it’s best to contact me as
                    soon as possible. Based on your due date, we’ll pencil in a
                    session. When baby arrives, let me know and we can adjust the date
                    if needed.</p>
            </div>
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How long can I expect the
                    newborn session to be?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Every baby is different and to allow time for feeding/changing,
                    each session is about 3-4 hours.</p>
            </div>
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> Where will the session be?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>There are two options:
                <ul>
                    <li><strong>Your Home</strong>: I can include all of those personal
                        elements into the photographs including the nursery. An additional
                        $50 charge is applied for newborn session at your home vs my
                        studio space.</li>
                    <li><strong>My Home Studio</strong>: Ensures you have access to all
                        my blankets and props instead of a selected amount. I do still
                        encourage you to bring personal items from home to add a unique
                        look to your session.</li>
                </ul>
                </p>
            </div>
        </div>
        <div class="row" style="padding-top: 20px;">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/newborn-faq-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/newborn-faq-4.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How can we prepare the baby?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>All of the following are guidelines, at the end of the day your
                    newborn runs the show and if they don’t want to follow everything
                    to the tee, then don’t stress! We will have a wonderful session
                    either way.</p>
                <ul>
                    <li>Giving your newborn a sponge bath the same day as yur session
                        will minimize any flakey skin.</li>
                    <li>Try to keep them awake 2 hours before hand so they are well
                        tuckered out for their session.</li>
                    <li>If your session is at your home, feed them upon my arrival to
                        get them ‘milk drunk’. By the time I’m set up and ready to go they
                        should be out.</li>
                    <li>If your session is at my studio, feed them upon arrival to the
                        studio.</li>
                </ul>
            </div>
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> How should we dress the baby?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>Don't worry about picking out clothes, unless there is something
                    of sentimental or religious value. About an hour before your
                    session place a loose diaper on them to avoid indentations on their
                    skin and wrap them in a swaddle. This will make it easier to
                    transition them from clothes to the shooting space. Also good are
                    hats. All babies look insanely adorable in hats.</p>
            </div>
            <div class="col-lg-12">
                <h4 class="page-header collapse-header"><i class="fa fa-plus-square"></i> Will we (the parents) be in the photos?</h4>
            </div>
            <div class="col-xs-12 collapse-content" style="padding-top: 20px;">
                <p>If you'd like to be! If you don't want to have your photo taken,
                    I would recommend at least holding/touching the baby or having
                    him/her grasp onto your fingers in some shots. This not only
                    illustrates the smallness of the newborn, but creates a very nice
                    family image. Make sure to wear solid colors, and have clean &
                    groomed nails.</p>
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