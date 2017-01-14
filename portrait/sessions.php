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
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
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
                <h1 class="page-header text-center">Session Information</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Sessions</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <p>From the moment you realize you’re having a baby, you know it’s
                    going to be an amazing journey. Capture those precious moments in
                    your families life with Saperstone Studios. The first year is when
                    your baby will change and grow the most. Find out more about our
                    milestone photography session options below.</p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Maternity</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/maternity-1.jpg<?php echo $rand; ?>" width="100%" alt="Maternity">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/maternity-2.jpg<?php echo $rand; ?>" width="100%" alt="Maternity">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Be sure to document your pregnancy with a maternity session.
                    Every pregnancy is unique so the exact timing of your session will
                    vary. Generally the best time is between 30 and 34 weeks. We want
                    you to be showing but also comfortable. If you're expecting twins
                    then schedule earlier!</p>
                <ul>
                    <li>Portraits of you and your significant other. Siblings welcome!</li>
                    <li>1 hour session at your home or on site location in Northern
                        VA/DC metro area</li>
                    <li>50+ fully edited images for viewing at your image review
                        session</li>
                </ul>
                <p>
                    <a href='galleries.php?w=2'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Newborn</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/newborn-1.jpg<?php echo $rand; ?>" width="100%" alt="Newborn">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/newborn-2.jpg<?php echo $rand; ?>" width="100%" alt="Newborn">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Newborns are best photographed during the first 10 days of life
                    to capture the sleepy stage. Be sure to schedule these sessions
                    well in advance as I only book a limited amount of newborns per
                    week to allow for flexibility with your due date. You'll have full
                    access to an array of beautiful props, blankets and accessories to
                    customize your newborns session. Upon booking, we'll pencil in your
                    newborns due date and schedule your session upon their arrival.</p>
                <p>
                    Newborn Sessions take place either at <a
                        href='/portrait/galleries.php?w=14'>your home</a> or <a
                        href='/portrait/galleries.php?w=15'>my studio</a> in Fairfax, VA.
                    They are best done within 14 days after birth. To ensure your spot,
                    please be sure to contact me before your third trimester. More
                    information about newborn shoots can be found <a
                        href='newbornfaq.php'>here</a>.
                </p>
                <ul>
                    <li>Portraits of you and your newborn</li>
                    <li>2-3 hour session</li>
                    <li>50+ fully edited images for viewing at your review session</li>
                </ul>
                <p>
                    <a href='galleries.php?w=13'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Sitting up - 6 Months</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/6-month-1.jpg<?php echo $rand; ?>" width="100%" alt="6 Month">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/6-month-2.jpg<?php echo $rand; ?>" width="100%" alt="6 Month">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>These sessions take place at 6-7 months, when your baby is able
                    to sit up unassisted. At this stage your baby will be smiling,
                    grabbing their toes, and showing off their sweet personality.</p>

                <p>Your baby can be photographed on location or at my home studio.
                    Both options are great and depend on the season/weather but also
                    what look you're going for. Outdoor shoots tend to be more candid
                    and varied, while indoor shoots tend to be more formal/planned with
                    backdrops and props.</p>
                <ul>
                    <li>Studio or Outdoors (outdoors April through October only)</li>
                    <li>45-60 minute session (2-3 setups/outfits)</li>
                    <li>50+ fully edited images for viewing at your ordering
                        appointment</li>
                    <li>The focus of this session is on the one child, but you may
                        include 2-3 sibling/family portraits</li>
                </ul>
                <p>
                    <a href='galleries.php?w=4'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Cake Smash - 1 Year</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/1-year-1.jpg<?php echo $rand; ?>" width="100%" alt="1 Year">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/1-year-2.jpg<?php echo $rand; ?>" width="100%" alt="1 Year">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>So much fun! What better way to celebrate your little one’s first
                    birthday than letting them go to town on a sugary surprise? Your
                    baby will likely be crawling if not walking by this stage! Cake
                    included.</p>
                <ul>
                    <li>Studio or Outdoors (outdoors April through October only)</li>
                    <li>45-60 minute session (2-3 setups/outfits)</li>
                    <li>50+ fully edited images for viewing at your ordering
                        appointment</li>
                    <li>The focus of this session is on the one child, but you may
                        include 2-3 sibling/family portraits</li>
                </ul>
                <p>
                    <a href='galleries.php?w=5'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">Family and Kids</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/family-1.jpg<?php echo $rand; ?>" width="100%" alt="Family">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/family-2.jpg<?php echo $rand; ?>" width="100%" alt="Family">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>When's the last time you updated your family portrait? Often the
                    answer I get goes something like this... 'My kids hate dressing up
                    and never smile for me so I just don't bother'. This makes me so
                    sad! Don't miss out on documenting your little ones because you
                    view the process as stressful, it doesn't have to be! Kids love to
                    laugh. and PLAY and that's what we do at your portrait session.
                    Every child's personality is different and I can cater to them as
                    needed. In my experience, I've found that you can't just TELL
                    someone to smile. It needs to come naturally and you do that by
                    having a little fun ;) Do your kids love dinosaurs? Let's rawr! Do
                    they love to run? Let's race!</p>
                <ul>
                    <li>Immediate families, up to 5 people</li>
                    <li>Family sessions are only available on location/outdoors (not in
                        studio)</li>
                    <li>1 hour session</li>
                    <li>50+ fully edited images for viewing at your image review
                        session</li>
                    <li>May include any combination of portraits you would like: whole
                        group, individuals, parents, kids, etc</li>
                    <li>I'll help you brainstorm a location that works for you and fits
                        your families style</li>
                </ul>
                <p>
                    <a href='galleries.php?w=6'>See More</a>
                </p>
            </div>

            <div class="col-lg-12">
                <h2 class="page-header">High School Senior</h2>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/senior-2.jpg<?php echo $rand; ?>" width="100%" alt="Senior">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/senior-1.jpg<?php echo $rand; ?>" width="100%" alt="Senior">
                </div>
            </div>
            <div class="col-xs-12" style="padding-top: 20px;">
                <p>Congratulations on this new chapter in your life! Break out of
                    the typical senior portrait box and create a custom session that
                    fits you and your personality.</p>
                <ul>
                    <li>1-2 hour session (multiple outfit changes allowed)</li>
                    <li>1-2 locations within time frame</li>
                    <li>100+ fully edited images for viewing at your ordering
                        appointment</li>
                    <li>I'll help you brainstorm a location that works for you and fits
                        your personal style</li>
                </ul>
                <p>
                    <a href='galleries.php?w=7'>See More</a>
                </p>
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