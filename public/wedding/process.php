<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php";
    ?>
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

    <?php
    $nav = "wedding";
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php";
    ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">The Process</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Process</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/process-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Wedding Process">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/process-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Wedding Process">
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
                    <li><strong>Consultation.</strong> I would love to find out more
                        about the two of you as a couple – Let's meet up at your favorite
                        hot beverage establishment or come on over to <a href='studio.php'>my
                            studio</a>! I'll walk you through how I approach your engagement
                        and wedding day and answer any questions you may have.</li>
                    <li><strong>Review/Sign Contract and Session Payment.</strong> Want
                        to book your wedding date with Saperstone Studios? Great! A signed
                        contract along with a 50% deposit will lock in your wedding date.
                        The remaining balance is due 3 weeks prior to your wedding day.</li>
                    <li><strong>Engagement Session!</strong> I include an engagement
                        session in every wedding package. This allows me to get to know
                        you as a couple and for you to see how I work and get comfortable
                        in front of the lens :) We'll brainstorm the perfect spot for your
                        session, I have tons of places I love to photograph and we'll pick
                        something that fits your style. Once you arrive at your session,
                        just leave it to me! We’re going to have fun!</li>
                    <li><strong>Your Image Review Session.</strong> Your appointment
                        takes place at my studio where you receive your USB of engagement
                        or wedding images. We'll go through your images on a big screen TV
                        and easily flag your favorites for later use. If you have the
                        ability to play a slideshow at your wedding reception, this is the
                        perfect way to pull aside your favorite engagement shots! Images
                        are easily pulled up side by side for comparison - SO much easier
                        than sorting through them in your web gallery. You'll be able to
                        view available wall art and heirloom albums and while no
                        additional purchases are required, I do include a print credit in
                        every wedding package to encourage you to pick out something you
                        will enjoy instead of posting a few images online and throwing
                        your USB into a junk drawer.</li>
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