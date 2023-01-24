<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php
    require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php";
    ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin()) {
        $rand = "?" . Strings::randomString();
        ?>
        <link href="/css/uploadfile.css" rel="stylesheet">
        <?php
    }
    ?>

</head>

<body>

<?php
$nav = "b'nai mitzvah";
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php";
?>

<!-- Page Content -->
<div class="page-content container">

    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">The Process</h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <li><a href="index.php">B'nai Mitzvahs</a></li>
                <li><a href="details.php">Details</a></li>
                <li class="active">Process</li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <!-- Features Section -->
    <div class="row">
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/process-1.jpg<?php echo $rand; ?>" width="100%"
                     alt="Mitzvah Process">
            </div>
        </div>
        <div class="col-xs-6">
            <div class="<?php if ($user->isAdmin()) {
                echo " editable horizontal";
            } ?>">
                <img src="img/process-2.jpg<?php echo $rand; ?>" width="100%"
                     alt="Mitzvah Process">
            </div>
        </div>
        <div class="col-lg-12" style="padding-top: 20px;">
            <p>I would love the opportunity to work with you! I pride myself on
                the customer service I provide to every single client. From your
                first inquiry to product delivery, I will walk with you through
                the entire process, to ensure your custom photography experience
                is a great one.</p>
        </div>
        <div class="col-md-12">
            <h2>So how does the whole process work?</h2>
            <ol>
                <li><strong>Contact Me!</strong> The first step is to reach out via
                    my <a href='/contact.php'>contact page</a>. Fill out the form and
                    I will get back to you as soon as possible (almost always within
                    24 hours).
                </li>
                <li><strong>Consultation.</strong> I would love to find out more about
                    your mitzvah and answer any questions you may have – I've found the
                    best way to do this is via virtual meeting or meeting up at
                    <a href='studio.php'>my studio</a>! I'll walk you through how I approach
                    photographing your mitzvah, find out how we can make it a custom
                    experience for your family and review pricing options.
                </li>
                <li><strong>Review/Sign Contract and Session Payment.</strong> Want
                    to book your mitzvah date with Saperstone Studios? Great! A signed
                    contract along with a 50% deposit will lock in your mitzvah date.
                    The remaining balance is due 3 weeks prior to your mitzvah day.
                </li>
                <li><strong>Timeline Review!</strong> Approx 3 weeks out from your event
                    I'll reach out to schedule a phone chat to review everything photography
                    for your mitzvah. During this 15-minute call, we'll go over a Google Doc
                    I'll have sent you to be the outline for the day. We'll customize all
                    the details and make sure we're on the same page for all your photo needs.
                </li>
                <li><strong>It's their Day!</strong> Finally, the day(s) have arrived! We'll
                    photograph your Bimah Session and/or Reception in style. Also be sure to
                    check out the <a href="experience.php">mitzvah experience</a> for more
                    info on my photography style!
                </li>
                <li><strong>Your Image Review Session.</strong> Your appointment takes place
                    at my studio where you receive your USB of mitzvah images. We'll go
                    through your images on a big screen TV and easily flag your favorites -
                    SO much easier than sorting through them in your web gallery. You'll be
                    able to view available <a href="products.php">wall art and heirloom albums</a>
                    and while no additional purchases are required, I do include a print credit
                    in every mitzvah package to encourage you to pick out something you will enjoy
                    daily instead of posting a few images online and throwing your USB into
                    a junk drawer.
                </li>
                <li><strong>Enjoy your products for a lifetime.</strong> Your art
                    products are lovingly designed, printed and packaged for pickup or
                    shipping. About 2-4 weeks after your ordering appointment, I will
                    let you know that your order has arrived and you can arrange a
                    time to pick up or that everything is in the mail. This is the
                    best part! Enjoy your memories with friends and loved ones :)
                </li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/footer.php"; ?>

</div>
<!-- /.container -->

<?php
if ($user->isAdmin()) {
    ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <?php
}
?>

</body>

</html>
