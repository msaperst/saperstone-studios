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

    <?php $nav = "b'nai mitzvah"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">The B'nai Mitzvah Experience</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">B'nai Mitzvahs</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Experience</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="<?php if ($user->isAdmin ()) { echo " editable horizontal"; } ?>">
                    <img width="100%" src="img/experience.jpg<?php echo $rand; ?>"
                        alt="Experience" />
                    <div class="overlay"></div>
                </div>
            </div>
        </div>

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <p>I'm often asked - For the love of ducks, how do you get a teeneger
                    to smile?! We. Have. Fun. I take some time to get to know your
                    mitzvah and what they enjoy. From there, I'll arrange everyone so
                    they look good and throw out fun prompts and terrible dad jokes
                    to get natural smiles.</p>
                <p>I'm a self proclaimed photo ninja. And when it comes down to it,
                    haven't you always wanted to have a ninja at your mitzvah? ;) I
                    have a photo journalistic style which means for the most part I
                    stay out of the way and capture pure emotion as it happens. That
                    being said, there are instances when I do step up and give
                    direction to make sure your photos tell a story in the best way
                    possible.</p>
                <p>Want to know more about the lady behind the camera? <a href="/leighAnn.php">
                        Here's what I'm all about.</a></p>
                <p>I'd love to chat through your day with you! <a href="/contact.php">Contact me
                        today</a> so we can customize photography that fits your family :)</p>
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