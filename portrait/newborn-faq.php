<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link
    href="/css/uploadfile.css"
    rel="stylesheet">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "portrait"; require_once "../nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Newborn FAQ</h1>
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
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/newborn-faq-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/newborn-faq-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">When should I book my session?</h2>
            </div>
            <div class="col-md-12">
                <p>Newborn sessions are done within the first 2 weeks of life to get
                    those precious sleepy baby photos so it’s best to contact me as
                    soon as possible. Based on your due date, we’ll pencil in a
                    session. When baby arrives, let me know and we can adjust the date
                    if needed.</p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">How long can I expect the newborn session to
                    be?</h2>
            </div>
            <div class="col-md-12">
                <p>Every baby is different and to allow time for feeding/changing,
                    each session is about 3-4 hours.</p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">Where will the session be?</h2>
            </div>
            <div class="col-md-12">
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
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/newborn-faq-3.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/newborn-faq-4.jpg<?php echo $rand; ?>" width="100%"
                        alt="Newborn">
                </div>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">How can we prepare the baby?</h2>
            </div>
            <div class="col-md-12">
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
                <h2 class="page-header">How should we dress the baby?</h2>
            </div>
            <div class="col-md-12">
                <p>Don't worry about picking out clothes, unless there is something
                    of sentimental or religious value. About an hour before your
                    session place a loose diaper on them to avoid indentations on their
                    skin and wrap them in a swaddle. This will make it easier to
                    transition them from clothes to the shooting space. Also good are
                    hats. All babies look insanely adorable in hats.</p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">Will we (the parents) be in the photos?</h2>
            </div>
            <div class="col-md-12">
                <p>If you'd like to be! If you don't want to have your photo taken,
                    I would recommend at least holding/touching the baby or having
                    him/her grasp onto your fingers in some shots. This not only
                    illustrates the smallness of the newborn, but creates a very nice
                    family image. Make sure to wear solid colors, and have clean &
                    groomed nails.</p>
            </div>
        </div>
        <!-- /.row -->

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