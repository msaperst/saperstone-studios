<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql ();
$user = new User ($sql);
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

    <?php $nav = "portrait"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Portrait Session Details</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Portraits</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/details-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="Details">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/details-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="Details">
                </div>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">What to Wear?</h2>
            </div>
            <div class="col-md-12">
                <p>
                    Need a little help coordinating a winning outfit combo for the
                    group? Be sure to check out <a href="what-to-wear.php">what to wear</a>
                    for your photography session.
                </p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">Turnaround time</h2>
            </div>
            <div class="col-md-12">
                <p>
                    Please allow 2-3 weeks after your portrait session for images to be
                    completed. During this time I carefully <a href='retouch.php'>make
                        selects, color correct and retouch</a> your images to be clean,
                    consistent and vibrant.
                </p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">What's Next?</h2>
            </div>
            <div class="col-md-12">
                <p>
                    When I'm close to completing your images I will reach out and
                    schedule a time for you to visit my <a href="studio.php">home
                        studio</a> in Fairfax, VA (within 2-3 weeks of your session date).
                </p>
                <p>A typical session will have approx. 50-100 images of you and your
                    loved ones to go through. I've found through the years that my
                    clients were overwhelmed with just being sent a web gallery link
                    and would delay making any decisions, therefore not enjoying their
                    images to their full extent. I remove that stress factor by giving
                    you personalized service at your image review where I help you flag
                    your favorite images in person. We're able to pull up multiple
                    similar poses side by side and zoom in on images to make your
                    selection process as smooth as possible.</p>
                <p>
                    We'll review all the art <a href='products.php'>product</a> options
                    to find what makes the most sense for your style and home. I'll
                    answer any questions you may have and make sure that when you
                    leave, you feel completely confident in your order. No minimum
                    purchases are required but on average, my clients invest
                    $800-$1,200 on their custom portraits.
                </p>
            </div>
            <div class="col-lg-12">
                <h2 class="page-header">Come Prepared!</h2>
            </div>
            <div class="col-md-12">
                <p>As a visual person myself, I wouldn't be able to make decisions
                    in regard to prints on my walls without seeing it myself first. If
                    you have a particular wall you want to hang photos on in your home,
                    send me a snapshot (from your phone is fine) of the wall with a
                    standard piece of office paper taped to it. I have an amazing ipad
                    application that allows me to show you how large/small images will
                    look on your actual wall to scale. The piece of paper taped to the
                    wall helps the application resize the image accordingly. It makes
                    customizing a cluster of images a breeze and it's fun to visualize
                    exactly what your wall will look like.</p>
                <p>
                    Have questions about newborn sessions in particular? Check out that
                    FAQ <a href='newborn-faq.php'>here</a>.
                </p>
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