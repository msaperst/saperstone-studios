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

    <?php $nav = "wedding"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Engagement Session</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Engagements</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img width="100%"
                        src="img/engagement-main-1.jpg<?php echo $rand; ?>"
                        alt="Engagements" />
                    <div class="overlay"></div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img width="100%"
                        src="img/engagement-main-2.jpg<?php echo $rand; ?>"
                        alt="Engagements" />
                    <div class="overlay"></div>
                </div>
            </div>
        </div>

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <h2>Congrats on your engagement!</h2>
                <p>
                    I love, love, love engagement sessions and getting to know my
                    clients without the rush and hurry on your big day. That's why I
                    offer a complimentary <a href='galleries.php?w=20'>engagement
                        session</a> in all of my wedding packages. It offers the
                    opportunity for you to be comfortable in front of the lens and to
                    see how I work. I get the chance to see you two as a couple and
                    what makes you smile, laugh and get some wonderful natural
                    reactions that are candid and you.
                </p>
                <p>
                    Interested in just the engagement session without the <a
                        href='galleries.php?w=24'>wedding photography</a>? That's alright
                    too! <a href='/contact.php'>Contact me</a> today for more
                    information.
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