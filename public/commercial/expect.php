<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User($sql);
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
    $nav = "commercial";
    require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php";
    ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">What to Expect</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">What to Expect</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/expect-1.jpg<?php echo $rand; ?>" width="100%"
                        alt="What to Expect">
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img src="img/expect-2.jpg<?php echo $rand; ?>" width="100%"
                        alt="What to Expect">
                </div>
            </div>
            <div class="col-lg-12" style="padding-top: 20px;">
                <p>I would love the opportunity to work with you! I pride myself on
                        the customer service I provide to every single client. From
                        your first inquiry to product delivery, I will walk with you
                        through the entire process, to ensure your custom photography
                        experience is a great one.</p>
            </div>
            <div class="col-md-12">
                <h2>So how does the whole process work?</h2>
                <ol>
                    <li><strong>Contact Me!</strong> The first step is to reach out
                            via my <a href='/contact.php'>contact page</a>. Fill out the form
                            and I will get back to you as soon as possible (almost always
                            within 24 hours). I'll answer any questions you may have and
                            we'll cultivate the perfect session to fit your needs.</li>
                    <li><strong>Consultation.</strong> I would love to chat about your
                            needs – what type of session interests you, what images do
                            you love most. I truly enjoy hearing my clients’ ideas and
                            working together to create a session you will love. Your
                            consultation can happen over the phone or through email.</li>
                    <li><strong>Review/Sign Contract.</strong> I'll send you over a
                            digital contract for you to review. Once digitally signed
                            and submitted you'll receive a copy via email. Upon the signed
                            contract being received we'll book a date on my calendar!</li>
                    <li><strong>Schedule and Attend your Session.</strong> Once you arrive
                            at your session, just leave it to me! We’re going to have fun!</li>
                    <li><strong>Images Delivered.</strong> Only the best images are selected
                            and color corrected for your web gallery. Expect an email with
                            your web gallery login info within 24 hours of your individual
                            headshot session. For large companies, turn around time is 72
                            hours but can be rushed if needed. Selects made will be given
                            final retouch and delivered within 72 hours of being submitted.</li>
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