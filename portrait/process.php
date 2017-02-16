<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once "../header.php";
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link href="/css/uploadfile.css" rel="stylesheet">
    <?php
    }
    ?>
    
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
                <h1 class="page-header text-center">The Process</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Process</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/process-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Portrait Process">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img src="img/process-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Portrait Process">
                </div>
            </div>
            <div class="col-lg-12" style="padding-top: 20px;">
                <p>I would love the opportunity to work with you! I pride myself on
                    the customer service I provide to every single client. From your
                    first inquiry to product delivery, I will walk with you through the
                    entire process, to ensure your custom photography experience is a
                    great one.</p>
            </div>
            <div class="col-md-12">
                <h2>So how does the whole process work?</h2>
                <ol>
                    <li><strong>Contact Me!</strong> The first step is to reach out via
                        my <a href='/contact.php'>contact page</a>. Fill out the form and
                        I will get back to you as soon as possible (almost always within
                        24 hours).</li>
                    <li><strong>Consultation.</strong> I would love to chat about your
                        needs – what type of session interests you, what images do you
                        love most, what products are you hoping to purchase, etc. I truly
                        enjoy hearing my clients’ ideas and working together to create a
                        session you will love. Your consultation can happen over the
                        phone, through email, or you are more than welcome to meet me in
                        the <a href='studio.php'>studio</a>.</li>
                    <li><strong>Review/Sign Contract and Session Payment.</strong> I'll
                        send you over a contract for you to review. Upon the signed
                        contract along with the $200 session fee being received we'll book
                        a date on my calendar!</li>
                    <li><strong>Schedule and Attend your Session.</strong> If you are
                        booking a newborn session, I will add a tentative date to my
                        calendar. When baby arrives, let me know and we can adjust the
                        date if needed. If you are booking a portrait session for your
                        child or family, your session will be officially scheduled once
                        your session fee is paid. We'll brainstorm a location that fits
                        you and your families personality and answer any Q's you may have.
                        Once you arrive at your session, just leave it to me! We’re going
                        to have fun!</li>
                    <li><strong>Your Image Review Session.</strong> Only the best
                        images are edited to present to you on the day of your reveal. The
                        appointment takes place at my studio where you will see all your
                        proofs and choose the products that best fit your family and
                        lifestyle. All of my available products will be in the studio so
                        you can see and touch them. It is here that you will finalize your
                        order and submit payment. Most families plan to invest $800-$1,200
                        on their portrait order. Remember that $200 session fee you paid?
                        That becomes a print credit for you to put towards your order of
                        prints and/or digital files!</li>
                    <li><strong>Enjoy your products for a lifetime.</strong> Your art
                        products are lovingly designed, printed and packaged for pickup or
                        shipping. About 2-4 weeks after your ordering appointment, I will
                        let you know that your order has arrived and you can arrange a
                        time to pick up or that everything is in the mail. This is the
                        best part! Enjoy your memories with friends and loved ones :)</li>
                </ol>
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
    <?php
    }
    ?>
    
</body>

</html>