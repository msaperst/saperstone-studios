<!DOCTYPE html>
<html lang="en">

<head>

    <?php require_once "../header.php"; ?>
    <link href="/css/hover-effect.css" rel="stylesheet">

    <?php
    $rand = "";
    if ($user->isAdmin ()) {
        require_once '../php/strings.php';
        $string = new Strings ();
        $rand = "?" . $string->randomString ();
        ?>
    <link
    href="http://hayageek.github.io/jQuery-Upload-File/4.0.10/uploadfile.css"
    rel="stylesheet">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css"
    rel="stylesheet">
    <?php
    }
    ?>
    
</head>

<body>

    <?php $nav = "wedding"; require_once "../nav.php"; ?>

    <!-- Page Content -->
    <div class="page-content container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header text-center">Wedding Session Details</h1>
                <ol class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li><a href="index.php">Weddings</a></li>
                    <li class="active">Details</li>
                </ol>
            </div>
        </div>
        <!-- /.row -->

        <!-- Features Section -->
        <!--         <div class="row"> -->
        <!--             <div class="col-xs-4"> -->
        <!--                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>"> -->
        <!--                    <img width="100%" src="img/wedding-main-1.jpg<?php echo $rand; ?>" -->
        <!--                         alt="Weddings"> -->
        <!--                 </div> -->
        <!--             </div> -->
        <!--             <div class="col-xs-8"> -->
        <!--                <div class="<?php if ($user->isAdmin ()) { echo " editable"; } ?>"> -->
        <!--                    <img width="100%" src="img/wedding-main-2.jpg<?php echo $rand; ?>" -->
        <!--                         alt="Weddings"> -->
        <!--                 </div> -->
        <!--             </div> -->
        <!--         </div> -->

        <div class="row" style='padding-top: 30px'>
            <div class="col-lg-12">
                <p>You said Yes! Congrats on stepping into this next chapter of your
                    lives as a force to be reckoned with. Nothing can take you guys
                    down, including the wedding planning process!</p>

                <p>
                    2017 wedding packages start at $2,700 but are customizable to fit
                    your needs for your day. All of my packages come with photography
                    time on your wedding day, digital files on a USB with personal
                    print release as well as an <a href='engagement.php'>engagement
                        session</a>. I love being able to spend time with my couples at an
                    engagement shoot before the rush of the big day. Who do you spend
                    the most time with on your wedding day? Most likely it's your
                    photographer so spending some time together to see how I work and
                    become comfortable in front of the lens can make a world of
                    difference :)
                </p>

                <p>
                    Want to know more about what it's like to work with Saperstone
                    Studios? Check out more about <a href='experience.php'>The Wedding
                        Experience</a>
                </p>

                <p>
                    Curious why you should choose Saperstone Studios over everyone else
                    out there? Check out more about <a href='/about.php'>how we
                        differentiate ourselves</a>
                </p>
            </div>
        </div>

        <div class="row" style='padding-top: 30px'>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="The Wedding Experience"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>The Wedding Experience</span> <img
                        class="img-responsive" src="img/wedding.jpg<?php echo $rand; ?>"
                        alt="The Wedding Experience">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="experience.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Engagements"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Engagements</span> <img
                        class="img-responsive"
                        src="img/engagement.jpg<?php echo $rand; ?>" alt="Engagements">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="engagement.php">See
                            More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="The Process"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
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
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Products and Investment</span> <img
                        class="img-responsive" src="img/products.jpg<?php echo $rand; ?>"
                        alt="Products and Investment">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="products.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Night Photography"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
                    <span class='preview-title'>Night Photography</span> <img
                        class="img-responsive" src="img/night.jpg<?php echo $rand; ?>"
                        alt="Night Photography">
                    <div class="overlay">
                        <br /> <br /> <br /> <a class="info" href="night.php">See More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div section="Photobooth"
                    class="hovereffect img-portfolio<?php if ($user->isAdmin ()) { echo " editable"; } ?>">
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

        <?php require_once "../footer.php"; ?>

    </div>
    <!-- /.container -->

    <?php
    if ($user->isAdmin ()) {
        ?>
    <script src="/js/edit-image.js"></script>
    <script src="/js/jquery.uploadfile.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <?php
    }
    ?>

</body>

</html>