<?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php"; ?>

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

    <?php require_once dirname( $_SERVER['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Services Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header text-center">Photography Services</h2>
            </div>
            <div class="col-md-4 col-xs-12">
                <div section='Portraits'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Portraits</span> <img
                        class="img-responsive"
                        src="/img/main/portraits.jpg<?php echo $rand; ?>" width="100%"
                        alt="Portraits">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="/portrait/index.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12">
                <div section='Weddings'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Weddings</span> <img
                        class="img-responsive" src="/img/main/weddings.jpg<?php echo $rand; ?>"
                        width="100%" alt="Weddings">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="/wedding/index.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xs-12">
                <div section='Commercial'
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Commercial</span> <img
                        class="img-responsive" src="/img/main/commercial.jpg<?php echo $rand; ?>"
                        width="100%" alt="Weddings">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="/commercial/index.php">See
                            More</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Welcome to Saperstone Studios</h2>
            </div>
            <div class="col-md-12">
                <img style='max-width: 100%;' alt="Where Saperstone Studios Works"
                    src='img/locationbox.png' align='right' />
                <p>
                    Allow me to introduce myself! I've worked in the photography
                    industry for over 10 years, coordinating photo shoots and
                    performing high level color management and retouch for program
                    books and billboards for Ringling Bros. Barnum & Bailey Circus <em>[[So
                        many elephants!]]</em> as well as a local DC fashion company
                    managing the retouch workflow and consistent quality control.
                </p>
                <p class="lead">I've started Saperstone Studios with the hope of
                    providing not just photography, but also an experience.</p>
                <p>I serve the DC Metro area photographing countless moments for
                    everything from weddings and engagements to family and events. Each
                    time I pull out my camera I strive to provide a unique, fun
                    photography experience where you can be, well, you! Many clients
                    have let me know they didn't know what to expect but were
                    pleasantly surprised how at ease they felt in front of the camera.
                    We'll have fun capturing natural, fun moments that reflect who you
                    are as a couple or family. My photography style is vibrant and
                    colorful to reflect how you love life.</p>
                <p>I truly love flipping through my grandparents wedding album
                    whenever I visit them, it brings me back to a time I obviously
                    wasn't around to know them but the stories that get told are
                    priceless and give insight to the people they were and the amazing
                    people they've become. Photography isn't just for you. It's for
                    your children and your children's children, and your children's
                    children's child....well, you get the point :) Slow down and take a
                    moment to capture life as it is now with a photography session with
                    Saperstone Studios.</p>
                <p>I also provide retouching services that include restoring old
                    photographs, enhancing poor wedding day photography, removing
                    people/distractions, opening eyes etc. The possibilities are
                    endless!</p>
                <p>
                    Have a photography or retouch assignment for Saperstone Studios? <a
                        href="/contact.php">Let us tell your story</a>!
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