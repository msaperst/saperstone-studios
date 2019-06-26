<?php require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php"; ?>

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

    <?php $nav = "commercial"; require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Headshots</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Commercial</a></li>
                    <li><a href="details.php">Details</a></li>
                    <li class="active">Headshots</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img width="100%"
                        src="img/headshots-main-1.jpg<?php echo $rand; ?>"
                        alt="Professional Headshots" />
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="galleries.php?w=53">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <img width="100%"
                        src="img/headshots-main-2.jpg<?php echo $rand; ?>"
                        alt="Company Headshots" />
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="galleries.php?w=54">See More</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <h2>Professional Headshots</h2>
                <p>
                    Stand out from the crowd with an updated headshot. Perfect for LinkedIn, Business cards or your
                    website. 'Say cheese!' so sooo outdated and will never be uttered at Saperstone Studios. We
                    have a relaxed session and strive to achieve natural smiles -- you really can tell the difference!
                </p>
                <h2>Company Headshots</h2>
                <p>
                    Have a business of 3 or 3000? No problem. Get a consistent look throughout all your employees
                    images - even the make up shots. We can bring the studio to you and can handle nearly any amount
                    of headshots in any time period.
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