<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$user = User::fromSystem();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/header.php"; ?>
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

<?php $nav = "b'nai mitzvah";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

<!-- Page Content -->
<div class="page-content container">

    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header text-center">B'nai Mitzvah Details</h1>
            <ol class="breadcrumb">
                <li><a href="/">Home</a></li>
                <li><a href="index.php">B'nai Mitzvahs</a></li>
                <li class="active">Details</li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <div class="row" style='padding-top: 30px'>
        <div class="col-lg-12">
            <p>Such a milestone! Take the opportunity to capture your mitzvah and unique family as you are now. Often
                when clients are in front of the lens they need a bit of direction on what to do. No fear, that's why
                you've hired me. It's my job to pose everyone in a way that's natural as well as flattering. To ensure
                that your posed moments are still ones that are candid, fun and personable, I'll have you interact with
                each other or myself...simple, right? But it's just oh so effective in making the moment your own. :) My
                photography style is vibrant and colorful to reflect how you love life.</p>
            <p>Curious why you should choose Saperstone Studios over everyone else out there? Check out more about <a
                        href="/about.php">how we differentiate ourselves</a></p>
        </div>
    </div>

    <div class="row" style='padding-top: 30px'>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div section="The Mitzvah Experience"
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>The Mitzvah Experience</span> <img
                        class="img-responsive" src="img/mitzvah.jpg<?php echo $rand; ?>"
                        alt="The Mitzvah Experience">
                <div class="overlay">
                    <br /> <br /> <br /> <a class="info" href="experience.php">See
                        More</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div section="The Process"
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>The Process</span> <img
                        class="img-responsive" src="img/process.jpg<?php echo $rand; ?>"
                        alt="The Process">
                <div class="overlay">
                    <br /> <br /> <br /> <a class="info" href="process.php">See More</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div section="Products and Investment"
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>Products and Investment</span> <img
                        class="img-responsive" src="img/products.jpg<?php echo $rand; ?>"
                        alt="Products and Investment">
                <div class="overlay">
                    <br /> <br /> <br /> <a class="info" href="products.php">See More</a>
                </div>
            </div>
        </div>
        <div class="col-md-offset-4 col-md-4 col-sm-6 col-xs-12">
            <div section="Photobooth"
                 class="hovereffect img-portfolio<?php if ($user->isAdmin()) {
                     echo " editable horizontal";
                 } ?>">
                <span class='preview-title'>Photobooth</span> <img
                        class="img-responsive"
                        src="img/photobooth.jpg<?php echo $rand; ?>" alt="Photobooth">
                <div class="overlay">
                    <br /> <br /> <br /> <a class="info" href="photobooth.php">See
                        More</a>
                </div>
            </div>
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