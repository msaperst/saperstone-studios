<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/strings.php";
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
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
                <h1 class="page-header text-center">The Wedding Experience</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Experience</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img width="100%" src="img/experience.jpg<?php echo $rand; ?>"
                        alt="Experience" />
                    <div class="overlay"></div>
                </div>
            </div>
        </div>

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <p>I'm a self proclaimed photo ninja. And when it comes down to it,
                    haven't you always wanted to have a ninja at your wedding? ;) I
                    have a photo journalistic style which means for the most part I
                    stay out of the way and capture pure emotion as it happens. That
                    being said, there are instances when I do step up and give
                    direction to make sure your photos tell a story in the best way
                    possible.</p>
                <p>Often when a couple is in front of the lens having their
                    portraits taken they need a bit of direction on what to do. No
                    fear, that's why you've hired me. It's my job to pose you both in a
                    way that's natural as well as flattering. To ensure that your posed
                    moments are still ones that are candid, fun and personable, I'll
                    have you interact with each other...simple, right? But it's just oh
                    so effective in making you both smile. :)</p>
                <p>There are other times when the light and/or composition is better
                    in a certain room, I'll be on the look out for harsh shadows and
                    and cluttered backgrounds and be sure to direct you when needed.</p>
                <p>
                    Be sure to also check out the <a href='night.php'>night photography</a>
                    I love to shoot on wedding days!
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